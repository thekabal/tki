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
// File: classes/KabalMove.php

namespace Tki;

class KabalMove
{
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
                    \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Used a wormhole to warp to a zone where attacks are allowed.");
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
                    \Tki\KabalToSecDef::secDef($pdo_db, $db, $langvars, $playerinfo, $targetlink, $tkireg); // Attack sector defenses

                    return;
                }
                else
                {
                    \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Move failed, the sector is defended by $all_sector_fighters fighters and $total_sector_mines mines.");

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
                \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Move failed with error: $error ");
            }
        }
        else
        {
            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Move failed due to lack of target link."); // We have no target link for some reason
        }
    }
}
