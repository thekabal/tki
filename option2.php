<?php declare(strict_types = 1);
/**
 * option2.php from The Kabal Invasion.
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

$login = new Tki\Login();
$login->checkLogin($pdo_db, $lang, $tkireg, $tkitimer, $template);

// Set a flag that we have not changed the language
$changed_language = false;
$lang = 'english';

// Get POST['newlang'] returns null if not found.
if (array_key_exists('newlang', $_POST) === true)
{
    $lang_dir = new DirectoryIterator('languages/');
    foreach ($lang_dir as $file_info) // Get a list of the files in the languages directory
    {
        // If it is a PHP file, add it to the list of accepted language files
        if ($file_info->isFile() && $file_info->getExtension() == 'php') // If it is a PHP file, add it to the list of accepted make galaxy files
        {
            $lang_file = substr($file_info->getFilename(), 0, -8); // The actual file name

            // Trim and compare the new langauge with the supported.
            if (trim($_POST['newlang']) == $lang_file)
            {
                // We have a match so set lang to the required supported language, then break out of loop.
                $lang = $lang_file;

                // Update the ship record to the requested language
                $sql = "UPDATE ::prefix::ships SET lang = :lang WHERE email = :email LIMIT 1";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':lang', $lang, PDO::PARAM_STR);
                $stmt->bindParam(':email', $_SESSION['username'], PDO::PARAM_STR);
                $stmt->execute();
                $lang_changed = $stmt->fetch(PDO::FETCH_ASSOC);

                // Set a flag that we changed the language
                $changed_language = true;
                break;
            }
        }
    }
}

$title = $langvars['l_opt2_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('combat', 'common',
                                'footer', 'insignias', 'news', 'option2',
                                'universal'));
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

    $players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
    $playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

    // Does the oldpass and the players password match?
    if (password_verify($oldpass, $playerinfo['password']))
    {
        // Yes they match so hash the password.  $hashedPassword will be a 60-character string.
        $new_hashed_pass = password_hash($newpass1, PASSWORD_DEFAULT);

        // Now update the players password.
        $sql = "UPDATE ::prefix::ships SET password = :pass WHERE ship_id = :ship_id LIMIT 1";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':pass', $new_hashed_pass, PDO::PARAM_STR);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], PDO::PARAM_INT);
        $stmt->execute();
        $playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

        // Now check to see if we have a valid update and have ONLY 1 changed record.
        if ($playerinfo === null)
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

if ($changed_language)
{
    // Tell the player that we successfully changed the language choice
    $langvars['l_opt2_chlang'] = str_replace("[lang]", "$lang", $langvars['l_opt2_chlang']);
    echo $langvars['l_opt2_chlang'] . "<p>";
}

echo "<br>";
Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
