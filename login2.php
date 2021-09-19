<?php declare(strict_types = 1);
/**
 * login2.php from The Kabal Invasion.
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

require_once './common.php';

// Test to see if server is closed to logins
$playerfound = false;

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'footer',
                                'insignias', 'login', 'login2', 'news',
                                'universal'));

// Detect if the server is configured using HTTP only - HTTPS is required for TKI to work correctly.
if(!isset($_SERVER['HTTPS']))
{
    die($langvars['l_login2_tls']);
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$filtered_post_password = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_URL);

if ($email !== null && $email !== false)
{
    $players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
    $playerinfo = $players_gateway->selectPlayerInfo($email);
    $playerfound = true;
    $lang = $playerinfo['lang'];
}
else
{
    $playerinfo = array();
    $playerfound = false;
    $lang = filter_input(INPUT_POST, 'lang', FILTER_SANITIZE_STRING);
}

if ($lang !== null)
{
    $link = '?lang=' . $lang;
}
else
{
    $lang = $tkireg->default_lang;
    $link = null;
}

if ($tkireg->game_closed)
{
    $title = $langvars['l_login_sclosed'];

    $header = new Tki\Header();
    $header->display($pdo_db, $lang, $template, $title);
    echo "<div style='text-align:center; color:#ff0; font-size:20px;'><br>" . $langvars['l_login_closed_message'] . "</div><br>\n";
    echo str_replace("[here]", "<a href='index.php'>" . $langvars['l_here'] . "</a>", $langvars['l_universal_main_login']);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
    die();
}

$title = $langvars['l_login_title2'];

// Check Banned
$banned = 0;

if (!empty($playerinfo) && $playerfound !== false)
{
    $res = $old_db->Execute("SELECT * FROM {$old_db->prefix}ip_bans WHERE ? LIKE ban_mask OR ? LIKE ban_mask;", array($request->server->get('REMOTE_ADDR'), $playerinfo['ip_address']));
    Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
    if ($res->RecordCount() != 0)
    {
        $banned = 1;
    }
}

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);
echo "<h1>" . $title . "</h1>\n";

if ($playerfound)
{
    if (password_verify($filtered_post_password, $playerinfo['password']))
    {
        $ban_result = Tki\CheckBan::isBanned($pdo_db, $playerinfo);
        if ($ban_result === null)
        {
            if ($playerinfo['ship_destroyed'] == 'N')
            {
                // Player's ship has not been destroyed
                Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::LOGIN, $request->server->get('REMOTE_ADDR'));
                $cur_time_stamp = date("Y-m-d H:i:s");
                $remote_addr = $request->server->get('REMOTE_ADDR');

                $sql = "UPDATE ::prefix::ships SET last_login = :time, ip_address = :ip_address WHERE ship_id = :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':time', $cur_time_stamp, \PDO::PARAM_STR);
                $stmt->bindParam(':ip_address', $remote_addr, \PDO::PARAM_INT);
                $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                $result = $stmt->execute();
                Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                $_SESSION['logged_in'] = true;
                $_SESSION['password'] = $filtered_post_password;
                $_SESSION['username'] = $playerinfo['email'];
                Tki\Text::gotoMain($pdo_db, $lang);

                // They have logged in successfully, so update their session ID as well
                $tkiSession->regen();
                header("Location: main.php"); // This redirect avoids any rendering for the user of login2. Its a direct transition, visually
            }
            else
            {
                // Player's ship has been destroyed
                if ($playerinfo['dev_escapepod'] == "Y")
                {
                    $rating = round($playerinfo['rating'] / 2);
                    $ships_gateway = new \Tki\Ships\ShipsGateway($pdo_db);
                    $ships_gateway->updateDestroyedShip($playerinfo['ship_id']);
                    $langvars['l_login_died'] = str_replace("[here]", "<a href='main.php'>" . $langvars['l_here'] . "</a>", $langvars['l_login_died']);
                    echo $langvars['l_login_died'];
                }
                else
                {
                    $langvars['l_login2_died'] = str_replace("[here]", "<a href='log.php'>" . $langvars['l_here'] . "</a>", $langvars['l_login2_died']);
                    echo $langvars['l_login2_died'] . "<br><br>";

                    // Check if $newbie_nice is set, if so, verify ship limits
                    if ($tkireg->newbie_nice)
                    {
                        $sql = "SELECT hull, engines, power, computer, sensors, armor, shields, beams, torp_launchers, cloak " .
                               "FROM {$old_db->prefix}ships WHERE ship_id = ? AND hull <= ? AND engines <= ? " .
                               "AND power <= ? AND computer <= ? AND sensors <= ? AND armor <= ? " .
                               "AND shields <= ? AND beams <= ? AND torp_launchers <= ? AND cloak <= ?;";
                        $newbie_info = $old_db->Execute($sql, array($playerinfo['ship_id'], $tkireg->newbie_hull, $tkireg->newbie_engines, $tkireg->newbie_power, $tkireg->newbie_computer, $tkireg->newbie_sensors, $tkireg->newbie_armor, $tkireg->newbie_shields, $tkireg->newbie_beams, $tkireg->newbie_torp_launchers, $tkireg->newbie_cloak));
                        Tki\Db::logDbErrors($pdo_db, $newbie_info, __LINE__, __FILE__);
                        $num_rows = $newbie_info->RecordCount();

                        if ($num_rows)
                        {
                            echo "<br><br>" . $langvars['l_login_newbie'] . "<br><br>";

                            $sql = "UPDATE ::prefix::ships SET hull=0," .
                                   "engines=0, power=0, computer=0, sensors=0," .
                                   "beams=0, torp_launchers=0, torps=0, armor=0," .
                                   "armor_pts=100, cloak=0, shields=0, sector=1," .
                                   "ship_ore=0, ship_organics=0, ship_energy=1000," .
                                   "ship_colonists=0, ship_goods=0," .
                                   "ship_fighters=100, ship_damage=0, credits=1000," .
                                   "on_planet='N', dev_warpedit=0, dev_genesis=0," .
                                   "dev_beacon=0, dev_emerwarp=0, dev_escapepod='N'," .
                                   "dev_fuelscoop='N', dev_minedeflector=0," .
                                   "ship_destroyed='N', dev_lssd='N' " .
                                   "WHERE ship_id = :ship_id";
                            $stmt = $pdo_db->prepare($sql);
                            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                            $result = $stmt->execute();
                            Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                            $langvars['l_login_newlife'] = str_replace("[here]", "<a href='main.php'>" . $langvars['l_here'] . "</a>", $langvars['l_login_newlife']);
                            echo $langvars['l_login_newlife'];
                        }
                        else
                        {
                            echo "<br><br>" . $langvars['l_login_looser'] . "<br><br>" . $langvars['l_login_looser2'];
                        }
                    }
                    else
                    {
                        echo "<br><br>" . $langvars['l_login_looser'] . "<br><br>" . $langvars['l_login_looser2'];
                    }
                }
            }
        }
        else
        {
            echo "<div style='font-size:18px; color:#FF0000;'>\n";
            if (array_key_exists('ban_type', $ban_result))
            {
                echo $langvars['l_login2_banned'];
            }

            if (array_key_exists('public_info', $ban_result) && strlen(trim($ban_result['public_info'])) > 0)
            {
                echo " " . $langvars['l_login2_why'] . "<br>\n";
                echo "<br>\n";
                echo "<div style='font-size:16px; color:#FFFF00;'>{$ban_result['public_info']}</div>\n";
            }

            echo "</div>\n";
            echo "<br>\n";
            echo "<div style='color:#FF0000;'>" . $langvars['l_login2_behave'] . "</div>\n";
            echo "<br>\n";
            echo str_replace("[here]", "<a href='index.php'>" . $langvars['l_here'] . "</a>", $langvars['l_universal_main_login']);
        }
    }
    else
    {
        // password is incorrect
        echo $langvars['l_login_4gotpw1a'] . "<br><br>" . $langvars['l_login_4gotpw1b'] . " <a href='mail.php?mail=" . $email . "'>" . $langvars['l_clickme'] . "</a> " . $langvars['l_login_4gotpw2a'] . "<br><br>" . $langvars['l_login_4gotpw2b'] . " <a href='index.php'>" . $langvars['l_clickme'] . "</a> " . $langvars['l_login_4gotpw3'] . " " . $request->server->get('REMOTE_ADDR') . "...";
        Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::BADLOGIN, $request->server->get('REMOTE_ADDR'));
        $admin_log = new Tki\AdminLog();
        $admin_log->writeLog($pdo_db, (1000 + \Tki\LogEnums::BADLOGIN), "{$request->server->get('REMOTE_ADDR')}|{$email}|{$filtered_post_password}");
    }
}
else
{
    // FUTURE: Add handling to pass the email address to the new signup.
    $langvars['l_login_noone'] = str_replace("[here]", "<a href='new.php" . $link . "'>" . $langvars['l_here'] . "</a>", $langvars['l_login_noone']);
    echo "<strong>" . $langvars['l_login_noone'] . "</strong><br>";
}

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
