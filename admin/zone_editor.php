<?php declare(strict_types = 1);
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
// File: admin/zone_editor.php

// Set array with all used variables in page
unset($variables);
$variables = array();

if (!array_key_exists('zone', $_POST))
{
    $_POST['zone'] = null;
}

if ($_POST['zone'] === null)
{
    $zones = array();

    $res = $db->Execute("SELECT zone_id, zone_name FROM {$db->prefix}zones ORDER BY zone_name");
    Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
    while (!$res->EOF)
    {
        $zones[] = $res->fields;
        $res->MoveNext();
    }

    $variables['zones'] = $zones;
    $variables['zone'] = null;
}
else
{
    $variables['zone'] = null;
    if ($_POST['operation'] == "edit")
    {
        $sql = "SELECT * FROM ::prefix::zones WHERE zone_id=:zone_id LIMIT 1";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':zone_id', $_POST['zone'], \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $variables['operation'] = "edit";
        $variables['zone_id'] = $row['zone_id'];
        $variables['zone_name'] = $row['zone_name'];
        $variables['allow_attack'] = $row['allow_attack'];
        $variables['allow_warpedit'] = $row['allow_warpedit'];
        $variables['allow_planet'] = $row['allow_planet'];
        $variables['max_hull'] = $row['max_hull'];
        $variables['zone'] = $_POST['zone'];

        $variables['allow_beacon'] = null;
        if ($row['allow_beacon'] == 'Y')
        {
            $variables['allow_beacon'] = 'checked="checked"';
        }

        $variables['allow_attack'] = null;
        if ($row['allow_attack'] == 'Y')
        {
            $variables['allow_attack'] = 'checked="checked"';
        }

        $variables['allow_warpedit'] = null;
        if ($row['allow_warpedit'] == 'Y')
        {
            $variables['allow_warpedit'] = 'checked="checked"';
        }

        $variables['allow_planet'] = null;
        if ($row['allow_planet'] == 'Y')
        {
            $variables['allow_planet'] = 'checked="checked"';
        }
    }
    elseif ($_POST['operation'] == "save")
    {
        $variables['operation'] = "save";
        $variables['zone'] = $_POST['zone'];
        // Update database
        $_zone_beacon = empty($zone_beacon) ? "N" : "Y";
        $_zone_attack = empty($zone_attack) ? "N" : "Y";
        $_zone_warpedit = empty($zone_warpedit) ? "N" : "Y";
        $_zone_planet = empty($zone_planet) ? "N" : "Y";
        $resx = $db->Execute("UPDATE {$db->prefix}zones SET zone_name = ?, allow_beacon = ? , allow_attack= ?  , allow_warpedit = ? , allow_planet = ?, max_hull = ? WHERE zone_id = ?;", array($zone_name, $_zone_beacon, $_zone_attack, $_zone_warpedit, $_zone_planet, $zone_hull, $_POST['zone']));
        Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
        $button_main = false;
    }
}

$variables['lang'] = $lang;
$variables['swordfish'] = $swordfish;

// Set the module name.
$variables['module'] = $module_name;

// Now set a container for the variables and langvars and send them off to the template system
$variables['container'] = "variable";
$langvars['container'] = "langvar";

$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
