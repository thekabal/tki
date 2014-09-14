<?php
// Copyright © 2014 The Kabal Invasion development team, Ron Harwood, and the BNT development team.
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
//
// File: create_universe/10.php

if (strpos($_SERVER['PHP_SELF'], '/10.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

// Determine current step, next step, and number of steps
$create_universe_info = Bnt\BigBang::findStep(__FILE__);

// Pull in the set config variables so we can get the correct sector max
$ini_keys = parse_ini_file("config/classic_config.ini.php", true);

foreach ($ini_keys as $config_category => $config_line)
{
    foreach ($config_line as $config_key => $config_value)
    {
        $bntreg->$config_key = $config_value;
    }
}

// Set variables
$variables['templateset'] = $bntreg->default_template;
$variables['body_class'] = 'create_universe';
$variables['swordfish']  = filter_input(INPUT_POST, 'swordfish', FILTER_SANITIZE_URL);
$variables['steps'] = $create_universe_info['steps'];
$variables['current_step'] = $create_universe_info['current_step'];
$variables['next_step'] = $create_universe_info['next_step'];
$variables['sector_max'] = $bntreg->sector_max;

// Get POST['newlang'] returns null if not found.
if (array_key_exists('newlang', $_POST) == true)
{
    $lang_dir = new DirectoryIterator('languages/');
    foreach ($lang_dir as $file_info) // Get a list of the files in the languages directory
    {
        // If it is a PHP file, add it to the list of accepted language files
        if ($file_info->isFile() && $file_info->getExtension() == 'php') // If it is a PHP file, add it to the list of accepted make galaxy files
        {
            $lang_file = mb_substr($file_info->getFilename(), 0, -8); // The actual file name

            // Trim and compare the new langauge with the supported.
            if (trim($_POST['newlang']) == $lang_file)
            {
                // We have a match so set lang to the required supported language
                $lang = $lang_file;
                $variables['newlang'] = filter_input(INPUT_POST, 'newlang', FILTER_SANITIZE_URL);
            }
        }
    }
}

// Database driven language entries
$langvars = Bnt\Translate::load($pdo_db, $lang, array('common', 'regional', 'footer', 'global_includes', 'create_universe', 'news'));
$template->addVariables('langvars', $langvars);

// Pull in footer variables from footer_t.php
include './footer_t.php';
$template->addVariables('variables', $variables);
$template->display('templates/classic/create_universe/10.tpl');
?>
