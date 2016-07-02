<?php
declare(strict_types=1);
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
// File: classes/Score.php

namespace Tki;

class Score
{
    public static function updateScore(\PDO $pdo_db, $ship_id, Reg $tkireg, $playerinfo) : int
    {
//      Not currently used in calculation!
//        $base_ore = $tkireg->base_ore;
//        $base_goods = $tkireg->base_goods;
//        $base_organics = $tkireg->base_organics;

        // These are all SQL Queries, so treat them like them.
        $calc_hull              = "ROUND(POW($tkireg->upgrade_factor, hull))";
        $calc_engines           = "ROUND(POW($tkireg->upgrade_factor, engines))";
        $calc_power             = "ROUND(POW($tkireg->upgrade_factor, power))";
        $calc_computer          = "ROUND(POW($tkireg->upgrade_factor, computer))";
        $calc_sensors           = "ROUND(POW($tkireg->upgrade_factor, sensors))";
        $calc_beams             = "ROUND(POW($tkireg->upgrade_factor, beams))";
        $calc_torp_launchers    = "ROUND(POW($tkireg->upgrade_factor, torp_launchers))";
        $calc_shields           = "ROUND(POW($tkireg->upgrade_factor, shields))";
        $calc_armor             = "ROUND(POW($tkireg->upgrade_factor, armor))";
        $calc_cloak             = "ROUND(POW($tkireg->upgrade_factor, cloak))";
        $calc_levels            = "($calc_hull + $calc_engines + $calc_power + $calc_computer + $calc_sensors + $calc_beams + $calc_torp_launchers + $calc_shields + $calc_armor + $calc_cloak) * $tkireg->upgrade_cost";

        $calc_torps             = "{$pdo_db->prefix}ships.torps * $tkireg->torpedo_price";
        $calc_armor_pts         = "armor_pts * $tkireg->armor_price";
        $calc_ship_ore          = "ship_ore * $tkireg->ore_price";
        $calc_ship_organics     = "ship_organics * $tkireg->organics_price";
        $calc_ship_goods        = "ship_goods * $tkireg->goods_price";
        $calc_ship_energy       = "ship_energy * $tkireg->energy_price";
        $calc_ship_colonists    = "ship_colonists * $tkireg->colonist_price";
        $calc_ship_fighters     = "ship_fighters * $tkireg->fighter_price";
        $calc_equip             = "$calc_torps + $calc_armor_pts + $calc_ship_ore + $calc_ship_organics + $calc_ship_goods + $calc_ship_energy + $calc_ship_colonists + $calc_ship_fighters";

        $calc_dev_warpedit      = "dev_warpedit * $tkireg->dev_warpedit_price";
        $calc_dev_genesis       = "dev_genesis * $tkireg->dev_genesis_price";
        $calc_dev_beacon        = "dev_beacon * $tkireg->dev_beacon_price";
        $calc_dev_emerwarp      = "dev_emerwarp * $tkireg->dev_emerwarp_price";
        $calc_dev_escapepod     = "IF(dev_escapepod='Y', $tkireg->dev_escapepod_price, 0)";
        $calc_dev_fuelscoop     = "IF(dev_fuelscoop='Y', $tkireg->dev_fuelscoop_price, 0)";
        $calc_dev_lssd          = "IF(dev_lssd='Y', $tkireg->dev_lssd_price, 0)";
        $calc_dev_minedeflector = "dev_minedeflector * $tkireg->dev_minedeflector_price";
        $calc_dev               = "$calc_dev_warpedit + $calc_dev_genesis + $calc_dev_beacon + $calc_dev_emerwarp + $calc_dev_escapepod + $calc_dev_fuelscoop + $calc_dev_minedeflector + $calc_dev_lssd";

        $calc_planet_goods      = "SUM({$pdo_db->prefix}planets.organics) * $tkireg->organics_price + SUM({$pdo_db->prefix}planets.ore) * $tkireg->ore_price + SUM({$pdo_db->prefix}planets.goods) * $tkireg->goods_price + SUM({$pdo_db->prefix}planets.energy) * $tkireg->energy_price";
        $calc_planet_colonists  = "SUM({$pdo_db->prefix}planets.colonists) * $tkireg->colonist_price";
        $calc_planet_defence    = "SUM({$pdo_db->prefix}planets.fighters) * $tkireg->fighter_price + IF({$pdo_db->prefix}planets.base='Y', $tkireg->base_credits + SUM({$pdo_db->prefix}planets.torps) * $tkireg->torpedo_price, 0)";
        $calc_planet_credits    = "SUM({$pdo_db->prefix}planets.credits)";

        $sql = "SELECT IF(COUNT(*)>0, $calc_planet_goods + $calc_planet_colonists + $calc_planet_defence + $calc_planet_credits, 0) AS planet_score " .
                                     "FROM {$pdo_db->prefix}planets WHERE owner=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $ship_id);
        $stmt->execute();
        $planet_score = $stmt->fetch(\PDO::FETCH_COLUMN);

        $sql = "SELECT IF(COUNT(*)>0, $calc_levels + $calc_equip + $calc_dev + {$pdo_db->prefix}ships.credits, 0) AS ship_score " .
               "FROM {$pdo_db->prefix}ships LEFT JOIN {$pdo_db->prefix}planets ON {$pdo_db->prefix}planets.owner=ship_id WHERE ship_id = :ship_id AND ship_destroyed='N'";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $ship_id);
        $stmt->execute();
        $ship_score = $stmt->fetch(\PDO::FETCH_COLUMN);

        $sql = "SELECT (balance-loan) AS bank_score FROM {$pdo_db->prefix}ibank_accounts WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $ship_id);
        $stmt->execute();
        $bank_score = $stmt->fetch(\PDO::FETCH_COLUMN);

        $score = $ship_score + $planet_score + $bank_score;
        if ($score < 0)
        {
            $score = 0;
        }
        $score = (int) round(sqrt($score));

        $stmt = $pdo_db->prepare("UPDATE {$pdo_db->prefix}ships SET score = :score WHERE ship_id=:ship_id");
        $stmt->bindParam(':score', $score);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
        $result = $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);

        return (int) $score;
    }
}
