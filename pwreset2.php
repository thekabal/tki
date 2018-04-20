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
// File: pwreset2.php

require_once './common.php';

$title = $langvars['l_pwr_title'];
$body_class = 'options';

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title, $body_class);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('mail', 'common', 'global_funcs', 'global_includes', 'global_funcs', 'combat', 'footer', 'news', 'options', 'pwreset', 'option2'));
echo "<h1>" . $title . "</h1>\n";

$reset_code  = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
$newpass1  = filter_input(INPUT_POST, 'newpass1', FILTER_SANITIZE_STRING);
$newpass2  = filter_input(INPUT_POST, 'newpass2', FILTER_SANITIZE_STRING);

// It is important to note that SQL (both MySQL and PostgreSQL) index differently (one longer)
// than php does, which is why the substr (6/8 instead of 5/8) has a start index one "larger" here than in the php calls
// Also, we start at the 5th digit (plus one for SQL) because the first four characterts (before md5) are always going to be $2a$, from phpass/blowfish
// In most cases, even after hashing, the 5th character and beyond are unique.
// We chose 8 characters of uniqueness because its reasonable if you have to type it in, and
// because 8 characters is 4,294,967,296 combinations, and that should be sufficiently secure

$result = $db->SelectLimit("SELECT ship_id, email, recovery_time FROM {$db->prefix}ships WHERE substr(MD5(password),6,8) = ?", 1, -1, array('password' => $reset_code));
Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);

if (!$result->EOF && $result !== false)
{
    $playerinfo = $result->fields;
    $recovery_time = $result->fields['recovery_time'];
    $expiration = $recovery_time + (90 * 60); // 90 minutes expiration for e-mailed password resets

    // If time is within reset passowrd period (less than expiration), prompt user for new password
    if (time() > $expiration)
    {
        echo $langvars['l_pwr_timeout'];
    }
    else
    {
        // Do it all here

        // Check to see if newpass1 and newpass2 is empty.
        if (empty($newpass1) && empty($newpass2))
        {
            // Yes both newpass1 and newpass2 are empty.
            echo $langvars['l_opt2_passunchanged'] . "<br><br>";

            // Redirect back to login page
            header('Refresh: 10;url=index.php');
        }
        // Chack to see if newpass1 and newpass2 do not match.
        elseif ($newpass1 !== $newpass2)
        {
            // Newpass1 and newpass2 do not match.
            echo $langvars['l_opt2_newpassnomatch'] . "<br><br>";

            // Redirect back to login page
            header('Refresh: 10;url=index.php');
        }
        // So newpass1 and newpass2 are not null and they do match.
        else
        {
            // Hash the password.  $hashedPassword will be a 60-character string.
            $hashed_pass = password_hash($newpass1, PASSWORD_DEFAULT);

            // They have changed their password successfully, so update their session ID as well
            session_regenerate_id();

            // Now update the players password.
            $sql = "UPDATE ::prefix::ships SET password=:hashed_pass WHERE ship_id=:ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':hashed_pass', $hashed_pass, \PDO::PARAM_STR);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $result = $stmt->execute();
            Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            // Now check to see if we have a valid update and have ONLY 1 changed record.
            if ((is_bool($rs) && $rs === false) || $db->Affected_Rows() != 1)
            {
                // Either we got an error in the SQL Query or <> 1 records was changed.
                echo $langvars['l_opt2_passchangeerr'] . "<br><br>";
            }
            else
            {
                // Log user in
                $_SESSION['password'] = $newpass1;
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $playerinfo['email'];
            }

            // Send email to user & admin notifying of password change
            $langvars['l_mail_message'] = str_replace("[ip]", $request->server->get('REMOTE_ADDR'), $langvars['l_mail_message']);
            $langvars['l_mail_message'] = str_replace("[game_name]", $tkireg->game_name, $langvars['l_mail_message']);

            // Some reason \r\n is broken, so replace them now.
            $langvars['l_mail_message'] = str_replace('\r\n', "\r\n", $langvars['l_mail_message']);

            // Need to set the topic with the game name.
            $langvars['l_mail_topic'] = str_replace("[game_name]", $tkireg->game_name, $langvars['l_mail_topic']);
            mail($playerinfo['email'], $langvars['l_mail_topic'], $langvars['l_mail_message'], "From: {$tkireg->admin_mail}\r\nReply-To: {$tkireg->admin_mail}\r\nX-Mailer: PHP/" . phpversion());

            // Reset recovery_time to zero
            $sql = "UPDATE ::prefix::ships SET recovery_time=NULL WHERE ship_id=:ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $result = $stmt->execute();
            Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            echo $langvars['l_pwr_success'] . "<br><br>";
            echo str_replace("[here]", "<a href='main.php'>" . $langvars['l_here'] . "</a>", $langvars['l_global_mmenu']);

            // Redirect to game
            header('Refresh: 5;url=main.php');
        }
    }
}
else
{
    // This reset code is not valid.
    echo $langvars['l_pwr_invalid'];

    // Admin log this attempt to use an invalid code
}

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
