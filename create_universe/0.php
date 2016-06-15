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
// File: create_universe/0.php

if (strpos($_SERVER['PHP_SELF'], '/0.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

// Determine current step, next step, and number of steps
$create_universe_info = Tki\BigBang::findStep(__FILE__);

// Set variables
$variables['templateset'] = $tkireg->default_template;
$variables['body_class'] = 'create_universe';
$variables['title'] = $langvars['l_cu_title'];
$variables['steps'] = $create_universe_info['steps'];
$variables['current_step'] = $create_universe_info['current_step'];
$variables['next_step'] = $create_universe_info['next_step'];

$lang_dir = new DirectoryIterator('languages/');
$lang_list = array();
$i = 0;

foreach ($lang_dir as $file_info) // Get a list of the files in the languages directory
{
    // If it is a PHP file, add it to the list of accepted language files
    if ($file_info->isFile() && $file_info->getExtension() == 'php') // If it is a PHP file, add it to the list of accepted make galaxy files
    {
        $lang_file = mb_substr($file_info->getFilename(), 0, -8); // The actual file name

        // Select from the database and return the localized name of the language
        $query = "SELECT value FROM {$pdo_db->prefix}languages WHERE category = 'regional' AND section = :section AND name = 'local_lang_name';";
        $result = $pdo_db->prepare($query);
        Tki\Db::logDbErrors($pdo_db, $query, __LINE__, __FILE__);

        if ($result !== false)
        {
            $result->bindParam(':section', $lang_file);
            $final_result = $result->execute();
            Tki\Db::logDbErrors($pdo_db, $query, __LINE__, __FILE__);
            $row = $result->fetch();
            if ($row !== false)
            {
                $variables['lang_list'][$i]['value'] = $row['value'];
            }
            else
            {
                // Load language ini file to get regional local_lang_name value
                $ini_file = './languages/' . $lang_file . '.ini.php';
                $parsed_lang_file = parse_ini_file($ini_file, true);
                $variables['lang_list'][$i]['value'] = $parsed_lang_file['regional']['local_lang_name'];
            }
        }
        else
        {
                // Load language ini file to get regional local_lang_name value
                $ini_file = './languages/' . $lang_file . '.ini.php';
                $parsed_lang_file = parse_ini_file($ini_file, true);
                $variables['lang_list'][$i]['value'] = $parsed_lang_file['regional']['local_lang_name'];
        }

        $variables['lang_list'][$i]['file'] = $lang_file;
        $variables['lang_list'][$i]['selected'] = $tkireg->default_lang;
        $i++;
    }
}
$variables['lang_list']['size'] = $i -1;

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'regional', 'footer', 'global_includes', 'create_universe', 'options', 'news'));

Tki\Header::display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);
$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('templates/classic/create_universe/0.tpl');
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
