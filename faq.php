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
// File: faq.php

require_once './common.php';

$lang = $bntreg->default_lang;
$link = null;

// Database driven language entries
$langvars = Bnt\Translate::load($pdo_db, $lang, array('common', 'faq', 'global_funcs'));

$variables = null;
$variables['lang'] = $lang;
$variables['link'] = $link;
$variables['body_class'] = 'faq';

if (empty ($_SESSION['username']))
{
    $variables['linkback'] = array("fulltext" => $langvars['l_global_mlogin'], "link" => "index.php");
}
else
{
    $variables['linkback'] = array("fulltext" => $langvars['l_global_mmenu'], "link" => "index.php");
}

// Now set a container for the variables and langvars and send them off to the template system
$variables['container'] = "variable";
$langvars['container'] = "langvars";

// Pull in footer variables from footer_t.php
require_once './footer_t.php';
$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('faq.tpl');
