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
// File: defense_report.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('defense_report', 'planet_report', 'main', 'device', 'port', 'modify_defenses', 'common', 'global_includes', 'global_funcs', 'combat', 'footer', 'news', 'regional'));
$title = $langvars['l_sdf_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

echo "<h1>" . $title . "</h1>\n";

// Get playerinfo from database
$sql = "SELECT * FROM ::prefix::ships WHERE email=:email LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

$query = "SELECT * FROM {$db->prefix}sector_defense WHERE ship_id = ?";
if ($sort !== null)
{
    $query .= " ORDER BY";
    if ($sort == "quantity")
    {
        $query .= " quantity ASC";
    }
    elseif ($sort == "mode")
    {
        $query .= " fm_setting ASC";
    }
    elseif ($sort == "type")
    {
        $query .= " defense_type ASC";
    }
    else
    {
        $query .= " sector_id ASC";
    }
}

$res = $db->Execute($query, array($playerinfo['ship_id']));
Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);

$i = 0;
if ($res)
{
    while (!$res->EOF)
    {
        $sector[$i] = $res->fields;
        $i++;
        $res->MoveNext();
    }
}

$num_sectors = $i;
if ($num_sectors < 1)
{
    echo "<br>" . $langvars['l_sdf_none'];
}
else
{
    echo $langvars['l_pr_clicktosort'] . "<br><br>";
    echo "<table width=\"100%\" border=0 cellspacing=0 cellpadding=2>";
    echo "<tr bgcolor=\"$tkireg->color_header\">";
    echo "<td><strong><a href=defense_report.php?sort=sector>" . $langvars['l_sector'] . "</a></strong></td>";
    echo "<td><strong><a href=defense_report.php?sort=quantity>" . $langvars['l_qty'] . "</a></strong></td>";
    echo "<td><strong><a href=defense_report.php?sort=type>" . $langvars['l_sdf_type'] . "</a></strong></td>";
    echo "<td><strong><a href=defense_report.php?sort=mode>" . $langvars['l_sdf_mode'] . "</a></strong></td>";
    echo "</tr>";
    $color = $tkireg->color_line1;
    for ($i = 0; $i < $num_sectors; $i++)
    {
        echo "<tr bgcolor=\"$color\">";
        echo "<td><a href=rsmove.php?engage=1&destination=". $sector[$i]['sector_id'] . ">". $sector[$i]['sector_id'] . "</a></td>";
        echo "<td>" . number_format($sector[$i]['quantity'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
        $defense_type = $sector[$i]['defense_type'] == 'F' ? $langvars['l_fighters'] : $langvars['l_mines'];
        echo "<td> $defense_type </td>";
        $mode = $sector[$i]['defense_type'] == 'F' ? $sector[$i]['fm_setting'] : $langvars['l_n_a'];
        if ($mode == 'attack')
        {
            $mode = $langvars['l_md_attack'];
        }
        else
        {
            $mode = $langvars['l_md_toll'];
        }

        echo "<td> " . $mode . " </td>";
        echo "</tr>";

        if ($color == $tkireg->color_line1)
        {
            $color = $tkireg->color_line2;
        }
        else
        {
            $color = $tkireg->color_line1;
        }
    }

    echo "</table>";
}

echo "<br><br>";
Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer;
$footer->display($pdo_db, $lang, $tkireg, $template);
