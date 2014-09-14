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
// File: index.php

$index_page = true;
require_once './common.php';

// If language has not been selected, give default
if (!array_key_exists('lang', $_GET))
{
    $_GET['lang'] = null;
    $lang = $bntreg->default_lang;
    $link = null;
}
else // Else set the language as requested by player
{
    $lang = $_GET['lang'];
    $link = '?lang=' . $lang;
}

if (!Bnt\Db::isActive($pdo_db))
{
    // If DB is not active, redirect to create universe to run install
    header('Location: create_universe.php');
    die();
}

// Database driven language entries
$langvars = Bnt\Translate::load($pdo_db, $lang, array('main', 'login', 'logout', 'index', 'common','regional', 'footer','global_includes'));

$variables = null;
$variables['lang'] = $lang;
$variables['link'] = $link;
$variables['title'] = $langvars['l_welcome_bnt'];
$variables['link_forums'] = $bntreg->link_forums;
$variables['admin_mail'] = $bntreg->admin_mail;
$variables['body_class'] = 'index';

// Get list of available languages
$variables['list_of_langs'] = Bnt\Languages::listAvailable($pdo_db, $lang);

// Temporarily set the template to the default template until we have a user option
$variables['template'] = $bntreg->default_template;

// Now set a container for the variables and langvars and send them off to the template system
$variables['container'] = 'variable';
$langvars['container'] = 'langvars';

// Pull in footer variables from footer_t.php
require_once './footer_t.php';
$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('index.tpl');
