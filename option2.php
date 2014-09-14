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
// File: option2.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $langvars, $tkireg, $template);

// Set a flag that we have not changed the language
$changed_language = false;

// Get POST['newlang'] returns null if not found.
if (array_key_exists('newlang', $_POST) == true)
{
    $lang_dir = new DirectoryIterator('languages/');
    foreach ($lang_dir as $file_info) // Get a list of the files in the languages directory
    {
        // If it is a PHP file, add it to the list of accepted language files
        if ($file_info->isFile() && $file_info->getExtension() == 'php') // If it is a PHP file, add it to the list of accepted make galaxy files
        {
            $lang_file = mb_substr($file_info->getFilename(), 0, -8); // The actual file name

            // Trim and compare the new langauge with the supported.
            if (trim($_POST['newlang']) == $lang_file)
            {
                // We have a match so set lang to the required supported language, then break out of loop.
                $lang = $lang_file;

                // Update the ship record to the requested language
                $res = $db->Execute("UPDATE {$db->prefix}ships SET lang = ? WHERE email = ?", array($lang, $_SESSION['username']));
                Tki\Db::logDbErrors($db, $res, __LINE__, __FILE__);

                // Set a flag that we changed the language
                $changed_language = true;
                break;
            }
        }
    }
}

$title = $langvars['l_opt2_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('option2', 'common', 'global_includes', 'global_funcs', 'combat', 'footer', 'news'));
echo "<h1>" . $title . "</h1>\n";

// Filter POST['oldpass'], POST['newpass1'], POST['newpass2']. Returns "0" if these specific values are not set because that is what the form gives if they exist but were not set.
// This filters to the FILTER_SANITIZE_STRING ruleset, because we need to allow spaces (URL doesn't)
$oldpass  = filter_input(INPUT_POST, 'oldpass', FILTER_SANITIZE_STRING);
$newpass1  = filter_input(INPUT_POST, 'newpass1', FILTER_SANITIZE_STRING);
$newpass2  = filter_input(INPUT_POST, 'newpass2', FILTER_SANITIZE_STRING);

// Check to see if newpass1 and newpass2 is empty.
if (empty($newpass1) && empty($newpass2))
{
    // Both newpass1 and newpass2 are empty.
    echo $langvars['l_opt2_passunchanged'] . "<br><br>";
}
// Chack to see if newpass1 and newpass2 do not match.
elseif ($newpass1 !== $newpass2)
{
    // Newpass1 and newpass2 do not match.
    echo $langvars['l_opt2_newpassnomatch'] . "<br><br>";
}
// So newpass1 and newpass2 are not null and they do match.
else
{
    // Load Player information from their username (i.e. email)
    $playerinfo = false;
    $rs = $db->SelectLimit("SELECT ship_id, password FROM {$db->prefix}ships WHERE email=?", 1, -1, array('email' => $_SESSION['username']));
    Tki\Db::logDbErrors($db, $rs, __LINE__, __FILE__);

    // Do we have a valid RecordSet?
    if ($rs instanceof ADORecordSet)
    {
        // We have a valid RecorSet, so now set $playerinfo.
        $playerinfo = $rs->fields;

        // Does the oldpass and the players password match?
        if (password_verify($oldpass, $playerinfo['password']))
        {
            // Yes they match so hash the password.  $hashedPassword will be a 60-character string.
            $new_hashed_pass = password_hash($newpass1, PASSWORD_DEFAULT);

            // Now update the players password.
            $rs = $db->Execute("UPDATE {$db->prefix}ships SET password = ? WHERE ship_id = ?;", array($new_hashed_pass, $playerinfo['ship_id']));
            Tki\Db::logDbErrors($db, $rs, __LINE__, __FILE__);

            // Now check to see if we have a valid update and have ONLY 1 changed record.
            if ((is_bool($rs) && $rs == false) || $db->Affected_Rows() != 1)
            {
                // Either we got an error in the SQL Query or <> 1 records was changed.
                echo $langvars['l_opt2_passchangeerr'] . "<br><br>";
            }
            else
            {
                // Everything went well so update the password session to the new password.
                echo $langvars['l_opt2_passchanged'] . "<br><br>";
                $_SESSION['password'] = $newpass1;

                // They have changed their password successfully, so update their session ID as well
                session_regenerate_id();
            }
        }
        else
        {
            // The oldpass did not match the players password.
            echo $langvars['l_opt2_srcpassfalse'] . "<br><br>";
        }
    }
}

if ($changed_language)
{
    // Tell the player that we successfully changed the language choice
    $langvars['l_opt2_chlang'] = str_replace("[lang]", "$lang", $langvars['l_opt2_chlang']);
    echo $langvars['l_opt2_chlang'] . "<p>";
}

echo "<br>";
Tki\Text::gotoMain($db, $lang, $langvars);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
