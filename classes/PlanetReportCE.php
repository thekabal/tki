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
// File: classes/PlanetReportCE.php
//
// FUTURE: These are horribly bad. They should be broken out of classes, and turned mostly into template
// behaviors. But in the interest of saying goodbye to the includes directory, and raw functions, this
// will at least allow us to auto-load and use classes instead. Plenty to do in the future, though!

namespace Tki;

class PlanetReportCE
{
    public static function buildBase(\PDO $pdo_db, $db, $langvars, $planet_id, $sector_id, Reg $tkireg)
    {
        echo "<br>";
        echo str_replace("[here]", "<a href='planet_report.php?preptype=1'>" . $langvars['l_here'] . "</a>", $langvars['l_pr_click_return_status']);
        echo "<br><br>";

        // Get playerinfo from database
        $sql = "SELECT * FROM {$pdo_db->prefix}ships WHERE email=:email LIMIT 1";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':email', $_SESSION['username']);
        $stmt->execute();
        $playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

        $result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id = ?;", array($planet_id));
        \Tki\Db::LogDbErrors($pdo_db, $result3, __LINE__, __FILE__);
        $planetinfo = $result3->fields;

        // Error out and return if the Player isn't the owner of the Planet
        // Verify player owns the planet which is to have the base created on.
        if ($planetinfo['owner'] != $playerinfo['ship_id'])
        {
            echo "<div style='color:#f00; font-size:16px;'>" . $langvars['l_pr_make_base_failed'] . "</div>\n";
            echo "<div style='color:#f00; font-size:16px;'>" . $langvars['l_pr_invalid_info'] . "</div>\n";

            return (boolean) false;
        }

        if (!is_numeric($planet_id) || !is_numeric($sector_id))
        {
            $ip = $_SERVER['REMOTE_ADDR'];
            $hack_id = 0x1337;
            \Tki\AdminLog::writeLog($pdo_db, LOG_ADMIN_PLANETCHEAT, "{$hack_id}|{$ip}|{$planet_id}|{$sector_id}|{$playerinfo['ship_id']}");
            echo "<div style='color:#f00; font-size:16px;'>" . $langvars['l_pr_make_base_failed'] . "</div>\n";

            return (boolean) false;
        }  // Build a base

        self::realSpaceMove($pdo_db, $db, $langvars, $sector_id, $tkireg);
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
            $result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id = ?;", array($planet_id));
            \Tki\Db::LogDbErrors($pdo_db, $result3, __LINE__, __FILE__);
            $planetinfo = $result3->fields;

            // Notify User Of Base Results
            echo $langvars['l_planet_bbuild'] . "<br><br>";

            // Calc Ownership and Notify User Of Results
            $ownership = \Tki\Ownership::calc($pdo_db, $db, $playerinfo['sector'], $tkireg->min_bases_to_own, $langvars);
            if ($ownership !== null)
            {
                echo $ownership . "<p>";
            }
        }
    }

    public static function collectCredits(\PDO $pdo_db, $db, $langvars, $planetarray, Reg $tkireg)
    {
        $CS = "GO"; // Current State

        // Look up the info for the player that wants to collect the credits.
        $result1 = $db->SelectLimit("SELECT * FROM {$db->prefix}ships WHERE email = ?", 1, -1, array('email' => $_SESSION['username']));
        \Tki\Db::LogDbErrors($pdo_db, $result1, __LINE__, __FILE__);
        $playerinfo = $result1->fields;

        // Set var as an array.
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
                $ip = $_SERVER['REMOTE_ADDR'];
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
            $CS = self::realSpaceMove($pdo_db, $db, $langvars, $s_p_pair[$i][0], $tkireg);

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

    public static function changePlanetProduction(\PDO $pdo_db, $db, $langvars, $prodpercentarray, Reg $tkireg)
    {
    //  Declare default production values from the config.php file
    //
    //  We need to track what the player_id is and what team they belong to if they belong to a team,
    //    these two values are not passed in as arrays
    //    ship_id = the owner of the planet          ($ship_id = $prodpercentarray['ship_id'])
    //    team_id = the team creators ship_id ($team_id = $prodpercentarray['team_id'])
    //
    //  First we generate a list of values based on the commodity
    //    (ore, organics, goods, energy, fighters, torps, team, sells)
    //
    //  Second we generate a second list of values based on the planet_id
    //  Because team and ship_id are not arrays we do not pass them through the second list command.
    //  When we write the ore production percent we also clear the selling and team values out of the db
    //  When we pass through the team array we set the value to $team we grabbed out of the array.
    //  in the sells and team the prodpercent = the planet_id.
    //
    //  We run through the database checking to see if any planet production is greater than 100, or possibly negative
    //    if so we set the planet to the default values and report it to the player.
    //
    //  There has got to be a better way, but at this time I am not sure how to do it.
    //  Off the top of my head if we could sort the data passed in, in order of planets we could check before we do the writes
    //  This would save us from having to run through the database a second time checking our work.

    //  This should patch the game from being hacked with planet Hack.

        $result = $db->Execute("SELECT ship_id, team FROM {$db->prefix}ships WHERE email = ?;", array($_SESSION['username']));
        \Tki\Db::LogDbErrors($pdo_db, $result, __LINE__, __FILE__);
        $ship_id = $result->fields['ship_id'];

        $planet_hack = false;
        $hack_id = 0x0000;
        $hack_count = array(0, 0, 0);

        echo str_replace("[here]", "<a href='planet_report.php?preptype=2'>" . $langvars['l_here'] . "</a>", $langvars['l_pr_click_return_prod']);
        echo "<br><br>";

        while (list($commod_type, $valarray) = each($prodpercentarray))
        {
            if ($commod_type != "team_id" && $commod_type != "ship_id")
            {
                while (list($planet_id, $prodpercent) = each($valarray))
                {
                    if ($commod_type == "prod_ore" || $commod_type == "prod_organics" || $commod_type == "prod_goods" || $commod_type == "prod_energy" || $commod_type == "prod_fighters" || $commod_type == "prod_torp")
                    {
                        $res = $db->Execute("SELECT COUNT(*) AS owned_planet FROM {$db->prefix}planets WHERE planet_id = ? AND owner = ?;", array($planet_id, $ship_id));
                        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
                        if ($res->fields['owned_planet'] == 0)
                        {
                            $ip = $_SERVER['REMOTE_ADDR'];
                            $planet_hack = true;
                            $hack_id = 0x18582;
                            $hack_count[0]++;
                            \Tki\AdminLog::writeLog($pdo_db, LOG_ADMIN_PLANETCHEAT, "{$hack_id}|{$ip}|{$planet_id}|{$ship_id}|commod_type={$commod_type}");
                        }

                        $resx = $db->Execute("UPDATE {$db->prefix}planets SET {$commod_type} = ? WHERE planet_id = ? AND owner = ?;", array($prodpercent, $planet_id, $ship_id));
                        \Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);

                        $resy = $db->Execute("UPDATE {$db->prefix}planets SET sells='N' WHERE planet_id = ? AND owner = ?;", array($planet_id, $ship_id));
                        \Tki\Db::LogDbErrors($pdo_db, $resy, __LINE__, __FILE__);

                        $resz = $db->Execute("UPDATE {$db->prefix}planets SET team=0 WHERE planet_id = ? AND owner = ?;", array($planet_id, $ship_id));
                        \Tki\Db::LogDbErrors($pdo_db, $resz, __LINE__, __FILE__);
                    }
                    elseif ($commod_type == "sells")
                    {
                        $resx = $db->Execute("UPDATE {$db->prefix}planets SET sells='Y' WHERE planet_id = ? AND owner = ?;", array($prodpercent, $ship_id));
                        \Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                    }
                    elseif ($commod_type == "team")
                    {
                        // Compare entered team_id and one in the db, if different then use one from db
                        $res = $db->Execute("SELECT {$db->prefix}ships.team as owner FROM {$db->prefix}ships, {$db->prefix}planets WHERE ( {$db->prefix}ships.ship_id = {$db->prefix}planets.owner ) AND ( {$db->prefix}planets.planet_id = ?);", array($prodpercent));
                        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
                        if ($res)
                        {
                            $team_id = $res->fields['owner'];
                        }
                        else
                        {
                            $team_id = 0;
                        }

                        $resx = $db->Execute("UPDATE {$db->prefix}planets SET team = ? WHERE planet_id = ? AND owner = ?;", array($team_id, $prodpercent, $ship_id));
                        \Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                        if (array_key_exists("team_id", $prodpercentarray) === true && $prodpercentarray['team_id'] != $team_id)
                        {
                            // They are different so send admin a log
                            $ip = $_SERVER['REMOTE_ADDR'];
                            $planet_hack = true;
                            $hack_id = 0x18531;
                            $hack_count[1]++;
                            \Tki\AdminLog::writeLog($pdo_db, LOG_ADMIN_PLANETCHEAT, "{$hack_id}|{$ip}|{$prodpercent}|{$ship_id}|{$prodpercentarray['team_id']} not {$team_id}");
                        }
                    }
                    else
                    {
                        $ip = $_SERVER['REMOTE_ADDR'];
                        $planet_hack = true;
                        $hack_id = 0x18598;
                        $hack_count[2]++;
                        \Tki\AdminLog::writeLog($pdo_db, LOG_ADMIN_PLANETCHEAT, "{$hack_id}|{$ip}|{$planet_id}|{$ship_id}|commod_type={$commod_type}");
                    }
                }
            }
        }

        if ($planet_hack)
        {
            $serial_data = serialize($prodpercentarray);
            \Tki\AdminLog::writeLog($pdo_db, LOG_ADMIN_PLANETCHEAT + 1000, "{$ship_id}|{$serial_data}");
            printf("<font color=\"red\"><strong>Your Cheat has been logged to the admin (%08x) [%02X:%02X:%02X].</strong></font><br>\n", (int) $hack_id, (int) $hack_count[0], (int) $hack_count[1], (int) $hack_count[2]);
        }

        echo "<br>";
        echo $langvars['l_pr_prod_updated'] . "<br><br>";
        echo $langvars['l_pr_checking_values'] . "<br><br>";

        $res = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE owner = ? ORDER BY sector_id;", array($ship_id));
        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
        $i = 0;
        $planet = array();
        $planets = array();
        if ($res)
        {
            while (!$res->EOF)
            {
                $planets[$i] = $res->fields;
                $i++;
                $res->MoveNext();
            }

            foreach ($planets as $planet)
            {
                if (empty ($planet['name']))
                {
                    $planet['name'] = $langvars['l_unnamed'];
                }

                if ($planet['prod_ore'] < 0)
                {
                    $planet['prod_ore'] = 110;
                }

                if ($planet['prod_organics'] < 0)
                {
                    $planet['prod_organics'] = 110;
                }

                if ($planet['prod_goods'] < 0)
                {
                    $planet['prod_goods'] = 110;
                }

                if ($planet['prod_energy'] < 0)
                {
                    $planet['prod_energy'] = 110;
                }

                if ($planet['prod_fighters'] < 0)
                {
                    $planet['prod_fighters'] = 110;
                }

                if ($planet['prod_torp'] < 0)
                {
                    $planet['prod_torp'] = 110;
                }

                if ($planet['prod_ore'] + $planet['prod_organics'] + $planet['prod_goods'] + $planet['prod_energy'] + $planet['prod_fighters'] + $planet['prod_torp'] > 100)
                {
                    $temp1 = str_replace("[planet_name]", $planet['name'], $langvars['l_pr_value_reset']);
                    $temp2 = str_replace("[sector_id]", $planet['sector_id'], $temp1);
                    echo $temp2 . "<br>";

                    $resa = $db->Execute("UPDATE {$db->prefix}planets SET prod_ore = ? WHERE planet_id = ?;", array($tkireg->default_prod_ore, $planet['planet_id']));
                    \Tki\Db::LogDbErrors($pdo_db, $resa, __LINE__, __FILE__);

                    $resb = $db->Execute("UPDATE {$db->prefix}planets SET prod_organics = ? WHERE planet_id = ?;", array($tkireg->default_prod_organics, $planet['planet_id']));
                    \Tki\Db::LogDbErrors($pdo_db, $resb, __LINE__, __FILE__);

                    $resc = $db->Execute("UPDATE {$db->prefix}planets SET prod_goods = ? WHERE planet_id = ?;", array($tkireg->default_prod_goods, $planet['planet_id']));
                    \Tki\Db::LogDbErrors($pdo_db, $resc, __LINE__, __FILE__);

                    $resd = $db->Execute("UPDATE {$db->prefix}planets SET prod_energy = ? WHERE planet_id = ?;", array($tkireg->default_prod_energy, $planet['planet_id']));
                    \Tki\Db::LogDbErrors($pdo_db, $resd, __LINE__, __FILE__);

                    $rese = $db->Execute("UPDATE {$db->prefix}planets SET prod_fighters = ? WHERE planet_id = ?;", array($tkireg->default_prod_fighters, $planet['planet_id']));
                    \Tki\Db::LogDbErrors($pdo_db, $rese, __LINE__, __FILE__);

                    $resf = $db->Execute("UPDATE {$db->prefix}planets SET prod_torp = ? WHERE planet_id = ?;", array($tkireg->default_prod_torp, $planet['planet_id']));
                    \Tki\Db::LogDbErrors($pdo_db, $resf, __LINE__, __FILE__);
                }
            }
        }
    }

    public static function takeCredits(\PDO $pdo_db, $db, $langvars, $planet_id)
    {
        $planet = array();

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
            $planet['name'] = $langvars['l_unnamed'];
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

    public static function realSpaceMove(\PDO $pdo_db, $db, $langvars, $destination, Reg $tkireg)
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
            $resx = $db->Execute("UPDATE {$db->prefix}ships SET cleared_defences=' ' WHERE ship_id = ?;", array($playerinfo['ship_id']));
            \Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);

            $retval = "BREAK-TURNS";
        }
        else
        {
            // Modified from traderoute.php - sector defense check
            $hostile = 0;

            $result99 = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id = ? AND ship_id <> ?;", array($destination, $playerinfo['ship_id']));
            \Tki\Db::LogDbErrors($pdo_db, $result99, __LINE__, __FILE__);
            if (!$result99->EOF)
            {
                $fighters_owner = $result99->fields;
                $nsresult = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($fighters_owner['ship_id']));
                \Tki\Db::LogDbErrors($pdo_db, $nsresult, __LINE__, __FILE__);
                $nsfighters = $nsresult->fields;
                if ($nsfighters['team'] != $playerinfo['team'] || $playerinfo['team']==0)
                {
                    $hostile = 1;
                }
            }

            $result98 = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id = ? AND ship_id <> ?;", array($destination, $playerinfo['ship_id']));
            \Tki\Db::LogDbErrors($pdo_db, $result98, __LINE__, __FILE__);
            if (!$result98->EOF)
            {
                $fighters_owner = $result98->fields;
                $nsresult = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($fighters_owner['ship_id']));
                \Tki\Db::LogDbErrors($pdo_db, $nsresult, __LINE__, __FILE__);
                $nsfighters = $nsresult->fields;
                if ($nsfighters['team'] != $playerinfo['team'] || $playerinfo['team']==0)
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
