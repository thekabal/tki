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

Tki\Login::checkLogin($pdo_db, $lang, $langvars, $tkireg, $template);

$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email = ?;", array($_SESSION['username']));
Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);
$playerinfo = $result->fields;

$result2 = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id = ?;", array($playerinfo['sector']));
Tki\Db::logDbErrors($db, $result2, __LINE__, __FILE__);
$sectorinfo = $result2->fields;

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
    $res = $db->Execute("SELECT allow_beacon FROM {$db->prefix}zones WHERE zone_id = ?;", array($sectorinfo['zone_id']));
    Tki\Db::logDbErrors($db, $res, __LINE__, __FILE__);
    $zoneinfo = $res->fields;
    if ($zoneinfo['allow_beacon'] == 'N')
    {
        echo $langvars['l_beacon_notpermitted'] . "<br><br>";
    }
    elseif ($zoneinfo['allow_beacon'] == 'L')
    {
        $result3 = $db->Execute("SELECT * FROM {$db->prefix}zones WHERE zone_id = ?;", array($sectorinfo['zone_id']));
        Tki\Db::logDbErrors($db, $result3, __LINE__, __FILE__);
        $zoneowner_info = $result3->fields;
        $result5 = $db->Execute("SELECT team FROM {$db->prefix}ships WHERE ship_id = ?;", array($zoneowner_info['owner']));
        Tki\Db::logDbErrors($db, $result5, __LINE__, __FILE__);
        $zoneteam = $result5->fields;

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
            echo "<tr><td>" . $langvars['l_beacon_enter'] . ":</td><td><input type=text name=beacon_text size=40 maxlength=80></td></tr>";
            echo "</table>";
            echo "<input type=submit value=" . $langvars['l_submit'] . "><input type=reset value=" . $langvars['l_reset'] . ">";
            echo "</form>";
        }
        else
        {
            $beacon_text = trim(htmlentities($beacon_text, ENT_HTML5, 'UTF-8'));
            echo $langvars['l_beacon_nowreads'] . ": " . $beacon_text . ".<br><br>";
            $update = $db->Execute("UPDATE {$db->prefix}universe SET beacon = ? WHERE sector_id = ?;", array($beacon_text, $sectorinfo['sector_id']));
            Tki\Db::logDbErrors($db, $update, __LINE__, __FILE__);
            $update = $db->Execute("UPDATE {$db->prefix}ships SET dev_beacon=dev_beacon-1 WHERE ship_id = ?;", array($playerinfo['ship_id']));
            Tki\Db::logDbErrors($db, $update, __LINE__, __FILE__);
        }
    }
}
else
{
    echo $langvars['l_beacon_donthave'] . "<br><br>";
}

Tki\Text::gotoMain($db, $lang, $langvars);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
