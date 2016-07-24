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
// File: beacon.php

require_once './common.php';

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('beacon', 'common',
                                'global_includes', 'global_funcs', 'combat',
                                'footer', 'news'));
$title = $langvars['l_beacon_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

echo "<h1>" . $title . "</h1>\n";

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

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

$allowed_rsw = "N";

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$beacon_text = null;
$beacon_text = filter_input(INPUT_POST, 'beacon_text', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($beacon_text)) === 0)
{
    $beacon_text = false;
}

if ($playerinfo['dev_beacon'] > 0)
{
    // Get playerinfo from database
    $sql = "SELECT allow_beacon FROM {$pdo_db->prefix}zones WHERE zone_id=:zone_id LIMIT 1";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindParam(':sector_id', $sectorinfo['zone_id']);
    $stmt->execute();
    $zoneinfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($zoneinfo['allow_beacon'] == 'N')
    {
        echo $langvars['l_beacon_notpermitted'] . "<br><br>";
    }
    elseif ($zoneinfo['allow_beacon'] == 'L')
    {
        $sql = "SELECT * FROM {$pdo_db->prefix}zones WHERE zone_id=:zone_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $sectorinfo['zone_id']);
        $stmt->execute();
        $zoneowner_info = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT team FROM {$pdo_db->prefix}ships WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $zoneowner_info['owner']);
        $stmt->execute();
        $zoneteam = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($zoneowner_info['owner'] != $playerinfo['ship_id'])
        {
            if (($zoneteam['team'] != $playerinfo['team']) || ($playerinfo['team'] == 0))
            {
                echo $langvars['l_beacon_notpermitted'] . "<br><br>";
            }
            else
            {
                $allowed_rsw = "Y";
            }
        }
        else
        {
            $allowed_rsw = "Y";
        }
    }
    else
    {
        $allowed_rsw = "Y";
    }

    if ($allowed_rsw == "Y")
    {
        if ($beacon_text === null)
        {
            if ($sectorinfo['beacon'] !== null)
            {
                echo $langvars['l_beacon_reads'] . ": " . $sectorinfo['beacon'] . "<br><br>";
            }
            else
            {
                echo $langvars['l_beacon_none'] . "<br><br>";
            }

            echo "<form accept-charset='utf-8' action=beacon.php method=post>";
            echo "<table>";
            echo "<tr><td>" . $langvars['l_beacon_enter'];
            echo ":</td><td><input type=text name=beacon_text size=40 maxlength=80></td></tr>";
            echo "</table>";
            echo "<input type=submit value=" . $langvars['l_submit'] . ">";
            echo "<input type=reset value=" . $langvars['l_reset'] . ">";
            echo "</form>";
        }
        else
        {
            $beacon_text = trim(htmlentities($beacon_text, ENT_HTML5, 'UTF-8'));
            echo $langvars['l_beacon_nowreads'] . ": " . $beacon_text . ".<br><br>";

            $sql = "UPDATE {$pdo_db->prefix}universe SET beacon=:beacon WHERE sector_id=:sector_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':beacon', $beacon_text);
            $stmt->bindParam(':sector_id', $sectorinfo['sector_id']);
            $stmt->execute();

            $sql = "UPDATE {$pdo_db->prefix}ships SET dev_beacon=dev_beacon-1 WHERE ship_id=:ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
            $stmt->execute();
        }
    }
}
else
{
    echo $langvars['l_beacon_donthave'] . "<br><br>";
}

Tki\Text::gotomain($pdo_db, $lang);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
