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
// File: zoneinfo.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('port', 'main', 'attack', 'zoneinfo', 'report', 'common', 'global_includes', 'global_funcs', 'footer', 'modify_defences'));
$title = $langvars['l_zi_title'];
$body_class = 'zoneinfo';
Tki\Header::display($pdo_db, $lang, $template, $title, $body_class);

echo "<h1>" . $title . "</h1>\n";
echo "<body class=" . $body_class . ">";
$zone = (int) filter_input(INPUT_GET, 'zone', FILTER_SANITIZE_NUMBER_INT);

// Get playerinfo from database
$sql = "SELECT * FROM {$pdo_db->prefix}ships WHERE email=:email LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['username']);
$stmt->execute();
$playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM {$pdo_db->prefix}zones WHERE zone_id=:zone_id LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':zone_id', $zone);
$stmt->execute();
$zoneinfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$zoneinfo)
{
    echo $langvars['l_zi_nexist'];
}
else
{
    if ($zoneinfo['zone_id'] < 5)
    {
        $zonevar = "l_zname_" . $zoneinfo['zone_id'];
        $zoneinfo['zone_name'] = $langvars[$zonevar];
    }

    if ($zoneinfo['zone_id'] == '2')
    {
        $ownername = $langvars['l_zi_feds'];
    }
    elseif ($zoneinfo['zone_id'] == '3')
    {
        $ownername = $langvars['l_zi_traders'];
    }
    elseif ($zoneinfo['zone_id'] == '1')
    {
        $ownername = $langvars['l_zi_nobody'];
    }
    elseif ($zoneinfo['zone_id'] == '4')
    {
        $ownername = $langvars['l_zi_war'];
    }
    else
    {
        // Sanitize ZoneName.
        $zoneinfo['zone_name'] = preg_replace('/[^A-Za-z0-9\_\s\-\.\']+/', '', $zoneinfo['zone_name']);

        if ($zoneinfo['team_zone'] == 'N')
        {
            $sql = "SELECT ship_id, character_name FROM {$pdo_db->prefix}ships WHERE ship_id=:ship_id LIMIT 1";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':ship_id', $zoneinfo['owner']);
            $stmt->execute();
            $ownerinfo = $stmt->fetch(PDO::FETCH_ASSOC);
            $ownername = $ownerinfo['character_name'];
        }
        else
        {
            $sql = "SELECT team_name, creator, id FROM {$pdo_db->prefix}teams WHERE id=:id LIMIT 1";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':id', $zoneinfo['owner']);
            $stmt->execute();
            $ownerinfo = $stmt->fetch(PDO::FETCH_ASSOC);
            $ownername = $ownerinfo['team_name'];
        }
    }

    if ($zoneinfo['allow_beacon'] == 'Y')
    {
        $beacon = $langvars['l_zi_allow'];
    }
    elseif ($zoneinfo['allow_beacon'] == 'N')
    {
        $beacon = $langvars['l_zi_notallow'];
    }
    else
    {
        $beacon = $langvars['l_zi_limit'];
    }

    if ($zoneinfo['allow_attack'] == 'Y')
    {
        $attack = $langvars['l_zi_allow'];
    }
    else
    {
        $attack = $langvars['l_zi_notallow'];
    }

    if ($zoneinfo['allow_defenses'] == 'Y')
    {
        $defense = $langvars['l_zi_allow'];
    }
    elseif ($zoneinfo['allow_defenses'] == 'N')
    {
        $defense = $langvars['l_zi_notallow'];
    }
    else
    {
        $defense = $langvars['l_zi_limit'];
    }

    if ($zoneinfo['allow_warpedit'] == 'Y')
    {
        $warpedit = $langvars['l_zi_allow'];
    }
    elseif ($zoneinfo['allow_warpedit'] == 'N')
    {
        $warpedit = $langvars['l_zi_notallow'];
    }
    else
    {
        $warpedit = $langvars['l_zi_limit'];
    }

    if ($zoneinfo['allow_planet'] == 'Y')
    {
        $planet = $langvars['l_zi_allow'];
    }
    elseif ($zoneinfo['allow_planet'] == 'N')
    {
        $planet = $langvars['l_zi_notallow'];
    }
    else
    {
        $planet = $langvars['l_zi_limit'];
    }

    if ($zoneinfo['allow_trade'] == 'Y')
    {
        $trade = $langvars['l_zi_allow'];
    }
    elseif ($zoneinfo['allow_trade'] == 'N')
    {
        $trade = $langvars['l_zi_notallow'];
    }
    else
    {
        $trade = $langvars['l_zi_limit'];
    }

    if ($zoneinfo['max_hull'] == 0)
    {
        $hull = $langvars['l_zi_ul'];
    }
    else
    {
        $hull = $zoneinfo['max_hull'];
    }

    if (($zoneinfo['team_zone'] == 'N' && $zoneinfo['owner'] == $playerinfo['ship_id']) || ($zoneinfo['team_zone'] == 'Y' && $zoneinfo['owner'] == $playerinfo['team'] && $playerinfo['ship_id'] == $ownerinfo['creator']))
    {
        echo "<center>" . $langvars['l_zi_control'] . ". <a href=zoneedit.php?zone=$zone>" . $langvars['l_clickme'] . "</a> " . $langvars['l_zi_tochange'] . "</center><p>";
    }

    echo "<table class=\"top\">\n" .
         "<tr><td class=\"zonename\"><strong>$zoneinfo[zone_name]</strong></td></tr></table>\n" .
         "<table class=\"bottom\">\n" .
         "<tr><td class=\"name\">&nbsp;" . $langvars['l_zi_owner'] . "</td><td class=\"value\">$ownername&nbsp;</td></tr>\n" .
         "<tr><td>&nbsp;" . $langvars['l_beacons'] . "</td><td>$beacon&nbsp;</td></tr>\n" .
         "<tr><td>&nbsp;" . $langvars['l_att_att'] . "</td><td>$attack&nbsp;</td></tr>\n" .
         "<tr><td>&nbsp;" . $langvars['l_md_title'] . "</td><td>$defense&nbsp;</td></tr>\n" .
         "<tr><td>&nbsp;" . $langvars['l_warpedit'] . "</td><td>$warpedit&nbsp;</td></tr>\n" .
         "<tr><td>&nbsp;" . $langvars['l_planets'] . "</td><td>$planet&nbsp;</td></tr>\n" .
         "<tr><td>&nbsp;" . $langvars['l_title_port'] . "</td><td>$trade&nbsp;</td></tr>\n" .
         "<tr><td>&nbsp;" . $langvars['l_zi_maxhull'] . "</td><td>$hull&nbsp;</td></tr>\n" .
         "</table>\n";
}
echo "<br><br>";

Tki\Text::gotomain($pdo_db, $lang);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
