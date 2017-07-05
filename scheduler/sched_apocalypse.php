<?php declare(strict_types = 1);
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
// File: sched_apocalypse.php

// FUTURE: Rewrite to use PDO, better handling ("break?!") of logic, substantial output management
$langvars = Tki\Translate::load($pdo_db, $lang, array('scheduler'));

echo "<strong>" . $langvars['l_apoc_title'] . "</strong><br><br>";
echo $langvars['l_apoc_begins'] . "..<br>";

$doomsday = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE colonists > ?;", array($tkireg->doomsday_value));
Tki\Db::LogDbErrors($pdo_db, $doomsday, __LINE__, __FILE__);
$chance = 9;
$reccount = $doomsday->RecordCount();
if ($reccount > 200)
{
    $chance = 7; // Increase the chance it will happen if we have lots of planets meeting the criteria
}

$affliction = random_int(1, $chance); // The chance something bad will happen
if ($doomsday && $affliction < 3 && $reccount > 0)
{
    $i = 1;
    $targetnum = random_int(1, (int) $reccount);
    while (!$doomsday->EOF)
    {
        if ($i == $targetnum)
        {
            $targetinfo = $doomsday->fields;
            break;
        }

        $i++;
        $doomsday->MoveNext();
    }

    if ($affliction == 1) // Space Plague
    {
        echo $langvars['l_apoc_plague'] . "<br>.";
        $resx = $db->Execute("UPDATE {$db->prefix}planets SET colonists = ROUND (colonists - colonists * ?) WHERE planet_id = ?;", array($space_plague_kills, $targetinfo['planet_id']));
        Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
        $logpercent = round($space_plague_kills * 100);
        Tki\PlayerLog::WriteLog($pdo_db, $targetinfo['owner'], \Tki\LogEnums::SPACE_PLAGUE, "$targetinfo[name]|$targetinfo[sector_id]|$logpercent");
    }
    else
    {
        echo $langvars['l_apoc_plasma'] . "<br>.";
        $resy = $db->Execute("UPDATE {$db->prefix}planets SET energy = 0 WHERE planet_id = ?;", array($targetinfo['planet_id']));
        Tki\Db::LogDbErrors($pdo_db, $resy, __LINE__, __FILE__);
        Tki\PlayerLog::WriteLog($pdo_db, $targetinfo['owner'], \Tki\LogEnums::PLASMA_STORM, "$targetinfo[name]|$targetinfo[sector_id]");
    }
}

echo "<br>";
