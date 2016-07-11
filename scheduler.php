<?php
// The Kabal Invasion - A web-based 4X space game
// Copyright © 2014 The Kabal Invasion development team, Ron Harwood, and the BNT development team
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero General Public License as
//  published by the Free Software Foundation, either version 3 of the
//  License, or (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero General Public License for more details.
//
//  You should have received a copy of the GNU Affero General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// File: scheduler.php

// Explanation of the scheduler
//
// Here are the scheduler DB fields, and what they are used for :
// - sched_id : Unique ID. Before calling the file responsible
//   for the event, the variable $sched_var_id will be set to
//   this value, so the called file can modify the triggering
//   scheduler entry if it needs to.
//
// - run_once : Set this to 'N' if you want the event to be
//   repeated endlessly. If this value is set to 'N', the 'spawn'
//   field is not used.
//
// - ticks_left : Used internally by the scheduler. It represents
//   the number of mins elapsed since the last call. ALWAYS set
//   this to 0 when scheduling a new event.
//
// - ticks_full : This is the interval in minutes between
//   different runs of your event. Set this to the frenquency
//   you wish the event to happen. For example, if you want your
//   event to be run every three minutes, set this to 3.
//
// - spawn : If you want your event to be run a certain number of
//   times only, set this to the number of times. For this to
//   work, loop must be set to 'N'. When the event has been run
//   spawn number of times, it is deleted from the scheduler.
//
// - sched_file : This is the file that will be called when an
//   event has been trigerred.
//
// - extra_info : This is a text variable that can be used to
//   store any extra information concerning the event triggered.
//   It will be made available to the called file through the
//   variable $sched_var_extrainfo.
//
//  If you are including files in your trigger file, it is important
//  to use include_once instead of include, as your file might
//  be called multiple times in a single execution. If you need to
//  define functions, you can put them in your own
//  include file, with an include statement. THEY CANNOT BE
//  DEFINED IN YOUR MAIN FILE BODY. This would cause PHP to issue a
//  multiple function declaration error.
//
//  End of scheduler explanation

$index_page = true; // Ensure that we do not set sessions
require_once './common.php';

$title = $langvars['l_sys_update'];
Tki\Header::display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('admin', 'common', 'global_includes', 'global_funcs', 'footer', 'news', 'scheduler'));
echo "<h1>" . $title . "</h1>\n";

// FUTURE: Add filtering to swordfish
if (array_key_exists('swordfish', $_GET))
{
    $swordfish = $_GET['swordfish'];
}
else
{
    if (array_key_exists('swordfish', $_POST))
    {
        $swordfish = $_POST['swordfish'];
    }
    else
    {
        $swordfish = null;
    }
}

if ($swordfish != \Tki\SecureConfig::ADMIN_PASS)
{
    echo "<form accept-charset='utf-8' action='scheduler.php' method='post'>";
    echo "Password: <input type='password' name='swordfish' size='20' maxlength='20'><br><br>";
    echo "<input type='submit' value='Submit'><input type='reset' value='Reset'>";
    echo "</form>";
}
else
{
    $starttime = time();
    $lastRun = 0;
    $schedCount = 0;
    $lastrunList = null;
    $sched_res = $db->Execute("SELECT * FROM {$db->prefix}scheduler");
    Tki\Db::LogDbErrors($pdo_db, $sched_res, __LINE__, __FILE__);
    if ($sched_res)
    {
        while (!$sched_res->EOF)
        {
            $event = $sched_res->fields;
            $multiplier = ($tkireg->sched_ticks / $event['ticks_full']) + ($event['ticks_left'] / $event['ticks_full']);
            $multiplier = (int) $multiplier;
            $ticks_left = ($tkireg->sched_ticks + $event['ticks_left']) % $event['ticks_full'];
            $lastRun += $event['last_run'];
            $schedCount++;

            // Store the last time the individual schedule was last run.
            $lastrunList[$event['sched_file']] = $event['last_run'];

            if ($event['run_once'] == 'Y')
            {
                if ($multiplier > $event['spawn'])
                {
                    $multiplier = $event['spawn'];
                }

                if ($event['spawn'] - $multiplier == 0)
                {
                    $resx = $db->Execute("DELETE FROM {$db->prefix}scheduler WHERE sched_id = ?", array($event['sched_id']));
                    Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                }
                else
                {
                    $resy = $db->Execute("UPDATE {$db->prefix}scheduler SET ticks_left = ?, spawn = spawn - ? WHERE sched_id = ?", array($ticks_left, $multiplier, $event['sched_id']));
                    Tki\Db::LogDbErrors($pdo_db, $resy, __LINE__, __FILE__);
                }
            }
            else
            {
                $resz = $db->Execute("UPDATE {$db->prefix}scheduler SET ticks_left = ? WHERE sched_id = ?", array($ticks_left, $event['sched_id']));
                Tki\Db::LogDbErrors($pdo_db, $resz, __LINE__, __FILE__);
            }

            $sched_var_id = $event['sched_id'];
            $sched_var_extrainfo = $event['extra_info'];

            $sched_i = 0;
            while ($sched_i < $multiplier)
            {
                include_once './scheduler/'. $event['sched_file'];
                $sched_i++;
            }
            $sched_res->MoveNext();
        }
        $lastRun /= $schedCount;
    }

    // Calculate the difference in time when the last good update happened.
    $schedDiff = ($lastRun - (time() - ($tkireg->sched_ticks * 60)));
    if (abs($schedDiff) > ($tkireg->sched_ticks * 60))
    {
        // Hmmm, seems that we have missed at least 1 update, so log it to the admin.
        Tki\AdminLog::writeLog($pdo_db, 2468, "Detected Scheduler Issue|{$lastRun}|". time() . "|" . (time() - ($tkireg->sched_ticks * 60)) . "|{$schedDiff}|" . serialize($lastrunList));
    }

    $runtime = time() - $starttime;
    echo "<p>The scheduler took $runtime seconds to execute.<p>";

    $res = $db->Execute("UPDATE {$db->prefix}scheduler SET last_run = ". time());
    Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
}

echo "<br>";
Tki\Text::gotomain($pdo_db, $lang);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
