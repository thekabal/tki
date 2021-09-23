<?php declare(strict_types = 1);
/**
 * emerwarp.php from The Kabal Invasion.
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

// Always make sure we are using empty vars before use.
$variables = null;

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'emerwarp',
                                'footer', 'insignias', 'news', 'universal'));
// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

if ($playerinfo['dev_emerwarp'] > 0)
{
    // Start at sector 1, as we no longer use sector 0.
    $dest_sector = random_int(1, (int) $tkireg->max_sectors - 1);

    $sql = "UPDATE ::prefix::ships SET sector = :sector, " .
           "dev_emerwarp = dev_emerwarp - 1 WHERE ship_id = :ship_id";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindParam(':sector', $dest_sector, PDO::PARAM_INT);
    $stmt->bindParam(':ship_id', $playerinfo['ship_id'], PDO::PARAM_INT);
    $stmt->execute();
    Tki\LogMove::writeLog($pdo_db, $playerinfo['ship_id'], $dest_sector);
    $variables['dest_sector'] = $dest_sector;
}

$variables['body_class'] = 'tki'; // No special css used for this page yet
$variables['playerinfo_dev_emerwarp'] = $playerinfo['dev_emerwarp'];
$variables['title'] = $langvars['l_ewd_title'];
$variables['linkback'] = array(
    "fulltext" => $langvars['l_universal_main_menu'],
    "link" => "main.php"
);

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);

$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('emerwarp.tpl');

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
