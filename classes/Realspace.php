<?php
declare(strict_types = 1);
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
// File: classes/Realspace.php

namespace Tki;

class Realspace
{
    public static function realSpaceMove(\PDO $pdo_db, \ADODB_mysqli $db, Array $langvars, $destination, Reg $tkireg)
    {
        $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email = ?;", array($_SESSION['username']));
        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
        $playerinfo = $res->fields;

        $result2 = $db->Execute("SELECT angle1, angle2, distance FROM {$db->prefix}universe WHERE sector_id = ?;", array($playerinfo['sector']));
        \Tki\Db::LogDbErrors($pdo_db, $result2, __LINE__, __FILE__);
        $start = $result2->fields;

        $result3 = $db->Execute("SELECT angle1, angle2, distance FROM {$db->prefix}universe WHERE sector_id = ?;", array($destination));
        \Tki\Db::LogDbErrors($pdo_db, $result3, __LINE__, __FILE__);
        $finish = $result3->fields;

        $deg = pi() / 180;
        $sa1 = $start['angle1'] * $deg;
        $sa2 = $start['angle2'] * $deg;
        $fa1 = $finish['angle1'] * $deg;
        $fa2 = $finish['angle2'] * $deg;
        $x = ($start['distance'] * sin($sa1) * cos($sa2)) - ($finish['distance'] * sin($fa1) * cos($fa2));
        $y = ($start['distance'] * sin($sa1) * sin($sa2)) - ($finish['distance'] * sin($fa1) * sin($fa2));
        $z = ($start['distance'] * cos($sa1)) - ($finish['distance'] * cos($fa1));
        $distance = round(sqrt(pow($x, 2) + pow($y, 2) + pow($z, 2)));
        $shipspeed = pow($tkireg->level_factor, $playerinfo['engines']);
        $triptime = round($distance / $shipspeed);

        if ($triptime == 0 && $destination != $playerinfo['sector'])
        {
            $triptime = 1;
        }

        if ($playerinfo['dev_fuelscoop'] == "Y")
        {
            $energyscooped = $distance * 100;
        }
        else
        {
            $energyscooped = 0;
        }

        if ($playerinfo['dev_fuelscoop'] == "Y" && $energyscooped == 0 && $triptime == 1)
        {
            $energyscooped = 100;
        }

        $free_power = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg) - $playerinfo['ship_energy'];

        // Amount of energy that can be stored is less than the amount scooped. Amount scooped is set to what can be stored.
        if ($free_power < $energyscooped)
        {
            $energyscooped = $free_power;
        }

        // Make sure energyscooped is not null
        if (!isset ($energyscooped))
        {
            $energyscooped = 0;
        }

        // Make sure energyscooped is not negative, or decimal
        if ($energyscooped < 1)
        {
            $energyscooped = 0;
        }

        // Check to see if player is already in that sector
        if ($destination == $playerinfo['sector'])
        {
            $triptime = 0;
            $energyscooped = 0;
        }

        if ($triptime > $playerinfo['turns'])
        {
            $langvars['l_rs_movetime'] = str_replace("[triptime]", number_format($triptime, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_rs_movetime']);
            echo $langvars['l_rs_movetime'] . "<br><br>";
            echo $langvars['l_rs_noturns'];
            $resx = $db->Execute("UPDATE {$db->prefix}ships SET cleared_defenses=' ' WHERE ship_id = ?;", array($playerinfo['ship_id']));
            \Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);

            $retval = "BREAK-TURNS";
        }
        else
        {
            // Modified from traderoute.php - sector defense check
            $hostile = 0;

            $result99 = $db->Execute("SELECT * FROM {$db->prefix}sector_defense WHERE sector_id = ? AND ship_id <> ?;", array($destination, $playerinfo['ship_id']));
            \Tki\Db::LogDbErrors($pdo_db, $result99, __LINE__, __FILE__);
            if (!$result99->EOF)
            {
                $fighters_owner = $result99->fields;
                $nsresult = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($fighters_owner['ship_id']));
                \Tki\Db::LogDbErrors($pdo_db, $nsresult, __LINE__, __FILE__);
                $nsfighters = $nsresult->fields;
                if ($nsfighters['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
                {
                    $hostile = 1;
                }
            }

            $result98 = $db->Execute("SELECT * FROM {$db->prefix}sector_defense WHERE sector_id = ? AND ship_id <> ?;", array($destination, $playerinfo['ship_id']));
            \Tki\Db::LogDbErrors($pdo_db, $result98, __LINE__, __FILE__);
            if (!$result98->EOF)
            {
                $fighters_owner = $result98->fields;
                $nsresult = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($fighters_owner['ship_id']));
                \Tki\Db::LogDbErrors($pdo_db, $nsresult, __LINE__, __FILE__);
                $nsfighters = $nsresult->fields;
                if ($nsfighters['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
                {
                    $hostile = 1;
                }
            }

            if (($hostile > 0) && ($playerinfo['hull'] > $tkireg->mine_hullsize))
            {
                $retval = "HOSTILE";
                echo str_replace("[destination]", $destination, $langvars['l_pr_cannot_move_defenses']). "<br>";
            }
            else
            {
                $stamp = date("Y-m-d H:i:s");
                $update = $db->Execute("UPDATE {$db->prefix}ships SET last_login = ?, sector = ?, ship_energy = ship_energy + ?, turns = turns - ?, turns_used = turns_used + ? WHERE ship_id = ?;", array($stamp, $destination, $energyscooped, $triptime, $triptime, $playerinfo['ship_id']));
                \Tki\Db::LogDbErrors($pdo_db, $update, __LINE__, __FILE__);
                $langvars['l_rs_ready_result'] = null;
                $langvars['l_rs_ready_result'] = str_replace("[sector]", $destination, $langvars['l_rs_ready']);
                $langvars['l_rs_ready_result'] = str_replace("[triptime]", number_format($triptime, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_rs_ready_result']);
                $langvars['l_rs_ready_result'] = str_replace("[energy]", number_format($energyscooped, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_rs_ready_result']);
                echo $langvars['l_rs_ready_result'] . "<br>";
                $retval = "GO";
            }
        }

        return ($retval);
    }
}
