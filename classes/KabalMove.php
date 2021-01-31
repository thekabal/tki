<?php declare(strict_types = 1);
/**
 * classes/KabalMove.php from The Kabal Invasion.
 * The Kabal Invasion is a Free & Opensource (FOSS), web-based 4X space/strategy game.
 *
 * @copyright 2020 The Kabal Invasion development team, Ron Harwood, and the BNT development team
 *
 * @license GNU AGPL version 3.0 or (at your option) any later version.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Tki;

class KabalMove
{
    public static function move(\PDO $pdo_db, $lang, $old_db, array $playerinfo, int $targetlink, array $langvars, Reg $tkireg): void
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
        if ($links_present !== false)
        {
            foreach ($links_present as $row)
            {
                // Obtain sector information
                $sectres = $old_db->Execute("SELECT sector_id,zone_id FROM {$old_db->prefix}universe WHERE sector_id = ?;", array($row['link_dest']));
                \Tki\Db::logDbErrors($pdo_db, $sectres, __LINE__, __FILE__);
                $sectrow = $sectres->fields;

                $zoneres = $old_db->Execute("SELECT zone_id,allow_attack FROM {$old_db->prefix}zones WHERE zone_id = ?;", array($sectrow['zone_id']));
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
                $sectres = $old_db->Execute("SELECT sector_id,zone_id FROM {$old_db->prefix}universe WHERE sector_id = ?;", array($wormto));
                \Tki\Db::logDbErrors($pdo_db, $sectres, __LINE__, __FILE__);
                $sectrow = $sectres->fields;

                $zoneres = $old_db->Execute("SELECT zone_id,allow_attack FROM {$old_db->prefix}zones WHERE zone_id = ?;", array($sectrow['zone_id']));
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
            $counter = 0;
            $all_sector_fighters = 0;
            $total_sector_mines = 0;
            $defenses = array();

            $sql = "SELECT * FROM ::prefix::sector_defense WHERE sector_id = :sector_id AND defense_type = 'F' ORDER BY quantity DESC";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $targetlink, \PDO::PARAM_INT);
            $stmt->execute();
            $defenses_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($defenses_present !== false)
            {
                foreach ($defenses_present as $tmp_defense)
                {
                    $defenses[$counter] = $tmp_defense;
                    $all_sector_fighters += $defenses[$counter]['quantity'];
                    $counter++;
                }
            }

            $counter = 0;
            $sql = "SELECT * FROM ::prefix::sector_defense WHERE sector_id = :sector_id AND defense_type = 'M'";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $targetlink, \PDO::PARAM_INT);
            $stmt->execute();
            $defenses_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($defenses_present !== false)
            {
                foreach ($defenses_present as $tmp_defense)
                {
                    $defenses[$counter] = $tmp_defense;
                    $total_sector_mines += $defenses[$counter]['quantity'];
                    $counter++;
                }
            }

            if ($all_sector_fighters > 0 || $total_sector_mines > 0 || ($all_sector_fighters > 0 && $total_sector_mines > 0)) // If destination link has defenses
            {
                if ($playerinfo['aggression'] == 2 || $playerinfo['aggression'] == 1)
                {
                    \Tki\KabalToSecDef::secDef($pdo_db, $lang, $playerinfo, $targetlink, $tkireg); // Attack sector defenses

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
            $cur_time_stamp = date("Y-m-d H:i:s");
            $sql = "UPDATE ::prefix::ships SET last_login = :stamp, turns_used = turns_used + 1, sector = :targetlink WHERE ship_id = :ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':stamp', $cur_time_stamp, \PDO::PARAM_STR);
            $stmt->bindParam(':targetlink', $targetlink, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $result = $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            echo "<br>" . $langvars['l_nonexistant_pl'] . "<br><br>";

            if (!$result)
            {
                $error = $old_db->ErrorMsg();
                \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Move failed with error: $error ");
            }
        }
        else
        {
            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Move failed due to lack of target link."); // We have no target link for some reason
        }
    }
}
