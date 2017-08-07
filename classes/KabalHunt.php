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
// File: classes/KabalHunt.php

namespace Tki;

class KabalHunt
{
    public static function hunt(\PDO $pdo_db, $db, array $playerinfo, int $kabalisdead, array $langvars, Reg $tkireg): void
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
        $counter = 1;
        $targetnum = random_int(1, $topnum);
        while (!$res->EOF)
        {
            if ($counter == $targetnum)
            {
                $targetinfo = $res->fields;
            }

            $counter++;
            $res->MoveNext();
        }

        // Make sure we have a target
        if (!$targetinfo)
        {
            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Hunt Failed: No Target ");
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
            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Kabal used a wormhole to warp to sector $targetinfo[sector] where he is hunting player $targetinfo[character_name].");
            if (!$move_result)
            {
                $error = $db->ErrorMsg();
                \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Move failed with error: $error ");

                return;
            }

            // Check for sector defenses
            $counter = 0;
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
                    $defenses[$counter] = $tmp_defense;
                    $all_sector_fighters += $defenses[$counter]['quantity'];
                    $counter++;
                }
            }

            $counter = 0;
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
                    $defenses[$counter] = $tmp_defense;
                    $total_sector_mines += $defenses[$counter]['quantity'];
                    $counter++;
                }
            }

            if ($all_sector_fighters > 0 || $total_sector_mines > 0 || ($all_sector_fighters > 0 && $total_sector_mines > 0)) // Destination link has defenses
            {
                // Attack sector defenses
                $targetlink = $targetinfo['sector'];
                \Tki\KabalToSecDef::secDef($pdo_db, $db, $langvars, $playerinfo, $targetlink, $tkireg);
            }

            if ($kabalisdead > 0)
            {
                return; // Sector defenses killed the Kabal
            }

            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Kabal launching an attack on $targetinfo[character_name]."); // Attack the target

            if ($targetinfo['planet_id'] > 0) // Is player target on a planet?
            {
                \Tki\KabalToPlanet::planet($pdo_db, $db, $targetinfo['planet_id'], $tkireg, $playerinfo, $langvars); // Yes, so move to that planet
            }
            else
            {
                \Tki\KabalToShip::ship($pdo_db, $db, $targetinfo['ship_id'], $tkireg, $playerinfo, $langvars); // Not on a planet, so move to the ship
            }
        }
        else
        {
            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Kabal hunt failed, target $targetinfo[character_name] was in a no attack zone (sector $targetinfo[sector]).");
        }
    }
}
