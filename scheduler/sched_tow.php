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
// File: sched_tow.php
//
// FUTURE: Clean up SQL, PDO, better debug/output handling, switch to a FetchAll for a single SQL instead of a loop?

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('scheduler'));

echo "<strong>" . $langvars['l_sched_tow_title'] . "</strong><br><br>";
echo $langvars['l_sched_tow_note'];
$num_to_tow = 0;
do
{
    $res = $db->Execute("SELECT ship_id,character_name,hull,sector,{$db->prefix}universe.zone_id,max_hull FROM {$db->prefix}ships,{$db->prefix}universe,{$db->prefix}zones WHERE sector=sector_id AND {$db->prefix}universe.zone_id={$db->prefix}zones.zone_id AND max_hull<>0 AND (({$db->prefix}ships.hull + {$db->prefix}ships.engines + {$db->prefix}ships.computer + {$db->prefix}ships.beams + {$db->prefix}ships.torp_launchers + {$db->prefix}ships.shields + {$db->prefix}ships.armor)/7) >max_hull AND ship_destroyed='N'");
    Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
    if ($res)
    {
        $num_to_tow = $res->RecordCount();
        $langvars['l_sched_tow_number'] = str_replace("[number]", $num_to_tow, $langvars['l_sched_tow_number']);
        echo "<br>" . $langvars['l_sched_tow_number'] . ":<br>";
        while (!$res->EOF)
        {
            $row = $res->fields;
            $langvars['l_sched_tow_who'] = str_replace("[character]", $row['character_name'], $langvars['l_sched_tow_who']);
            $langvars['l_sched_tow_who'] = str_replace("[sector]", $row['sector'], $langvars['l_sched_tow_who']);
            echo $langvars['l_sched_tow_who'];

            $newsector = random_int(0, (int) $max_sectors - 1);
            $langvars['l_sched_tow_where'] = str_replace("[sector]", $newsector, $langvars['l_sched_tow_where']);
            echo $langvars['l_sched_tow_where'] . ".<br>";

            $query = $db->Execute("UPDATE {$db->prefix}ships SET sector = ?, cleared_defenses=' ' WHERE ship_id=?", array($newsector, $row['ship_id']));
            Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);
            Tki\PlayerLog::WriteLog($pdo_db, $row['ship_id'], \Tki\LogEnums::TOW, "$row[sector]|$newsector|$row[max_hull]");
            Tki\LogMove::writeLog($pdo_db, $row['ship_id'], $newsector);
            $res->MoveNext();
        }
    }
    else
    {
        echo $langvars['l_sched_tow_none']. ".<br>";
    }
} while ($num_to_tow);

echo "<br>";
$multiplier = 0; // No need to run this again
