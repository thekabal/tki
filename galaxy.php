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
// File: galaxy.php

require_once './common.php';

$login = new Tki\Login;
$login->checkLogin($pdo_db, $lang, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('main', 'port', 'galaxy', 'common', 'global_includes', 'global_funcs', 'footer'));
$title = $langvars['l_map_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

echo "<h1>" . $title . "</h1>\n";

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

$sql = "SELECT distinct ::prefix::movement_log.sector_id, port_type, beacon FROM ::prefix::movement_log,::prefix::universe WHERE ship_id = :ship_id AND ::prefix::movement_log.sector_id=::prefix::universe.sector_id order by sector_id ASC";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ship_id', $playerinfo['ship_id'], PDO::PARAM_INT);
$stmt->execute();
$discovered_sectors = $stmt->fetchAll(PDO::FETCH_ASSOC);
$discovered_count = 0;

$tile['special'] = "port-special.png";
$tile['ore'] = "port-ore.png";
$tile['organics'] = "port-organics.png";
$tile['energy'] = "port-energy.png";
$tile['goods'] = "port-goods.png";
$tile['none'] = "space.png";
$tile['unknown'] = "uspace.png";

$cur_sector = 1; // Clear this before iterating through the sectors

// Display sectors as imgs, and each class in css in header.php; then match the width and height here
$div_w = 20; // Only this width to match the included images
$div_h = 20; // Only this height to match the included images
$div_border = 2; // CSS border is 1 so this should be 2
$div_xmax = 50; // Where to wrap to next line
$div_ymax = $tkireg->max_sectors / $div_xmax;
$map_width = ($div_w + $div_border) * $div_xmax;  // Define the containing div to be the right width to wrap at $div_xmax

// Setup containing div to hold the width of the images
echo "\n<div id='map' style='position:relative;background-color:#0000ff;width:" . $map_width . "px'>\n";
for ($r = 0; $r < $div_ymax; $r++) // Loop the rows
{
    for ($c = 0; $c < $div_xmax; $c++) // Loop the columns
    {
        if ((count($discovered_sectors) > $discovered_count) && ($discovered_sectors[$discovered_count]['sector_id'] !== null && ($discovered_sectors[$discovered_count]['sector_id'] === $cur_sector)))
        {
            $p = $discovered_sectors[$discovered_count]['port_type'];
            // Build the alt text for each image
            $alt = $langvars['l_sector'] . ": {$discovered_sectors[$discovered_count]['sector_id']} Port: {$discovered_sectors[$discovered_count]['port_type']} ";

            if ($discovered_sectors[$discovered_count]['beacon'] !== null)
            {
                $alt .= "{$discovered_sectors[$discovered_count]['beacon']}";
            }

            echo "\n<a href=\"rsmove.php?engage=1&amp;destination=" . $discovered_sectors[$discovered_count]['sector_id'] . "\">";
            echo "<img class='map " . $discovered_sectors[$discovered_count]['port_type'] . "' src='" . $template->getVariables('template_dir') . "/images/" . $tile[$p] . "' alt='" . $alt . "' style='width:20px; height:20px'></a> ";

            // Move to next explored sector in database results
            if ($discovered_count < count($discovered_sectors))
            {
                $discovered_count++;
            }

            $cur_sector++;
        }
        else
        {
            // Build the alt text for each image
            $alt = $cur_sector . " - " . $langvars['l_unknown'] . " ";
            echo "<a href=\"rsmove.php?engage=1&amp;destination=" . $cur_sector . "\">";
            echo "<img class='map un' src='" . $template->getVariables('template_dir') . "/images/" . $tile['unknown'] . "' alt='" . $alt . "' style='width:20px; height:20px'></a> ";
            $cur_sector++;
        }
    }
}

// These are the row numbers on the side of the map
for ($a = 1; $a <= ($tkireg->max_sectors / 50); $a++)
{
    echo "\n<div style='position:absolute;left:" . ($map_width + 10) . "px;top:".(($a - 1) * ($div_h + $div_border)) . "px;'>" . ($a * 50) . "</div>";
}

echo "</div><div style='clear:both'></div><br>";
echo "    <div><img style='height:20px; width:20px' alt='" . $langvars['l_port'] . ": " . $langvars['l_special_port'] . "' src='" . $template->getVariables('template_dir') . "/images/{$tile['special']}'> &lt;- " . $langvars['l_special_port'] . "</div>\n";
echo "    <div><img style='height:20px; width:20px' alt='" . $langvars['l_port'] . ": " . $langvars['l_ore_port'] . "' src='" . $template->getVariables('template_dir') . "/images/{$tile['ore']}'> &lt;- " . $langvars['l_ore_port'] . "</div>\n";
echo "    <div><img style='height:20px; width:20px' alt='" . $langvars['l_port'] . ": " . $langvars['l_organics_port'] . "' src='" . $template->getVariables('template_dir') . "/images/{$tile['organics']}'> &lt;- " . $langvars['l_organics_port'] . "</div>\n";
echo "    <div><img style='height:20px; width:20px' alt='" . $langvars['l_port'] . ": " . $langvars['l_energy_port'] . "' src='" . $template->getVariables('template_dir') . "/images/{$tile['energy']}'> &lt;- " . $langvars['l_energy_port'] . "</div>\n";
echo "    <div><img style='height:20px; width:20px' alt='" . $langvars['l_port'] . ": " . $langvars['l_goods_port'] . "' src='" . $template->getVariables('template_dir') . "/images/{$tile['goods']}'> &lt;- " . $langvars['l_goods_port'] . "</div>\n";
echo "    <div><img style='height:20px; width:20px' alt='" . $langvars['l_port'] . ": " . $langvars['l_no_port'] . "' src='" . $template->getVariables('template_dir') . "/images/{$tile['none']}'> &lt;- " . $langvars['l_no_port'] . "</div>\n";
echo "    <div><img style='height:20px; width:20px' alt='" . $langvars['l_port'] . ": " . $langvars['l_unexplored'] . "' src='" . $template->getVariables('template_dir') . "/images/{$tile['unknown']}'> &lt;- " . $langvars['l_unexplored'] . "</div>\n";

echo "<br><br>";
Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
