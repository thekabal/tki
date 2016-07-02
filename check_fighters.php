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
// File: check_fighters.php

if (strpos($_SERVER['PHP_SELF'], 'check_fighters.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('check_fighters', 'common', 'global_includes', 'global_funcs', 'combat', 'footer', 'news', 'regional'));

// Get sectorinfo from database
$sql = "SELECT * FROM {$pdo_db->prefix}universe WHERE sector_id=:sector_id LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':sector_id', $sector);
$stmt->execute();
$sectorinfo = $stmt->fetch(PDO::FETCH_ASSOC);

$result3 = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id = ? and defence_type ='F' ORDER BY quantity DESC;", array($sector));
Tki\Db::LogDbErrors($pdo_db, $result3, __LINE__, __FILE__);

// Put the defence information into the array "defences"
$i = 0;
$total_sector_fighters = 0;
$owner = true;

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$response = null;
$response = filter_input(INPUT_POST, 'response', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($response)) === 0)
{
    $response = false;
}

$destination = null;
if (array_key_exists('destination', $_REQUEST) === true)
{
    $destination = $_REQUEST['destination'];
}

$engage = null;
if (array_key_exists('engage', $_REQUEST) === true)
{
    $engage = $_REQUEST['engage'];
}

while (!$result3->EOF)
{
    $row = $result3->fields;
    $defences[$i] = $row;
    $total_sector_fighters += $defences[$i]['quantity'];
    if ($defences[$i]['ship_id'] != $playerinfo['ship_id'])
    {
        $owner = false;
    }
    $i++;
    $result3->MoveNext();
}

$num_defences = $i;
if ($num_defences > 0 && $total_sector_fighters > 0 && !$owner)
{
    // Find out if the fighter owner and player are on the same team
    // All sector defences must be owned by members of the same team
    $fm_owner = $defences[0]['ship_id'];
    $result2 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($fm_owner));
    Tki\Db::LogDbErrors($pdo_db, $result2, __LINE__, __FILE__);
    $fighters_owner = $result2->fields;
    if ($fighters_owner['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
    {
        switch ($response)
        {
            case "fight":
                $resx = $db->Execute("UPDATE {$db->prefix}ships SET cleared_defences = ' ' WHERE ship_id = ?;", array($playerinfo['ship_id']));
                Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                echo "<h1>" . $title . "</h1>\n";
                include_once './sector_fighters.php';
                break;

            case "retreat":
                $resx = $db->Execute("UPDATE {$db->prefix}ships SET cleared_defences = ' ' WHERE ship_id = ?;", array($playerinfo['ship_id']));
                Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                $stamp = date("Y-m-d H:i:s");
                $resx = $db->Execute("UPDATE {$db->prefix}ships SET last_login='$stamp', turns = turns - 2, turns_used = turns_used + 2, sector=? WHERE ship_id=?;", array($playerinfo['sector'], $playerinfo['ship_id']));
                Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                echo "<h1>" . $title . "</h1>\n";
                echo $langvars['l_chf_youretreatback'] . "<br>";
                Tki\Text::gotomain($pdo_db, $lang);
                die();

            case "pay":
                $resx = $db->Execute("UPDATE {$db->prefix}ships SET cleared_defences = ' ' WHERE ship_id = ?;", array($playerinfo['ship_id']));
                Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                $fighterstoll = $total_sector_fighters * $fighter_price * 0.6;
                if ($playerinfo['credits'] < $fighterstoll)
                {
                    echo $langvars['l_chf_notenoughcreditstoll'] . "<br>";
                    echo $langvars['l_chf_movefailed'] . "<br>";
                    // Undo the move
                    $resx = $db->Execute("UPDATE {$db->prefix}ships SET sector=? WHERE ship_id=?;", array($playerinfo['sector'], $playerinfo['ship_id']));
                    Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                    $ok = 0;
                }
                else
                {
                    $tollstring = number_format($fighterstoll, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
                    $langvars['l_chf_youpaidsometoll'] = str_replace("[chf_tollstring]", $tollstring, $langvars['l_chf_youpaidsometoll']);
                    echo $langvars['l_chf_youpaidsometoll'] . "<br>";
                    $resx = $db->Execute("UPDATE {$db->prefix}ships SET credits=credits - $fighterstoll WHERE ship_id = ?;", array($playerinfo['ship_id']));
                    Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                    Tki\Toll::distribute($pdo_db, $sector, $fighterstoll, $total_sector_fighters);
                    Tki\PlayerLog::WriteLog($pdo_db, $playerinfo['ship_id'], LOG_TOLL_PAID, "$tollstring|$sector");
                    $ok = 1;
                }
                break;

            case "sneak":
                $resx = $db->Execute("UPDATE {$db->prefix}ships SET cleared_defences = ' ' WHERE ship_id = ?;", array($playerinfo['ship_id']));
                Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                $success = Tki\Scan::success($fighters_owner['sensors'], $playerinfo['cloak']);
                if ($success < 5)
                {
                    $success = 5;
                }
                if ($success > 95)
                {
                    $success = 95;
                }
                $roll = random_int(1, 100);
                if ($roll < $success)
                {
                    // Sector defences detect incoming ship
                    echo "<h1>" . $title . "</h1>\n";
                    echo $langvars['l_chf_thefightersdetectyou'] . "<br>";
                    include_once './sector_fighters.php';
                    break;
                }
                else
                {
                    // Sector defences don't detect incoming ship
                    $ok = 1;
                }
                break;

            default:
                $interface_string = $calledfrom . '?sector=' . $sector . '&destination=' . $destination . '&engage=' . $engage;
                $resx = $db->Execute("UPDATE {$db->prefix}ships SET cleared_defences = ? WHERE ship_id = ?;", array($interface_string, $playerinfo['ship_id']));
                Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                $fighterstoll = $total_sector_fighters * $fighter_price * 0.6;
                echo "<h1>" . $title . "</h1>\n";
                echo "<form accept-charset='utf-8' action='{$calledfrom}' method='post'>";
                $langvars['l_chf_therearetotalfightersindest'] = str_replace("[chf_total_sector_fighters]", $total_sector_fighters, $langvars['l_chf_therearetotalfightersindest']);
                echo $langvars['l_chf_therearetotalfightersindest'] . "<br>";
                if ($defences[0]['fm_setting'] == "toll")
                {
                    $langvars['l_chf_creditsdemanded'] = str_replace("[chf_number_fighterstoll]", number_format($fighterstoll, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_chf_creditsdemanded']);
                    echo $langvars['l_chf_creditsdemanded'] . "<br>";
                }

                $langvars['l_chf_youcanretreat'] = str_replace("[retreat]", "<strong>Retreat</strong>", $langvars['l_chf_youcanretreat']);
                echo $langvars['l_chf_youcan'] . " <br><input type='radio' name='response' value='retreat'>" . $langvars['l_chf_youcanretreat'] . "<br></input>";
                if ($defences[0]['fm_setting'] == "toll")
                {
                    $langvars['l_chf_inputpay'] = str_replace("[pay]", "<strong>Pay</strong>", $langvars['l_chf_inputpay']);
                    echo "<input type='radio' name='response' checked value='pay'>" . $langvars['l_chf_inputpay'] . "<br></input>";
                }

                echo "<input type='radio' name='response' checked value='fight'>";
                $langvars['l_chf_inputfight'] = str_replace("[fight]", "<strong>Fight</strong>", $langvars['l_chf_inputfight']);
                echo $langvars['l_chf_inputfight'] . "<br></input>";

                echo "<input type=radio name=response checked value=sneak>";
                $langvars['l_chf_inputcloak'] = str_replace("[cloak]", "<strong>Cloak</strong>", $langvars['l_chf_inputcloak']);
                echo $langvars['l_chf_inputcloak'] . "<br></input><br>";

                echo "<input type='submit' value='" . $langvars['l_chf_go'] . "'><br><br>";
                echo "<input type='hidden' name='sector' value='{$sector}'>";
                echo "<input type='hidden' name='engage' value='1'>";
                echo "<input type='hidden' name='destination' value='{$destination}'>";
                echo "</form>";
                die();

        }
        // Clean up any sectors that have used up all mines or fighters
        $resx = $db->Execute("DELETE FROM {$db->prefix}sector_defence WHERE quantity <= 0 ");
        Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
    }
}
