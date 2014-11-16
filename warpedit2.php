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
// File: warpedit2.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $langvars, $tkireg, $template);

$title = $langvars['l_warp_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('warpedit', 'common', 'global_includes', 'global_funcs', 'footer', 'news'));
echo "<h1>" . $title . "</h1>\n";

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$oneway = null;
$oneway = filter_input(INPUT_POST, 'oneway', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($oneway)) === 0)
{
    $oneway = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$target_sector = null;
$target_sector = (int) filter_input(INPUT_POST, 'target_sector', FILTER_SANITIZE_NUMBER_INT);
if (mb_strlen(trim($target_sector)) === 0)
{
    $target_sector = false;
}

$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email = ?;", array($_SESSION['username']));
Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);
$playerinfo = $result->fields;

if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_warp_turn'] . "<br><br>";
    Tki\Text::gotoMain($db, $lang, $langvars);
    Tki\Footer::display($pdo_db, $lang, $tkireg, $template, $langvars);
    die();
}

if ($playerinfo['dev_warpedit'] < 1)
{
    echo $langvars['l_warp_none'] . "<br><br>";
    Tki\Text::gotoMain($db, $lang, $langvars);
    Tki\Footer::display($pdo_db, $lang, $tkireg, $template, $langvars);
    die();
}

if (is_null($target_sector))
{
    // This is the best that I can do without adding a new language variable.
    $langvars['l_warp_twoerror'] = str_replace('[target_sector]', $langvars['l_unknown'], $langvars['l_warp_twoerror']);
    echo $langvars['l_warp_twoerror'] ."<br><br>";
    Tki\Text::gotoMain($db, $lang, $langvars);
    die();
}

$res = $db->Execute("SELECT allow_warpedit,{$db->prefix}universe.zone_id FROM {$db->prefix}zones, {$db->prefix}universe WHERE sector_id=? AND {$db->prefix}universe.zone_id = {$db->prefix}zones.zone_id;", array($playerinfo['sector']));
Tki\Db::logDbErrors($db, $res, __LINE__, __FILE__);
$zoneinfo = $res->fields;
if ($zoneinfo['allow_warpedit'] == 'N')
{
    echo $langvars['l_warp_forbid'] . "<br><br>";
    Tki\Text::gotoMain($db, $lang, $langvars);
    Tki\Footer::display($pdo_db, $lang, $tkireg, $template, $langvars);
    die();
}

$target_sector = round($target_sector);
$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email = ?;", array($_SESSION['username']));
Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);
$playerinfo = $result->fields;

$result2 = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id = ?;", array($target_sector));
Tki\Db::logDbErrors($db, $result2, __LINE__, __FILE__);
$row = $result2->fields;
if (!$row)
{
    echo $langvars['l_warp_nosector'] . "<br><br>";
    Tki\Text::gotoMain($db, $lang, $langvars);
    die();
}

$res = $db->Execute("SELECT allow_warpedit,{$db->prefix}universe.zone_id FROM {$db->prefix}zones, {$db->prefix}universe WHERE sector_id=? AND {$db->prefix}universe.zone_id = {$db->prefix}zones.zone_id;", array($target_sector));
Tki\Db::logDbErrors($db, $res, __LINE__, __FILE__);
$zoneinfo = $res->fields;
if ($zoneinfo['allow_warpedit'] == 'N' && !$oneway)
{
    $langvars['l_warp_twoerror'] = str_replace("[target_sector]", $target_sector, $langvars['l_warp_twoerror']);
    echo $langvars['l_warp_twoerror'] . "<br><br>";
    Tki\Text::gotoMain($db, $lang, $langvars);
    Tki\Footer::display($pdo_db, $lang, $tkireg, $template, $langvars);
    die();
}

$res = $db->Execute("SELECT COUNT(*) as count FROM {$db->prefix}links WHERE link_start = ?;", array($playerinfo['sector']));
Tki\Db::logDbErrors($db, $res, __LINE__, __FILE__);
$row = $res->fields;
$numlink_start = $row['count'];

if ($numlink_start >= $max_links)
{
    $langvars['l_warp_sectex'] = str_replace("[link_max]", $max_links, $langvars['l_warp_sectex']);
    echo $langvars['l_warp_sectex'] . "<br><br>";
    Tki\Text::gotoMain($db, $lang, $langvars);
    Tki\Footer::display($pdo_db, $lang, $tkireg, $template, $langvars);
    die();
}

$result3 = $db->Execute("SELECT * FROM {$db->prefix}links WHERE link_start = ?;", array($playerinfo['sector']));
Tki\Db::logDbErrors($db, $result3, __LINE__, __FILE__);
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

    if ($flag == 1)
    {
        $langvars['l_warp_linked'] = str_replace("[target_sector]", $target_sector, $langvars['l_warp_linked']);
        echo $langvars['l_warp_linked'] . "<br><br>";
    }
    elseif ($playerinfo['sector'] == $target_sector)
    {
        echo $langvars['l_warp_cantsame'];
    }
    else
    {
        $insert1 = $db->Execute("INSERT INTO {$db->prefix}links SET link_start=?, link_dest = ?;", array($playerinfo['sector'], $target_sector));
        Tki\Db::logDbErrors($db, $insert1, __LINE__, __FILE__);

        $update1 = $db->Execute("UPDATE {$db->prefix}ships SET dev_warpedit = dev_warpedit - 1, turns = turns - 1, turns_used = turns_used + 1 WHERE ship_id = ?;", array($playerinfo['ship_id']));
        Tki\Db::logDbErrors($db, $update1, __LINE__, __FILE__);

        if (!is_null($oneway))
        {
            echo $langvars['l_warp_coneway'] . " " . $target_sector . " " . "<br><br>";
        }
        else
        {
            $result4 = $db->Execute("SELECT * FROM {$db->prefix}links WHERE link_start = ?;", array($target_sector));
            Tki\Db::logDbErrors($db, $result4, __LINE__, __FILE__);
            if ($result4 instanceof ADORecordSet)
            {
                $flag2 = 0;
                while (!$result4->EOF)
                {
                    $row = $result4->fields;
                    if ($playerinfo['sector'] == $row['link_dest'])
                    {
                        $flag2 = 1;
                    }
                    $result4->MoveNext();
                }
            }
            if ($flag2 != 1)
            {
                $insert2 = $db->Execute("INSERT INTO {$db->prefix}links SET link_start = ?, link_dest = ?;", array($target_sector, $playerinfo['sector']));
                Tki\Db::logDbErrors($db, $insert2, __LINE__, __FILE__);
            }
            echo $langvars['l_warp_ctwoway'] . " " . $target_sector . ".<br><br>";
        }
    }
}

Tki\Text::gotoMain($db, $lang, $langvars);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template, $langvars);
