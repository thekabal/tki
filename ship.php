<?php declare(strict_types = 1);
/**
 * ship.php from The Kabal Invasion.
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
$login->checkLogin($pdo_db, $lang, $tkireg, $template);

$title = $langvars['l_ship_title'];
$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('ship', 'planet', 'main', 'common', 'global_includes', 'global_funcs', 'footer', 'news'));
echo "<h1>" . $title . "</h1>\n";

// PHP7 Null coalescing operator - if it is set, great, if not, set to null
$ship_id = $_GET['ship_id'] ?? null;

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

$sql = "SELECT team, ship_name, character_name, sector FROM ::prefix::ships WHERE ship_id = :ship_id";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ship_id', $ship_id, PDO::PARAM_INT);
$stmt->execute();
$othership = $stmt->fetch(PDO::FETCH_ASSOC);

if ($othership['sector'] != $playerinfo['sector'])
{
    echo $langvars['l_ship_the'] . " <font color=white>" . $othership['ship_name'] . "</font> " . $langvars['l_ship_nolonger'] . " " . $playerinfo['sector'] . "<br>";
}
else
{
    $_SESSION['ship_selected'] = $ship_id;
    echo $langvars['l_ship_youc'] . " <font color=white>" . $othership['ship_name'] . "</font>, " . $langvars['l_ship_owned'] . " <font color=white>" . $othership['character_name'] . "</font>.<br><br>";
    echo $langvars['l_ship_perform'] . "<br><br>";
    echo "<a href=scan.php?ship_id=$ship_id>" . $langvars['l_planet_scn_link'] . "</a><br>";

    if (!Tki\Team::isSameTeam($playerinfo['team'], $othership['team']))
    {
        echo "<a href=attack.php?ship_id=$ship_id>" . $langvars['l_planet_att_link'] . "</a><br>";
    }

    echo "<a href=mailto.php?to=$ship_id>" . $langvars['l_send_msg'] . "</a><br>";
}

echo "<br>";
Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
