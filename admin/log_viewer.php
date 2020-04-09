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
// File: admin/log_viewer.php

$players = array();
$res = $db->Execute("SELECT ship_id, character_name FROM {$db->prefix}ships ORDER BY character_name ASC");
Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
while (!$res->EOF)
{
    $players[] = $res->fields;
    $res->MoveNext();
}

$variables = array();
$variables['lang'] = $lang;
$variables['swordfish'] = $swordfish;
$variables['players'] = $players;

// Set the module name.
$variables['module'] = $module_name;

// Now set a container for the variables and langvars and send them off to the template system
$variables['container'] = "variable";
$langvars = array();
$langvars['container'] = "langvar";

$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
