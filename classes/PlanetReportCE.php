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
// File: classes/PlanetReportCE.php
//
// FUTURE: These are horribly bad. They should be broken out of classes, and turned mostly into template
// behaviors. But in the interest of saying goodbye to the includes directory, and raw functions, this
// will at least allow us to auto-load and use classes instead. Plenty to do in the future, though!

namespace Tki;

use Symfony\Component\HttpFoundation\Request;

class PlanetReportCE
{
    public static function buildBase(\PDO $pdo_db, $db, Array $langvars, int $planet_id, int $sector_id, Reg $tkireg)
    {
        $request = Request::createFromGlobals();

        echo "<br>";
        echo str_replace("[here]", "<a href='planet_report.php?preptype=1'>" . $langvars['l_here'] . "</a>", $langvars['l_pr_click_return_status']);
        echo "<br><br>";

        // Get playerinfo from database
        $sql = "SELECT * FROM ::prefix::ships WHERE email=:email LIMIT 1";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':email', $_SESSION['username']);
        $stmt->execute();
        $playerinfo = $stmt->fetch(\PDO::FETCH_ASSOC);

        $sql = "SELECT * FROM ::prefix::planets WHERE planet_id=:planet_id LIMIT 1";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':planet_id', $planet_id);
        $stmt->execute();
        $planetinfo = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Error out and return if the Player isn't the owner of the Planet
        // Verify player owns the planet which is to have the base created on.
        if ($planetinfo['owner'] != $playerinfo['ship_id'])
        {
            echo "<div style='color:#f00; font-size:16px;'>" . $langvars['l_pr_make_base_failed'] . "</div>\n";
            echo "<div style='color:#f00; font-size:16px;'>" . $langvars['l_pr_invalid_info'] . "</div>\n";

            return (bool) false;
        }

        if (!is_numeric($planet_id) || !is_numeric($sector_id))
        {
            $ip = $request->query->get('REMOTE_ADDR');
            $hack_id = 0x1337;
            \Tki\AdminLog::writeLog($pdo_db, LOG_ADMIN_PLANETCHEAT, "{$hack_id}|{$ip}|{$planet_id}|{$sector_id}|{$playerinfo['ship_id']}");
            echo "<div style='color:#f00; font-size:16px;'>" . $langvars['l_pr_make_base_failed'] . "</div>\n";

            return (bool) false;
        }  // Build a base

        \Tki\Realspace\realSpaceMove($pdo_db, $db, $langvars, $sector_id, $tkireg);
        echo "<br>";
        echo str_replace("[here]", "<a href='planet.php?planet_id=$planet_id'>" . $langvars['l_here'] . "</a>", $langvars['l_pr_click_return_planet']);
        echo "<br><br>";

        if ($planetinfo['ore'] >= $tkireg->base_ore && $planetinfo['organics'] >= $tkireg->base_organics && $planetinfo['goods'] >= $tkireg->base_goods && $planetinfo['credits'] >= $tkireg->base_credits)
        {
            // Create The Base
            $update1 = $db->Execute("UPDATE {$db->prefix}planets SET base='Y', ore= ? - ?, organics = ? - ?, goods = ? - ?, credits = ? - ? WHERE planet_id = ?;", array($planetinfo['ore'], $tkireg->base_ore, $planetinfo['organics'], $tkireg->base_organics, $planetinfo['goods'], $tkireg->base_goods, $planetinfo['credits'], $tkireg->base_credits, $planet_id));
            \Tki\Db::LogDbErrors($pdo_db, $update1, __LINE__, __FILE__);

            // Update User Turns
            $update1b = $db->Execute("UPDATE {$db->prefix}ships SET turns = turns - 1, turns_used = turns_used + 1 WHERE ship_id = ?;", array($playerinfo['ship_id']));
            \Tki\Db::LogDbErrors($pdo_db, $update1b, __LINE__, __FILE__);

            // Refresh Planet Info
            $sql = "SELECT * FROM ::prefix::planets WHERE planet_id=:planet_id LIMIT 1";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':planet_id', $planet_id);
            $stmt->execute();
            $planetinfo = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Notify User Of Base Results
            echo $langvars['l_planet_bbuild'] . "<br><br>";

            // Calc Ownership and Notify User Of Results
            $ownership = \Tki\Ownership::calc($pdo_db, $db, $playerinfo['sector'], $tkireg->min_bases_to_own, $langvars);
            if ($ownership !== null)
            {
                echo $ownership . "<p>";
            }

            return $planetinfo;
        }
    }

    public static function collectCredits(\PDO $pdo_db, $db, Array $langvars, $planetarray, Reg $tkireg)
    {
        $request = Request::createFromGlobals();

        $CS = "GO"; // Current State

        // Look up the info for the player that wants to collect the credits.
        $result1 = $db->SelectLimit("SELECT * FROM {$db->prefix}ships WHERE email = ?", 1, -1, array('email' => $_SESSION['username']));
        \Tki\Db::LogDbErrors($pdo_db, $result1, __LINE__, __FILE__);
        $playerinfo = $result1->fields;

        // Set s_p_pair as an array.
        $s_p_pair = array();

        // Create an array of sector -> planet pairs
        $temp_count = count($planetarray);
        for ($i = 0; $i < $temp_count; $i++)
        {
            $res = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id = ?;", array($planetarray[$i]));
            \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);

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
            $CS = \Tki\Realspace\realSpaceMove($pdo_db, $db, $langvars, $s_p_pair[$i][0], $tkireg);

            if ($CS == "HOSTILE")
            {
                $CS = "GO";
            }
            elseif ($CS == "GO")
            {
                $CS = self::takeCredits($pdo_db, $db, $langvars, $s_p_pair[$i][1]);
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

    public static function takeCredits(\PDO $pdo_db, $db, Array $langvars, int $planet_id)
    {
        // Get basic Database information (ship and planet)
        $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email = ?;", array($_SESSION['username']));
        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
        $playerinfo = $res->fields;

        $res = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id = ?;", array($planet_id));
        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
        $planetinfo = $res->fields;

        // Set the name for unamed planets to be "unnamed"
        if (empty ($planetinfo['name']))
        {
            $planetinfo['name'] = $langvars['l_unnamed'];
        }

        // Verify player is still in same sector as the planet
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
                    $res = $db->Execute("UPDATE {$db->prefix}planets SET credits = 0 WHERE planet_id = ?;", array($planetinfo['planet_id']));
                    \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);

                    // update the player info with updated credits
                    $res = $db->Execute("UPDATE {$db->prefix}ships SET credits = ? WHERE email = ?;", array($NewShipCredits, $_SESSION['username']));
                    \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);

                    // update the player info with updated turns
                    $res = $db->Execute("UPDATE {$db->prefix}ships SET turns = turns - 1 WHERE email = ?;", array($_SESSION['username']));
                    \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);

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
