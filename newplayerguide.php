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
// File: newplayerguide.php

require_once './common.php';

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('global_funcs', 'new_player_guide'));

if (array_key_exists('username', $_SESSION))
{
    $variables['session_username'] = $_SESSION['username'];
}
else
{
    $variables['session_username'] = null;
}

$variables['body_class'] = 'faq';
$variables['lang'] = $lang;
$variables['linkback'] = array("fulltext" => $langvars['l_global_mlogin'], "link" => "index.php");
$variables['title'] = $langvars['l_npg_title'];

Tki\Header::display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);

$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('newplayerguide.tpl');

Tki\Footer::display($pdo_db, $lang, $tkireg, $template, $langvars);
