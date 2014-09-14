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
// File: new2.php

require_once './common.php';

$title = $langvars['l_new_title2'];
Bnt\Header::display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Bnt\Translate::load($pdo_db, $lang, array('new', 'login', 'common', 'global_includes', 'combat', 'footer', 'news'));
echo '<h1>' . $title . '</h1>';

if ($bntreg->account_creation_closed)
{
    die ($langvars['l_new_closed_message']); // This should ideally use a class based error handler instead
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$character = null;
$character = filter_input(INPUT_POST, 'character', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($character)) === 0)
{
    $character = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$shipname = null;
$shipname = filter_input(INPUT_POST, 'shipname', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($shipname)) === 0)
{
    $shipname = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$username = null;
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_EMAIL);
if (mb_strlen(trim($username)) === 0)
{
    $username = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$filtered_post_password = null;
$filtered_post_password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_URL);
if (mb_strlen(trim($filtered_post_password)) === 0)
{
    $filtered_post_password = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$newlang = null;
$newlang = filter_input(INPUT_POST, 'newlang', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($newlang)) === 0)
{
    $newlang = false;
}

if ($newlang !== null && $newlang !== null)
{
    $lang   = $newlang;
}
else
{
    $lang = $bntreg->default_lang;
}

$result = $db->Execute("SELECT email, character_name, ship_name FROM {$db->prefix}ships WHERE email=? || character_name=? || ship_name=?;", array($username, $character, $shipname));
Bnt\Db::logDbErrors($db, $result, __LINE__, __FILE__);
$flag = 0;

if ($username === null || $character === null || $shipname === null)
{
    echo $langvars['l_new_blank'] . '<br>';
    $flag = 1;
}

while (($result instanceof ADORecordSet) && !$result->EOF)
{
    $row = $result->fields;
    if (mb_strtolower($row['email']) == mb_strtolower($username))
    {
        echo $langvars['l_new_inuse'] . ' ' .  $langvars['l_new_4gotpw1'] . ' <a href=mail.php?mail=' . $username . '>' . $langvars['l_clickme'] . '</a> ' . $langvars['l_new_4gotpw2'] . '<br>';
        $flag = 1;
    }
    if (mb_strtolower($row['character_name']) == mb_strtolower($character))
    {
        $langvars['l_new_inusechar'] = str_replace('[character]', $character, $langvars['l_new_inusechar']);
        echo $langvars['l_new_inusechar'] . '<br>';
        $flag = 1;
    }
    if (mb_strtolower($row['ship_name']) == mb_strtolower($shipname))
    {
        $langvars['l_new_inuseship'] = str_replace('[shipname]', $shipname, $langvars['l_new_inuseship']);
        echo $langvars['l_new_inuseship'] . '<br>';
        $flag = 1;
    }
    $result->MoveNext();
}

if ($flag == 0)
{
    // Insert code to add player to database
    $stamp = date('Y-m-d H:i:s');
    $query = $db->Execute("SELECT MAX(turns_used + turns) AS mturns FROM {$db->prefix}ships;");
    Bnt\Db::logDbErrors($db, $query, __LINE__, __FILE__);
    $res = $query->fields;

    $mturns = $res['mturns'];

    if ($mturns > $bntreg->max_turns)
    {
        $mturns = $bntreg->max_turns;
    }

    // Hash the password.  $hashed_pass will be a 60-character string.
    $hashed_pass = password_hash($filtered_post_password, PASSWORD_DEFAULT); // PASSWORD_DEFAULT is the strongest algorithm available to PHP at the current time - today, it is BCRYPT.

    $result2 = $db->Execute("INSERT INTO {$db->prefix}ships (ship_name, ship_destroyed, character_name, password, email, armor_pts, credits, ship_energy, ship_fighters, turns, on_planet, dev_warpedit, dev_genesis, dev_beacon, dev_emerwarp, dev_escapepod, dev_fuelscoop, dev_minedeflector, last_login, ip_address, trade_colonists, trade_fighters, trade_torps, trade_energy, cleared_defences, lang, dev_lssd)
                             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);", array($shipname, 'N', $character, $hashed_pass, $username, $bntreg->start_armor, $bntreg->start_credits, $bntreg->start_energy, $bntreg->start_fighters, $mturns, 'N', $bntreg->start_editors, $bntreg->start_genesis, $bntreg->start_beacon, $bntreg->start_emerwarp, $bntreg->start_escape_pod, $bntreg->start_scoop, $bntreg->start_minedeflectors, $stamp, $_SERVER['REMOTE_ADDR'], 'Y', 'N', 'N', 'Y', NULL, $lang, $bntreg->start_lssd));
    Bnt\Db::logDbErrors($db, $result2, __LINE__, __FILE__);

    if (!$result2)
    {
        echo $db->ErrorMsg() . '<br>';
    }
    else
    {
        $result2 = $db->Execute("SELECT ship_id FROM {$db->prefix}ships WHERE email = ?;", array($username));
        Bnt\Db::logDbErrors($db, $result2, __LINE__, __FILE__);

        $shipid = $result2->fields;

        // To do: build a bit better "new player" message
        $langvars['l_new_message'] = str_replace('[pass]', $filtered_post_password, $langvars['l_new_message']);
        $langvars['l_new_message'] = str_replace('[ip]', $_SERVER['REMOTE_ADDR'], $langvars['l_new_message']);

        // Some reason \r\n is broken, so replace them now.
        $langvars['l_new_message'] = str_replace('\r\n', "\r\n", $langvars['l_new_message']);

        $link_to_game = 'http://' . $_SERVER['HTTP_HOST'] . Bnt\SetPaths::setGamepath();
        $langvars['l_new_message'] = str_replace('[website]', $link_to_game, $langvars['l_new_message']);
        $langvars['l_new_message'] = str_replace('[npg]', $link_to_game . 'newplayerguide.php', $langvars['l_new_message']);
        $langvars['l_new_message'] = str_replace('[faq]', $link_to_game . 'faq.php', $langvars['l_new_message']);
        $langvars['l_new_message'] = str_replace('[forums]', 'http://kabal.tk/forums/', $langvars['l_new_message']);

        mail("$username", $langvars['l_new_topic'], $langvars['l_new_message'] . "\r\n\r\n" . $link_to_game, 'From: ' . $bntreg->admin_mail . "\r\nReply-To: " . $bntreg->admin_mail . "\r\nX-Mailer: PHP/" . phpversion());

        Bnt\LogMove::writeLog($db, $shipid['ship_id'], 0); // A new player is placed into sector 0. Make sure his movement log shows it, so they see it on the galaxy map.
        $resx = $db->Execute("INSERT INTO {$db->prefix}zones VALUES (NULL, ?, ?, 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 0);", array($character ."\'s Territory", $shipid['ship_id']));
        Bnt\Db::logDbErrors($db, $resx, __LINE__, __FILE__);

        $resx = $db->Execute("INSERT INTO {$db->prefix}ibank_accounts (ship_id,balance,loan) VALUES (?,0,0);", array($shipid['ship_id']));
        Bnt\Db::logDbErrors($db, $resx, __LINE__, __FILE__);

        // Add presets for new player
        for ($zz=0; $zz<$bntreg->preset_max; $zz++)
        {
            $sql = "INSERT INTO {$pdo_db->prefix}presets (ship_id, preset, type) " .
                   "VALUES (:ship_id, :preset, :type)";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindValue(':ship_id', $shipid['ship_id']);
            $stmt->bindValue(':preset', 1);
            $stmt->bindValue(':type', 'R');
            $resxx = $stmt->execute();
        }

        echo $langvars['l_new_welcome_sent'] . '<br><br>';

        // They have logged in successfully, so update their session ID as well
        session_regenerate_id();

        $_SESSION['logged_in'] = true;
        $_SESSION['password'] = $filtered_post_password;
        $_SESSION['username'] = $username;
        Bnt\Text::gotoMain($db, $lang, $langvars);
        header('Refresh: 2;url=main.php');
    }
}
else
{
    $langvars['l_new_err'] = str_replace('[here]', "<a href='new.php'>" . $langvars['l_here'] . '</a>', $langvars['l_new_err']);
    echo $langvars['l_new_err'];
}

Bnt\Footer::display($pdo_db, $lang, $bntreg, $template);
?>
