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
// File: warpedit3.php

require_once './common.php';

Bnt\Login::checkLogin($pdo_db, $lang, $langvars, $bntreg, $template);

$title = $langvars['l_warp_title'];
Bnt\Header::display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Bnt\Translate::load($pdo_db, $lang, array('warpedit', 'common', 'global_includes', 'global_funcs', 'footer', 'news'));
echo "<h1>" . $title . "</h1>\n";

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$bothway = null;
$bothway = filter_input(INPUT_POST, 'bothway', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($bothway)) === 0)
{
    $bothway = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$target_sector = null;
$target_sector = (int) filter_input(INPUT_POST, 'target_sector', FILTER_SANITIZE_NUMBER_INT);
if (mb_strlen(trim($target_sector)) === 0)
{
    $target_sector = false;
}

$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email = ?;", array($_SESSION['username']));
Bnt\Db::logDbErrors($db, $result, __LINE__, __FILE__);
$playerinfo = $result->fields;

if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_warp_turn'] . "<br><br>";
    Bnt\Text::gotoMain($db, $lang, $langvars);
    Bnt\Footer::display($pdo_db, $lang, $bntreg, $template);
    die();
}

if ($playerinfo['dev_warpedit'] < 1)
{
    echo $langvars['l_warp_none'] . "<br><br>";
    Bnt\Text::gotoMain($db, $lang, $langvars);
    Bnt\Footer::display($pdo_db, $lang, $bntreg, $template);
    die();
}

if (is_null($target_sector))
{
    // This is the best that I can do without adding a new language variable.
    echo $langvars['l_warp_nosector'] ."<br><br>";
    Bnt\Text::gotoMain($db, $lang, $langvars);
    die();
}

$res = $db->Execute("SELECT allow_warpedit,{$db->prefix}universe.zone_id FROM {$db->prefix}zones,{$db->prefix}universe WHERE sector_id=? AND {$db->prefix}universe.zone_id={$db->prefix}zones.zone_id;", array($playerinfo['sector']));
Bnt\Db::logDbErrors($db, $res, __LINE__, __FILE__);
$zoneinfo = $res->fields;
if ($zoneinfo['allow_warpedit'] == 'N')
{
    echo $langvars['l_warp_forbid'] . "<br><br>";
    Bnt\Text::gotoMain($db, $lang, $langvars);
    Bnt\Footer::display($pdo_db, $lang, $bntreg, $template);
    die();
}

$target_sector = round($target_sector);
$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email = ?;", array($_SESSION['username']));
Bnt\Db::logDbErrors($db, $result, __LINE__, __FILE__);
$playerinfo = $result->fields;

$res = $db->Execute("SELECT allow_warpedit,{$db->prefix}universe.zone_id FROM {$db->prefix}zones,{$db->prefix}universe WHERE sector_id=? AND {$db->prefix}universe.zone_id={$db->prefix}zones.zone_id;", array($target_sector));
Bnt\Db::logDbErrors($db, $res, __LINE__, __FILE__);
$zoneinfo = $res->fields;
if ($zoneinfo['allow_warpedit'] == 'N' && $bothway)
{
    $langvars['l_warp_forbidtwo'] = str_replace("[target_sector]", $target_sector, $langvars['l_warp_forbidtwo']);
    echo $langvars['l_warp_forbidtwo'] . "<br><br>";
    Bnt\Text::gotoMain($db, $lang, $langvars);
    Bnt\Footer::display($pdo_db, $lang, $bntreg, $template);
    die();
}

$result2 = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id = ?;", array($target_sector));
Bnt\Db::logDbErrors($db, $result2, __LINE__, __FILE__);
$row = $result2->fields;
if (!$row)
{
    echo $langvars['l_warp_nosector'] . "<br><br>";
    Bnt\Text::gotoMain($db, $lang, $langvars);
    die();
}

$result3 = $db->Execute("SELECT * FROM {$db->prefix}links WHERE link_start = ?;", array($playerinfo['sector']));
Bnt\Db::logDbErrors($db, $result3, __LINE__, __FILE__);
if ($result3 instanceof ADORecordSet)
{
    $flag = 0;
    while (!$result3->EOF)
    {
        $row = $result3->fields;
        if ($target_sector == $row['link_dest'])
        {
            $flag = 1;
        }
        $result3->MoveNext();
    }
    if ($flag != 1)
    {
        $langvars['l_warp_unlinked'] = str_replace("[target_sector]", $target_sector, $langvars['l_warp_unlinked']);
        echo $langvars['l_warp_unlinked'] . "<br><br>";
    }
    else
    {
        $delete1 = $db->Execute("DELETE FROM {$db->prefix}links WHERE link_start = ? AND link_dest = ?;", array($playerinfo['sector'], $target_sector));
        Bnt\Db::logDbErrors($db, $delete1, __LINE__, __FILE__);

        $update1 = $db->Execute("UPDATE {$db->prefix}ships SET dev_warpedit = dev_warpedit - 1, turns = turns - 1, turns_used = turns_used + 1 WHERE ship_id = ?;", array($playerinfo['ship_id']));
        Bnt\Db::logDbErrors($db, $update1, __LINE__, __FILE__);
        if (is_null($bothway))
        {
            echo $langvars['l_warp_removed'] . " " . $target_sector . ".<br><br>";
        }
        else
        {
            $delete2 = $db->Execute("DELETE FROM {$db->prefix}links WHERE link_start = ? AND link_dest = ?;", array($target_sector, $playerinfo['sector']));
            Bnt\Db::logDbErrors($db, $delete2, __LINE__, __FILE__);
            echo $langvars['l_warp_removedtwo'] . " " . $target_sector . ".<br><br>";
        }
    }
}

Bnt\Text::gotoMain($db, $lang, $langvars);
Bnt\Footer::display($pdo_db, $lang, $bntreg, $template);
?>
