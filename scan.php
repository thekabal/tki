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
// File: scan.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

$title = $langvars['l_scan_title'];

$header = new Tki\Header;
$header->display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('scan', 'common', 'bounty', 'report', 'main', 'global_includes', 'global_funcs', 'footer', 'news', 'planet', 'regional'));

// Get playerinfo from database
$sql = "SELECT * FROM ::prefix::ships WHERE email=:email LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$filtered_ship_id = null;
$filtered_ship_id = filter_input(INPUT_GET, 'ship_id', FILTER_SANITIZE_EMAIL);
if (($filtered_ship_id === null) || (mb_strlen(trim($filtered_ship_id)) === 0))
{
    $filtered_ship_id = false;
}

$result2 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($filtered_ship_id));
Tki\Db::logDbErrors($pdo_db, $result2, __LINE__, __FILE__);
$targetinfo = $result2->fields;

$targetinfo['ship_id'] = (int) $targetinfo['ship_id'];
$targetinfo['cloak'] = (int) $targetinfo['cloak'];

$playerscore = Tki\Score::updateScore($pdo_db, $playerinfo['ship_id'], $tkireg, $playerinfo);
$targetscore = Tki\Score::updateScore($pdo_db, $targetinfo['ship_id'], $tkireg, $playerinfo);

$playerscore = $playerscore * $playerscore;
$targetscore = $targetscore * $targetscore;

echo "<h1>" . $title . "</h1>\n";

// Kami Multi Browser Window Attack Fix
if (array_key_exists('ship_selected', $_SESSION) === false || $_SESSION['ship_selected'] != $_GET['ship_id'])
{
    echo "You need to Click on the ship first.<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer;
    $footer->display($pdo_db, $lang, $tkireg, $template);
    die();
}

unset($_SESSION['ship_selected']);

// Check to ensure target is in the same sector as player
if ($targetinfo['sector'] != $playerinfo['sector'])
{
    echo $langvars['l_planet_noscan'];
}
else
{
    if ($playerinfo['turns'] < 1)
    {
        echo $langvars['l_scan_turn'];
    }
    else
    {
        // Determine per cent chance of success in scanning target ship - based on player's sensors and opponent's cloak
        $success = Tki\Scan::success($playerinfo['sensors'], $targetinfo['cloak']);
        if ($success < 5)
        {
            $success = 5;
        }

        if ($success > 95)
        {
            $success = 95;
        }

        $roll = random_int(1, 100);
        if ($roll > $success)
        {
            // If scan fails - inform both player and target.
            echo $langvars['l_planet_noscan'];
            Tki\PlayerLog::writeLog($pdo_db, $targetinfo['ship_id'], \Tki\LogEnums::SHIP_SCAN_FAIL, $playerinfo['character_name']);
        }
        else
        {
            // If scan succeeds, show results and inform target. Scramble results by scan error factor.

            // Get total bounty on this player, if any
            $btyamount = 0;
            $hasbounty = $db->Execute("SELECT SUM(amount) AS btytotal FROM {$db->prefix}bounty WHERE bounty_on = ?", array($targetinfo['ship_id']));
            Tki\Db::logDbErrors($pdo_db, $hasbounty, __LINE__, __FILE__);

            if ($hasbounty)
            {
                $resx = $hasbounty->fields;
                if ($resx['btytotal'] > 0)
                {
                    $btyamount = number_format($resx['btytotal'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
                    $langvars['l_scan_bounty'] = str_replace("[amount]", $btyamount, $langvars['l_scan_bounty']);
                    echo $langvars['l_scan_bounty'] . "<br>";
                    $btyamount = 0;

                    // Check for Federation bounty
                    $hasfedbounty = $db->Execute("SELECT SUM(amount) AS btytotal FROM {$db->prefix}bounty WHERE bounty_on = ? AND placed_by = 0", array($targetinfo['ship_id']));
                    Tki\Db::logDbErrors($pdo_db, $hasfedbounty, __LINE__, __FILE__);
                    if ($hasfedbounty)
                    {
                        $resy = $hasfedbounty->fields;
                        if ($resy['btytotal'] > 0)
                        {
                            $btyamount = $resy['btytotal'];
                            echo $langvars['l_scan_fedbounty'] . "<br>";
                        }
                    }
                }
            }

            // Player will get a Federation Bounty on themselves if they attack a player who's score is less than bounty_ratio of
            // themselves. If the target has a Federation Bounty, they can attack without attracting a bounty on themselves.
            if ($btyamount == 0 && ((($targetscore / $playerscore) < $tkireg->bounty_ratio) || $targetinfo['turns_used'] < $tkireg->bounty_minturns))
            {
                echo $langvars['l_by_fedbounty'] . "<br><br>";
            }
            else
            {
                echo $langvars['l_by_nofedbounty'] . "<br><br>";
            }

            $sc_error = Tki\Scan::error($playerinfo['sensors'], $targetinfo['cloak'], $tkireg->scan_error_factor);
            echo $langvars['l_scan_ron'] . " " . $targetinfo['ship_name'] . ", " . $langvars['l_scan_capt'] . " " . $targetinfo['character_name'] . "<br><br>";
            echo "<strong>" . $langvars['l_ship_levels'] . ":</strong><br><br>";
            echo "<table  width=\"\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">";
            echo "<tr><td>" . $langvars['l_hull'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_hull = round($targetinfo['hull'] * $sc_error / 100);
                echo "<td>$sc_hull</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_engines'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_engines = round($targetinfo['engines'] * $sc_error / 100);
                echo "<td>$sc_engines</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_power'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_power = round($targetinfo['power'] * $sc_error / 100);
                echo "<td>$sc_power</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_computer'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_computer = round($targetinfo['computer'] * $sc_error / 100);
                echo "<td>$sc_computer</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_sensors'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_sensors = round($targetinfo['sensors'] * $sc_error / 100);
                echo "<td>$sc_sensors</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_beams'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_beams = round($targetinfo['beams'] * $sc_error / 100);
                echo "<td>$sc_beams</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_torp_launch'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_torp_launchers = round($targetinfo['torp_launchers'] * $sc_error / 100);
                echo "<td>$sc_torp_launchers</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_armor'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_armor = round($targetinfo['armor'] * $sc_error / 100);
                echo "<td>$sc_armor</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_shields'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_shields = round($targetinfo['shields'] * $sc_error / 100);
                echo "<td>$sc_shields</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_cloak'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_cloak = round($targetinfo['cloak'] * $sc_error / 100);
                echo "<td>" . $sc_cloak . "</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "</table><br>";
            echo "<strong>" . $langvars['l_scan_arma'] . "</strong><br><br>";
            echo "<table  width=\"\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">";
            echo "<tr><td>" . $langvars['l_armorpts'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_armor_pts = round($targetinfo['armor_pts'] * $sc_error / 100);
                echo "<td>" . $sc_armor_pts . "</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_fighters'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_ship_fighters = round($targetinfo['ship_fighters'] * $sc_error / 100);
                echo "<td>$sc_ship_fighters</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_torps'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_torps = round($targetinfo['torps'] * $sc_error / 100);
                echo "<td>$sc_torps</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "</table><br>";
            echo "<strong>" . $langvars['l_scan_carry'] . "</strong><br><br>";
            echo "<table  width=\"\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">";
            echo "<tr><td>Credits:</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_credits = round($targetinfo['credits'] * $sc_error / 100);
                echo "<td>$sc_credits</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_colonists'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_ship_colonists = round($targetinfo['ship_colonists'] * $sc_error / 100);
                echo "<td>$sc_ship_colonists</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_energy'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_ship_energy = round($targetinfo['ship_energy'] * $sc_error / 100);
                echo "<td>$sc_ship_energy</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_ore'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_ship_ore = round($targetinfo['ship_ore'] * $sc_error / 100);
                echo "<td>$sc_ship_ore</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_organics'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_ship_organics = round($targetinfo['ship_organics'] * $sc_error / 100);
                echo "<td>$sc_ship_organics</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_goods'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_ship_goods = round($targetinfo['ship_goods'] * $sc_error / 100);
                echo "<td>$sc_ship_goods</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "</table><br>";
            echo "<strong>" . $langvars['l_devices'] . ":</strong><br><br>";
            echo "<table  width=\"\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">";
            echo "<tr><td>" . $langvars['l_warpedit'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_dev_warpedit = round($targetinfo['dev_warpedit'] * $sc_error / 100);
                echo "<td>$sc_dev_warpedit</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_genesis'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_dev_genesis = round($targetinfo['dev_genesis'] * $sc_error / 100);
                echo "<td>$sc_dev_genesis</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_deflect'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_dev_minedeflector = round($targetinfo['dev_minedeflector'] * $sc_error / 100);
                echo "<td>$sc_dev_minedeflector</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_ewd'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                $sc_dev_emerwarp = round($targetinfo['dev_emerwarp'] * $sc_error / 100);
                echo "<td>$sc_dev_emerwarp</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_escape_pod'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                echo "<td>$targetinfo[dev_escapepod]</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "<tr><td>" . $langvars['l_fuel_scoop'] . ":</td>";
            $roll = random_int(1, 100);
            if ($roll < $success)
            {
                echo "<td>" . $targetinfo['dev_fuelscoop'] . "</td></tr>";
            }
            else
            {
                echo "<td>???</td></tr>";
            }

            echo "</table><br>";
            Tki\PlayerLog::writeLog($pdo_db, $targetinfo['ship_id'], \Tki\LogEnums::SHIP_SCAN, "$playerinfo[character_name]");
        }

        $sql = "UPDATE ::prefix::ships SET turns=turns-1, turns_used=turns_used+1 WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $result = $stmt->execute();
        Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    }
}

echo "<br><br>";
Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer;
$footer->display($pdo_db, $lang, $tkireg, $template);
