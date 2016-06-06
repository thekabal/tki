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
// File: self_destruct.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('self_destruct', 'ranking', 'common', 'global_includes', 'global_funcs', 'news', 'footer'));
$title = $langvars['l_die_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

echo "<h1>" . $title . "</h1>\n";

$result = $db->Execute("SELECT ship_id,character_name FROM {$db->prefix}ships WHERE email = ?;", array($_SESSION['username']));
Tki\Db::logDbErrors($pdo_db, $db, $result, __LINE__, __FILE__);
$playerinfo = $result->fields;

if (array_key_exists('sure', $_GET))
{
    $sure = $_GET['sure'];
}

if (!isset($sure))
{
    echo "<font color=red><strong>" . $langvars['l_die_rusure'] . "</strong></font><br><br>";
    echo "Please Note: You will loose all your Planets if you Self-Destruct!.<br>\n";
    echo "<a href='main.php'>" . $langvars['l_die_nonono'] . "</a> " . $langvars['l_die_what'] . "<br><br>";
    echo "<a href=self_destruct.php?sure=1>" . $langvars['l_yes'] . "!</a> " . $langvars['l_die_goodbye'] . "<br><br>";
}
elseif ($sure == 1)
{
    echo "<font color=red><strong>" . $langvars['l_die_check'] . "</strong></font><br><br>";
    echo "Please Note: You will loose all your Planets if you Self-Destruct!.<br>\n";
    echo "<a href='main.php'>" . $langvars['l_die_nonono'] . "</a> " . $langvars['l_die_what'] . "<br><br>";
    echo "<a href=self_destruct.php?sure=2>" . $langvars['l_yes'] . "!</a> " . $langvars['l_die_goodbye'] . "<br><br>";
}
elseif ($sure == 2)
{
    echo $langvars['l_die_count'] . "<br>";
    echo $langvars['l_die_vapor'] . "<br><br>";
    $langvars['l_die_please'] = str_replace("[logout]", "<a href='logout.php'>" . $langvars['l_logout'] . "</a>", $langvars['l_die_please']);
    echo $langvars['l_die_please'] . "<br>";
    Tki\Character::kill($pdo_db, $db, $playerinfo['ship_id'], $langvars, $tkireg, true);
    Tki\Bounty::cancel($pdo_db, $db, $playerinfo['ship_id']);
    Tki\AdminLog::writeLog($pdo_db, $db, LOG_ADMIN_HARAKIRI, "$playerinfo[character_name]|" . $_SERVER['REMOTE_ADDR'] . "");
    Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_HARAKIRI, $_SERVER['REMOTE_ADDR']);
    echo "Due to nobody looking after your Planets, all your Planets have reduced into dust and ruble. Your Planets are no more.<br>\n";
}
else
{
    echo $langvars['l_die_exploit'] . "<br><br>";
}

Tki\Text::gotoMain($pdo_db, $lang, $langvars);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
