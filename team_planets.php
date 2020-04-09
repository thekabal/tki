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
// File: team_planets.php

require_once './common.php';

$login = new Tki\Login();
$login->checkLogin($pdo_db, $lang, $tkireg, $template);

$title = $langvars['l_teamplanet_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('team_planets', 'planet_report', 'planet', 'main', 'port', 'common', 'global_includes', 'global_funcs', 'footer', 'news', 'regional'));

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

if ($playerinfo['team'] == 0)
{
    echo "<br>" . $langvars['l_teamplanet_notally'];
    echo "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $template);
    return;
}

$query = "SELECT * FROM {$db->prefix}planets WHERE team = " . $playerinfo['team'];
if ($sort !== null)
{
    $query .= " ORDER BY";
    if ($sort == "name")
    {
        $query .= " $sort ASC";
    }
    elseif ($sort == "organics" || $sort == "ore" || $sort == "goods" || $sort == "energy" || $sort == "colonists" || $sort == "credits" || $sort == "fighters")
    {
        $query .= " $sort DESC";
    }
    elseif ($sort == "torp")
    {
        $query .= " torps DESC";
    }
    else
    {
        $query .= " sector_id ASC";
    }
}

$res = $db->Execute($query);
Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
echo "<h1>" . $title . "</h1>\n";

echo "<br>";
echo "<strong><a href=planet_report.php>" . $langvars['l_teamplanet_personal'] . "</a></strong>";
echo "<br>";
echo "<br>";


$planet = array();
$i = 0;
if ($res)
{
    while (!$res->EOF)
    {
        $planet[$i] = $res->fields;
        $i++;
        $res->Movenext();
    }
}

$num_planets = $i;
if ($num_planets < 1)
{
    echo "<br>" . $langvars['l_teamplanet_noplanet'];
}
else
{
    echo $langvars['l_pr_clicktosort'] . "<br><br>";
    echo "<table width=\"100%\" border=0 cellspacing=0 cellpadding=2>";
    echo "<tr bgcolor=\"$tkireg->color_header\">";
    echo "<td><strong><a href=team_planets.php?sort=sector>" . $langvars['l_sector'] . "</a></strong></td>";
    echo "<td><strong><a href=team_planets.php?sort=name>" . $langvars['l_name'] . "</a></strong></td>";
    echo "<td><strong><a href=team_planets.php?sort=ore>" . $langvars['l_ore'] . "</a></strong></td>";
    echo "<td><strong><a href=team_planets.php?sort=organics>" . $langvars['l_organics'] . "</a></strong></td>";
    echo "<td><strong><a href=team_planets.php?sort=goods>" . $langvars['l_goods'] . "</a></strong></td>";
    echo "<td><strong><a href=team_planets.php?sort=energy>" . $langvars['l_energy'] . "</a></strong></td>";
    echo "<td><strong><a href=team_planets.php?sort=colonists>" . $langvars['l_colonists'] . "</a></strong></td>";
    echo "<td><strong><a href=team_planets.php?sort=credits>" . $langvars['l_credits'] . "</a></strong></td>";
    echo "<td><strong><a href=team_planets.php?sort=fighters>" . $langvars['l_fighters'] . "</a></strong></td>";
    echo "<td><strong><a href=team_planets.php?sort=torp>" . $langvars['l_torps'] . "</a></strong></td>";
    echo "<td><strong>" . $langvars['l_base'] . "?</strong></td><td><strong>" . $langvars['l_selling'] . "?</strong></td>";
    echo "<td><strong>Player</strong></td>";
    echo "</tr>";
    $total_organics = 0;
    $total_ore = 0;
    $total_goods = 0;
    $total_energy = 0;
    $total_colonists = 0;
    $total_credits = 0;
    $total_fighters = 0;
    $total_torp = 0;
    $total_base = 0;
    $total_selling = 0;
    $color = $tkireg->color_line1;
    for ($i = 0; $i < $num_planets; $i++)
    {
        $total_organics += $planet[$i]['organics'];
        $total_ore += $planet[$i]['ore'];
        $total_goods += $planet[$i]['goods'];
        $total_energy += $planet[$i]['energy'];
        $total_colonists += $planet[$i]['colonists'];
        $total_credits += $planet[$i]['credits'];
        $total_fighters += $planet[$i]['fighters'];
        $total_torp += $planet[$i]['torps'];
        if ($planet[$i]['base'] == "Y")
        {
            $total_base++;
        }

        if ($planet[$i]['sells'] == "Y")
        {
            $total_selling++;
        }

        if (empty($planet[$i]['name']))
        {
            $planet[$i]['name'] = $langvars['l_unnamed'];
        }

        $owner = $planet[$i]['owner'];
        $res = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE ship_id = " . $owner);
        Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
        $player = $res->fields['character_name'];

        echo "<tr bgcolor=\"$color\">";
        echo "<td><a href=rsmove.php?engage=1&destination=" . $planet[$i]['sector_id'] . ">" . $planet[$i]['sector_id'] . "</a></td>";
        echo "<td>" . $planet[$i]['name']              . "</td>";
        echo "<td>" . number_format($planet[$i]['ore'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep'])       . "</td>";
        echo "<td>" . number_format($planet[$i]['organics'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep'])  . "</td>";
        echo "<td>" . number_format($planet[$i]['goods'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep'])     . "</td>";
        echo "<td>" . number_format($planet[$i]['energy'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep'])    . "</td>";
        echo "<td>" . number_format($planet[$i]['colonists'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
        echo "<td>" . number_format($planet[$i]['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep'])   . "</td>";
        echo "<td>" . number_format($planet[$i]['fighters'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep'])  . "</td>";
        echo "<td>" . number_format($planet[$i]['torps'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep'])     . "</td>";
        echo "<td>" . ($planet[$i]['base'] == 'Y' ? $langvars['l_yes'] : $langvars['l_no']) . "</td>";
        echo "<td>" . ($planet[$i]['sells'] == 'Y' ? $langvars['l_yes'] : $langvars['l_no']) . "</td>";
        echo "<td>" . $player                        . "</td>";
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

    echo "<tr bgcolor=\"$color\">";
    echo "<td></td>";
    echo "<td>" . $langvars['l_pr_totals'] . "</td>";
    echo "<td>" . number_format($total_ore, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
    echo "<td>" . number_format($total_organics, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
    echo "<td>" . number_format($total_goods, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
    echo "<td>" . number_format($total_energy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
    echo "<td>" . number_format($total_colonists, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
    echo "<td>" . number_format($total_credits, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
    echo "<td>" . number_format($total_fighters, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
    echo "<td>" . number_format($total_torp, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
    echo "<td>" . number_format($total_base, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
    echo "<td>" . number_format($total_selling, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
    echo "<td></td>";
    echo "</tr>";
    echo "</table>";
}

echo "<br><br>";
Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
