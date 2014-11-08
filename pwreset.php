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
// File: pwreset.php

require_once './common.php';

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('mail', 'common', 'global_funcs', 'global_includes', 'global_funcs', 'combat', 'footer', 'news', 'options', 'pwreset'));
$title = $langvars['l_pwr_title'];
$body_class = 'options';
Tki\Header::display($pdo_db, $lang, $template, $title, $body_class);

echo "<h1>" . $title . "</h1>\n";

$reset_code  = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);

// It is important to note that SQL (both MySQL and PostgreSQL) index differently (one longer)
// than php does, which is why the substr (6/8 instead of 5/8) has a start index one "larger" here than in the php calls
// Also, we start at the 5th digit (plus one for SQL) because the first four characterts (before md5) are always going to be $2a$, from phpass/blowfish
// In most cases, even after hashing, the 5th character and beyond are unique.
// We chose 8 characters of uniqueness because its reasonable if you have to type it in, and
// because 8 characters is 4,294,967,296 combinations, and that should be sufficiently secure

$result = $db->SelectLimit("SELECT character_name, email, recovery_time FROM {$db->prefix}ships WHERE substr(MD5(password),6,8) = ?", 1, -1, array('password' => $reset_code));
Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);

if (!$result->EOF && $result !== false)
{
    $recovery_time = $result->fields['recovery_time'];
    $expiration = $recovery_time + (90 * 60); // 90 minutes expiration for e-mailed password resets

    // If time is within reset passowrd period (less than expiration), prompt user for new password
    if (time() > $expiration)
    {
        echo $langvars['l_pwr_timeout'];
    }
    else
    {
        echo "<form accept-charset='utf-8' action=pwreset2.php method=post>";
        echo "<table>";
        echo "<tr>";
        echo "<th colspan=2><strong>" . $langvars['l_opt_chpass'] . "</strong></th>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>" . $langvars['l_opt_newpass'] . "</td>";
        echo "<td><input type=password name=newpass1 size=20 maxlength=20 value=\"\"></td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>" . $langvars['l_opt_newpagain'] . "</td>";
        echo "<td><input type=password name=newpass2 size=20 maxlength=20 value=\"\"></td>";
        echo "</tr>";
        echo "</table>";
        echo "<input type=hidden name=code value=" . $reset_code . ">";
        echo "<br>";
        echo "<input type=submit value=" . $langvars['l_opt_save'] . ">";
        echo "</form><br>";
    }
}
else
{
    // This reset code is not valid.
    echo $langvars['l_pwr_invalid'];

    // Admin log this attempt to use an invalid code
}

// Set password based on user input
// FUTURE

/// Send email to user & admin notifying of password change
//$langvars['l_mail_message'] = str_replace ("[ip]", $_SERVER['REMOTE_ADDR'], $langvars['l_mail_message']);
//$langvars['l_mail_message'] = str_replace ("[game_name]", $tkireg->game_name, $langvars['l_mail_message']);

/// Some reason \r\n is broken, so replace them now.
//$langvars['l_mail_message'] = str_replace ('\r\n', "\r\n", $langvars['l_mail_message']);

/// Need to set the topic with the game name.
//$langvars['l_mail_topic'] = str_replace ("[game_name]", $tkireg->game_name, $langvars['l_mail_topic']);

//mail ($playerinfo['email'], $langvars['l_mail_topic'], $langvars['l_mail_message'], "From: {$tkireg->admin_mail}\r\nReply-To: {$tkireg->admin_mail}\r\nX-Mailer: PHP/" . phpversion());

/// Reset recovery_time to zero
//$recovery_update_result = $db->Execute ("UPDATE {$db->prefix}ships SET recovery_time = null WHERE email = ?;", array($playerinfo['email']));
//var_dump (Tki\Db::logDbErrors ($db, $recovery_update_result, __LINE__, __FILE__));

/// Log user in (like login does)

/// Redirect to game (like login does)
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
