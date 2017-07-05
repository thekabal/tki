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
// File: classes/Kabal.php

namespace Tki;

class Kabal
{
    public static function trade(\PDO $pdo_db, array $playerinfo, Reg $tkireg): void
    {
        // FUTURE: We need to get rid of this.. the bug causing it needs to be identified and squashed. In the meantime, we want working kabal. :)
        $tkireg->ore_price = 11;
        $tkireg->organics_price = 5;
        $tkireg->goods_price = 15;
        $shipore = null;
        $shiporganics = null;
        $shipgoods = null;

        // Obtain sector information
        $sql = "SELECT * FROM ::prefix::universe WHERE sector_id=:sector_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $playerinfo['sector'], \PDO::PARAM_INT);
        $stmt->execute();
        $sectorinfo = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Obtain zone information
        $sql = "SELECT zone_id, allow_attack, allow_trade FROM ::prefix::zones WHERE zone_id=:zone_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $sectorinfo['zone_id'], \PDO::PARAM_INT);
        $stmt->execute();
        $zonerow = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Make sure we can trade here
        if ($zonerow['allow_trade'] == "N")
        {
            return;
        }

        // Check for a port we can use
        if ($sectorinfo['port_type'] == "none")
        {
            return;
        }

        // Kabal do not trade at energy ports since they regen energy
        if ($sectorinfo['port_type'] == "energy")
        {
            return;
        }

        // Check for negative credits or cargo
        if ($playerinfo['ship_ore'] < 0)
        {
            $playerinfo['ship_ore'] = 0;
            $shipore = 0;
        }

        if ($playerinfo['ship_organics'] < 0)
        {
            $playerinfo['ship_organics'] = 0;
            $shiporganics = 0;
        }

        if ($playerinfo['ship_goods'] < 0)
        {
            $playerinfo['ship_goods'] = 0;
            $shipgoods = 0;
        }

        if ($playerinfo['credits'] < 0)
        {
            $playerinfo['credits'] = 0;
        }

        if ($sectorinfo['port_ore'] <= 0)
        {
            return;
        }

        if ($sectorinfo['port_organics'] <= 0)
        {
            return;
        }

        if ($sectorinfo['port_goods'] <= 0)
        {
            return;
        }

        //  Check Kabal Credits & Cargo
        if ($playerinfo['ship_ore'] > 0)
        {
            $shipore = $playerinfo['ship_ore'];
        }

        if ($playerinfo['ship_organics'] > 0)
        {
            $shiporganics = $playerinfo['ship_organics'];
        }

        if ($playerinfo['ship_goods'] > 0)
        {
            $shipgoods = $playerinfo['ship_goods'];
        }

        // Make sure we have cargo or credits
        if (!$playerinfo['credits'] > 0 && !$playerinfo['ship_ore'] > 0 && !$playerinfo['ship_goods'] > 0 && !$playerinfo['ship_organics'] > 0)
        {
            return;
        }

        //  Make sure cargo is compatible
        if ($sectorinfo['port_type'] == "ore" && $shipore > 0)
        {
            return;
        }

        if ($sectorinfo['port_type'] == "organics" && $shiporganics > 0)
        {
            return;
        }

        if ($sectorinfo['port_type'] == "goods" && $shipgoods > 0)
        {
            return;
        }

        // Lets trade some cargo
        if ($sectorinfo['port_type'] == "ore") // Port ore
        {
            // Set the prices
            $tkireg->ore_price = $tkireg->ore_price - $tkireg->ore_delta * $sectorinfo['port_ore'] / $tkireg->ore_limit * $tkireg->inventory_factor;
            $tkireg->organics_price = $tkireg->organics_price + $tkireg->organics_delta * $sectorinfo['port_organics'] / $tkireg->organics_limit * $tkireg->inventory_factor;
            $tkireg->goods_price = $tkireg->goods_price + $tkireg->goods_delta * $sectorinfo['port_goods'] / $tkireg->goods_limit * $tkireg->inventory_factor;

            //  Set cargo buy/sell
            $amount_organics = $playerinfo['ship_organics'];
            $amount_goods = $playerinfo['ship_goods'];

            // Since we sell all other holds we set amount to be our total hold limit
            $amount_ore = \Tki\CalcLevels::holds($playerinfo['hull'], $tkireg);

            // We adjust this to make sure it does not exceed what the port has to sell
            $amount_ore = min($amount_ore, $sectorinfo['port_ore']);

            // We adjust this to make sure it does not exceed what we can afford to buy
            $amount_ore = min($amount_ore, floor(($playerinfo['credits'] + $amount_organics * $tkireg->organics_price + $amount_goods * $tkireg->goods_price) / $tkireg->ore_price));

            // Buy / sell cargo
            $total_cost = round(($amount_ore * $tkireg->ore_price) - ($amount_organics * $tkireg->organics_price + $amount_goods * $tkireg->goods_price));
            $newcredits = max(0, $playerinfo['credits'] - $total_cost);
            $newore = $playerinfo['ship_ore'] + $amount_ore;
            $neworganics = max(0, $playerinfo['ship_organics'] - $amount_organics);
            $newgoods = max(0, $playerinfo['ship_goods'] - $amount_goods);

            $sql = "UPDATE ::prefix::ships SET rating=rating+1, credits=:credits, ship_ore=:ship_ore, ship_organics=:ship_organics, ship_goods=:ship_goods WHERE ship_id=:ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':credits', $newcredits, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_ore', $newore, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_organics', $neworganics, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_goods', $newgoods, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->execute();

            $sql = "UPDATE ::prefix::universe SET port_ore=port_ore -:port_ore, port_organics = port_organics + :port_organics, port_goods = port_goods + :port_goods WHERE sector_id = :sector_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':port_ore', $amount_ore, \PDO::PARAM_INT);
            $stmt->bindParam(':port_organics', $amount_organics, \PDO::PARAM_INT);
            $stmt->bindParam(':port_goods', $amount_goods, \PDO::PARAM_INT);
            $stmt->bindParam(':sector_id', $sectorinfo['sector_id'], \PDO::PARAM_INT);
            $stmt->execute();

            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LOG_RAW, "Kabal Trade Results: Sold $amount_organics Organics Sold $amount_goods Goods Bought $amount_ore Ore Cost $total_cost");
        }

        if ($sectorinfo['port_type'] == "organics") // Port organics
        {
            // Set the prices
            $tkireg->organics_price = $tkireg->organics_price - $tkireg->organics_delta * $sectorinfo['port_organics'] / $tkireg->organics_limit * $tkireg->inventory_factor;
            $tkireg->ore_price = $tkireg->ore_price + $tkireg->ore_delta * $sectorinfo['port_ore'] / $tkireg->ore_limit * $tkireg->inventory_factor;
            $tkireg->goods_price = $tkireg->goods_price + $tkireg->goods_delta * $sectorinfo['port_goods'] / $tkireg->goods_limit * $tkireg->inventory_factor;

            // Set cargo buy / sell
            $amount_ore = $playerinfo['ship_ore'];
            $amount_goods = $playerinfo['ship_goods'];

            // SINCE WE SELL ALL OTHER HOLDS WE SET AMOUNT TO BE OUR TOTAL HOLD LIMIT
            $amount_organics = \Tki\CalcLevels::holds($playerinfo['hull'], $tkireg);

            // WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEED WHAT THE PORT HAS TO SELL
            $amount_organics = min($amount_organics, $sectorinfo['port_organics']);

            // WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEES WHAT WE CAN AFFORD TO BUY
            $amount_organics = min($amount_organics, floor(($playerinfo['credits'] + $amount_ore * $tkireg->ore_price + $amount_goods * $tkireg->goods_price) / $tkireg->organics_price));

            // Buy / sell cargo
            $total_cost = round(($amount_organics * $tkireg->organics_price) - ($amount_ore * $tkireg->ore_price + $amount_goods * $tkireg->goods_price));
            $newcredits = max(0, $playerinfo['credits'] - $total_cost);
            $newore = max(0, $playerinfo['ship_ore'] - $amount_ore);
            $neworganics = $playerinfo['ship_organics'] + $amount_organics;
            $newgoods = max(0, $playerinfo['ship_goods'] - $amount_goods);

            $sql = "UPDATE ::prefix::ships SET rating=rating+1, credits=:credits, ship_ore=:ship_ore, ship_organics=:ship_organics, ship_goods=:ship_goods WHERE ship_id=:ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':credits', $newcredits, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_ore', $newore, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_organics', $neworganics, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_goods', $newgoods, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->execute();

            $sql = "UPDATE ::prefix::universe SET port_ore=port_ore -:port_ore, port_organics = port_organics + :port_organics, port_goods = port_goods + :port_goods WHERE sector_id = :sector_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':port_ore', $amount_ore, \PDO::PARAM_INT);
            $stmt->bindParam(':port_organics', $amount_organics, \PDO::PARAM_INT);
            $stmt->bindParam(':port_goods', $amount_goods, \PDO::PARAM_INT);
            $stmt->bindParam(':sector_id', $sectorinfo['sector_id'], \PDO::PARAM_INT);
            $stmt->execute();

            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LOG_RAW, "Kabal Trade Results: Sold $amount_goods Goods Sold $amount_ore Ore Bought $amount_organics Organics Cost $total_cost");
        }

        if ($sectorinfo['port_type'] == "goods") // Port goods
        {
            // Set the prices
            $tkireg->goods_price = $tkireg->goods_price - $tkireg->goods_delta * $sectorinfo['port_goods'] / $tkireg->goods_limit * $tkireg->inventory_factor;
            $tkireg->ore_price = $tkireg->ore_price + $tkireg->ore_delta * $sectorinfo['port_ore'] / $tkireg->ore_limit * $tkireg->inventory_factor;
            $tkireg->organics_price = $tkireg->organics_price + $tkireg->organics_delta * $sectorinfo['port_organics'] / $tkireg->organics_limit * $tkireg->inventory_factor;

            // Set cargo buy / sell
            $amount_ore = $playerinfo['ship_ore'];
            $amount_organics = $playerinfo['ship_organics'];

            // Since we sell all other holds we set amount to be our total hold limit
            $amount_goods = \Tki\CalcLevels::holds($playerinfo['hull'], $tkireg);

            // We adjust this to make sure it does not exceed what the port has to sell
            $amount_goods = min($amount_goods, $sectorinfo['port_goods']);

            // We adjust this to make sure it does not exceed what we can afford to buy
            $amount_goods = min($amount_goods, floor(($playerinfo['credits'] + $amount_ore * $tkireg->ore_price + $amount_organics * $tkireg->organics_price) / $tkireg->goods_price));

            // Buy / sell cargo
            $total_cost = round(($amount_goods * $tkireg->goods_price) - ($amount_organics * $tkireg->organics_price + $amount_ore * $tkireg->ore_price));
            $newcredits = max(0, $playerinfo['credits'] - $total_cost);
            $newore = max(0, $playerinfo['ship_ore'] - $amount_ore);
            $neworganics = max(0, $playerinfo['ship_organics'] - $amount_organics);
            $newgoods = $playerinfo['ship_goods'] + $amount_goods;

            $sql = "UPDATE ::prefix::ships SET rating=rating+1, credits=:credits, ship_ore=:ship_ore, ship_organics=:ship_organics, ship_goods=:ship_goods WHERE ship_id=:ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':credits', $newcredits, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_ore', $newore, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_organics', $neworganics, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_goods', $newgoods, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->execute();

            $sql = "UPDATE ::prefix::universe SET port_ore=port_ore -:port_ore, port_organics = port_organics + :port_organics, port_goods = port_goods + :port_goods WHERE sector_id = :sector_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':port_ore', $amount_ore, \PDO::PARAM_INT);
            $stmt->bindParam(':port_organics', $amount_organics, \PDO::PARAM_INT);
            $stmt->bindParam(':port_goods', $amount_goods, \PDO::PARAM_INT);
            $stmt->bindParam(':sector_id', $sectorinfo['sector_id'], \PDO::PARAM_INT);
            $stmt->execute();

            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LOG_RAW, "Kabal Trade Results: Sold $amount_ore Ore Sold $amount_organics Organics Bought $amount_goods Goods Cost $total_cost");
        }
    }

    public static function move(\PDO $pdo_db, $db, array $playerinfo, int $targetlink, array $langvars, Reg $tkireg): void
    {
        // Obtain a target link
        if ($targetlink == $playerinfo['sector'])
        {
            $targetlink = 0;
        }

        $sql = "SELECT * FROM ::prefix::links WHERE link_start = :link_start";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':link_start', $playerinfo['sector'], \PDO::PARAM_INT);
        $stmt->execute();
        $links_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($links_present !== null)
        {
            foreach ($links_present as $row)
            {
                // Obtain sector information
                $sectres = $db->Execute("SELECT sector_id,zone_id FROM {$db->prefix}universe WHERE sector_id = ?;", array($row['link_dest']));
                \Tki\Db::logDbErrors($pdo_db, $sectres, __LINE__, __FILE__);
                $sectrow = $sectres->fields;

                $zoneres = $db->Execute("SELECT zone_id,allow_attack FROM {$db->prefix}zones WHERE zone_id = ?;", array($sectrow['zone_id']));
                \Tki\Db::logDbErrors($pdo_db, $zoneres, __LINE__, __FILE__);
                $zonerow = $zoneres->fields;
                if ($zonerow['allow_attack'] == "Y") // Dest link must allow attacking
                {
                    $setlink = random_int(0, 2);                        // 33% Chance of replacing destination link with this one
                    if ($setlink == 0 || !$targetlink > 0)           // Unless there is no dest link, choose this one
                    {
                        $targetlink = $row['link_dest'];
                    }
                }
            }
        }

        if (!$targetlink > 0) // If there is no acceptable link, use a worm hole.
        {
            $wormto = random_int(1, (int) ($tkireg->max_sectors - 15));  // Generate a random sector number
            $limitloop = 1;                             // Limit the number of loops
            while (!$targetlink > 0 && $limitloop < 15)
            {
                // Obtain sector information
                $sectres = $db->Execute("SELECT sector_id,zone_id FROM {$db->prefix}universe WHERE sector_id = ?;", array($wormto));
                \Tki\Db::logDbErrors($pdo_db, $sectres, __LINE__, __FILE__);
                $sectrow = $sectres->fields;

                $zoneres = $db->Execute("SELECT zone_id,allow_attack FROM {$db->prefix}zones WHERE zone_id = ?;", array($sectrow['zone_id']));
                \Tki\Db::logDbErrors($pdo_db, $zoneres, __LINE__, __FILE__);
                $zonerow = $zoneres->fields;
                if ($zonerow['allow_attack'] == "Y")
                {
                    $targetlink = $wormto;
                    \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LOG_RAW, "Used a wormhole to warp to a zone where attacks are allowed.");
                }

                $wormto++;
                $wormto++;
                $limitloop++;
            }
        }

        if ($targetlink > 0) // Check for sector defenses
        {
            // Check for sector defenses
            $i = 0;
            $all_sector_fighters = 0;
            $total_sector_mines = 0;
            $defenses = array();

            $sql = "SELECT * FROM ::prefix::sector_defense WHERE sector_id = :sector_id AND defense_type = 'F' ORDER BY quantity DESC";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $targetlink, \PDO::PARAM_INT);
            $stmt->execute();
            $defenses_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($defenses_present !== null)
            {
                foreach ($defenses_present as $tmp_defense)
                {
                    $defenses[$i] = $tmp_defense;
                    $all_sector_fighters += $defenses[$i]['quantity'];
                    $i++;
                }
            }

            $i = 0;
            $sql = "SELECT * FROM ::prefix::sector_defense WHERE sector_id = :sector_id AND defense_type = 'M'";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $targetlink, \PDO::PARAM_INT);
            $stmt->execute();
            $defenses_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($defenses_present !== null)
            {
                foreach ($defenses_present as $tmp_defense)
                {
                    $defenses[$i] = $tmp_defense;
                    $total_sector_mines += $defenses[$i]['quantity'];
                    $i++;
                }
            }

            if ($all_sector_fighters > 0 || $total_sector_mines > 0 || ($all_sector_fighters > 0 && $total_sector_mines > 0)) // If destination link has defenses
            {
                if ($playerinfo['aggression'] == 2 || $playerinfo['aggression'] == 1)
                {
                    \Tki\KabalTo::secDef($pdo_db, $db, $langvars, $playerinfo, $targetlink, $tkireg); // Attack sector defenses

                    return;
                }
                else
                {
                    \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LOG_RAW, "Move failed, the sector is defended by $all_sector_fighters fighters and $total_sector_mines mines.");

                    return;
                }
            }
        }

        if ($targetlink > 0) // Move to target link
        {
            $stamp = date("Y-m-d H:i:s");

            $move_result = $db->Execute("UPDATE {$db->prefix}ships SET last_login = ?, turns_used = turns_used + 1, sector = ? WHERE ship_id = ?", array($stamp, $targetlink, $playerinfo['ship_id']));
            \Tki\Db::logDbErrors($pdo_db, $move_result, __LINE__, __FILE__);
            if (!$move_result)
            {
                $error = $db->ErrorMsg();
                \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LOG_RAW, "Move failed with error: $error ");
            }
        }
        else
        {
            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LOG_RAW, "Move failed due to lack of target link."); // We have no target link for some reason
        }
    }

    public static function goHunt(\PDO $pdo_db, $db, array $playerinfo, int $kabalisdead, array $langvars, Reg $tkireg): void
    {
        $targetinfo = array();
        $rescount = $db->Execute("SELECT COUNT(*) AS num_players FROM {$db->prefix}ships WHERE ship_destroyed='N' AND email NOT LIKE '%@kabal' AND ship_id > 1");
        \Tki\Db::logDbErrors($pdo_db, $rescount, __LINE__, __FILE__);
        $rowcount = $rescount->fields;
        $topnum = min(10, $rowcount['num_players']);

        // If we have killed all the players in the game then stop here.
        if ($topnum < 1)
        {
            return;
        }

        $res = $db->SelectLimit("SELECT * FROM {$db->prefix}ships WHERE ship_destroyed='N' AND email NOT LIKE '%@kabal' AND ship_id > 1 ORDER BY score DESC", $topnum);
        \Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);

        // Choose a target from the top player list
        $i = 1;
        $targetnum = random_int(1, $topnum);
        while (!$res->EOF)
        {
            if ($i == $targetnum)
            {
                $targetinfo = $res->fields;
            }

            $i++;
            $res->MoveNext();
        }

        // Make sure we have a target
        if (!$targetinfo)
        {
            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LOG_RAW, "Hunt Failed: No Target ");
            return;
        }

        // Jump to target sector
        $sectres = $db->Execute("SELECT sector_id, zone_id FROM {$db->prefix}universe WHERE sector_id = ?;", array($targetinfo['sector']));
        \Tki\Db::logDbErrors($pdo_db, $sectres, __LINE__, __FILE__);
        $sectrow = $sectres->fields;

        $zoneres = $db->Execute("SELECT zone_id,allow_attack FROM {$db->prefix}zones WHERE zone_id = ?;", array($sectrow['zone_id']));
        \Tki\Db::logDbErrors($pdo_db, $zoneres, __LINE__, __FILE__);
        $zonerow = $zoneres->fields;

        // Only travel there if we can attack in the target sector
        if ($zonerow['allow_attack'] == "Y")
        {
            $stamp = date("Y-m-d H:i:s");
            $move_result = $db->Execute("UPDATE {$db->prefix}ships SET last_login = ?, turns_used = turns_used + 1, sector = ? WHERE ship_id = ?", array($stamp, $targetinfo['sector'], $playerinfo['ship_id']));
            \Tki\Db::logDbErrors($pdo_db, $move_result, __LINE__, __FILE__);
            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LOG_RAW, "Kabal used a wormhole to warp to sector $targetinfo[sector] where he is hunting player $targetinfo[character_name].");
            if (!$move_result)
            {
                $error = $db->ErrorMsg();
                \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LOG_RAW, "Move failed with error: $error ");

                return;
            }

            // Check for sector defenses
            $i = 0;
            $all_sector_fighters = 0;
            $defenses = array();

            $sql = "SELECT * FROM ::prefix::sector_defense WHERE sector_id = :sector_id AND defense_type = 'F' ORDER BY quantity DESC";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $targetinfo['sector'], \PDO::PARAM_INT);
            $stmt->execute();
            $defenses_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($defenses_present !== null)
            {
                foreach ($defenses_present as $tmp_defense)
                {
                    $defenses[$i] = $tmp_defense;
                    $all_sector_fighters += $defenses[$i]['quantity'];
                    $i++;
                }
            }

            $i = 0;
            $total_sector_mines = 0;

            $sql = "SELECT * FROM ::prefix::sector_defense WHERE sector_id = :sector_id AND defense_type = 'M'";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $targetinfo['sector'], \PDO::PARAM_INT);
            $stmt->execute();
            $defenses_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($defenses_present !== null)
            {
                foreach ($defenses_present as $tmp_defense)
                {
                    $defenses[$i] = $tmp_defense;
                    $total_sector_mines += $defenses[$i]['quantity'];
                    $i++;
                }
            }

            if ($all_sector_fighters > 0 || $total_sector_mines > 0 || ($all_sector_fighters > 0 && $total_sector_mines > 0)) // Destination link has defenses
            {
                // Attack sector defenses
                $targetlink = $targetinfo['sector'];
                \Tki\KabalTo::secDef($pdo_db, $db, $langvars, $playerinfo, $targetlink, $tkireg);
            }

            if ($kabalisdead > 0)
            {
                return; // Sector defenses killed the Kabal
            }

            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LOG_RAW, "Kabal launching an attack on $targetinfo[character_name]."); // Attack the target

            if ($targetinfo['planet_id'] > 0) // Is player target on a planet?
            {
                \Tki\KabalTo::planet($pdo_db, $db, $targetinfo['planet_id'], $tkireg, $playerinfo, $langvars); // Yes, so move to that planet
            }
            else
            {
                \Tki\KabalTo::ship($pdo_db, $db, $targetinfo['ship_id'], $tkireg, $playerinfo, $langvars); // Not on a planet, so move to the ship
            }
        }
        else
        {
            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LOG_RAW, "Kabal hunt failed, target $targetinfo[character_name] was in a no attack zone (sector $targetinfo[sector]).");
        }
    }

    public static function regen(\PDO $pdo_db, array $playerinfo, $kabal_unemployment, Reg $tkireg): void
    {
        $gena = null;
        $gene = null;
        $genf = null;
        $gent = null;

        // Kabal Unempoyment Check
        $playerinfo['credits'] = $playerinfo['credits'] + $kabal_unemployment;
        $maxenergy = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg); // Regenerate energy
        if ($playerinfo['ship_energy'] <= ($maxenergy - 50))  // Stop regen when within 50 of max
        {
            $playerinfo['ship_energy'] = $playerinfo['ship_energy'] + round(($maxenergy - $playerinfo['ship_energy']) / 2); // Regen half of remaining energy
            $gene = "regenerated Energy to $playerinfo[ship_energy] units,";
        }

        $maxarmor = \Tki\CalcLevels::armor($playerinfo['armor'], $tkireg); // Regenerate armor
        if ($playerinfo['armor_pts'] <= ($maxarmor - 50))  // Stop regen when within 50 of max
        {
            $playerinfo['armor_pts'] = $playerinfo['armor_pts'] + round(($maxarmor - $playerinfo['armor_pts']) / 2); // Regen half of remaining armor
            $gena = "regenerated Armor to $playerinfo[armor_pts] points,";
        }

        // Buy fighters & torpedos at 6 credits per fighter
        $available_fighters = \Tki\CalcLevels::fighters($playerinfo['computer'], $tkireg) - $playerinfo['ship_fighters'];
        if (($playerinfo['credits'] > 5) && ($available_fighters > 0))
        {
            if (round($playerinfo['credits'] / 6) > $available_fighters)
            {
                $purchase = ($available_fighters * 6);
                $playerinfo['credits'] = $playerinfo['credits'] - $purchase;
                $playerinfo['ship_fighters'] = $playerinfo['ship_fighters'] + $available_fighters;
                $genf = "purchased $available_fighters fighters for $purchase credits,";
            }

            if (round($playerinfo['credits'] / 6) <= $available_fighters)
            {
                $purchase = (round($playerinfo['credits'] / 6));
                $playerinfo['ship_fighters'] = $playerinfo['ship_fighters'] + $purchase;
                $genf = "purchased $purchase fighters for $playerinfo[credits] credits,";
                $playerinfo['credits'] = 0;
            }
        }

        // Kabal pay 3 credits per torpedo
        $available_torpedoes = \Tki\CalcLevels::torpedoes($playerinfo['torp_launchers'], $tkireg) - $playerinfo['torps'];
        if (($playerinfo['credits'] > 2) && ($available_torpedoes > 0))
        {
            if (round($playerinfo['credits'] / 3) > $available_torpedoes)
            {
                $purchase = ($available_torpedoes * 3);
                $playerinfo['credits'] = $playerinfo['credits'] - $purchase;
                $playerinfo['torps'] = $playerinfo['torps'] + $available_torpedoes;
                $gent = "purchased $available_torpedoes torpedoes for $purchase credits,";
            }

            if (round($playerinfo['credits'] / 3) <= $available_torpedoes)
            {
                $purchase = (round($playerinfo['credits'] / 3));
                $playerinfo['torps'] = $playerinfo['torps'] + $purchase;
                $gent = "purchased $purchase torpedoes for $playerinfo[credits] credits,";
                $playerinfo['credits'] = 0;
            }
        }

        // Update Kabal record
        $sql = "UPDATE ::prefix::ships SET ship_energy = :ship_energy, armor_pts = :armor_pts, ship_fighters = :ship_fighters, torps = :torps, credits = :credits WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_energy', $playerinfo['ship_energy'], \PDO::PARAM_INT);
        $stmt->bindParam(':armor_pts', $playerinfo['armor_pts'], \PDO::PARAM_INT);
        $stmt->bindParam(':ship_fighters', $playerinfo['ship_fighters'], \PDO::PARAM_INT);
        $stmt->bindParam(':torps', $playerinfo['torps'], \PDO::PARAM_INT);
        $stmt->bindParam(':credits', $playerinfo['credits'], \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();

        if (!$gene === null || !$gena === null || !$genf === null || !$gent === null)
        {
            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LOG_RAW, "Kabal $gene $gena $genf $gent and has been updated.");
        }
    }
}
