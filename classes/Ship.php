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
    public static function isDestroyed(\PDO $pdo_db, $lang, Reg $tkireg, $langvars, $template, $playerinfo)
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

    public static function leavePlanet(\PDO $pdo_db, $db, $ship_id)
    {
        $own_pl_result = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE owner = ?", array($ship_id));
        Db::LogDbErrors($pdo_db, $own_pl_result, __LINE__, __FILE__);

        if ($own_pl_result instanceof \adodb\ADORecordSet)
        {
            while (!$own_pl_result->EOF)
            {
                $row = $own_pl_result->fields;
                $on_pl_result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE on_planet = 'Y' AND planet_id = ? AND ship_id <> ?", array($row['planet_id'], $ship_id));
                Db::LogDbErrors($pdo_db, $on_pl_result, __LINE__, __FILE__);
                if ($on_pl_result instanceof \adodb\ADORecordSet)
                {
                    while (!$on_pl_result->EOF)
                    {
                        $cur = $on_pl_result->fields;
                        $uppl_res = $db->Execute("UPDATE {$db->prefix}ships SET on_planet = 'N',planet_id = '0' WHERE ship_id = ?", array($cur['ship_id']));
                        Db::LogDbErrors($pdo_db, $uppl_res, __LINE__, __FILE__);
                        PlayerLog::WriteLog($pdo_db, $cur['ship_id'], LOG_PLANET_EJECT, $cur['sector'] .'|'. $row['character_name']);
                        $on_pl_result->MoveNext();
                    }
                }
                $own_pl_result->MoveNext();
            }
        }
    }
}
