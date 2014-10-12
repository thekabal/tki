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
// File: team.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $langvars, $tkireg, $template);

$title = $langvars['l_teamm_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('team', 'common', 'global_funcs', 'global_includes', 'combat', 'footer', 'news'));

$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email = ?;", array($_SESSION['username']));
Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);
$playerinfo = $result->fields;

$planet_id = preg_replace('/[^0-9]/', '', $planet_id);

$result2 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id = ?", array($planet_id));
Tki\Db::logDbErrors($db, $result2, __LINE__, __FILE__);
if ($result2)
{
    $planetinfo = $result2->fields;
}

if ($planetinfo['owner'] == $playerinfo['ship_id'] || ($planetinfo['team'] == $playerinfo['team'] && $playerinfo['team'] > 0))
{
    echo "<h1>" . $title . "</h1>\n";
    if ($action == "planetteam")
    {
        echo $langvars['l_teamm_toteam'] . "<br>";
        $result = $db->Execute("UPDATE {$db->prefix}planets SET team=?, owner=? WHERE planet_id = ?;", array($playerinfo['team'], $playerinfo['ship_id'], $planet_id));
        Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);
        $ownership = Tki\Ownership::calc($db, $playerinfo['sector'], $min_bases_to_own, $langvars);

        if (!empty($ownership))
        {
            echo "<p>$ownership<p>";
        }
    }

    if ($action == "planetpersonal")
    {
        echo $langvars['l_teamm_topersonal'] . "<br>";
        $result = $db->Execute("UPDATE {$db->prefix}planets SET team='0', owner = ? WHERE planet_id = ?;", array($playerinfo['ship_id'], $planet_id));
        Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);
        $ownership = Tki\Ownership::calc($db, $playerinfo['sector'], $min_bases_to_own, $langvars);

        // Kick other players off the planet
        $result = $db->Execute("UPDATE {$db->prefix}ships SET on_planet='N' WHERE on_planet='Y' AND planet_id = ? AND ship_id <> ?;", array($planet_id, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);
        if (!empty($ownership))
        {
            echo "<p>" . $ownership . "<p>";
        }
    }
    Tki\Text::gotoMain($db, $lang, $langvars);
}
else
{
    echo "<br>" . $langvars['l_team_exploit'] . "<br>";
    Tki\Text::gotoMain($db, $lang, $langvars);
}

Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
