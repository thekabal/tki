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
// File: sched_degrade.php

if (strpos($_SERVER['PHP_SELF'], 'sched_degrade.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

echo "<strong>Degrading Sector Fighters with no friendly base</strong><br><br>";
$res = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE defence_type = 'F'");
Bnt\Db::logDbErrors($db, $res, __LINE__, __FILE__);

while (!$res->EOF)
{
    $row = $res->fields;
    $res3 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($row['ship_id']));
    Bnt\Db::logDbErrors($db, $res3, __LINE__, __FILE__);
    $sched_playerinfo = $res3->fields;
    $res2 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE (owner = ? OR (corp = ? AND ? <> 0)) AND sector_id = ? AND energy > 0;", array($row['ship_id'], $sched_playerinfo['team'], $sched_playerinfo['team'], $row['sector_id']));
    Bnt\Db::logDbErrors($db, $res2, __LINE__, __FILE__);
    if ($res2->EOF)
    {
        $resa = $db->Execute("UPDATE {$db->prefix}sector_defence SET quantity = quantity - GREATEST(ROUND(quantity * ?),1) WHERE defence_id = ? AND quantity > 0;", array($defence_degrade_rate, $row['defence_id']));
        Bnt\Db::logDbErrors($db, $resa, __LINE__, __FILE__);
        $degrade_rate = $defence_degrade_rate * 100;
        Bnt\PlayerLog::writeLog($db, $row['ship_id'], LOG_DEFENCE_DEGRADE, $row['sector_id'] ."|". $degrade_rate);
    }
    else
    {
        $energy_required = round($row['quantity'] * $energy_per_fighter);
        $res4 = $db->Execute("SELECT IFNULL(SUM(energy),0) AS energy_available FROM {$db->prefix}planets WHERE (owner = ? OR (corp = ? AND ? <> 0)) AND sector_id = ?", array($row['ship_id'], $sched_playerinfo['team'], $sched_playerinfo['team'], $row['sector_id']));
        Bnt\Db::logDbErrors($db, $res4, __LINE__, __FILE__);
        $planet_energy = $res4->fields;
        $energy_available = $planet_energy['energy_available'];
        echo "available $energy_available, required $energy_required.";
        if ($energy_available > $energy_required)
        {
            while (!$res2->EOF)
            {
                $degrade_row = $res2->fields;
                $resb = $db->Execute("UPDATE {$db->prefix}planets SET energy = energy - GREATEST(ROUND(? * (energy / ?)),1)  WHERE planet_id = ?", array($energy_required, $energy_available, $degrade_row['planet_id']));
                Bnt\Db::logDbErrors($db, $resb, __LINE__, __FILE__);
                $res2->MoveNext();
            }
        }
        else
        {
            $resc = $db->Execute("UPDATE {$db->prefix}sector_defence SET quantity = quantity - GREATEST(ROUND(quantity * ?),1) WHERE defence_id = ?;", array($defence_degrade_rate, $row['defence_id']));
            Bnt\Db::logDbErrors($db, $resc, __LINE__, __FILE__);
            $degrade_rate = $defence_degrade_rate * 100;
            Bnt\PlayerLog::writeLog($db, $row['ship_id'], LOG_DEFENCE_DEGRADE, $row['sector_id'] ."|". $degrade_rate);
        }
    }
    $res->MoveNext();
}
$resx = $db->Execute("DELETE FROM {$db->prefix}sector_defence WHERE quantity <= 0");
Bnt\Db::logDbErrors($db, $resx, __LINE__, __FILE__);
?>
