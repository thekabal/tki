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
// File: planet_report.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('main', 'planet', 'port', 'common', 'global_includes', 'global_funcs', 'footer', 'planet_report', 'regional'));
$title = $langvars['l_pr_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

$preptype = null;
if (array_key_exists('preptype', $_GET))
{
    $preptype = $_GET['preptype'];
}

// Get playerinfo from database
$sql = "SELECT * FROM {$pdo_db->prefix}ships WHERE email=:email LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['username']);
$stmt->execute();
$playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Determine what type of report is displayed and display it's title
if ($preptype == 1 || !isset($preptype)) // Display the commodities on the planets
{
    $title = $title . ": Status";
    echo "<h1>" . $title . "</h1>\n";
    Tki\PlanetReport::standardReport($pdo_db, $db, $langvars, $playerinfo, $sort, $tkireg);
}
elseif ($preptype == 2)                  // Display the production values of your planets and allow changing
{
    $title = $title . ": Production";
    echo "<h1>" . $title . "</h1>\n";
    Tki\PlanetReport::planetProductionChange($pdo_db, $db, $langvars, $playerinfo, $sort, $tkireg);
}
elseif ($preptype == 0)                  // For typing in manually to get a report menu
{
    $title = $title . ": Menu";
    echo "<h1>" . $title . "</h1>\n";
    Tki\PlanetReport::planetReportMenu($playerinfo, $langvars);
}
else                                  // Display the menu if no valid options are passed in
{
    $title = $title . ": Status";
    echo "<h1>" . $title . "</h1>\n";
    Tki\PlanetReport::planetReportMenu($playerinfo, $langvars);
}

echo "<br><br>";
Tki\Text::gotomain($pdo_db, $lang);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
