<?php declare(strict_types = 1);
/**
 * admin.php from The Kabal Invasion.
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

require_once './common.php';

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('admin', 'combat',
                                'common', 'footer', 'insignias', 'main',
                                'news', 'planet', 'report', 'universal',
                                'zoneedit'));
$title = $langvars['l_admin_title'];

// We only want menu values that come from $_POST, and only want string values
$menu = filter_input(INPUT_POST, 'menu', FILTER_SANITIZE_STRING);
$swordfish  = filter_input(INPUT_POST, 'swordfish', FILTER_SANITIZE_URL);
$filename = null;
$menu_location = null;
$button_main = false;

// Clear variables array for use with all variables in page
$variables = array();

$variables['isAdmin'] = false;
$variables['module'] = null;
$variables['title'] = $langvars['l_admin_title'];

if ($swordfish == \Tki\SecureConfig::ADMIN_PASS)
{
    $file_count = 0;
    $variables['isAdmin'] = true;
    $option_title = array();
    $admin_dir = new DirectoryIterator('admin/');
    // Get a list of the files in the admin directory
    foreach ($admin_dir as $file_info)
    {
        // If it is a PHP file, add it to the list of accepted admin files
        if ($file_info->isFile() && $file_info->getExtension() == 'php')
        {
            $file_count++; // Increment counter so we know how many files there are
            // Actual file name
            $filename[$file_count]['file'] = $file_info->getFilename();

            // Set option title to lang string of the form l_admin + file name
            $option_title = 'l_admin_' . substr($filename[$file_count]['file'], 0, -4);

            if ($langvars[$option_title] !== null)
            {
                // The language translated title for options
                $filename[$file_count]['option_title'] = $langvars[$option_title];
            }
            else
            {
                // The placeholder text for a not translated module
                $filename[$file_count]['option_title'] = $langvars['l_admin_new_module'] . $filename[$file_count]['file'];
            }

            if ($menu !== null)
            {
                if ($menu == $filename[$file_count]['file'])
                {
                    $button_main = true;
                    $variables['module_name'] = substr($filename[$file_count]['file'], 0, -4);
                    include_once './admin/' . $filename[$file_count]['file'];
                }
            }
        }
    }
}

$langvars = Tki\Translate::load($pdo_db, $lang, array('admin', 'combat',
                                'common', 'footer', 'insignias', 'main',
                                'news', 'planet', 'report', 'universal',
                                'zoneedit'));
$variables['body_class'] = 'admin';
$variables['lang'] = $lang;
$variables['swordfish'] = $swordfish;
$variables['linkback'] = array('fulltext' => $langvars['l_universal_main_menu'], 'link' => 'main.php');
$variables['menu'] = $menu;
$variables['filename'] = $filename;
$variables['menu_location'] = $menu_location;
$variables['button_main'] = $button_main;

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);

$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('admin.tpl');

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
