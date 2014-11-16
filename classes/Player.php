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
// File: classes/Player.php

namespace Tki;

class Player
{
    public static function handleAuth(\PDO $pdo_db, $lang, $langvars, Reg $tkireg, $template)
    {
        $flag = true;
        $error_status = null;

        if (array_key_exists('username', $_SESSION) === false)
        {
            $_SESSION['username'] = null;
        }

        if (array_key_exists('password', $_SESSION) === false)
        {
            $_SESSION['password'] = null;
        }

        if (is_null($_SESSION['username']) === false && is_null($_SESSION['password']) === false)
        {
            $sql = "SELECT ip_address, password, last_login, ship_id, ship_destroyed, dev_escapepod FROM {$pdo_db->prefix}ships WHERE email=:email LIMIT 1";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':email', $_SESSION['username']);
            $stmt->execute();
            $playerinfo = $stmt->fetch();

            if ($playerinfo !== false)
            {
                // Check the password against the stored hashed password
                // Check the cookie to see if username/password are empty - check password against database
                if (password_verify($_SESSION['password'], $playerinfo['password']))
                {
                    $stamp = date('Y-m-d H:i:s');
                    $timestamp['now']  = (int) strtotime($stamp);
                    $timestamp['last'] = (int) strtotime($playerinfo['last_login']);

                    // Update the players last_login every 60 seconds to cut back SQL Queries.
                    if ($timestamp['now'] >= ($timestamp['last'] + 60))
                    {
                        $sql = "UPDATE {$pdo_db->prefix}ships SET last_login = :last_login, ip_address = :ip_address WHERE ship_id=:ship_id";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':last_login', $stamp);
                        $stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR']);
                        $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
                        $stmt->execute();
                        Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                        // Reset the last activity time on the session so that the session renews - this is the
                        // replacement for the (now removed) update_cookie function.
                        $_SESSION['last_activity'] = $timestamp['now'];
                    }
                    $flag = false;
                }
            }
        }

        if ($flag)
        {
            $title = $langvars['l_error'];
            $error_status .= str_replace('[here]', "<a href='index.php'>" . $langvars['l_here'] . '</a>', $langvars['l_global_needlogin']);
            $title = $langvars['l_error'];
            Header::display($pdo_db, $lang, $template, $title);
            echo $error_status;
            Footer::display($pdo_db, $lang, $tkireg, $template, $langvars);
            die();
        }
        else
        {
            return $playerinfo;
        }
    }

    public static function handleBan($pdo_db, $lang, $timestamp, $template, $playerinfo)
    {
        // Check to see if the player is banned every 60 seconds (may need to ajust this).
        if ($timestamp['now'] >= ($timestamp['last'] + 60))
        {
            $ban_result = CheckBan::isBanned($pdo_db, $playerinfo);
            if ($ban_result === false || (array_key_exists('ban_type', $ban_result) && $ban_result['ban_type'] === ID_WATCH))
            {
                return false;
            }
            else
            {
                // Set login status to false, then clear the session array, and clear the session cookie
                $_SESSION['logged_in'] = false;
                $_SESSION = array();
                setcookie('tki_session', '', 0, '/');

                // Destroy the session entirely
                session_destroy();

                $error_status = "<div style='font-size:18px; color:#FF0000;'>\n";
                if (array_key_exists('ban_type', $ban_result) && $ban_result['ban_type'] === ID_LOCKED)
                {
                    $error_status .= 'Your account has been Locked';
                }
                else
                {
                    $error_status .= 'Your account has been Banned';
                }

                if (array_key_exists('public_info', $ban_result) && mb_strlen(trim($ban_result['public_info'])) >0)
                {
                    $error_status .=" for the following:<br>\n";
                    $error_status .="<br>\n";
                    $error_status .="<div style='font-size:16px; color:#FFFF00;'>";
                    $error_status .= $ban_result['public_info'] . "</div>\n";
                }
                $error_status .= "</div>\n";
                $error_status .= "<br>\n";
                $error_status .= "<div style='color:#FF0000;'>Maybe you will behave yourself next time.</div>\n";
                $error_status .= "<br />\n";
                $error_status .= str_replace('[here]', "<a href='index.php'>" . $langvars['l_here'] . '</a>', $langvars['l_global_mlogin']);

                $title = $langvars['l_error'];
                Header::display($pdo_db, $lang, $template, $title);
                echo $error_status;
                Footer::display($pdo_db, $lang, $tkireg, $template);
                die();
            }
        }
    }
}
