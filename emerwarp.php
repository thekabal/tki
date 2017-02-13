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
// File: emerwarp.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

// Always make sure we are using empty vars before use.
$variables = null;

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('emerwarp', 'common', 'global_includes', 'global_funcs', 'footer', 'news'));

// Get playerinfo from database
$sql = "SELECT * FROM ::prefix::ships WHERE email=:email LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['username'], \PDO::PARAM_STR);
$stmt->execute();
$playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

if ($playerinfo['dev_emerwarp'] > 0)
{
    $dest_sector = random_int(0, (int) $max_sectors - 1);

    $sql = "UPDATE ::prefix::ships SET sector=:sector, dev_emerwarp=dev_emerwarp-1 WHERE ship_id=:ship_id";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindParam(':sector', $dest_sector);
    $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
    $stmt->execute();
    Tki\LogMove::writeLog($pdo_db, $playerinfo['ship_id'], $dest_sector);
    $langvars['l_ewd_used'] = str_replace("[sector]", $dest_sector, $langvars['l_ewd_used']);
    $variables['dest_sector'] = $dest_sector;
}

$variables['body_class'] = 'tki'; // No special css used for this page yet
$variables['playerinfo_dev_emerwarp'] = $playerinfo['dev_emerwarp'];
$variables['title'] = $langvars['l_ewd_title'];

$variables['linkback'] = array("fulltext" => $langvars['l_global_mmenu'], "link" => "main.php");

Tki\Header::display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);

$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('emerwarp.tpl');

Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
