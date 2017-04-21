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
// File: classes/PlanetReportCE.php

namespace Tki;

use Symfony\Component\HttpFoundation\Request;

class PlanetReportCE
{
    public static function collectCredits(\PDO $pdo_db, $db, array $langvars, array $planetarray, Reg $tkireg): void
    {
        $request = Request::createFromGlobals();

        $CS = "GO"; // Current State

        // Look up the info for the player that wants to collect the credits.
        $sql = "SELECT * FROM ::prefix::ships WHERE email=:email LIMIT 1";
        $stmt = $pdo_db->prepare($sql);
        $sql_test = \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        if ($sql_test === true)
        {
            $stmt->bindParam(':email', $_SESSION['username'], \PDO::PARAM_STR);
            $stmt->execute();
            $playerinfo = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        // Set s_p_pair as an array.
        $s_p_pair = array();

        // Create an array of sector -> planet pairs
        $temp_count = count($planetarray);
        for ($i = 0; $i < $temp_count; $i++)
        {
            $res = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id = ?;", array($planetarray[$i]));
            \Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);

            // Only add to array if the player owns the planet.
            if ($res->fields['owner'] == $playerinfo['ship_id'] && $res->fields['sector_id'] < $tkireg->max_sectors)
            {
                $s_p_pair[$i] = array($res->fields['sector_id'], $planetarray[$i]);
            }
            else
            {
                $hack_id = 20100401;
                $ip = $request->query->get('REMOTE_ADDR');
                $planet_id = $res->fields['planet_id'];
                $sector_id = $res->fields['sector_id'];
                \Tki\AdminLog::writeLog($pdo_db, LOG_ADMIN_PLANETCHEAT, "{$hack_id}|{$ip}|{$planet_id}|{$sector_id}|{$playerinfo['ship_id']}");
                break;
            }
        }

        // Sort the array so that it is in order of sectors, lowest number first, not closest
        sort($s_p_pair);
        reset($s_p_pair);

        // Run through the list of sector planet pairs realspace moving to each sector and then performing the transfer.
        // Based on the way realspace works we don't need a sub loop -- might add a subloop to clean things up later.

        $temp_count2 = count($s_p_pair);
        for ($i = 0; $i < $temp_count2 && $CS == "GO"; $i++)
        {
            echo "<br>";
            $CS = \Tki\Realspace::realSpaceMove($pdo_db, $langvars, $s_p_pair[$i][0], $tkireg);

            if ($CS == "HOSTILE")
            {
                $CS = "GO";
            }
            elseif ($CS == "GO")
            {
                $CS = self::takeCredits($pdo_db, $langvars, $s_p_pair[$i][1]);
            }
            else
            {
                echo "<br>" . $langvars['l_pr_low_turns'] . "<br>";
            }

            echo "<br>";
        }

        if ($CS != "GO" && $CS != "HOSTILE")
        {
            echo "<br>" . $langvars['l_pr_low_turns'] . "<br>";
        }

        echo "<br>";
        echo str_replace("[here]", "<a href='planet_report.php?preptype=1'>" . $langvars['l_here'] . "</a>", $langvars['l_pr_click_return_status']);
        echo "<br><br>";
    }

    public static function takeCredits(\PDO $pdo_db, array $langvars, int $planet_id): string
    {
        // Get playerinfo from database
        $sql = "SELECT * FROM ::prefix::ships WHERE email=:email LIMIT 1";
        $stmt = $pdo_db->prepare($sql);
        $sql_test = \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        if ($sql_test === true)
        {
            $stmt->bindParam(':email', $_SESSION['username'], \PDO::PARAM_STR);
            $stmt->execute();
            $playerinfo = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        // Get planetinfo from database
        $sql = "SELECT * FROM ::prefix::planets WHERE planet_id=:planet_id LIMIT 1";
        $stmt = $pdo_db->prepare($sql);
        $sql_test = \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        if ($sql_test === true)
        {
            $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_STR);
            $stmt->execute();
            $planetinfo = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        // Set the name for unamed planets to be "unnamed"
        if (empty($planetinfo['name']))
        {
            $planetinfo['name'] = $langvars['l_unnamed'];
        }

        // Verify player is still in the same sector as the planet
        if ($playerinfo['sector'] == $planetinfo['sector_id'])
        {
            if ($playerinfo['turns'] >= 1)
            {
                // Verify player owns the planet to take credits from
                if ($planetinfo['owner'] == $playerinfo['ship_id'])
                {
                    // Get number of credits from the planet and current number player has on ship
                    $CreditsTaken = $planetinfo['credits'];
                    $CreditsOnShip = $playerinfo['credits'];
                    $NewShipCredits = $CreditsTaken + $CreditsOnShip;

                    // Update the planet record for credits
                    $sql = "UPDATE ::prefix::planets SET credits = 0 WHERE planet_id = :planet_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':planet_id', $planetinfo['planet_id'], \PDO::PARAM_INT);
                    $update = $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);

                    // Update the player info with updated credits
                    $sql = "UPDATE ::prefix::ships SET credits = :newshipcredits WHERE email = :username";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':newshipcredits', $NewShipCredits, \PDO::PARAM_INT);
                    $stmt->bindParam(':username', $_SESSION['username'], \PDO::PARAM_INT);
                    $update = $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);

                    // Update the player info with updated turns
                    $sql = "UPDATE ::prefix::ships SET turns = turns - 1 WHERE email = :username";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':username', $_SESSION['username'], \PDO::PARAM_INT);
                    $update = $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);

                    $tempa1 = str_replace("[credits_taken]", number_format($CreditsTaken, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_pr_took_credits']);
                    $tempa2 = str_replace("[planet_name]", $planetinfo['name'], $tempa1);
                    echo $tempa2 . "<br>";

                    $tempb1 = str_replace("[ship_name]", $playerinfo['ship_name'], $langvars['l_pr_have_credits_onboard']);
                    $tempb2 = str_replace("[new_ship_credits]", number_format($NewShipCredits, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $tempb1);
                    echo $tempb2 . "<br>";
                    $retval = "GO";
                }
                else
                {
                    echo "<br><br>" . str_replace("[planet_name]", $planetinfo['name'], $langvars['l_pr_not_your_planet']) . "<br><br>";
                    $retval = "BREAK-INVALID";
                }
            }
            else
            {
                $tempc1 = str_replace("[planet_name]", $planetinfo['name'], $langvars['l_pr_not_enough_turns']);
                $tempc2 = str_replace("[sector_id]", $planetinfo['sector_id'], $tempc1);
                echo "<br><br>" . $tempc2 . "<br><br>";
                $retval = "BREAK-TURNS";
            }
        }
        else
        {
            echo "<br><br>" . $langvars['l_pr_must_same_sector'] . "<br><br>";
            $retval = "BREAK-SECTORS";
        }

        return ($retval);
    }
}
