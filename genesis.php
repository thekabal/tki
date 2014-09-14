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
// File: genesis.php

// If anyone who's coded this thing is willing to update it to
// support multiple planets, go ahead. I suggest removing this
// code completely from here and putting it in the planet menu
// instead. Easier to manage, makes more sense too.

require_once './common.php';

Bnt\Login::checkLogin($pdo_db, $lang, $langvars, $bntreg, $template);

// Database driven language entries
$langvars = Bnt\Translate::load($pdo_db, $lang, array('genesis', 'common', 'global_includes', 'global_funcs', 'footer', 'news'));
$title = $langvars['l_gns_title'];
Bnt\Header::display($pdo_db, $lang, $template, $title);

// Adding db lock to prevent more than 5 planets in a sector
$resx = $db->Execute("LOCK TABLES {$db->prefix}ships WRITE, {$db->prefix}planets WRITE, {$db->prefix}universe READ, {$db->prefix}zones READ, {$db->prefix}adodb_logsql WRITE");
Bnt\Db::logDbErrors($db, $resx, __LINE__, __FILE__);

$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email=?;", array($_SESSION['username']));
Bnt\Db::logDbErrors($db, $result, __LINE__, __FILE__);
$playerinfo = $result->fields;

$result2 = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=?;", array($playerinfo['sector']));
Bnt\Db::logDbErrors($db, $result2, __LINE__, __FILE__);
$sectorinfo = $result2->fields;

$result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE sector_id=?;", array($playerinfo['sector']));
Bnt\Db::logDbErrors($db, $result3, __LINE__, __FILE__);
$planetinfo = $result3->fields;
$num_planets = $result3->RecordCount();

// Generate Planetname
$planetname = mb_substr($playerinfo['character_name'], 0, 1) . mb_substr($playerinfo['ship_name'], 0, 1) . "-" . $playerinfo['sector'] . "-" . ($num_planets + 1);

echo "<h1>" . $title . "</h1>\n";

$destroy = null;
if (array_key_exists('destroy', $_GET))
{
    $destroy = $_GET['destroy'];
}

if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_gns_turn'];
}
elseif ($playerinfo['on_planet'] == 'Y')
{
    echo $langvars['l_gns_onplanet'];
}
elseif ($num_planets >= $bntreg->max_planets_sector)
{
    echo $langvars['l_gns_full'];
}
elseif ($sectorinfo['sector_id'] >= $bntreg->sector_max)
{
    echo "Invalid sector<br>\n";
}
elseif ($playerinfo['dev_genesis'] < 1)
{
    echo $langvars['l_gns_nogenesis'];
}
else
{
    $res = $db->Execute("SELECT allow_planet, corp_zone, owner FROM {$db->prefix}zones WHERE zone_id = ?;", array($sectorinfo['zone_id']));
    Bnt\Db::logDbErrors($db, $res, __LINE__, __FILE__);
    $zoneinfo = $res->fields;
    if ($zoneinfo['allow_planet'] == 'N')
    {
        echo $langvars['l_gns_forbid'];
    }
    elseif ($zoneinfo['allow_planet'] == 'L')
    {
        if ($zoneinfo['corp_zone'] == 'N')
        {
            if ($playerinfo['team'] == 0 && $zoneinfo['owner'] != $playerinfo['ship_id'])
            {
                echo $langvars['l_gns_bforbid'];
            }
            else
            {
                $res = $db->Execute("SELECT team FROM {$db->prefix}ships WHERE ship_id = ?;", array($zoneinfo['owner']));
                Bnt\Db::logDbErrors($db, $res, __LINE__, __FILE__);
                $ownerinfo = $res->fields;
                if ($ownerinfo['team'] != $playerinfo['team'])
                {
                    echo $langvars['l_gns_bforbid'];
                }
                else
                {
                    $update1 = $db->Execute("INSERT INTO {$db->prefix}planets VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);", array(NULL, $playerinfo['sector'], $planetname, 0, 0, 0, 0, 0, 0, 0, 0, $playerinfo['ship_id'], 0, 'N', 'N', $bntreg->default_prod_organics, $bntreg->default_prod_ore, $bntreg->default_prod_goods, $bntreg->default_prod_energy, $bntreg->default_prod_fighters, $bntreg->default_prod_torp, 'N'));
                    Bnt\Db::logDbErrors($db, $update1, __LINE__, __FILE__);
                    $update2 = $db->Execute("UPDATE {$db->prefix}ships SET turns_used = turns_used + 1, turns = turns - 1, dev_genesis = dev_genesis - 1 WHERE ship_id = ?;", array($playerinfo['ship_id']));
                    Bnt\Db::logDbErrors($db, $update2, __LINE__, __FILE__);
                    echo $langvars['l_gns_pcreate'];
                }
            }
        }
        elseif ($playerinfo['team'] != $zoneinfo['owner'])
        {
            echo $langvars['l_gns_bforbid'];
        }
        else
        {
            $update1 = $db->Execute("INSERT INTO {$db->prefix}planets VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);", array(NULL, $playerinfo['sector'], '$planetname', 0, 0, 0, 0, 0, 0, 0, 0, $playerinfo['ship_id'], 0, 'N', 'N', $bntreg->default_prod_organics, $bntreg->default_prod_ore, $bntreg->default_prod_goods, $bntreg->default_prod_energy, $bntreg->default_prod_fighters, $bntreg->default_prod_torp, 'N'));
            Bnt\Db::logDbErrors($db, $update1, __LINE__, __FILE__);
            $update2 = $db->Execute("UPDATE {$db->prefix}ships SET turns_used = turns_used + 1, turns = turns - 1, dev_genesis = dev_genesis - 1 WHERE ship_id=?;", array($playerinfo['ship_id']));
            Bnt\Db::logDbErrors($db, $update2, __LINE__, __FILE__);
            echo $langvars['l_gns_pcreate'];
        }
    }
    else
    {
        $update1 = $db->Execute("INSERT INTO {$db->prefix}planets VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);", array(NULL, $playerinfo['sector'], $planetname, 0, 0, 0, 0, 0, 0, 0, 0, $playerinfo['ship_id'], 0, 'N', 'N', $bntreg->default_prod_organics, $bntreg->default_prod_ore, $bntreg->default_prod_goods, $bntreg->default_prod_energy, $bntreg->default_prod_fighters, $bntreg->default_prod_torp, 'N'));
        Bnt\Db::logDbErrors($db, $update1, __LINE__, __FILE__);
        $update2 = $db->Execute("UPDATE {$db->prefix}ships SET turns_used = turns_used + 1, turns = turns - 1, dev_genesis = dev_genesis - 1 WHERE ship_id=?;", array($playerinfo['ship_id']));
        Bnt\Db::logDbErrors($db, $update2, __LINE__, __FILE__);
        echo $langvars['l_gns_pcreate'];
    }
}

$resx = $db->Execute("UNLOCK TABLES");
Bnt\Db::logDbErrors($db, $resx, __LINE__, __FILE__);
echo "<br><br>";

Bnt\Text::gotoMain($db, $lang, $langvars);
Bnt\Footer::display($pdo_db, $lang, $bntreg, $template);
?>
