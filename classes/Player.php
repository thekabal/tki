<?php declare(strict_types = 1);
/**
 * classes/Player.php from The Kabal Invasion.
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

namespace Tki;

use Symfony\Component\HttpFoundation\Request;

class Player
{
    public static function auth(\PDO $pdo_db, string $lang, Registry $tkireg, Timer $tkitimer, Smarty $template): array | bool
    {
        $request = Request::createFromGlobals();
        $error_status = null;

        if (array_key_exists('username', $_SESSION) === false)
        {
            $_SESSION['username'] = null;
        }

        if (array_key_exists('password', $_SESSION) === false)
        {
            $_SESSION['password'] = null;
        }

        if ($_SESSION['username'] !== null && $_SESSION['password'] !== null)
        {
            $players_gateway = new Players\PlayersGateway($pdo_db);
            $playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

            // Check the password against the stored hashed password
            // Check the cookie to see if username/password are empty - check password against database
            if (password_verify($_SESSION['password'], $playerinfo['password']))
            {
                $cur_time_stamp = date('Y-m-d H:i:s');
                $timestamp = array();
                $timestamp['now']  = (int) strtotime($cur_time_stamp);
                $timestamp['last'] = (int) strtotime($playerinfo['last_login']);

                // Update the players last_login every 60 seconds to cut back SQL Queries.
                if ($timestamp['now'] >= ($timestamp['last'] + 60))
                {
                    $remote_ip = $request->server->get('REMOTE_ADDR');
                    $sql = "UPDATE ::prefix::ships SET last_login = :last_login, ip_address = :ip_address WHERE ship_id = :ship_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':last_login', $cur_time_stamp, \PDO::PARAM_STR);
                    $stmt->bindParam(':ip_address', $remote_ip, \PDO::PARAM_STR);
                    $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                    // Reset the last activity time on the session so that the session renews - this is the
                    // replacement for the (now removed) update_cookie function.
                    $_SESSION['last_activity'] = $timestamp['now'];
                }

                return $playerinfo;
            }
            else
            {
                $langvars = Translate::load($pdo_db, $lang, array('universal', 'common'));
                $error_status .= str_replace('[here]', "<a href='index.php'>" . $langvars['l_here'] . '</a>', $langvars['l_universal_need_login']);
                $title = $langvars['l_error'];

                $header = new \Tki\Header();
                $header->display($pdo_db, $lang, $template, $title);

                echo $error_status;

                $footer = new \Tki\Footer();
                $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
                die();
            }
        }
        else
        {
            return array();
        }
    }

    public static function ban(\PDO $pdo_db, string $lang, array $timestamp, Smarty $template, array $playerinfo, Registry $tkireg, Timer $tkitimer): bool
    {
        // Check to see if the player is banned every 60 seconds (may need to ajust this).
        if ($timestamp['now'] >= ($timestamp['last'] + 60))
        {
            $ban_result = CheckBan::isBanned($pdo_db, $playerinfo);
            if ($ban_result === null)
            {
                return false;
            }
            else
            {
                // Set login status to false, then clear the session array, and clear the session cookie
                $_SESSION['logged_in'] = false;
                $_SESSION = array();
                setcookie('tkiSession', '', 0, '/');

                // Destroy the session entirely
                session_destroy();

                $error_status = "<div style='font-size:18px; color:#FF0000;'>\n";
                if (array_key_exists('ban_type', $ban_result))
                {
                    $error_status .= 'Your account has been Banned';
                }

                if (array_key_exists('public_info', $ban_result) && strlen(trim($ban_result['public_info'])) > 0)
                {
                    $error_status .= " for the following:<br>\n";
                    $error_status .= "<br>\n";
                    $error_status .= "<div style='font-size:16px; color:#FFFF00;'>";
                    $error_status .= $ban_result['public_info'] . "</div>\n";
                }

                $langvars = Translate::load($pdo_db, $lang, array('universal', 'common'));
                $error_status .= "</div>\n";
                $error_status .= "<br>\n";
                $error_status .= "<div style='color:#FF0000;'>Maybe you will behave yourself next time.</div>\n";
                $error_status .= "<br />\n";
                $error_status .= str_replace('[here]', "<a href='index.php'>" . $langvars['l_here'] . '</a>', $langvars['l_universal_main_login']);

                $title = $langvars['l_error'];

                $header = new \Tki\Header();
                $header->display($pdo_db, $lang, $template, $title);
                echo $error_status;

                $footer = new \Tki\Footer();
                $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
                return true;
            }
        }
        else
        {
            return false;
        }
    }
}
