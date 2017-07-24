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
// File: mail.php

require_once './common.php';

$title = $langvars['l_mail_title'];

$header = new Tki\Header;
$header->display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('mail', 'common', 'global_funcs', 'global_includes', 'global_funcs', 'combat', 'footer', 'news'));
echo "<h1>" . $title . "</h1>\n";

$mail = filter_input(INPUT_GET, 'mail', FILTER_SANITIZE_EMAIL);

$result = $db->SelectLimit("SELECT character_name, email, password, ship_id FROM {$db->prefix}ships WHERE email = ?", 1, -1, array('email' => $mail));
Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);

if (!$result->EOF)
{
    if ($mail == $tkireg->admin_mail)
    {
        echo "<div style='font-size:14px; font-weight:bold; color:#f00;'>";
        echo $langvars['l_mail_admin_denied'];
        echo "</div><br>\n";

        if (array_key_exists('logged_in', $_SESSION) === true)
        {
            echo str_replace("[here]", "<a href='main.php'>" . $langvars['l_here'] . "</a>", $langvars['l_global_mmenu']);
        }
        else
        {
            echo str_replace("[here]", "<a href='index.php'>" . $langvars['l_here'] . "</a>", $langvars['l_global_mlogin']);
        }
    }
    else
    {
        $playerinfo = $result->fields;
        $link_to_reset = "https://" . $request->server->get('HTTP_HOST') . Tki\SetPaths::setGamepath();
        $link_to_reset .= "pwreset.php?code=" . mb_substr(md5($playerinfo['password']), 5, 8);

        $langvars['l_mail_message'] = str_replace("[link]", htmlentities($link_to_reset, ENT_QUOTES | ENT_HTML5, 'UTF-8'), $langvars['l_mail_message']);
        $langvars['l_mail_message'] = str_replace("[name]", $playerinfo['character_name'], $langvars['l_mail_message']);
        $langvars['l_mail_message'] = str_replace("[ip]", $request->server->get('REMOTE_ADDR'), $langvars['l_mail_message']);
        $langvars['l_mail_message'] = str_replace("[game_name]", $tkireg->game_name, $langvars['l_mail_message']);

        // Some reason \r\n is broken, so replace them now.
        $langvars['l_mail_message'] = str_replace('\r\n', "\r\n", $langvars['l_mail_message']);

        // Need to set the topic with the game name.
        $langvars['l_mail_topic'] = str_replace("[game_name]", $tkireg->game_name, $langvars['l_mail_topic']);

        // Recovery time is a timestamp at the time of recovery attempt, which is valid for 30 minutes
        // After 30 minutes, it will be cleared to null by scheduler. If it is used, it will also be cleared.
        $sql = "UPDATE ::prefix::ships SET recovery_time=:time WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':time', time(), \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $result = $stmt->execute();
        Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        mail($playerinfo['email'], $langvars['l_mail_topic'], $langvars['l_mail_message'] . "\r\n\r\n" . htmlentities($link_to_reset, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "\r\n", "From: {$tkireg->admin_mail}\r\nReply-To: {$tkireg->admin_mail}\r\nX-Mailer: PHP/" . phpversion());
        echo "<div style='color:#fff; text-align:left;'>" . $langvars['l_mail_sent'] . " <span style='color:#0f0;'>{$mail}</span></div>\n";
        echo "<br>\n";
        echo "<div style='font-size:14px; font-weight:bold; color:#f00;'>";
        echo $langvars['l_mail_note_1'] . "<br><br>";
        // $langvars['l_mail_note_2'] = htmlentities($langvars['l_mail_note_2'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        echo mb_strtoupper($langvars['l_mail_note_2']);
        echo "</div>\n";
    }
}
else
{
    $langvars['l_mail_noplayer'] = str_replace("[here]", "<a href='new.php'>" . $langvars['l_here'] . "</a>", $langvars['l_mail_noplayer']);
    echo "<div style='color:#FFF; width:400px; text-align:left; font-size:12px; padding:6px;'>" . $langvars['l_mail_noplayer'] . "</div>\n";

    echo "<br>\n";
    if ($_SESSION['logged_in'] !== null && $_SESSION['logged_in'] === true)
    {
        echo str_replace("[here]", "<a href='main.php'>" . $langvars['l_here'] . "</a>", $langvars['l_global_mmenu']);
    }
    else
    {
        echo str_replace("[here]", "<a href='index.php'>" . $langvars['l_here'] . "</a>", $langvars['l_global_mlogin']);
    }
}

$footer = new Tki\Footer;
$footer->display($pdo_db, $lang, $tkireg, $template);
