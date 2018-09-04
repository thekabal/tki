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
// File: classes/KabalToShip.php

namespace Tki;

class KabalToShip
{
    public static function ship(\PDO $pdo_db, $db, int $ship_id, Reg $tkireg, array $playerinfo, array $langvars): void
    {
        $armor_lost = null;
        $fighters_lost = null;
        $character_object = new Character;

        // Lookup target details
        $resultt = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($ship_id));
        \Tki\Db::logDbErrors($pdo_db, $resultt, __LINE__, __FILE__);
        $targetinfo = $resultt->fields;

        // Verify not attacking another Kabal
        // Added because the kabal were killing each other off
        if (mb_strstr($targetinfo['email'], '@kabal'))                       // He's a kabal
        {
            return;
        }

        // Verify sector allows attack
        $sql = "SELECT sector_id, zone_id FROM ::prefix::universe WHERE sector_id=:sector_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $targetinfo['sector'], PDO::PARAM_INT);
        $stmt->execute();
        $sectrow = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT zone_id, allow_attack FROM ::prefix::zones WHERE zone_id=:zone_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $sectrow['zone_id'], PDO::PARAM_INT);
        $stmt->execute();
        $zonerow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($zonerow['allow_attack'] == "N")                        //  Dest link must allow attacking
        {
            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Attack failed, you are in a sector that prohibits attacks.");

            return;
        }

        // Use emergency warp device
        if ($targetinfo['dev_emerwarp'] > 0)
        {
            \Tki\PlayerLog::writeLog($pdo_db, $targetinfo['ship_id'], LogEnums::ATTACK_EWD, "Kabal $playerinfo[character_name]");
            $dest_sector = random_int(1, (int) $tkireg->max_sectors);

            $sql = "UPDATE ::prefix::ships SET sector = :sector, dev_emerwarp = dev_emerwarp - 1 WHERE ship_id=:ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector', $dest_sector, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $targetinfo['ship_id'], \PDO::PARAM_INT);
            $result = $stmt->execute();
            Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            return;
        }

        // Setup attacker variables
        $attackerbeams = \Tki\CalcLevels::abstractLevels($playerinfo['beams'], $tkireg);
        if ($attackerbeams > $playerinfo['ship_energy'])
        {
            $attackerbeams = $playerinfo['ship_energy'];
        }

        $playerinfo['ship_energy'] = $playerinfo['ship_energy'] - $attackerbeams;
        $attackershields = \Tki\CalcLevels::abstractLevels($playerinfo['shields'], $tkireg);
        if ($attackershields > $playerinfo['ship_energy'])
        {
            $attackershields = $playerinfo['ship_energy'];
        }

        $playerinfo['ship_energy'] = $playerinfo['ship_energy'] - $attackershields;
        $attackertorps = round(pow($tkireg->level_factor, $playerinfo['torp_launchers'])) * 2;
        if ($attackertorps > $playerinfo['torps'])
        {
            $attackertorps = $playerinfo['torps'];
        }

        $playerinfo['torps'] = $playerinfo['torps'] - $attackertorps;
        $attackertorpdamage = $tkireg->torp_dmg_rate * $attackertorps;
        $attackerarmor = $playerinfo['armor_pts'];
        $attackerfighters = $playerinfo['ship_fighters'];

        // Setup target variables
        $targetbeams = \Tki\CalcLevels::abstractLevels($targetinfo['beams'], $tkireg);
        if ($targetbeams > $targetinfo['ship_energy'])
        {
            $targetbeams = $targetinfo['ship_energy'];
        }

        $targetinfo['ship_energy'] = $targetinfo['ship_energy'] - $targetbeams;
        $targetshields = \Tki\CalcLevels::abstractLevels($targetinfo['shields'], $tkireg);
        if ($targetshields > $targetinfo['ship_energy'])
        {
            $targetshields = $targetinfo['ship_energy'];
        }

        $targetinfo['ship_energy'] = $targetinfo['ship_energy'] - $targetshields;
        $targettorpnum = round(pow($tkireg->level_factor, $targetinfo['torp_launchers'])) * 2;
        if ($targettorpnum > $targetinfo['torps'])
        {
            $targettorpnum = $targetinfo['torps'];
        }

        $targetinfo['torps'] = $targetinfo['torps'] - $targettorpnum;
        $targettorpdmg = $tkireg->torp_dmg_rate * $targettorpnum;
        $targetarmor = $targetinfo['armor_pts'];
        $targetfighters = $targetinfo['ship_fighters'];

        // Begin combat procedures
        if ($attackerbeams > 0 && $targetfighters > 0)                  // Attacker has beams - target has fighters - beams vs. fighters
        {
            if ($attackerbeams > round($targetfighters / 2))           // Attacker beams GT half target fighters
            {
                $lost = $targetfighters - (round($targetfighters / 2));
                $targetfighters = $targetfighters - $lost;              // T loses half all fighters
            }
            else                                                        // Attacker beams LE half target fighters
            {
                $targetfighters = $targetfighters - $attackerbeams;     // T loses fighters EQ to A beams
                $attackerbeams = 0;                                     // A loses all beams
            }
        }

        if ($attackerfighters > 0 && $targetbeams > 0)                      // Target has beams - Attacker has fighters - beams vs. fighters
        {
            if ($targetbeams > round($attackerfighters / 2))                // Target beams GT half attacker fighters
            {
                $lost = $attackerfighters - (round($attackerfighters / 2));
                $attackerfighters = $attackerfighters - $lost;               // A loses half of all fighters
                $targetbeams = $targetbeams - $lost;                         // T loses beams EQ to half A fighters
            }
            else
            {                                                                 // Target beams LE half attacker fighters
                $attackerfighters = $attackerfighters - $targetbeams;         // A loses fighters EQ to T beams A loses fighters
                $targetbeams = 0;                                             // T loses all beams
            }
        }

        if ($attackerbeams > 0)
        {                                                                   // Attacker has beams left - continue combat - Beams vs. shields
            if ($attackerbeams > $targetshields)                            // Attacker beams GT target shields
            {
                $attackerbeams = $attackerbeams - $targetshields;           // A loses beams EQ to T shields
            }
            else
            {                                                               // Attacker beams LE target shields
                $attackerbeams = 0;                                         // A loses all beams
            }
        }

        if ($targetbeams > 0)
        {                                                                   // Target has beams left - continue combat - beams VS shields
            if ($targetbeams > $attackershields)
            {                                                               // Target beams GT Attacker shields
                $targetbeams = $targetbeams - $attackershields;             // T loses beams EQ to A shields
            }
            else
            {                                                               // Target beams LE Attacker shields
                $targetbeams = 0;                                           // T loses all beams
            }
        }

        if ($attackerbeams > 0)
        {                                                                   // Attacker has beams left - continue combat - beams VS armor
            if ($attackerbeams > $targetarmor)
            {                                                               // Attacker beams GT target armor
                $targetarmor = 0;                                           // T loses all armor (T DESTROYED)
            }
            else
            {                                                               // Attacker beams LE target armor
                $targetarmor = $targetarmor - $attackerbeams;               // T loses armor EQ to A beams
            }
        }

        if ($targetbeams > 0)
        {                                                                   // Target has beams left - continue combat - beams VS armor
            if ($targetbeams > $attackerarmor)
            {                                                               // Target beams GT Attacker armor
                $attackerarmor = 0;                                         // A loses all armor (A DESTROYED)
            }
            else
            {                                                               // Target beams LE Attacker armor
                $attackerarmor = $attackerarmor - $targetbeams;             // A loses armor EQ to T beams
            }
        }

        if ($targetfighters > 0 && $attackertorpdamage > 0)
        {                                                                   // Attacker fires torps - target has fighters - torps VS fighters
            if ($attackertorpdamage > round($targetfighters / 2))
            {                                                               // Attacker fired torps GT half target fighters
                $lost = $targetfighters - (round($targetfighters / 2));
                $targetfighters = $targetfighters - $lost;                  // T loses half all fighters
                $attackertorpdamage = $attackertorpdamage - $lost;          // A loses fired torps EQ to half T fighters
            }
            else
            {                                                               // Attacker fired torps LE half target fighters
                $targetfighters = $targetfighters - $attackertorpdamage;    // T loses fighters EQ to A torps fired
                $attackertorpdamage = 0;                                    // A loses all torps fired
            }
        }

        if ($attackerfighters > 0 && $targettorpdmg > 0)
        {                                                                   // Target fires torps - Attacker has fighters - torps VS fighters
            if ($targettorpdmg > round($attackerfighters / 2))
            {                                                               // Target fired torps GT half Attacker fighters
                $lost = $attackerfighters - (round($attackerfighters / 2));
                $attackerfighters = $attackerfighters - $lost;               // A loses half all fighters
                $targettorpdmg = $targettorpdmg - $lost;                     // T loses fired torps EQ to half A fighters
            }
            else
            {                                                                // Target fired torps LE half Attacker fighters
                $attackerfighters = $attackerfighters - $targettorpdmg;      // A loses fighters EQ to T torps fired
                $targettorpdmg = 0;                                          // T loses all torps fired
            }
        }

        if ($attackertorpdamage > 0)
        {                                                                   // Attacker fires torps - continue combat - torps VS armor
            if ($attackertorpdamage > $targetarmor)
            {                                                               // Attacker fired torps GT half target armor
                $targetarmor = 0;                                           // T loses all armor (T DESTROYED)
            }
            else
            {                                                                // Attacker fired torps LE half target armor
                $targetarmor = $targetarmor - $attackertorpdamage;           // T loses armor EQ to A torps fired
            }
        }

        if ($targettorpdmg > 0)
        {                                                                   // Target fires torps - continue combat - torps VS armor
            if ($targettorpdmg > $attackerarmor)
            {                                                               // Target fired torps GT half Attacker armor
                $attackerarmor = 0;                                         // A loses all armor (A DESTROYED)
            }
        }

        if ($attackerfighters > 0 && $targetfighters > 0)
        {                                                                   // Attacker has fighters - target has fighters - fighters VS fighters
            if ($attackerfighters > $targetfighters)
            {                                                               // Attacker fighters GT target fighters
                $temptargfighters = 0;                                      // T will lose all fighters
            }
            else
            {                                                               // Attacker fighters LE target fighters
                $temptargfighters = $targetfighters - $attackerfighters;    // T will lose fighters EQ to A fighters
            }

            if ($targetfighters > $attackerfighters)
            {                                                               // Target fighters GT Attacker fighters
                $tempplayfighters = 0;                                      // A will lose all fighters
            }
            else
            {                                                               // Target fighters LE Attacker fighters
                $tempplayfighters = $attackerfighters - $targetfighters;    // A will lose fighters EQ to T fighters
            }

            $attackerfighters = $tempplayfighters;
            $targetfighters = $temptargfighters;
        }

        if ($attackerfighters > 0)
        {                                                                   // Attacker has fighters - continue combat - fighters VS armor
            if ($attackerfighters > $targetarmor)
            {                                                               // Attacker fighters GT target armor
                $targetarmor = 0;                                           // T loses all armor (T DESTROYED)
            }
            else
            {                                                               // Attacker fighters LE target armor
                $targetarmor = $targetarmor - $attackerfighters;            // T loses armor EQ to A fighters
            }
        }

        if ($targetfighters > 0)
        {                                                                   // Target has fighters - continue combat - fighters VS armor
            if ($targetfighters > $attackerarmor)
            {                                                               // Target fighters GT Attacker armor
                $attackerarmor = 0;                                         // A loses all armor (A DESTROYED)
            }
            else
            {                                                               // Target fighters LE Attacker armor
                $attackerarmor = $attackerarmor - $targetfighters;          // A loses armor EQ to T fighters
            }
        }

        // Fix negative value vars
        if ($attackerfighters < 0)
        {
            $attackerfighters = 0;
        }

        if ($attackertorps < 0)
        {
            $attackertorps = 0;
        }

        if ($attackerarmor < 0)
        {
            $attackerarmor = 0;
        }

        if ($targetfighters < 0)
        {
            $targetfighters = 0;
        }

        if ($targettorpnum < 0)
        {
            $targettorpnum = 0;
        }

        if ($targetarmor < 0)
        {
            $targetarmor = 0;
        }

        // Desl with destroyed ships

        // Target ship was destroyed
        if (!$targetarmor > 0)
        {
            if ($targetinfo['dev_escapepod'] == "Y")
            // Target had no escape pod
            {
                $rating = round($targetinfo['rating'] / 2);
                $resc = $db->Execute("UPDATE {$db->prefix}ships SET hull = 0, engines = 0, power = 0, computer = 0, sensors = 0, beams = 0, torp_launchers = 0, torps = 0, armor = 0, armor_pts = 100, cloak = 0, shields = 0, sector = 1, ship_ore = 0, ship_organics = 0, ship_energy = 1000, ship_colonists = 0, ship_goods = 0, ship_fighters = 100, ship_damage = 0, on_planet='N', planet_id = 0, dev_warpedit = 0, dev_genesis = 0, dev_beacon = 0, dev_emerwarp = 0, dev_escapepod = 'N', dev_fuelscoop = 'N', dev_minedeflector = 0, ship_destroyed = 'N', rating = ?, dev_lssd='N' WHERE ship_id = ?;", array($rating, $targetinfo['ship_id']));
                \Tki\Db::logDbErrors($pdo_db, $resc, __LINE__, __FILE__);
                \Tki\PlayerLog::writeLog($pdo_db, $targetinfo['ship_id'], LogEnums::ATTACK_LOSE, "Kabal $playerinfo[character_name]|Y");
            }
            else
            // Target had no pod
            {
                \Tki\PlayerLog::writeLog($pdo_db, $targetinfo['ship_id'], LogEnums::ATTACK_LOSE, "Kabal $playerinfo[character_name]|N");
                $character_object->kill($pdo_db, $targetinfo['ship_id'], $langvars, $tkireg, false);
            }

            if ($attackerarmor > 0)
            {
                // Attacker still alive to salvage target
                $rating_change = round($targetinfo['rating'] * $tkireg->rating_combat_factor);
                $free_ore = round($targetinfo['ship_ore'] / 2);
                $free_organics = round($targetinfo['ship_organics'] / 2);
                $free_goods = round($targetinfo['ship_goods'] / 2);
                $free_holds = \Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
                $salv_goods = 0;
                if ($free_holds > $free_goods)
                {                                                        // Figure out what we can carry
                    $salv_goods = $free_goods;
                    $free_holds = $free_holds - $free_goods;
                }
                elseif ($free_holds > 0)
                {
                    $salv_goods = $free_holds;
                    $free_holds = 0;
                }

                $salv_ore = 0;
                if ($free_holds > $free_ore)
                {
                    $salv_ore = $free_ore;
                }
                elseif ($free_holds > 0)
                {
                    $salv_ore = $free_holds;
                }

                $salv_organics = 0;
                if ($free_holds > $free_organics)
                {
                    $salv_organics = $free_organics;
                }
                elseif ($free_holds > 0)
                {
                    $salv_organics = $free_holds;
                }

                $ship_value = $tkireg->upgrade_cost * (round(pow($tkireg->upgrade_factor, $targetinfo['hull'])) + round(pow($tkireg->upgrade_factor, $targetinfo['engines'])) + round(pow($tkireg->upgrade_factor, $targetinfo['power'])) + round(pow($tkireg->upgrade_factor, $targetinfo['computer'])) + round(pow($tkireg->upgrade_factor, $targetinfo['sensors'])) + round(pow($tkireg->upgrade_factor, $targetinfo['beams'])) + round(pow($tkireg->upgrade_factor, $targetinfo['torp_launchers'])) + round(pow($tkireg->upgrade_factor, $targetinfo['shields'])) + round(pow($tkireg->upgrade_factor, $targetinfo['armor'])) + round(pow($tkireg->upgrade_factor, $targetinfo['cloak'])));
                $ship_salvage_rate = random_int(10, 20);
                $ship_salvage = $ship_value * $ship_salvage_rate / 100;
                \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Attack successful, $targetinfo[character_name] was defeated and salvaged for $ship_salvage credits.");
                $resd = $db->Execute("UPDATE {$db->prefix}ships SET ship_ore = ship_ore + ?, ship_organics = ship_organics + ?, ship_goods = ship_goods + ?, credits = credits + ? WHERE ship_id = ?;", array($salv_ore, $salv_organics, $salv_goods, $ship_salvage, $playerinfo['ship_id']));
                \Tki\Db::logDbErrors($pdo_db, $resd, __LINE__, __FILE__);
                $armor_lost = $playerinfo['armor_pts'] - $attackerarmor;
                $fighters_lost = $playerinfo['ship_fighters'] - $attackerfighters;
                $energy = $playerinfo['ship_energy'];
                $rese = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy = ?, ship_fighters = ship_fighters - ?, torps = torps - ?, armor_pts = armor_pts - ?, rating = rating - ? WHERE ship_id = ?;", array($energy, $fighters_lost, $attackertorps, $armor_lost, $rating_change, $playerinfo['ship_id']));
                \Tki\Db::logDbErrors($pdo_db, $rese, __LINE__, __FILE__);
            }
        }

        // Target and attacker live
        if ($targetarmor > 0 && $attackerarmor > 0)
        {
            $rating_change = round($targetinfo['rating'] * .1);
            $armor_lost = $playerinfo['armor_pts'] - $attackerarmor;
            $fighters_lost = $playerinfo['ship_fighters'] - $attackerfighters;
            $energy = $playerinfo['ship_energy'];
            $target_rating_change = round($targetinfo['rating'] / 2);
            $target_armor_lost = $targetinfo['armor_pts'] - $targetarmor;
            $target_fighters_lost = $targetinfo['ship_fighters'] - $targetfighters;
            $target_energy = $targetinfo['ship_energy'];
            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Attack failed, $targetinfo[character_name] survived.");
            \Tki\PlayerLog::writeLog($pdo_db, $targetinfo['ship_id'], LogEnums::ATTACK_WIN, "Kabal $playerinfo[character_name]|$target_armor_lost|$target_fighters_lost");
            $resf = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy = ?, ship_fighters = ship_fighters - ?, torps = torps - ? , armor_pts = armor_pts - ?, rating=rating - ? WHERE ship_id = ?;", array($energy, $fighters_lost, $attackertorps, $armor_lost, $rating_change, $playerinfo['ship_id']));
            \Tki\Db::logDbErrors($pdo_db, $resf, __LINE__, __FILE__);
            $resg = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy = ?, ship_fighters = ship_fighters - ?, armor_pts=armor_pts - ?, torps=torps - ?, rating = ? WHERE ship_id = ?;", array($target_energy, $target_fighters_lost, $target_armor_lost, $targettorpnum, $target_rating_change, $targetinfo['ship_id']));
            \Tki\Db::logDbErrors($pdo_db, $resg, __LINE__, __FILE__);
        }

        // Attacker ship destroyed
        if (!$attackerarmor > 0)
        {
            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "$targetinfo[character_name] destroyed your ship!");
            $character_object->kill($pdo_db, $playerinfo['ship_id'], $langvars, $tkireg, false);
            if ($targetarmor > 0)
            {
                // Target still alive to salvage attacker
                $rating_change = round($playerinfo['rating'] * $tkireg->rating_combat_factor);
                $free_ore = round($playerinfo['ship_ore'] / 2);
                $free_organics = round($playerinfo['ship_organics'] / 2);
                $free_goods = round($playerinfo['ship_goods'] / 2);
                $free_holds = \Tki\CalcLevels::abstractLevels($targetinfo['hull'], $tkireg) - $targetinfo['ship_ore'] - $targetinfo['ship_organics'] - $targetinfo['ship_goods'] - $targetinfo['ship_colonists'];
                $salv_goods = 0;
                if ($free_holds > $free_goods)
                {                                                        // Figure out what target can carry
                    $salv_goods = $free_goods;
                    $free_holds = $free_holds - $free_goods;
                }
                elseif ($free_holds > 0)
                {
                    $salv_goods = $free_holds;
                    $free_holds = 0;
                }

                $salv_ore = 0;
                if ($free_holds > $free_ore)
                {
                    $salv_ore = $free_ore;
                    $free_holds = $free_holds - $free_ore;
                }
                elseif ($free_holds > 0)
                {
                    $salv_ore = $free_holds;
                    $free_holds = 0;
                }

                $salv_organics = 0;
                if ($free_holds > $free_organics)
                {
                    $salv_organics = $free_organics;
                }
                elseif ($free_holds > 0)
                {
                    $salv_organics = $free_holds;
                }

                $ship_value = $tkireg->upgrade_cost * (round(pow($tkireg->upgrade_factor, $playerinfo['hull'])) + round(pow($tkireg->upgrade_factor, $playerinfo['engines'])) + round(pow($tkireg->upgrade_factor, $playerinfo['power'])) + round(pow($tkireg->upgrade_factor, $playerinfo['computer'])) + round(pow($tkireg->upgrade_factor, $playerinfo['sensors'])) + round(pow($tkireg->upgrade_factor, $playerinfo['beams'])) + round(pow($tkireg->upgrade_factor, $playerinfo['torp_launchers'])) + round(pow($tkireg->upgrade_factor, $playerinfo['shields'])) + round(pow($tkireg->upgrade_factor, $playerinfo['armor'])) + round(pow($tkireg->upgrade_factor, $playerinfo['cloak'])));
                $ship_salvage_rate = random_int(10, 20);
                $ship_salvage = $ship_value * $ship_salvage_rate / 100;
                \Tki\PlayerLog::writeLog($pdo_db, $targetinfo['ship_id'], LogEnums::ATTACK_WIN, "Kabal $playerinfo[character_name]|$armor_lost|$fighters_lost");
                \Tki\PlayerLog::writeLog($pdo_db, $targetinfo['ship_id'], LogEnums::RAW, "You destroyed the Kabal ship and salvaged $salv_ore units of ore, $salv_organics units of organics, $salv_goods units of goods, and salvaged $ship_salvage_rate% of the ship for $ship_salvage credits.");
                $resh = $db->Execute("UPDATE {$db->prefix}ships SET ship_ore = ship_ore + ?, ship_organics = ship_organics + ?, ship_goods = ship_goods + ?, credits = credits + ? WHERE ship_id = ?;", array($salv_ore, $salv_organics, $salv_goods, $ship_salvage, $targetinfo['ship_id']));
                \Tki\Db::logDbErrors($pdo_db, $resh, __LINE__, __FILE__);
                $armor_lost = $targetinfo['armor_pts'] - $targetarmor;
                $fighters_lost = $targetinfo['ship_fighters'] - $targetfighters;
                $energy = $targetinfo['ship_energy'];
                $resi = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy = ? , ship_fighters = ship_fighters - ?, torps = torps - ?, armor_pts = armor_pts - ?, rating=rating - ? WHERE ship_id = ?;", array($energy, $fighters_lost, $targettorpnum, $armor_lost, $rating_change, $targetinfo['ship_id']));
                \Tki\Db::logDbErrors($pdo_db, $resi, __LINE__, __FILE__);
            }
        }
    }
}
