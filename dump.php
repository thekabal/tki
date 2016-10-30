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
// File: dump.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

$title = $langvars['l_dump_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('dump', 'main', 'common', 'global_includes', 'global_funcs', 'combat', 'footer', 'news'));

// Get playerinfo from database
$sql = "SELECT * FROM ::prefix::ships WHERE email=:email LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['username']);
$stmt->execute();
$playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM ::prefix::universe WHERE sector_id=:sector_id LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':sector_id', $playerinfo['sector']);
$stmt->execute();
$sectorinfo = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h1>" . $title . "</h1>\n";

if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_dump_turn']  . "<br><br>";
    Tki\Text::gotomain($pdo_db, $lang);
    Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
    die();
}

if ($playerinfo['ship_colonists'] == 0)
{
    echo $langvars['l_dump_nocol'] . "<br><br>";
}
elseif ($sectorinfo['port_type'] == "special")
{
    $sql = "UPDATE ::prefix::ships SET ship_colonists=0, turns=turns-1, turns_used=turns_used+1 WHERE ship_id=:ship_id";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
    $stmt->execute();
    echo $langvars['l_dump_dumped'] . "<br><br>";
}
else
{
    echo $langvars['l_dump_nono'] . "<br><br>";
}

Tki\Text::gotomain($pdo_db, $lang);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
