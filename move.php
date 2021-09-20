<?php declare(strict_types = 1);
/**
 * move.php from The Kabal Invasion.
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

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('combat', 'footer',
                                'insignias', 'move', 'news', 'universal'));
$title = $langvars['l_move_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

$sector = (int) filter_input(INPUT_GET, 'sector', FILTER_SANITIZE_NUMBER_INT);

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

// Check to see if the player has less than one turn available
// and if so return to the main menu
if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_move_turn'] . '<br><br>';
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
    die();
}

// Get sectorinfo from database
$sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db);
$sectorinfo = $sectors_gateway->selectSectorInfo($playerinfo['sector']);

// Retrive all the warp links out of the current sector
$result3 = $old_db->Execute("SELECT * FROM {$old_db->prefix}links WHERE link_start = ?;", array($playerinfo['sector']));
Tki\Db::logDbErrors($pdo_db, $result3, __LINE__, __FILE__);
$i = 0;
$flag = 0;

// Loop through the available warp links to make sure it's a valid move
while (!$result3->EOF)
{
    $row = $result3->fields;
    if ($row['link_dest'] == $sector && $row['link_start'] == $playerinfo['sector'])
    {
        $flag = 1;
    }

    $i++;
    $result3->MoveNext();
}

// Check if there was a valid warp link to move to
if ($flag == 1)
{
    $calledfrom = "move.php";
    Tki\CheckDefenses::fighters($pdo_db, $lang, $sector, $playerinfo, $tkireg, $title, $calledfrom);

    $cur_time_stamp = date("Y-m-d H:i:s");
    Tki\LogMove::writeLog($pdo_db, $playerinfo['ship_id'], $sector);
    $move_result = $old_db->Execute("UPDATE {$old_db->prefix}ships SET last_login = ?," .
                                "turns = turns - 1, turns_used = turns_used + 1," .
                                "sector = ? WHERE ship_id = ?;", array($cur_time_stamp, $sector, $playerinfo['ship_id']));
    Tki\Db::logDbErrors($pdo_db, $move_result, __LINE__, __FILE__);
    if (!$move_result)
    {
        // Is this really STILL needed?
        $error = $old_db->ErrorMsg();
        mail($tkireg->admin_mail, "Move Error", "Start Sector: $sectorinfo[sector_id]\n" .
            "End Sector: $sector\nPlayer: $playerinfo[character_name] - " .
            $playerinfo['ship_id'] . "\n\nQuery:  $query\n\nSQL error: $error");
    }

    // Enter code for checking dangers in new sector
    Tki\CheckDefenses::mines($pdo_db, $lang, $sector, $title, $playerinfo, $tkireg);
    header("Location: main.php");
}
else
{
    echo $langvars['l_move_failed'] . '<br><br>';
    $resx = $old_db->Execute("UPDATE {$old_db->prefix}ships SET cleared_defenses=' ' " .
                         "WHERE ship_id = ?;", array($playerinfo['ship_id']));
    Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
    Tki\Text::gotoMain($pdo_db, $lang);
}

echo "</body></html>";
