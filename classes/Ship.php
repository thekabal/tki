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
// File: classes/Ship.php

namespace Tki;

class Ship
{
    public static function isDestroyed(\PDO $pdo_db, $lang, Reg $tkireg, $langvars, $template, $playerinfo) : bool
    {
        // Check for destroyed ship
        if ($playerinfo['ship_destroyed'] === 'Y')
        {
            // if the player has an escapepod, set the player up with a new ship
            if ($playerinfo['dev_escapepod'] === 'Y')
            {
                $sql = "UPDATE {$pdo_db->prefix}ships SET hull=0, engines=0, power=0," .
                               "computer=0, sensors=0, beams=0, torp_launchers=0, torps=0, armor=0, " .
                               "armor_pts=100, cloak=0, shields=0, sector=1, ship_ore=0, " .
                               "ship_organics=0, ship_energy=1000, ship_colonists=0, ship_goods=0, " .
                               "ship_fighters=100, ship_damage=0, on_planet='N', dev_warpedit=0, " .
                               "dev_genesis=0, dev_beacon=0, dev_emerwarp=0, dev_escapepod='N', " .
                               "dev_fuelscoop='N', dev_minedeflector=0, ship_destroyed='N', " .
                               "dev_lssd='N' WHERE email=:email";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':email', $_SESSION['username']);
                $stmt->execute();
                Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                // $error_status = str_replace('[here]', "<a href='main.php'>" . $langvars['l_here'] . '</a>', $langvars['l_login_died']); Error status is not used anywhere
            }
            else
            {
                // if the player doesn't have an escapepod - they're dead, delete them.
                // But we can't delete them yet. (This prevents the self-distruct inherit bug)
                $error_status = str_replace('[here]', "<a href='log.php'>" .
                                 ucfirst($langvars['l_here']) . '</a>', $langvars['l_global_died']) .
                                 '<br><br>' . $langvars['l_global_died2'];
                $error_status .= str_replace('[logout]', "<a href='logout.php'>" .
                                 $langvars['l_logout'] . '</a>', $langvars['l_die_please']);
                $title = $langvars['l_error'];
                Header::display($pdo_db, $lang, $template, $title);
                echo $error_status;
                Footer::display($pdo_db, $lang, $tkireg, $template);
                die();
            }
        }
        else
        {
            return false;
        }
    }

    // FUTURE: Reduce the number of SQL calls needed to accomplish this. Maybe do the update without two selects?
    public static function leavePlanet(\PDO $pdo_db, $ship_id)
    {
        $sql = "SELECT * FROM {$pdo_db->prefix}planets WHERE owner=:owner";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':owner', $ship_id);
        $stmt->execute();
        $planets_owned = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($planets_owned !== null)
        {
            foreach ($planets_owned as $tmp_planet)
            {
                $sql = "SELECT * FROM {$pdo_db->prefix}ships WHERE on_planet='Y' AND planet_id = :planet_id AND ship_id <> :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':planet_id', $planet_id);
                $stmt->bindParam(':ship_id', $ship_id);
                $stmt->execute();
                $ships_on_planet = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($ships_on_planet !== null)
                {
                    foreach ($ships_on_planet as $tmp_ship)
                    {
                        $sql = "UPDATE {$pdo_db->prefix}ships SET on_planet='N', planet_id = '0' WHERE ship_id = :ship_id";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':ship_id', $tmp_ship['ship_id']);
                        $stmt->execute();
                        PlayerLog::WriteLog($pdo_db, $tmp_ship['ship_id'], LOG_PLANET_EJECT, $tmp_ship['sector'] .'|'. $tmp_ship['character_name']);
                    }
                }
            }
        }
    }
}
