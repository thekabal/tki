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
// File: error.php

require_once './common.php';

// Database driven language entries
$langvars = Bnt\Translate::load($pdo_db, $lang, array('footer', 'common', 'error', 'main'));
// Always make sure we are using empty vars before use.
$variables = null;

// Set array with all used variables in page
$variables['lang'] = $lang;
$variables['l_lang_attribute'] = $langvars['l_lang_attribute'];
$variables['error_img'] = 'images/error.jpg';
$variables['body_class'] = 'error';
$variables['no_ticker'] = 0;

if (isset ($error_line)) // An error thrown in a regular TKI file
{
    $variables['error_page'] = $error_file;
    $variables['error_line'] = $error_line;
    $variables['error_type'] = 'standard';
}
elseif (isset ($error_file)) // Directly accessing a page like check_fighters
{
    $variables['error_page'] = $error_file;
    // There is no error line in a direct access
    $variables['error_type'] = 'direct';
}
else // All other errors, probably an error handler error like a 404
{
    $variables['error_page'] = $_SERVER['SCRIPT_NAME'];
    // There is no error page or line in a server error
}

if (empty ($_POST))
{
    $variables['post'] = $langvars['l_error_empty'];
}
else
{
    $variables['post'] = var_export($_POST, true);
}

if (empty ($_GET))
{
    $variables['get'] = $langvars['l_error_empty'];
}
else
{
    $variables['get'] = var_export($_GET, true);
}

if (empty ($_SESSION))
{
    $variables['session'] = $langvars['l_error_empty'];
}
else
{
    $variables['session'] = var_export($_SESSION, true);
}

$variables['request_uri'] = print_r($_SERVER['REQUEST_URI'], true);
$variables['linkforums']['link'] = $bntreg->link_forums;
$variables['linkback']['link'] = 'index.php';

// Now set a container for the variables and langvars and send them off to the template system
$variables['container'] = "variable";
$langvars['container'] = "langvar";

// Pull in footer variables from footer_t.php
require_once './footer_t.php';
$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('error.tpl');
die ();
?>
