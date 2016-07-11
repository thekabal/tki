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
// File: sched_planets.php

if (strpos($_SERVER['SCRIPT_NAME'], 'sched_planets.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

echo "<strong>PLANETS</strong><p>";

$res = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE owner > 0");
Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
// We are now using transactions to off load the SQL stuff in full to the Database Server.

$result = $db->Execute("START TRANSACTION");
Tki\Db::LogDbErrors($pdo_db, $result, __LINE__, __FILE__);
while (!$res->EOF)
{
    $row = $res->fields;
    $production = floor(min($row['colonists'], $colonist_limit) * $colonist_production_rate);
    $organics_production = floor($production * $organics_prate * $row['prod_organics'] / 100.0);// - ($production * $organics_consumption);
    $organics_production -= floor($production * $organics_consumption);

    if ($row['organics'] + $organics_production < 0)
    {
        $organics_production = -$row['organics'];
        $starvation = floor($row['colonists'] * $starvation_death_rate);
        if ($row['owner'] && $starvation >= 1)
        {
            Tki\PlayerLog::WriteLog($pdo_db, $row['owner'], LOG_STARVATION, "$row[sector_id]|$starvation");
        }
    }
    else
    {
        $starvation = 0;
    }

    $ore_production = floor($production * $ore_prate * $row['prod_ore'] / 100.0);
    $goods_production = floor($production * $goods_prate * $row['prod_goods'] / 100.0);
    $energy_production = floor($production * $energy_prate * $row['prod_energy'] / 100.0);
    $reproduction = floor(($row['colonists'] - $starvation) * $colonist_reproduction_rate);

    if (($row['colonists'] + $reproduction - $starvation) > $colonist_limit)
    {
        $reproduction = $colonist_limit - $row['colonists'];
    }

    $total_percent = $row['prod_organics'] + $row['prod_ore'] + $row['prod_goods'] + $row['prod_energy'];

    if ($row['owner'])
    {
        $fighter_production = floor($production * $fighter_prate * $row['prod_fighters'] / 100.0);
        $torp_production = floor($production * $torpedo_prate * $row['prod_torp'] / 100.0);
        $total_percent += $row['prod_fighters'] + $row['prod_torp'];
    }
    else
    {
        $fighter_production = 0;
        $torp_production = 0;
    }

    $credits_production = floor($production * $credits_prate * (100.0 - $total_percent) / 100.0);
    $ret = $db->Execute("UPDATE {$db->prefix}planets SET organics = organics + ?, ore = ore + ?, goods = goods + ?, energy = energy + ?, colonists = colonists + ? - ?, torps = torps + ?, fighters = fighters + ?, credits = credits * ? + ? WHERE planet_id = ? LIMIT 1; ", array($organics_production, $ore_production, $goods_production, $energy_production, $reproduction, $starvation, $torp_production, $fighter_production, $interest_rate, $credits_production, $row['planet_id']));
    Tki\Db::LogDbErrors($pdo_db, $ret, __LINE__, __FILE__);
    $res->MoveNext();
}

$ret = $db->Execute("COMMIT");
Tki\Db::LogDbErrors($pdo_db, $ret, __LINE__, __FILE__);
if ($tkireg->sched_planet_valid_credits)
{
    $ret = $db->Execute("UPDATE {$db->prefix}planets SET credits = ? WHERE credits > ? AND base = 'N';", array($tkireg->max_credits_without_base, $tkireg->max_credits_without_base));
    Tki\Db::LogDbErrors($pdo_db, $ret, __LINE__, __FILE__);
}

echo "Planets updated.<br><br>";
