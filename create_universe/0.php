<?php declare(strict_types = 1);
/**
 * create_univserse/0.php from The Kabal Invasion.
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

// Determine current step, next step, and number of steps
$step_finder = new Tki\BigBang();
$create_universe_info = $step_finder->findStep(__FILE__);
$temp_pass_validation = $variables['goodpass'];

// Set variables
$variables = array();
$variables['templateset'] = $tkireg->default_template;
$variables['goodpass'] = $temp_pass_validation;
$variables['body_class'] = 'create_universe';
$variables['steps'] = $create_universe_info['steps'];
$variables['current_step'] = $create_universe_info['current_step'];
$variables['next_step'] = $create_universe_info['next_step'];

/*
$lang_dir = new DirectoryIterator('languages/');
$lang_list = array();
$i = 0;

foreach ($lang_dir as $file_info) // Get a list of the files in the languages directory
{
    // If it is a PHP file, add it to the list of accepted language files
    if ($file_info->isFile() && $file_info->getExtension() == 'php') // If it is a PHP file, add it to the list of accepted make galaxy files
    {
        $lang_file = substr($file_info->getFilename(), 0, -8); // The actual file name

        // Select from the database and return the localized name of the language
        $query = "SELECT value FROM ::prefix::languages WHERE category = 'regional' AND section = :section AND name = 'local_lang_name';";
        $result = $pdo_db->prepare($query);
        Tki\Db::logDbErrors($pdo_db, $query, __LINE__, __FILE__);

        if ($result !== false)
        {
            $result->bindParam(':section', $lang_file, \PDO::PARAM_STR);
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
                $ini_file = './languages/' . $lang_file . '.ini';
                $parsed_lang_file = parse_ini_file($ini_file, true);
                if (is_array($parsed_lang_file))
                {
                    $variables['lang_list'][$i]['value'] = $parsed_lang_file['regional']['local_lang_name'];
                }
                else
                {
                    $variables['lang_list'][$i]['value'] = '';
                }
            }
        }
        else
        {
            // Load language ini file to get regional local_lang_name value
            $ini_file = './languages/' . $lang_file . '.ini';
            $parsed_lang_file = parse_ini_file($ini_file, true);
            if (is_array($parsed_lang_file))
            {
                $variables['lang_list'][$i]['value'] = $parsed_lang_file['regional']['local_lang_name'];
            }
            else
            {
                $variables['lang_list'][$i]['value'] = '';
            }
        }

        $variables['lang_list'][$i]['file'] = $lang_file;
        $variables['lang_list'][$i]['selected'] = $tkireg->default_lang;
        $i++;
    }
}

$variables['lang_list']['size'] = $i - 1;
*/

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common',
                                'create_universe', 'footer', 'insignias',
                                'news', 'options', 'regional'));
$variables['title'] = $langvars['l_cu_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);
$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('templates/classic/create_universe/0.tpl');

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
