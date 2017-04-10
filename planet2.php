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
// File: planet2.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

$title = $langvars['l_planet2_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('main', 'report', 'planet', 'bounty', 'common', 'global_includes', 'global_funcs', 'footer', 'news'));

// Needs to be validated and type cast into their correct types.
// [GET]
// (int) planet_id
//
// [POST]
// (int) transfer_ore
//          tpore
//          allore
// (int) transfer_organics
//          tporganics
//          allorganics
// (int) transfer_goods
//          tpgoods
//          allgoods
// (int) transfer_energy
//          tpenergy
//          allenergy
// (int) transfer_colonists
//          tpcolonists
//          allcolonists
// (int) transfer_fighters
//          tpfighters
//          allfighters
// (int) transfer_torps
//          tptorps
//          alltorps
// (int) transfer_credits
//          tpcredits
//          allcredits

// Array list of valid vars and their types that are alowed for this page.
// I know this is rather crude but it works.
$valid_vars = null;
$valid_vars[] = array("pref" => "_POST", "var" => "transfer_ore", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "tpore", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "allore", "type" => "int");

$valid_vars[] = array("pref" => "_POST", "var" => "transfer_organics", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "tporganics", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "allorganics", "type" => "int");

$valid_vars[] = array("pref" => "_POST", "var" => "transfer_goods", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "tpgoods", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "allgoods", "type" => "int");

$valid_vars[] = array("pref" => "_POST", "var" => "transfer_energy", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "tpenergy", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "allenergy", "type" => "int");

$valid_vars[] = array("pref" => "_POST", "var" => "transfer_colonists", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "tpcolonists", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "allcolonists", "type" => "int");

$valid_vars[] = array("pref" => "_POST", "var" => "transfer_fighters", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "tpfighters", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "allfighters", "type" => "int");

$valid_vars[] = array("pref" => "_POST", "var" => "transfer_torps", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "tptorps", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "alltorps", "type" => "int");

$valid_vars[] = array("pref" => "_POST", "var" => "transfer_credits", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "tpcredits", "type" => "int");
$valid_vars[] = array("pref" => "_POST", "var" => "allcredits", "type" => "int");

$valid_vars[] = array("pref" => "_GET", "var" => "planet_id", "type" => "int");

foreach ($valid_vars as $key => $value)
{
    // Is it found ?
    if (!isset(${$value['pref']}[$value['var']]))
    {
        // if not found set var to 0.
        ${$value['pref']}[$value['var']] = 0;
    }

    // Set var type to set type.
    settype(${$value['pref']}[$value['var']], $value['type']);
}

// Validate and set the type of $_POST vars
$transfer_ore       = (int) $_POST['transfer_ore'];
$tpore              = $_POST['tpore'];
$allore             = $_POST['allore'];

$transfer_organics  = (int) $_POST['transfer_organics'];
$tporganics         = $_POST['tporganics'];
$allorganics        = $_POST['allorganics'];

$transfer_goods     = (int) $_POST['transfer_goods'];
$tpgoods            = $_POST['tpgoods'];
$allgoods           = $_POST['allgoods'];

$transfer_energy    = (int) $_POST['transfer_energy'];
$tpenergy           = $_POST['tpenergy'];
$allenergy          = $_POST['allenergy'];

$transfer_colonists = (int) $_POST['transfer_colonists'];
$tpcolonists        = $_POST['tpcolonists'];
$allcolonists       = $_POST['allcolonists'];

$transfer_fighters  = (int) $_POST['transfer_fighters'];
$tpfighters         = $_POST['tpfighters'];
$allfighters        = $_POST['allfighters'];

$transfer_torps     = (int) $_POST['transfer_torps'];
$tptorps            = $_POST['tptorps'];
$alltorps           = $_POST['alltorps'];

$transfer_credits   = (int) $_POST['transfer_credits'];
$tpcredits          = $_POST['tpcredits'];
$allcredits         = $_POST['allcredits'];

// Validate and set the type of $_GET vars;
$planet_id = (int) $_GET['planet_id'];

// Display Page Title.
echo "<h1>" . $title . "</h1>\n";

// Empty out Planet and Ship vars
$planetinfo = null;
$playerinfo = null;

// Check if planet_id is valid.
if ($planet_id <= 0)
{
    echo "Invalid Planet<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);
    Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
    die();
}

// Get playerinfo from database
$sql = "SELECT * FROM ::prefix::ships WHERE email=:email LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Get the Planet Info
$sql = "SELECT * FROM ::prefix::planets WHERE planet_id=:planet_id AND planet_id > 0 LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':planet_id', $planet_id, PDO::PARAM_INT);
$stmt->execute();
$planetinfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Check to see if it returned valid planet info.
if ($planetinfo === false)
{
    echo "Invalid Planet<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);
    die();
}

// Check to see Ship and Planet are in the same sector
if ($planetinfo['sector_id'] != $playerinfo['sector'])
{
    echo $langvars['l_planet2_sector'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);
    die();
}

// Check if the player has enough turns
if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_planet2_noturn'] . "<br><br>";
}
else
{
    $free_holds = Tki\CalcLevels::holds($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
    $free_power = Tki\CalcLevels::energy($playerinfo['power'], $tkireg) - $playerinfo['ship_energy'];
    $fighter_max = Tki\CalcLevels::fighters($playerinfo['computer'], $tkireg) - $playerinfo['ship_fighters'];
    $torpedo_max = Tki\CalcLevels::torpedoes($playerinfo['torp_launchers'], $tkireg) - $playerinfo['torps'];

    // First setup the tp flags
    if ($tpore != -1)
    {
        $tpore = 1;
    }

    if ($tporganics != -1)
    {
        $tporganics = 1;
    }

    if ($tpgoods != -1)
    {
        $tpgoods = 1;
    }

    if ($tpenergy != -1)
    {
        $tpenergy = 1;
    }

    if ($tpcolonists != -1)
    {
        $tpcolonists = 1;
    }

    if ($tpcredits != -1)
    {
        $tpcredits = 1;
    }

    if ($tptorps != -1)
    {
        $tptorps = 1;
    }

    if ($tpfighters != -1)
    {
        $tpfighters = 1;
    }

    // Now multiply all the transfer amounts by 1 to eliminate any trailing spaces
    $transfer_ore       = $transfer_ore * 1;
    $transfer_organics  = $transfer_organics * 1;
    $transfer_goods     = $transfer_goods * 1;
    $transfer_energy    = $transfer_energy * 1;
    $transfer_colonists = $transfer_colonists * 1;
    $transfer_credits   = $transfer_credits * 1;
    $transfer_torps     = $transfer_torps * 1;
    $transfer_fighters  = $transfer_fighters * 1;

    if ($allore == -1)
    {
        if ($tpore == -1)
        {
            $transfer_ore = $playerinfo['ship_ore'];
        }
        else
        {
            $transfer_ore = $planetinfo['ore'];
        }
    }

    if ($allorganics == -1)
    {
        if ($tporganics == -1)
        {
            $transfer_organics = $playerinfo['ship_organics'];
        }
        else
        {
            $transfer_organics = $planetinfo['organics'];
        }
    }

    if ($allgoods == -1)
    {
        if ($tpgoods == -1)
        {
            $transfer_goods = $playerinfo['ship_goods'];
        }
        else
        {
            $transfer_goods = $planetinfo['goods'];
        }
    }

    if ($allenergy == -1)
    {
        if ($tpenergy == -1)
        {
            $transfer_energy = $playerinfo['ship_energy'];
        }
        else
        {
            $transfer_energy = $planetinfo['energy'];
        }
    }

    if ($allcolonists == -1)
    {
        if ($tpcolonists == -1)
        {
            $transfer_colonists = $playerinfo['ship_colonists'];
        }
        else
        {
            $transfer_colonists = $planetinfo['colonists'];
        }
    }

    if ($allcredits == -1)
    {
        if ($tpcredits == -1)
        {
            $transfer_credits = $playerinfo['credits'];
        }
        else
        {
            $transfer_credits = $planetinfo['credits'];
        }
    }

    if ($alltorps == -1)
    {
        if ($tptorps == -1)
        {
            $transfer_torps = $playerinfo['torps'];
        }
        else
        {
            $transfer_torps = $planetinfo['torps'];
        }
    }

    if ($allfighters == -1)
    {
        if ($tpfighters == -1)
        {
            $transfer_fighters = $playerinfo['ship_fighters'];
        }
        else
        {
            $transfer_fighters = $planetinfo['fighters'];
        }
    }

    // ok now get rid of all negative amounts so that all operations are expressed in terms of positive units
    if ($transfer_ore < 0)
    {
        $transfer_ore = -1 * $transfer_ore;
        $tpore = -1 * $tpore;
    }

    if ($transfer_organics < 0)
    {
        $transfer_organics = -1 * $transfer_organics;
        $tporganics = -1 * $tporganics;
    }

    if ($transfer_goods < 0)
    {
        $transfer_goods = -1 * $transfer_goods;
        $tpgoods = -1 * $tpgoods;
    }

    if ($transfer_energy < 0)
    {
        $transfer_energy = -1 * $transfer_energy;
        $tpenergy = -1 * $tpenergy;
    }

    if ($transfer_colonists < 0)
    {
        $transfer_colonists = -1 * $transfer_colonists;
        $tpcolonists = -1 * $tpcolonists;
    }

    if ($transfer_credits < 0)
    {
        $transfer_credits = -1 * $transfer_credits;
        $tpcredits = -1 * $tpcredits;
    }

    if ($transfer_torps < 0)
    {
        $transfer_torps = -1 * $transfer_torps;
        $tptorps = -1 * $tptorps;
    }

    if ($transfer_fighters < 0)
    {
        $transfer_fighters = -1 * $transfer_fighters;
        $tpfighters = -1 * $tpfighters;
    }

    // Now make sure that the source for each commodity transfer has sufficient numbers to fill the transfer
    if (($tpore == -1) && ($transfer_ore > $playerinfo['ship_ore']))
    {
        $transfer_ore = $playerinfo['ship_ore'];
        echo $langvars['l_planet2_noten'] . " " .  $langvars['l_ore'] . ". " . $langvars['l_planet2_settr'] . " " . $transfer_ore . " " . $langvars['l_units'] . " " . $langvars['l_ore'] . ".<br>\n";
    }
    elseif (($tpore == 1) && ($transfer_ore > $planetinfo['ore']))
    {
        $transfer_ore = $planetinfo['ore'];
        echo $langvars['l_planet2_losup'] . " " . $transfer_ore . " " . $langvars['l_units'] . " " . $langvars['l_ore'] . ".<br>\n";
    }

    if (($tporganics == -1) && ($transfer_organics > $playerinfo['ship_organics']))
    {
        $transfer_organics = $playerinfo['ship_organics'];
        echo $langvars['l_planet2_noten'] . " " . $langvars['l_organics'] . ". " . $langvars['l_planet2_settr'] . " " . $transfer_organics . " " . $langvars['l_units'] . ".<br>\n";
    }
    elseif (($tporganics == 1) && ($transfer_organics > $planetinfo['organics']))
    {
        $transfer_organics = $planetinfo['organics'];
        echo $langvars['l_planet2_losup'] . " " . $transfer_organics . " " . $langvars['l_units'] . " " . $langvars['l_organics'] . ".<br>\n";
    }

    if (($tpgoods == -1) && ($transfer_goods > $playerinfo['ship_goods']))
    {
        $transfer_goods = $playerinfo['ship_goods'];
        echo $langvars['l_planet2_noten'] . " " .  $langvars['l_goods'] . ". " . $langvars['l_planet2_settr'] . " " . $transfer_goods . " " . $langvars['l_units'] . ".<br>\n";
    }
    elseif (($tpgoods == 1) && ($transfer_goods > $planetinfo['goods']))
    {
        $transfer_goods = $planetinfo['goods'];
        echo $langvars['l_planet2_losup'] . " " . $transfer_goods . " " . $langvars['l_units'] . " " . $langvars['l_goods'] . ".<br>\n";
    }

    if (($tpenergy == -1) && ($transfer_energy > $playerinfo['ship_energy']))
    {
        $transfer_energy = $playerinfo['ship_energy'];
        echo $langvars['l_planet2_noten'] . " " . $langvars['l_energy'] . ". " . $langvars['l_planet2_settr'] . " " . $transfer_energy . " " . $langvars['l_units'] . ".<br>\n";
    }
    elseif (($tpenergy == 1) && ($transfer_energy > $planetinfo['energy']))
    {
        $transfer_energy = $planetinfo['energy'];
        echo $langvars['l_planet2_losup'] . " " . $transfer_energy . " " . $langvars['l_units'] . " " . $langvars['l_energy'] . ".<br>\n";
    }

    if (($tpcolonists == -1) && ($transfer_colonists > $playerinfo['ship_colonists']))
    {
        $transfer_colonists = $playerinfo['ship_colonists'];
        echo $langvars['l_planet2_noten'] . " " . $langvars['l_colonists'] . ". " . $langvars['l_planet2_settr'] . " " . $transfer_colonists . " " . $langvars['l_colonists'] . ".<br>\n";
    }
    elseif (($tpcolonists == 1) && ($transfer_colonists > $planetinfo['colonists']))
    {
        $transfer_colonists = $planetinfo['colonists'];
        echo $langvars['l_planet2_losup'] . " " . $transfer_colonists . " " . $langvars['l_colonists'] . ".<br>\n";
    }

    if (($tpcredits == -1) && ($transfer_credits > $playerinfo['credits']))
    {
        $transfer_credits = $playerinfo['credits'];
        echo $langvars['l_planet2_noten'] . " " . $langvars['l_credits'] . ". " . $langvars['l_planet2_settr'] . " " . $transfer_credits . " " . $langvars['l_credits'] . ".<br>\n";
    }
    elseif (($tpcredits == 1) && ($transfer_credits > $planetinfo['credits']))
    {
        $transfer_credits = $planetinfo['credits'];
        echo $langvars['l_planet2_losup'] . " " . $transfer_credits . " " . $langvars['l_credits'] . ".<br>\n";
    }

    if (($tpcredits == -1) && $planetinfo['base'] == 'N' && ($transfer_credits + $planetinfo['credits'] > $max_credits_without_base))
    {
        $transfer_credits = max($max_credits_without_base - $planetinfo['credits'], 0);
        echo $langvars['l_planet2_baseexceeded'] . " " . $langvars['l_planet2_settr'] . " " .  $transfer_credits . " " . $langvars['l_credits'] . ".<br>\n";
    }

    if (($tptorps == -1) && ($transfer_torps > $playerinfo['torps']))
    {
        $transfer_torps = $playerinfo['torps'];
        echo $langvars['l_planet2_noten'] . " " .  $langvars['l_torps'] . ". " . $langvars['l_planet2_settr'] . " " . $transfer_torps . " " . $langvars['l_torps'] . ".<br>\n";
    }
    elseif (($tptorps == 1) && ($transfer_torps > $planetinfo['torps']))
    {
        $transfer_torps = $planetinfo['torps'];
        echo $langvars['l_planet2_losup'] . " " . $transfer_torps . " " . $langvars['l_torps'] . ".<br>\n";
    }

    if (($tpfighters == -1) && ($transfer_fighters > $playerinfo['ship_fighters']))
    {
        $transfer_fighters = $playerinfo['ship_fighters'];
        echo $langvars['l_planet2_noten'] . " " . $langvars['l_fighters'] . ". " . $langvars['l_planet2_settr'] . " " . $transfer_fighters . " " . $langvars['l_fighters'] . ".<br>\n";
    }
    elseif (($tpfighters == 1) && ($transfer_fighters > $planetinfo['fighters']))
    {
        $transfer_fighters = $planetinfo['fighters'];
        echo $langvars['l_planet2_losup'] . " " . $transfer_fighters . " " . $langvars['l_fighters'] . ".<br>\n";
    }

    // Now that we have the amounts adjusted to suit available resources, go ahead and multiply them by their tpflag.
    $transfer_ore = $transfer_ore * $tpore;
    $transfer_organics = $transfer_organics * $tporganics;
    $transfer_goods = $transfer_goods * $tpgoods;
    $transfer_energy = $transfer_energy * $tpenergy;
    $transfer_colonists = $transfer_colonists * $tpcolonists;
    $transfer_credits = $transfer_credits * $tpcredits;
    $transfer_torps = $transfer_torps * $tptorps;
    $transfer_fighters = $transfer_fighters * $tpfighters;

    $total_holds_needed = $transfer_ore + $transfer_organics + $transfer_goods + $transfer_colonists;

    if ($playerinfo['ship_id'] != $planetinfo['owner'] && $transfer_credits != 0 && !$team_planet_transfers)
    {
        echo $langvars['l_planet2_noteamtransfer'] . "<p>";
        echo "<a href=planet.php?planet_id=$planet_id>" . $langvars['l_clickme'] . "</a> " . $langvars['l_toplanetmenu'] . "<br><br>";
    }
    elseif ($total_holds_needed > $free_holds)
    {
        echo $langvars['l_planet2_noten'] . " " . $langvars['l_holds'] . " " . $langvars['l_planet2_fortr'] . "<br><br>";
        echo "<a href=planet.php?planet_id=$planet_id>" . $langvars['l_clickme'] . "</a> " . $langvars['l_toplanetmenu'] . "<br><br>";
    }
    else
    {
        if ($planetinfo !== null)
        {
            if ($planetinfo['owner'] == $playerinfo['ship_id'] || ($planetinfo['team'] == $playerinfo['team'] && $playerinfo['team'] != 0))
            {
                if ($transfer_ore < 0 && $playerinfo['ship_ore'] < abs($transfer_ore))
                {
                    echo $langvars['l_planet2_noten'] . " " . $langvars['l_ore'] . " " . $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_ore = 0;
                }
                elseif ($transfer_ore > 0 && $planetinfo['ore'] < abs($transfer_ore))
                {
                    echo $langvars['l_planet2_noten'] . " " . $langvars['l_ore'] . " " . $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_ore = 0;
                }

                if ($transfer_organics < 0 && $playerinfo['ship_organics'] < abs($transfer_organics))
                {
                    echo $langvars['l_planet2_noten'] . " " . $langvars['l_organics'] . " " .  $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_organics = 0;
                }
                elseif ($transfer_organics > 0 && $planetinfo['organics'] < abs($transfer_organics))
                {
                    echo $langvars['l_planet2_noten'] . " " .  $langvars['l_organics'] . " " .  $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_organics = 0;
                }

                if ($transfer_goods < 0 && $playerinfo['ship_goods'] < abs($transfer_goods))
                {
                    echo $langvars['l_planet2_noten'] . " " . $langvars['l_goods'] . " " . $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_goods = 0;
                }
                elseif ($transfer_goods > 0 && $planetinfo['goods'] < abs($transfer_goods))
                {
                    echo $langvars['l_planet2_noten'] . " " .  $langvars['l_goods'] . " " .  $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_goods = 0;
                }

                if ($transfer_energy < 0 && $playerinfo['ship_energy'] < abs($transfer_energy))
                {
                    echo $langvars['l_planet2_noten'] . " " . $langvars['l_energy'] . " " . $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_energy = 0;
                }
                elseif ($transfer_energy > 0 && $planetinfo['energy'] < abs($transfer_energy))
                {
                    echo $langvars['l_planet2_noten'] . " " .  $langvars['l_energy'] . " " .  $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_energy = 0;
                }
                elseif ($transfer_energy > 0 && abs($transfer_energy) > $free_power)
                {
                    echo $langvars['l_planet2_noten'] . " " .  $langvars['l_planet2_power'] . " " .  $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_energy = 0;
                }

                if ($transfer_colonists < 0 && $playerinfo['ship_colonists'] < abs($transfer_colonists))
                {
                    echo $langvars['l_planet2_noten'] . " " . $langvars['l_colonists'] . " " .  $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_colonists = 0;
                }
                elseif ($transfer_colonists > 0 && $planetinfo['colonists'] < abs($transfer_colonists))
                {
                    echo $langvars['l_planet2_noten'] . " " .  $langvars['l_colonists'] . " " .  $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_colonists = 0;
                }

                if ($transfer_fighters < 0 && $playerinfo['ship_fighters'] < abs($transfer_fighters))
                {
                    echo $langvars['l_planet2_noten'] . " " . $langvars['l_fighters'] . " " .  $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_fighters = 0;
                }
                elseif ($transfer_fighters > 0 && $planetinfo['fighters'] < abs($transfer_fighters))
                {
                    echo $langvars['l_planet2_noten'] . " " .  $langvars['l_fighters'] . " " .  $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_fighters = 0;
                }
                elseif ($transfer_fighters > 0 && abs($transfer_fighters) > $fighter_max)
                {
                    echo $langvars['l_planet2_noten'] . " " .  $langvars['l_planet2_comp'] . " " .  $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_fighters = 0;
                }

                if ($transfer_torps < 0 && $playerinfo['torps'] < abs($transfer_torps))
                {
                    echo $langvars['l_planet2_noten'] . " " .  $langvars['l_torps'] . " " .  $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_torps = 0;
                }
                elseif ($transfer_torps > 0 && $planetinfo['torps'] < abs($transfer_torps))
                {
                    echo $langvars['l_planet2_noten'] . " " .  $langvars['l_torps'] . " " .  $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_torps = 0;
                }
                elseif ($transfer_torps > 0 && abs($transfer_torps) > $torpedo_max)
                {
                    echo $langvars['l_planet2_noten'] . " " . $langvars['l_planet2_laun'] . " " .  $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_torps = 0;
                }

                if ($transfer_credits < 0 && $playerinfo['credits'] < abs($transfer_credits))
                {
                    echo $langvars['l_planet2_noten'] . " " . $langvars['l_credits'] . " " . $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_credits = 0;
                }
                elseif ($transfer_credits > 0 && $planetinfo['credits'] < abs($transfer_credits))
                {
                    echo $langvars['l_planet2_noten'] . " " . $langvars['l_credits'] . " " .  $langvars['l_planet2_fortr'] . "<br>";
                    $transfer_credits = 0;
                }

                $sql = "UPDATE ::prefix::ships SET ship_ore=ship_ore+:ship_ore, ship_organics=ship_organics+:ship_organics, ship_goods=ship_goods+:ship_goods, ship_energy=ship_energy+:ship_energy, ship_colonists=ship_colonists+:ship_colonists, torps=torps+:torps, ship_fighters=ship_fighters+:ship_fighters, credits=credits+:credits, turns=turns-1, turns_used=turns_used+1 WHERE ship_id=:ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':ship_ore', $transfer_ore, PDO::PARAM_INT);
                $stmt->bindParam(':ship_organics', $transfer_organics, PDO::PARAM_INT);
                $stmt->bindParam(':ship_goods', $transfer_goods, PDO::PARAM_INT);
                $stmt->bindParam(':ship_energy', $transfer_energy, PDO::PARAM_INT);
                $stmt->bindParam(':ship_colonists', $transfer_colonists, PDO::PARAM_INT);
                $stmt->bindParam(':torps', $transfer_torps, PDO::PARAM_INT);
                $stmt->bindParam(':ship_fighters', $transfer_fighters, PDO::PARAM_INT);
                $stmt->bindParam(':credits', $transfer_credits, PDO::PARAM_INT);
                $stmt->bindParam(':ship_id', $playerinfo['ship_id'], PDO::PARAM_INT);
                $stmt->execute();

                $sql = "UPDATE ::prefix::planets SET ore=ore-:ore, organics=organics-:organics, goods=goods-:goods, energy=energy-:energy, colonists=colonists-:colonists, torps=torps-:torps, fighters=fighters-:fighters, credits=credits-:credits WHERE planet_id=:planet_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':ore', $transfer_ore, PDO::PARAM_INT);
                $stmt->bindParam(':organics', $transfer_organics, PDO::PARAM_INT);
                $stmt->bindParam(':goods', $transfer_goods, PDO::PARAM_INT);
                $stmt->bindParam(':energy', $transfer_energy, PDO::PARAM_INT);
                $stmt->bindParam(':colonists', $transfer_colonists, PDO::PARAM_INT);
                $stmt->bindParam(':torps', $transfer_torps, PDO::PARAM_INT);
                $stmt->bindParam(':fighters', $transfer_fighters, PDO::PARAM_INT);
                $stmt->bindParam(':credits', $transfer_credits, PDO::PARAM_INT);
                $stmt->bindParam(':planet_id', $planet_id, PDO::PARAM_INT);
                $stmt->execute();

                echo $langvars['l_planet2_compl'] . "<br><a href=planet.php?planet_id=$planet_id>" . $langvars['l_clickme'] . "</a> " . $langvars['l_toplanetmenu'] . "<br><br>";
            }
            else
            {
                echo $langvars['l_planet2_notowner'] . "<br><br>";
            }
        }
        else
        {
            echo $langvars['l_planet_none'] . "<br><br>";
        }
    }
}

Tki\Text::gotoMain($pdo_db, $lang);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
