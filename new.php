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
// File: new.php

require_once './common.php';

if (!array_key_exists('lang', $_GET))
{
    $_GET['lang'] = null;
    $lang = $bntreg->default_lang;
    $link = null;
}
else
{
    $lang = $_GET['lang'];
    $link = "?lang=" . $lang;
}

// Database driven language entries
$langvars = Bnt\Translate::load($pdo_db, $lang, array('new', 'login', 'common', 'global_includes', 'global_funcs', 'footer', 'news', 'index', 'options'));

$variables = null;
$variables['lang'] = $lang;
$variables['link'] = $link;
$variables['admin_mail'] = $bntreg->admin_mail;
$variables['body_class'] = 'index';
$variables['template'] = $bntreg->default_template; // Temporarily set the template to the default template until we have a user option

// Now set a container for the variables and langvars and send them off to the template system
$variables['container'] = "variable";
$langvars['container'] = "langvars";

$variables['selected_lang'] = null;
$lang_dir = new DirectoryIterator('languages/');
foreach ($lang_dir as $file_info) // Get a list of the files in the languages directory
{
    // If it is a PHP file, add it to the list of accepted language files
    if ($file_info->isFile() && $file_info->getExtension() == 'php') // If it is a PHP file, add it to the list of accepted make galaxy files
    {
        $lang_file = mb_substr($file_info->getFilename(), 0, -8); // The actual file name

        // Select from the database and return the localized name of the language
        $result = $db->Execute("SELECT value FROM {$db->prefix}languages WHERE category = 'regional' AND section = ? AND name = 'local_lang_name';", array($lang_file));
        Bnt\Db::logDbErrors($db, $result, __LINE__, __FILE__);
        while ($result && !$result->EOF)
        {
            $row = $result->fields;
            if ($lang_file == $_GET['lang'])
            {
                $variables['selected_lang'] = $lang_file;
            }
            $variables['lang_name'][] = $row['value'];
            $variables['lang_file'][] = $lang_file;
            $result->MoveNext();
        }
    }
}

// Pull in footer variables from footer_t.php
require_once './footer_t.php';
$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('new.tpl');
