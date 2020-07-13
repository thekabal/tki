<?php declare(strict_types = 1);
/**
 * admin/zone_editor.php from The Kabal Invasion.
 * The Kabal Invasion is a Free & Opensource (FOSS), web-based 4X space/strategy game.
 *
 * @copyright 2020 The Kabal Invasion development team, Ron Harwood, and the BNT development team
 *
 * @license GNU AGPL version 3.0 or (at your option) any later version.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

// Set array with all used variables in page
$variables = array();

if (!array_key_exists('zone', $_POST))
{
    $_POST['zone'] = null;
}

if ($_POST['zone'] === null)
{
    $zones = array();

    $res = $old_db->Execute("SELECT zone_id, zone_name FROM {$old_db->prefix}zones ORDER BY zone_name");
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
        // Get zoneinfo from database
        $zones_gateway = new \Tki\Zones\ZonesGateway($pdo_db); // Build a zone gateway object to handle the SQL calls
        $zoneinfo = $zones_gateway->selectZoneInfo($_POST['zone']);
        if (empty($zoneinfo))
        {
            die("Empty zone info");
        }

        $variables['operation'] = "edit";
        $variables['zone_id'] = $zoneinfo['zone_id'];
        $variables['zone_name'] = $zoneinfo['zone_name'];
        $variables['allow_attack'] = $zoneinfo['allow_attack'];
        $variables['allow_warpedit'] = $zoneinfo['allow_warpedit'];
        $variables['allow_planet'] = $zoneinfo['allow_planet'];
        $variables['max_hull'] = $zoneinfo['max_hull'];
        $variables['zone'] = $_POST['zone'];

        $variables['allow_beacon'] = null;
        if ($zoneinfo['allow_beacon'] == 'Y')
        {
            $variables['allow_beacon'] = 'checked="checked"';
        }

        $variables['allow_attack'] = null;
        if ($zoneinfo['allow_attack'] == 'Y')
        {
            $variables['allow_attack'] = 'checked="checked"';
        }

        $variables['allow_warpedit'] = null;
        if ($zoneinfo['allow_warpedit'] == 'Y')
        {
            $variables['allow_warpedit'] = 'checked="checked"';
        }

        $variables['allow_planet'] = null;
        if ($zoneinfo['allow_planet'] == 'Y')
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
        $resx = $old_db->Execute("UPDATE {$old_db->prefix}zones SET zone_name = ?, allow_beacon = ? , allow_attack= ?  , allow_warpedit = ? , allow_planet = ?, max_hull = ? WHERE zone_id = ?;", array($zone_name, $_zone_beacon, $_zone_attack, $_zone_warpedit, $_zone_planet, $zone_hull, $_POST['zone']));
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
$langvars = array();
$langvars['container'] = "langvar";

$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
