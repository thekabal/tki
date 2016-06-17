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
// File: warpedit.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

$title = $langvars['l_warp_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('warpedit', 'common', 'global_includes', 'global_funcs', 'footer', 'news'));
echo "<h1>" . $title . "</h1>\n";

// Get playerinfo from database
$sql = "SELECT * FROM {$pdo_db->prefix}ships WHERE email=:email LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['username']);
$stmt->execute();
$playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Get sectorinfo from database
$sql = "SELECT * FROM {$pdo_db->prefix}universe WHERE sector_id=:sector_id LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':sector_id', $playerinfo['sector']);
$stmt->execute();
$sectorinfo = $stmt->fetch(PDO::FETCH_ASSOC);

if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_warp_turn'] . "<br><br>";
    Tki\Text::gotomain($pdo_db, $lang);
    Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
    die();
}

if ($playerinfo['dev_warpedit'] < 1)
{
    echo $langvars['l_warp_none'] . ".<br><br>";
    Tki\Text::gotomain($pdo_db, $lang);
    Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
    die();
}

// Get playerinfo from database
$sql = "SELECT allow_warpedit FROM {$pdo_db->prefix}zones WHERE zone_id=:zone_id";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':zone_id', $sectorinfo['zone_id']);
$stmt->execute();
$zoneinfo = $stmt->fetch(PDO::FETCH_ASSOC);

if ($zoneinfo['allow_warpedit'] == 'N')
{
    echo $langvars['l_warp_forbid'] . "<br><br>";
    Tki\Text::gotomain($pdo_db, $lang);
    Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
    die();
}

if ($zoneinfo['allow_warpedit'] == 'L')
{
    // Get playerinfo from database
    $sql = "SELECT * FROM {$pdo_db->prefix}zones WHERE zone_id=:zone_id LIMIT 1";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindParam(':zone_id', $sectorinfo['zone_id']);
    $stmt->execute();
    $zoneowner_info = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get playerinfo from database
    $sql = "SELECT team FROM {$pdo_db->prefix}ships WHERE ship_id=:ship_id";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindParam(':sector_id', $zoneowner_info['owner']);
    $stmt->execute();
    $zoneteam = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($zoneowner_info['owner'] != $playerinfo['ship_id'])
    {
        if (($zoneteam['team'] != $playerinfo['team']) || ($playerinfo['team'] == 0))
        {
            echo $langvars['l_warp_forbid'] . "<br><br>";
            Tki\Text::gotomain($pdo_db, $lang);
            Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
            die();
        }
    }
}

$result2 = $db->Execute("SELECT * FROM {$db->prefix}links WHERE link_start = ? ORDER BY link_dest ASC;", array($playerinfo['sector']));
Tki\Db::LogDbErrors($pdo_db, $result2, __LINE__, __FILE__);
if (!$result2 instanceof ADORecordSet)
{
    echo $langvars['l_warp_nolink'] . "<br><br>";
}
else
{
    echo $langvars['l_warp_linkto'] . " ";
    while (!$result2->EOF)
    {
        echo $result2->fields['link_dest'] . " ";
        $result2->MoveNext();
    }
    echo "<br><br>";
}

echo "<form accept-charset='utf-8' action=\"warpedit2.php\" method=\"post\">";
echo "<table>";
echo "<tr><td>" . $langvars['l_warp_query'] . "</td><td><input type=\"text\" name=\"target_sector\" size=\"6\" maxlength=\"6\" value=\"\"></td></tr>";
echo "<tr><td>" . $langvars['l_warp_oneway'] . "?</td><td><input type=\"checkbox\" name=\"oneway\" value=\"oneway\"></td></tr>";
echo "</table>";
echo "<input type=\"submit\" value=\"" . $langvars['l_submit'] . "\"><input type=\"reset\" value=\"" . $langvars['l_reset'] . "\">";
echo "</form>";
echo "<br><br>" . $langvars['l_warp_dest'] . "<br><br>";
echo "<form accept-charset='utf-8' action=\"warpedit3.php\" method=\"post\">";
echo "<table>";
echo "<tr><td>" . $langvars['l_warp_destquery'] . "</td><td><input type=\"text\" name=\"target_sector\" size=\"6\" maxlength=\"6\" value=\"\"></td></tr>";
echo "<tr><td>" . $langvars['l_warp_bothway'] . "?</td><td><input type=\"checkbox\" name=\"bothway\" value=\"bothway\"></td></tr>";
echo "</table>";
echo "<input type=\"submit\" value=\"" . $langvars['l_submit'] . "\"><input type=\"reset\" value=\"" . $langvars['l_reset'] . "\">";
echo "</form>";

Tki\Text::gotomain($pdo_db, $lang);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
