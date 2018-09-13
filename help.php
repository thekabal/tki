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
// File: help.php

require_once './common.php';

$login = new Tki\Login;
$login->checkLogin($pdo_db, $lang, $tkireg, $template);

$title = $langvars['l_help'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);
echo "<h1>" . $title . "</h1>\n";

echo "Greetings and welcome to The Kabal Invasion!";
echo "<br><br>";
echo "This is a game of inter-galactic exploration. Players explore the universe, trading for commodities and ";
echo "increasing their wealth and power. Battles can be fought over space sectors and planets.";
echo "<br><br>";
echo "<a href=#mainmenu>Main Menu commands</a><br>";
echo "<a href=#techlevels>Tech levels</a><br>";
echo "<a href=#devices>Devices</a><br>";
echo "<a href=#zones>Zones</a><br>";
echo "<a name=mainmenu></a><h2>Main Menu commands:</h2>";
echo "<strong>Ship report:</strong><br>";
echo "Display a detailed report on your ship's systems, cargo and weaponry. You can display this report by ";
echo "clicking on your ship's name at the top of the main page.";
echo "<br><br>";
echo "<strong>Warp links:</strong><br>";
echo "Move from one sector to another through warp links, by clicking on the sector numbers.";
echo "<br><br>";
echo "<strong>Long-range scan:</strong><br>";
echo "Scan a neighboring sector with your long range scanners without actually moving there.";
if ($tkireg->allow_fullscan)
{
    echo " A full scan will give you an outlook on all the neighboring sectors in one wide sweep of your ";
    echo "sensors.";
}

echo "<br><br>";
echo "<strong>Ships:</strong><br>";
echo "Scan or attack a ship (if it shows up on your sensors) by clicking on the appropriate link on the right ";
echo "of the ship's name. The attacked ship may evade your offensive maneuver depending on its tech levels.";
echo "<br><br>";
echo "<strong>Trading ports:</strong><br>";
echo "Access the port trading menu by clicking on a port's type when you enter a sector where one is present.";
echo "<br><br>";
echo "<strong>Planets:</strong><br>";
echo "Access the planet menu by clicking on a planet's name when you enter a sector where one is present.";
echo "<br><br>";
if ($tkireg->allow_navcomp)
{
    echo "<strong>Navigation computer:</strong><br>";
    echo "Use your computer to find a route to a specific sector. The navigation computer's power depends on ";
    echo "your computer tech level.";
    echo "<br><br>";
}

echo "<strong>RealSpace:</strong><br>";
echo "Use your ship's engines to get to a specific sector. Upgrade your engines' tech level to use RealSpace ";
echo "moves effectively. By clicking on the 'Presets' link you can memorize up to " . $tkireg->max_presets . " sector numbers for quick ";
echo "movement or you can target any sector using the 'Other' link.";
echo "<br><br>";
echo "<strong>Trade routes:</strong><br>";
echo "Use trade routes to quickly trade commodities between ports. Trade routes take advantage of RealSpace ";
echo "movements to go back and forth between two ports and trade the maximum amount of commodities at each ";
echo "end. Ensure the remote sector contains a trading port before using a trade route. The trade route ";
echo "presets are shared with the RealSpace ones. As with RealSpace moves, any sector can be targeted using ";
echo "the 'Other' link";
echo "<br><br>";
echo "<h3>Menu bar (bottom part of the main page):</h3>";
echo "<strong>Devices:</strong><br>";
echo "Use the different devices that your ship carries (Genesis Torpedoes, beacons, Warp Editors, etc.). For ";
echo "more details on each individual device, scroll down to the 'Devices' section.";
echo "<br><br>";
echo "<strong>Planets:</strong><br>";
echo "Display a list of all your planets, with current totals on commodities, weaponry and credits.";
echo "<br><br>";
echo "<strong>Log:</strong><br>";
echo "Display the log of events that have happened to your ship.";
echo "<br><br>";
echo "<strong>Send Message:</strong><br>";
echo "Send an e-mail to another player.";
echo "<br><br>";
echo "<strong>Rankings:</strong><br>";
echo "Display the list of the top players, ranked by their current scores.";
echo "<br><br>";
echo "<strong>Last Users:</strong><br>";
echo "Display the list of users who recently logged on to the game.";
echo "<br><br>";
echo "<strong>Options:</strong><br>";
echo "Change user-specific options (currently, only the password can be changed).";
echo "<br><br>";
echo "<strong>Feedback:</strong><br>";
echo "Send an e-mail to the game admin.";
echo "<br><br>";
echo "<strong>Self-Destruct:</strong><br>";
echo "Destroy your ship and remove yourself from the game.";
echo "<br><br>";
echo "<strong>Help:</strong><br>";
echo "Display the help page (what you're reading right now).";
echo "<br><br>";
echo "<strong>Logout:</strong><br>";
echo "Remove any game cookies from your system, ending your current session.";
echo "<br><br>";
echo "<a name=techlevels></a><h2>Tech levels:</h2>";
echo "You can upgrade your ship components at any special port. Each component upgrade improves your ship's ";
echo "attributes and capabilities.";
echo "<br><br>";
echo "<strong>Hull:</strong><br>";
echo "Determines the number of holds available on your ship (for transporting commodities and ";
echo "colonists).";
echo "<br><br>";
echo "<strong>Engines:</strong><br>";
echo "Determines the size of your engines. Larger engines can move through RealSpace at a faster pace.";
echo "<br><br>";
echo "<strong>Power:</strong><br>";
echo "Determines the number of energy your ship can carry.";
echo "<br><br>";
echo "<strong>Computer:</strong><br>";
echo "Determines the number of fighters your ship can control.";
echo "<br><br>";
echo "<strong>Sensors:</strong><br>";
echo "Determines the precision of your sensors when scanning a ship or planet. Scan success is dependent upon ";
echo "the target's cloak level.";
echo "<br><br>";
echo "<strong>Armor:</strong><br>";
echo "Determines the number of armor points your ship can use.";
echo "<br><br>";
echo "<strong>Shields:</strong><br>";
echo "Determines the efficiency of your ship's shield system during combat.";
echo "<br><br>";
echo "<strong>Beams:</strong><br>";
echo "Determines the efficiency of your ship's beam weapons during combat.";
echo "<br><br>";
echo "<strong>Torpedo launchers:</strong><br>";
echo "Determines the number of torpedoes your ship can use.";
echo "<br><br>";
echo "<strong>Cloak:</strong><br>";
echo "Determines the efficiency of your ship's cloaking system. See 'Sensors' for more details.";
echo "<br><br>";
echo "<a name=devices></a><h2>Devices:</h2>";
echo "<strong>Space Beacons:</strong><br>";
echo "Post a warning or message which will be displayed to anyone entering this sector. Only 1 beacon can be ";
echo "active in each sector, so a new beacon removes the existing one (if any).";
echo "<br><br>";
echo "<strong>Warp Editors:</strong><br>";
echo "Create or destroy warp links to another sector.";
echo "<br><br>";
echo "<strong>Genesis Torpedoes:</strong><br>";
echo "Create a planet in the current sector (if one does not yet exist).";
echo "<br><br>";
echo "<strong>Mine Deflector:</strong><br>";
echo "Protect the player against mines dropped in space. Each deflector takes out 1 mine.";
echo "<br><br>";
echo "<strong>Emergency Warp Device:</strong><br>";
echo "Transport your ship to a random sector, if manually engaged. Otherwise, an Emergency Warp Device can ";
echo "protect your ship when attacked by transporting you out of the reach of the attacker.";
echo "<br><br>";
echo "<strong>Escape Pod (maximum of 1):</strong><br>";
echo "Keep yourself alive when your ship is destroyed, enabling you to keep your credits and planets.";
echo "<br><br>";
echo "<strong>Fuel Scoop (maximum of 1):</strong><br>";
echo "Accumulate energy units when using RealSpace movement.";
echo "<br><br>";
echo "<a name=zones></a><h2>Zones:</h2>";
echo "The galaxy is divided into different areas with different rules being enforced in each zone. To display ";
echo "the restrictions attached to your current sector, just click on the zone name (top right corner of the ";
echo "main page). Your ship can be towed out of a zone to a random sector when your hull size exceeds the ";
echo "maximum allowed level for that specific zone. Attacking other players and using some devices can also ";
echo "be disallowed in some zones.";
echo "<br><br>";

Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
