<?php declare(strict_types = 1);
/**
 * planet_report.php from The Kabal Invasion.
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
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'footer',
                                'insignias', 'main', 'planet',
                                'planet_report', 'port', 'regional',
                                'universal'));
$title = $langvars['l_pr_title'];
$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

$preptype = null;
if (array_key_exists('preptype', $_GET))
{
    $preptype = $_GET['preptype'];
}

$sort = '';
if (array_key_exists('sort', $_GET))
{
    $sort = $_GET['sort'];
}

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

// Determine what type of report is displayed and display it's title
if ($preptype == 1 || !isset($preptype)) // Display the commodities on the planets
{
    $title = $title . ": Status";
    echo "<h1>" . $title . "</h1>\n";
    Tki\PlanetReport::standardReport($pdo_db, $langvars, $playerinfo, $sort, $tkireg);
}
elseif ($preptype == 2)                  // Display the production values of your planets and allow changing
{
    $title = $title . ": Production";
    echo "<h1>" . $title . "</h1>\n";
    Tki\PlanetProduction::productionChange($pdo_db, $old_db, $lang, $sort, $tkireg);
}
elseif ($preptype == 0)                  // For typing in manually to get a report menu
{
    $title = $title . ": Menu";
    echo "<h1>" . $title . "</h1>\n";
    Tki\PlanetReport::menu($playerinfo, $langvars);
}
else                                  // Display the menu if no valid options are passed in
{
    $title = $title . ": Status";
    echo "<h1>" . $title . "</h1>\n";
    Tki\PlanetReport::menu($playerinfo, $langvars);
}

echo "<br><br>";
Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
