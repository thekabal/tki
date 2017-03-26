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
// File: device.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('device', 'common', 'global_includes', 'global_funcs', 'report', 'footer', 'regional'));
$title = $langvars['l_device_title'];
$body_class = 'device';
Tki\Header::display($pdo_db, $lang, $template, $title, $body_class);

echo "<body class=" . $body_class . "><br>";
echo "<h1>" . $title . "</h1>\n";

// Get playerinfo from database
$sql = "SELECT * FROM ::prefix::ships WHERE email=:email LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

echo $langvars['l_device_expl'] . "<br><br>";
echo "<table style=\"width:33%\">";
echo "<tr><th style=\"text-align:left;\">" . $langvars['l_device'] . "</th><th>" . $langvars['l_qty'] . "</th><th>" . $langvars['l_usage'] . "</th></tr>";
echo "<tr>";
echo "<td><a href='beacon.php'>" . $langvars['l_beacons'] . "</a></td><td>" . number_format($playerinfo['dev_beacon'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>" . $langvars['l_manual'] . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td><a href='warpedit.php'>" . $langvars['l_warpedit'] . "</a></td><td>" . number_format($playerinfo['dev_warpedit'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>" . $langvars['l_manual'] . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td><a href='genesis.php'>" . $langvars['l_genesis'] . "</a></td><td>" . number_format($playerinfo['dev_genesis'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>" . $langvars['l_manual'] . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>" . $langvars['l_deflect'] . "</td><td>" . number_format($playerinfo['dev_minedeflector'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>" . $langvars['l_automatic'] . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td><a href='mines.php?op=1'>" . $langvars['l_mines'] . "</a></td><td>" . number_format($playerinfo['torps'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>" . $langvars['l_manual'] . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td><a href='mines.php?op=2'>" . $langvars['l_fighters'] . "</a></td><td>" . number_format($playerinfo['ship_fighters'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>" . $langvars['l_manual'] . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td><a href='emerwarp.php'>" . $langvars['l_ewd'] . "</a></td><td>" . number_format($playerinfo['dev_emerwarp'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>" . $langvars['l_manual'] . "/" . $langvars['l_automatic'] . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>" . $langvars['l_escape_pod'] . "</td><td>" . (($playerinfo['dev_escapepod'] == 'Y') ? $langvars['l_yes'] : $langvars['l_no']) . "</td><td>" . $langvars['l_automatic'] . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>" . $langvars['l_fuel_scoop'] . "</td><td>" . (($playerinfo['dev_fuelscoop'] == 'Y') ? $langvars['l_yes'] : $langvars['l_no']) . "</td><td>" . $langvars['l_automatic'] . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>" . $langvars['l_lssd'] . "</td><td>" . (($playerinfo['dev_lssd'] == 'Y') ? $langvars['l_yes'] : $langvars['l_no']) . "</td><td>" . $langvars['l_automatic'] . "</td>";
echo "</tr>";
echo "</table>";
echo "<br>";

Tki\Text::gotoMain($pdo_db, $lang);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
