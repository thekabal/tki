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

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('genesis', 'common', 'global_includes', 'global_funcs', 'footer', 'news'));
$title = $langvars['l_gns_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

// Get playerinfo from database
$sql = "SELECT * FROM ::prefix::ships WHERE email=:email LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['username'], \PDO::PARAM_STR);
$stmt->execute();
$playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Get sectorinfo from database
$sql = "SELECT * FROM ::prefix::universe WHERE sector_id=:sector_id LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':sector_id', $playerinfo['sector']);
$stmt->execute();
$sectorinfo = $stmt->fetch(PDO::FETCH_ASSOC);

$result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE sector_id = ?;", array($playerinfo['sector']));
Tki\Db::LogDbErrors($pdo_db, $result3, __LINE__, __FILE__);
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
elseif ($num_planets >= $tkireg->max_planets_sector)
{
    echo $langvars['l_gns_full'];
}
elseif ($sectorinfo['sector_id'] >= $tkireg->max_sectors)
{
    echo "Invalid sector<br>\n";
}
elseif ($playerinfo['dev_genesis'] < 1)
{
    echo $langvars['l_gns_nogenesis'];
}
else
{
    $res = $db->Execute("SELECT allow_planet, team_zone, owner FROM {$db->prefix}zones WHERE zone_id = ?;", array($sectorinfo['zone_id']));
    Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
    $zoneinfo = $res->fields;
    if ($zoneinfo['allow_planet'] == 'N')
    {
        echo $langvars['l_gns_forbid'];
    }
    elseif ($zoneinfo['allow_planet'] == 'L')
    {
        if ($zoneinfo['team_zone'] == 'N')
        {
            if ($playerinfo['team'] == 0 && $zoneinfo['owner'] != $playerinfo['ship_id'])
            {
                echo $langvars['l_gns_bforbid'];
            }
            else
            {
                $res = $db->Execute("SELECT team FROM {$db->prefix}ships WHERE ship_id = ?;", array($zoneinfo['owner']));
                Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
                $ownerinfo = $res->fields;
                if ($ownerinfo['team'] != $playerinfo['team'])
                {
                    echo $langvars['l_gns_bforbid'];
                }
                else
                {
                    $update1 = $db->Execute("INSERT INTO {$db->prefix}planets VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);", array(NULL, $playerinfo['sector'], $planetname, 0, 0, 0, 0, 0, 0, 0, 0, $playerinfo['ship_id'], 0, 'N', 'N', $tkireg->default_prod_organics, $tkireg->default_prod_ore, $tkireg->default_prod_goods, $tkireg->default_prod_energy, $tkireg->default_prod_fighters, $tkireg->default_prod_torp, 'N'));
                    Tki\Db::LogDbErrors($pdo_db, $update1, __LINE__, __FILE__);
                    $update2 = $db->Execute("UPDATE {$db->prefix}ships SET turns_used = turns_used + 1, turns = turns - 1, dev_genesis = dev_genesis - 1 WHERE ship_id = ?;", array($playerinfo['ship_id']));
                    Tki\Db::LogDbErrors($pdo_db, $update2, __LINE__, __FILE__);
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
            $update1 = $db->Execute("INSERT INTO {$db->prefix}planets VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);", array(NULL, $playerinfo['sector'], '$planetname', 0, 0, 0, 0, 0, 0, 0, 0, $playerinfo['ship_id'], 0, 'N', 'N', $tkireg->default_prod_organics, $tkireg->default_prod_ore, $tkireg->default_prod_goods, $tkireg->default_prod_energy, $tkireg->default_prod_fighters, $tkireg->default_prod_torp, 'N'));
            Tki\Db::LogDbErrors($pdo_db, $update1, __LINE__, __FILE__);
            $update2 = $db->Execute("UPDATE {$db->prefix}ships SET turns_used = turns_used + 1, turns = turns - 1, dev_genesis = dev_genesis - 1 WHERE ship_id=?;", array($playerinfo['ship_id']));
            Tki\Db::LogDbErrors($pdo_db, $update2, __LINE__, __FILE__);
            echo $langvars['l_gns_pcreate'];
        }
    }
    else
    {
        $update1 = $db->Execute("INSERT INTO {$db->prefix}planets VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);", array(NULL, $playerinfo['sector'], $planetname, 0, 0, 0, 0, 0, 0, 0, 0, $playerinfo['ship_id'], 0, 'N', 'N', $tkireg->default_prod_organics, $tkireg->default_prod_ore, $tkireg->default_prod_goods, $tkireg->default_prod_energy, $tkireg->default_prod_fighters, $tkireg->default_prod_torp, 'N'));
        Tki\Db::LogDbErrors($pdo_db, $update1, __LINE__, __FILE__);
        $update2 = $db->Execute("UPDATE {$db->prefix}ships SET turns_used = turns_used + 1, turns = turns - 1, dev_genesis = dev_genesis - 1 WHERE ship_id=?;", array($playerinfo['ship_id']));
        Tki\Db::LogDbErrors($pdo_db, $update2, __LINE__, __FILE__);
        echo $langvars['l_gns_pcreate'];
    }
}

echo "<br><br>";

Tki\Text::gotoMain($pdo_db, $lang);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
