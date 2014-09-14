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
// File: zoneedit.php

require_once './common.php';

Bnt\Login::checkLogin($pdo_db, $lang, $langvars, $bntreg, $template);

$title = $langvars['l_ze_title'];
Bnt\Header::display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Bnt\Translate::load($pdo_db, $lang, array('zoneedit', 'report', 'port', 'main', 'zoneinfo', 'common', 'global_includes', 'global_funcs', 'footer', 'news'));
echo "<h1>" . $title . "</h1>\n";

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$command = null;
$command = filter_input(INPUT_GET, 'command', FILTER_SANITIZE_EMAIL);
if (mb_strlen(trim($command)) === 0)
{
    $command = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$zone = null;
$zone = filter_input(INPUT_GET, 'zone', FILTER_SANITIZE_EMAIL);
if (mb_strlen(trim($zone)) === 0)
{
    $zone = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$name = null;
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_EMAIL);
if (mb_strlen(trim($name)) === 0)
{
    $name = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$beacons = null;
$beacons = filter_input(INPUT_POST, 'beacons', FILTER_SANITIZE_EMAIL);
if (mb_strlen(trim($beacons)) === 0)
{
    $beacons = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$attacks = null;
$attacks = filter_input(INPUT_POST, 'attacks', FILTER_SANITIZE_EMAIL);
if (mb_strlen(trim($attacks)) === 0)
{
    $attacks = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$warpedits = null;
$warpedits = filter_input(INPUT_POST, 'warpedits', FILTER_SANITIZE_EMAIL);
if (mb_strlen(trim($warpedits)) === 0)
{
    $warpedits = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$defenses = null;
$defenses = filter_input(INPUT_POST, 'defenses', FILTER_SANITIZE_EMAIL);
if (mb_strlen(trim($defenses)) === 0)
{
    $defenses = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$planets = null;
$planets = filter_input(INPUT_POST, 'planets', FILTER_SANITIZE_EMAIL);
if (mb_strlen(trim($planets)) === 0)
{
    $planets = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$trades = null;
$trades = filter_input(INPUT_POST, 'trades', FILTER_SANITIZE_EMAIL);
if (mb_strlen(trim($trades)) === 0)
{
    $trades = false;
}

$res = $db->Execute("SELECT * FROM {$db->prefix}zones WHERE zone_id=?", array($zone));
Bnt\Db::logDbErrors($db, $res, __LINE__, __FILE__);
if ($res->EOF)
{
    echo "<p>" . $langvars['l_zi_nexist'] . "<p>";
    Bnt\Text::gotoMain($db, $lang, $langvars);
    Bnt\Footer::display($pdo_db, $lang, $bntreg, $template);
    die();
}
$curzone = $res->fields;

// Sanitize ZoneName.
$curzone['zone_name'] = preg_replace('/[^A-Za-z0-9\_\s\-\.\']+/', '', $curzone['zone_name']);

if ($curzone['corp_zone'] == 'N')
{
    $result = $db->Execute("SELECT ship_id FROM {$db->prefix}ships WHERE email = ?;", array($_SESSION['username']));
    Bnt\Db::logDbErrors($db, $result, __LINE__, __FILE__);
    $ownerinfo = $result->fields;
}
else
{
    $result = $db->Execute("SELECT creator, id FROM {$db->prefix}teams WHERE creator = ?;", array($curzone['owner']));
    Bnt\Db::logDbErrors($db, $result, __LINE__, __FILE__);
    $ownerinfo = $result->fields;
}

if (($curzone['corp_zone'] == 'N' && $curzone['owner'] != $ownerinfo['ship_id']) || ($curzone['corp_zone'] == 'Y' && $curzone['owner'] != $ownerinfo['id'] && $row['owner'] == $ownerinfo['creator']))
{
    echo "<p>" . $langvars['l_ze_notowner'] . "<p>";
    Bnt\Text::gotoMain($db, $lang, $langvars);
    Bnt\Footer::display($pdo_db, $lang, $bntreg, $template);
    die();
}

if ($command == 'change')
{
    // Sanitize zone name.
    $name = preg_replace('/[^A-Za-z0-9\_\s\-\.\']+/', '', $name);

    if (!get_magic_quotes_gpc())
    {
        $name = addslashes($name);
    }

    $resx = $db->Execute("UPDATE {$db->prefix}zones SET zone_name = ?, allow_beacon = ?, allow_attack = ?, allow_warpedit = ?, allow_planet = ?, allow_trade = ?, allow_defenses = ? WHERE zone_id = ?;", array($name, $beacons, $attacks, $warpedits, $planets, $trades, $defenses, $zone));
    Bnt\Db::logDbErrors($db, $resx, __LINE__, __FILE__);
    echo $langvars['l_ze_saved'] . "<p>";
    echo "<a href=zoneinfo.php?zone=$zone>" . $langvars['l_clickme'] . "</a> " . $langvars['l_ze_return'] . ".<p>";
    Bnt\Text::gotoMain($db, $lang, $langvars);
    Bnt\Footer::display($pdo_db, $lang, $bntreg, $template);
    die();
}

$ybeacon = null;
$nbeacon = null;
$lbeacon = null;
if ($curzone['allow_beacon'] == 'Y')
{
    $ybeacon = "checked";
}
elseif ($curzone['allow_beacon'] == 'N')
{
    $nbeacon = "checked";
}
else
{
    $lbeacon = "checked";
}

$yattack = null;
$nattack = null;
if ($curzone['allow_attack'] == 'Y')
{
    $yattack = "checked";
}
else
{
    $nattack = "checked";
}

$ywarpedit = null;
$nwarpedit = null;
$lwarpedit = null;
if ($curzone['allow_warpedit'] == 'Y')
{
    $ywarpedit = "checked";
}
elseif ($curzone['allow_warpedit'] == 'N')
{
    $nwarpedit = "checked";
}
else
{
    $lwarpedit = "checked";
}

$yplanet = null;
$nplanet = null;
$lplanet = null;
if ($curzone['allow_planet'] == 'Y')
{
    $yplanet = "checked";
}
elseif ($curzone['allow_planet'] == 'N')
{
    $nplanet = "checked";
}
else
{
    $lplanet = "checked";
}

$ytrade = null;
$ntrade = null;
$ltrade = null;
if ($curzone['allow_trade'] == 'Y')
{
    $ytrade = "checked";
}
elseif ($curzone['allow_trade'] == 'N')
{
    $ntrade = "checked";
}
else
{
    $ltrade = "checked";
}

$ydefense = null;
$ndefense = null;
$ldefense = null;
if ($curzone['allow_defenses'] == 'Y')
{
    $ydefense = "checked";
}
elseif ($curzone['allow_defenses'] == 'N')
{
    $ndefense = "checked";
}
else
{
    $ldefense = "checked";
}

echo "<form accept-charset='utf-8' action=zoneedit.php?command=change&zone=$zone method=post>" .
     "<table border=0><tr>" .
     "<td align=right><font size=2><strong>" . $langvars['l_ze_name'] . " : &nbsp;</strong></font></td>" .
     "<td><input type=text name=name size=30 maxlength=30 value=\"$curzone[zone_name]\"></td>" .
     "</tr><tr>" .
     "<td align=right><font size=2><strong>" . $langvars['l_ze_allow'] . " " . $langvars['l_beacons'] . " : &nbsp;</strong></font></td>" .
     "<td><input type=radio name=beacons value=Y $ybeacon>&nbsp;" . $langvars['l_yes'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=beacons value=N $nbeacon>&nbsp;" . $langvars['l_no'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=beacons value=L $lbeacon>&nbsp;" . $langvars['l_zi_limit'] . "</td>" .
     "</tr><tr>" .
     "<td align=right><font size=2><strong>" . $langvars['l_ze_attacks'] . " : &nbsp;</strong></font></td>" .
     "<td><input type=radio name=attacks value=Y $yattack>&nbsp;" . $langvars['l_yes'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=attacks value=N $nattack>&nbsp;" . $langvars['l_no'] . "</td>" .
     "</tr><tr>" .
     "<td align=right><font size=2><strong>" . $langvars['l_ze_allow'] . " " . $langvars['l_warpedit'] . " : &nbsp;</strong></font></td>" .
     "<td><input type=radio name=warpedits value=Y $ywarpedit>&nbsp;" . $langvars['l_yes'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=warpedits value=N $nwarpedit>&nbsp;" . $langvars['l_no'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=warpedits value=L $lwarpedit>&nbsp;" . $langvars['l_zi_limit'] . "</td>" .
     "</tr><tr>" .
     "<td align=right><font size=2><strong>" . $langvars['l_zi_allow'] . " " . $langvars['l_sector_def'] . " : &nbsp;</strong></font></td>" .
     "<td><input type=radio name=defenses value=Y $ydefense>&nbsp;" . $langvars['l_yes'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=defenses value=N $ndefense>&nbsp;" . $langvars['l_no'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=defenses value=L $ldefense>&nbsp;" . $langvars['l_zi_limit'] . "</td>" .
     "</tr><tr>" .
     "<td align=right><font size=2><strong>" . $langvars['l_ze_genesis'] . " : &nbsp;</strong></font></td>" .
     "<td><input type=radio name=planets value=Y $yplanet>&nbsp;" . $langvars['l_yes'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=planets value=N $nplanet>&nbsp;" . $langvars['l_no'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=planets value=L $lplanet>&nbsp;" . $langvars['l_zi_limit'] . "</td>" .
     "</tr><tr>" .
     "<td align=right><font size=2><strong>" . $langvars['l_zi_allow'] . " " . $langvars['l_title_port'] . " : &nbsp;</strong></font></td>" .
     "<td><input type=radio name=trades value=Y $ytrade>&nbsp;" . $langvars['l_yes'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=trades value=N $ntrade>&nbsp;" . $langvars['l_no'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=trades value=L $ltrade>&nbsp;" . $langvars['l_zi_limit'] . "</td>" .
     "</tr><tr>" .
     "<td colspan=2 align=center><br><input type=submit value=" . $langvars['l_submit'] . "></td></tr>" .
     "</table>" .
     "</form>";

echo "<a href=zoneinfo.php?zone=$zone>" . $langvars['l_clickme'] . "</a> " . $langvars['l_ze_return'] . ".<p>";
Bnt\Text::gotoMain($db, $lang, $langvars);
Bnt\Footer::display($pdo_db, $lang, $bntreg, $template);
