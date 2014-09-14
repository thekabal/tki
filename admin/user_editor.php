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
// File: admin/user_editor.php

if (strpos($_SERVER['PHP_SELF'], 'user_editor.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

$button_main = true;

if (!array_key_exists('operation', $_POST))
{
    $_POST['operation'] = null;
}

if (empty ($_POST['user']))
{
    $res = $db->Execute("SELECT ship_id, character_name FROM {$db->prefix}ships ORDER BY character_name");
    Tki\Db::logDbErrors($db, $res, __LINE__, __FILE__);
    while (!$res->EOF)
    {
        $players[]=$res->fields;
        $res->MoveNext();
    }
    $variables['user'] = null;
    $variables['players'] = $players;
}
else
{
    if ($_POST['operation'] === null)
    {
        $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id=?;", array($_POST['user']));
        Tki\Db::logDbErrors($db, $res, __LINE__, __FILE__);
        $row = $res->fields;
        $variables['operation'] = $_POST['operation'];
        $variables['user'] = $_POST['user'];
        $variables['character_name'] = $row['character_name'];
        $variables['password'] = $row['password'];
        $variables['email'] = $row['email'];
        $variables['ship_name'] = $row['ship_name'];
        $variables['hull'] = $row['hull'];
        $variables['engines'] = $row['engines'];
        $variables['power'] = $row['power'];
        $variables['computer'] = $row['computer'];
        $variables['sensors'] = $row['sensors'];
        $variables['beams'] = $row['beams'];
        $variables['armor'] = $row['armor'];
        $variables['shields'] = $row['shields'];
        $variables['torp_launchers'] = $row['torp_launchers'];
        $variables['cloak'] = $row['cloak'];
        $variables['ship_ore'] = $row['ship_ore'];
        $variables['ship_organics'] = $row['ship_organics'];
        $variables['ship_goods'] = $row['ship_goods'];
        $variables['ship_energy'] = $row['ship_energy'];
        $variables['ship_colonists'] = $row['ship_colonists'];
        $variables['ship_fighters'] = $row['ship_fighters'];
        $variables['torps'] = $row['torps'];
        $variables['armor_pts'] = $row['armor_pts'];
        $variables['dev_beacon'] = $row['dev_beacon'];
        $variables['dev_emerwarp'] = $row['dev_emerwarp'];
        $variables['dev_warpedit'] = $row['dev_warpedit'];
        $variables['dev_genesis'] = $row['dev_genesis'];
        $variables['dev_minedeflector'] = $row['dev_minedeflector'];
        $variables['credits'] = $row['credits'];
        $variables['turns'] = $row['turns'];
        $variables['sector'] = $row['sector'];

        // For checkboxes, switch out the database stored value of Y/N for the html checked="checked", so the checkbox actually is checked.
        $variables['dev_escapepod'] = null;
        if ($row['dev_escapepod'] == 'Y')
        {
            $variables['dev_escapepod'] = 'checked="checked"';
        }

        $variables['dev_fuelscoop'] = null;
        if ($row['dev_fuelscoop'] == 'Y')
        {
            $variables['dev_fuelscoop'] = 'checked="checked"';
        }

        $variables['ship_destroyed'] = null;
        if ($row['ship_destroyed'] == 'Y')
        {
            $variables['ship_destroyed'] = 'checked="checked"';
        }
    }
    elseif ($_POST['operation'] == 'save')
    {
        // update database
        $_ship_destroyed = empty($_POST['ship_destroyed']) ? "N" : "Y";
        $_dev_escapepod = empty($_POST['dev_escapepod']) ? "N" : "Y";
        $_dev_fuelscoop = empty($_POST['dev_fuelscoop']) ? "N" : "Y";
        $variables['debug'] = $_dev_escapepod;
        $resx = $db->Execute("UPDATE {$db->prefix}ships SET character_name=?, password=?, email=?, ship_name=?, ship_destroyed=?, hull=?, engines=?, power=?, computer=?, sensors=?, armor=?, shields=?, beams=?, torp_launchers=?, cloak=?, credits=?, turns=?, dev_warpedit=?, dev_genesis=?, dev_beacon=?, dev_emerwarp=?, dev_escapepod=?, dev_fuelscoop=?, dev_minedeflector=?, sector=?, ship_ore=?, ship_organics=?, ship_goods=?, ship_energy=?, ship_colonists=?, ship_fighters=?, torps=?, armor_pts=? WHERE ship_id=?", array($_POST['character_name'], $_POST['password2'], $_POST['email'], $_POST['ship_name'], $_ship_destroyed, $_POST['hull'], $_POST['engines'], $_POST['power'], $_POST['computer'], $_POST['sensors'], $_POST['armor'], $_POST['shields'], $_POST['beams'], $_POST['torp_launchers'], $_POST['cloak'], $_POST['credits'], $_POST['turns'], $_POST['dev_warpedit'], $_POST['dev_genesis'], $_POST['dev_beacon'], $_POST['dev_emerwarp'], $_dev_escapepod, $_dev_fuelscoop, $_POST['dev_minedeflector'], $_POST['sector'], $_POST['ship_ore'], $_POST['ship_organics'], $_POST['ship_goods'], $_POST['ship_energy'], $_POST['ship_colonists'], $_POST['ship_fighters'], $_POST['torps'], $_POST['armor_pts'], $_POST['user']));
        Tki\Db::logDbErrors($db, $resx, __LINE__, __FILE__);
        $button_main = false;
        $variables['user'] = $_POST['user'];
    }
}

$variables['lang'] = $lang;
$variables['swordfish'] = $swordfish;
$variables['operation'] = $_POST['operation'];

// Set the module name.
$variables['module'] = $module_name;

// Now set a container for the variables and langvars and send them off to the template system
$variables['container'] = "variable";
$langvars['container'] = "langvar";

$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
