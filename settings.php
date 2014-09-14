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
// File: settings.php

require_once './common.php';

$link = null;
if (array_key_exists('lang', $_GET))
{
    $link = "?lang=" . $_GET['lang'];
}

// Database driven language entries
$langvars = Bnt\Translate::load($pdo_db, $lang, array('settings', 'common', 'global_includes', 'global_funcs', 'footer', 'news', 'main', 'regional'));
$title = $langvars['l_settings'];
Bnt\Header::display($pdo_db, $lang, $template, $title);

$line_color = $bntreg->color_line1;

function title($value, $align = "center")
{
    echo "<tr bgcolor=\"$line_color\"><td colspan=\"2\" style='text-align:{$align};'>{$value}</td></tr>\n";
    if ($line_color == $bntreg->color_line1)
    {
        $line_color = $bntreg->color_line2;
    }
    else
    {
        $line_color = $bntreg->color_line1;
    }
}

function line($item, $value, $align = "left")
{
    echo "<tr bgcolor=\"$line_color\"><td>&nbsp;{$item}</td><td style='text-align:{$align};'>{$value}&nbsp;</td></tr>\n";
    if ($line_color == $bntreg->color_line1)
    {
        $line_color = $bntreg->color_line2;
    }
    else
    {
        $line_color = $bntreg->color_line1;
    }
}

function line2($item, $value, $align = "left")
{
    echo "<tr bgcolor=\"$line_color\"><td style='border-left:1px #FFCC00 solid;'>&nbsp;{$item}</td><td style='text-align:{$align}; border-right:1px #FFCC00 solid;'>{$value}&nbsp;</td></tr>\n";
    if ($line_color == $bntreg->color_line1)
    {
        $line_color = $bntreg->color_line2;
    }
    else
    {
        $line_color = $bntreg->color_line1;
    }
}

function line_a($value, $align = "left")
{
    echo "<tr bgcolor=\"#FFCC00\"><td colspan=\"2\" style='text-align:{$align};'>{$value}</td></tr>\n";
    if ($line_color == $bntreg->color_line1)
    {
        $line_color = $bntreg->color_line2;
    }
    else
    {
        $line_color = $bntreg->color_line1;
    }
}

function line_spacer()
{
    echo "<tr><td colspan='2' style='height:2px; padding:0px;'></td></tr>\n";
    if ($line_color == $bntreg->color_line1)
    {
        $line_color = $bntreg->color_line2;
    }
    else
    {
        $line_color = $bntreg->color_line1;
    }
}

/*
$title="Game Reset Information";
echo "<h1>" . $title . "</h1>\n";
echo "<table style='width:800px; font-size:14px; color:#fff; border:#fff 1px solid;' border='0' cellspacing='0' cellpadding='2'>";
line("Last Reset:", "<span style='color:#ff0; font-size:14px;'>~ {$last_reset}</span>", "right");
line("Next Reset:", "<span style='color:#ff0; font-size:14px;'>~ {$next_reset}</span>", "right");
line("Game Duration:", "<span style='color:#0f0; font-size:14px;'>$duration</span>", "right");
line("Game Status:", "<span style='color:#0f0; font-size:14px;'>". ucfirst($status['status']) ."</span>", "right");
line("Game Type:", "<span style='color:#0f0; font-size:14px;'>". ucfirst($status['type']) ."</span>", "right");
echo "</table>\n";
echo "<br>\n";
echo "<br>\n";
*/

$title="Game Administrators";
echo "<h1>" . $title . "</h1>\n";
$found_blues = 0;
$admin_list = array(); // Define admins here for now, but this needs to be a setting from the admin panel
foreach ($admin_list as $key => $admin)
{
    if ($admin['role'] === "developer" || $admin['role'] === "admin")
    {
        echo "<table style='width:800px; font-size:14px; color:#fff; border:#fff 1px solid;' border='0' cellspacing='0' cellpadding='2'>";
        line("Admin Name:",  "<span style='color:#ff0; font-size:14px;'>{$admin['name']}</span>", "right");
        line("Character:",  "<span style='color:#09f; font-size:14px;'>{$admin['character']}</span>", "right");
        line("Admin Level:", "<span style='color:#09f; font-size:14px;'>{$admin['level']}</span>", "right");
        line("Online:", "<span style='color:#99FF00; font-size:14px;'>Not Enabled</span>", "right");
        echo "</table>\n";
        echo "<br>\n";
        $found_blues +=1;
    }
}

if ($found_blues === 0)
{
    echo "<div style='width:798px; font-size:14px; color:#fff; background-color:#500050; padding-top:2px; padding-bottom:2px; border:#fff 1px solid;'>&nbsp;No Admins or Developers Found.</div>\n";
}
echo "<br>\n";

echo "<h1>" . $title . "</h1>\n";

$title="Game Settings";
echo "<h1>" . $title . "</h1>\n";
echo "<table style='width:800px; font-size:14px; color:#fff; border:#fff 1px solid;' border='0' cellspacing='0' cellpadding='2'>";
line("Game version:", $bntreg->release_version, "right");
line("Game name:", $bntreg->game_name, "right");
line("Average tech level needed to hit mines", $bntreg->mine_hullsize, "right");
line("Averaged Tech level When Emergency Warp Degrades", $bntreg->ewd_maxhullsize, "right");

$num = number_format($sector_max, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
line("Number of Sectors", $num, "right");
line("Maximum Links per sector", $link_max, "right");
line("Maximum average tech level for Federation Sectors", $fed_max_hull, "right");

$bank_enabled = $allow_ibank ? "Yes" : "No";
line("Intergalactic Bank Enabled", $bank_enabled, "right");

if ($allow_ibank)
{
    $rate = $ibank_interest * 100;
    line("IGB Interest rate per update", $rate, "right");

    $rate = $ibank_loaninterest * 100;
    line("IGB Loan rate per update", $rate, "right");
}
line("Tech Level upgrade for Bases", $base_defense, "right");

$num = number_format($colonist_limit, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
line("Colonists Limit", $num, "right");

$num = number_format($bntreg->max_turns, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
line("Maximum number of accumulated turns", $num, "right");
line("Maximum number of planets per sector", $max_planets_sector, "right");
line("Maximum number of traderoutes per player", $max_traderoutes_player, "right");
line("Colonist Production Rate", $colonist_production_rate, "right");
line("Unit of Energy used per sector fighter", $energy_per_fighter, "right");

$rate = $defence_degrade_rate * 100;
line("Sector fighter degradation percentage rate", $rate, "right");
line("Number of planets with bases need for sector ownership&nbsp;", $min_bases_to_own, "right");

$rate = number_format(($interest_rate - 1) * 100, 3, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
line("Planet interest rate", $rate, "right");

$rate = 1 / $colonist_production_rate;

$num = number_format($rate / $fighter_prate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
line("Colonists needed to produce 1 Fighter each turn", $num, "right");

$num = number_format($rate/$torpedo_prate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
line("Colonists needed to produce 1 Torpedo each turn", $num, "right");

$num = number_format($rate/$ore_prate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
line("Colonists needed to produce 1 Ore each turn", $num, "right");

$num = number_format($rate/$organics_prate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
line("Colonists needed to produce 1 Organics each turn", $num, "right");

$num = number_format($rate/$goods_prate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
line("Colonists needed to produce 1 Goods each turn", $num, "right");

$num = number_format($rate/$energy_prate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
line("Colonists needed to produce 1 Energy each turn", $num, "right");

$num = number_format($rate/$credits_prate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
line("Colonists needed to produce 1 Credits each turn", $num, "right");
echo "</table>\n";
echo "<br>\n";
echo "<br>\n";

$title="Game Scheduler Settings";
echo "<h1>" . $title . "</h1>\n";

$line_color = $bntreg->color_line1;

echo "<table style='width:800px; font-size:14px; color:#fff; border:#fff 1px solid;' border='0' cellspacing='0' cellpadding='2'>";
line("Ticks happen every", "{$sched_ticks} minutes", "right");
line("{$bntreg->turns_per_tick} Turns will happen every", "{$bntreg->sched_turns} minutes", "right");
line("Defenses will be checked every", "{$sched_turns} minutes", "right");
line("Xenobes will play every", "{$sched_turns} minutes", "right");

if ($allow_ibank)
{
    line("Interests on IGB accounts will be accumulated every&nbsp;", "{$sched_igb} minutes", "right");
}

line("News will be generated every", "{$sched_news} minutes", "right");
line("Planets will generate production every", "{$sched_planets} minutes", "right");
$use_new_sched_planet = true; // We merged this change in, so all new versions use this
line(" -> Using new Planet Update Code", ($use_new_sched_planet?"<span style='color:#0f0;'>Yes</span>":"<span style='color:#ff0;'>No</span>"), "right");
line(" -> Limit captured planets Max Credits to ". number_format($max_credits_without_base, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), ($sched_planet_valid_credits?"<span style='color:#0f0;'>Yes</span>":"<span style='color:#ff0;'>No</span>"), "right");
line("Ports will regenerate x {$port_regenrate} every", "{$sched_ports} minutes", "right");
line("Ships will be towed from fed sectors every", "{$sched_turns} minutes", "right");
line("Rankings will be generated every", "{$sched_ranking} minutes", "right");
line("Sector Defences will degrade every", "{$sched_degrade} minutes", "right");
line("The planetary apocalypse will occur every&nbsp;", "{$sched_apocalypse} minutes", "right");

echo "</table>";
echo "<br>\n";
echo "<br>\n";

if (empty ($_SESSION['username']))
{
    echo str_replace("[here]", "<a href='index.php" . $link . "'>" . $langvars['l_here'] . "</a>", $langvars['l_global_mlogin']);
}
else
{
    echo str_replace("[here]", "<a href='main.php" . $link . "'>" . $langvars['l_here'] . "</a>", $langvars['l_global_mmenu']);
}

Bnt\Footer::display($pdo_db, $lang, $bntreg, $template);
?>
