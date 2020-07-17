<?php declare(strict_types = 1);
/**
 * create_univserse/50.php from The Kabal Invasion.
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

// Set variables
$variables = array();
$variables['templateset']            = $tkireg->default_template;
$variables['body_class']             = 'create_universe';
$variables['steps']                  = $create_universe_info['steps'];
$variables['current_step']           = $create_universe_info['current_step'];
$variables['next_step']              = $create_universe_info['next_step'];
$variables['max_sectors']            = (int) filter_input(INPUT_POST, 'max_sectors', FILTER_SANITIZE_NUMBER_INT); // Sanitize the input and typecast it to an int
$variables['spp']                    = filter_input(INPUT_POST, 'spp', FILTER_SANITIZE_NUMBER_INT);
$variables['oep']                    = filter_input(INPUT_POST, 'oep', FILTER_SANITIZE_NUMBER_INT);
$variables['ogp']                    = filter_input(INPUT_POST, 'ogp', FILTER_SANITIZE_NUMBER_INT);
$variables['gop']                    = filter_input(INPUT_POST, 'gop', FILTER_SANITIZE_NUMBER_INT);
$variables['enp']                    = filter_input(INPUT_POST, 'enp', FILTER_SANITIZE_NUMBER_INT);
$variables['nump']                   = filter_input(INPUT_POST, 'nump', FILTER_SANITIZE_NUMBER_INT);
$variables['empty']                  = $variables['max_sectors'] - $variables['spp'] - $variables['oep'] - $variables['ogp'] - $variables['gop'] - $variables['enp'];
$variables['initscommod']            = filter_input(INPUT_POST, 'initscommod', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$variables['initbcommod']            = filter_input(INPUT_POST, 'initbcommod', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$variables['fedsecs']                = filter_input(INPUT_POST, 'fedsecs', FILTER_SANITIZE_NUMBER_INT);
$variables['loops']                  = filter_input(INPUT_POST, 'loops', FILTER_SANITIZE_NUMBER_INT);
$variables['swordfish']              = filter_input(INPUT_POST, 'swordfish', FILTER_SANITIZE_URL);
$variables['autorun']                = filter_input(INPUT_POST, 'autorun', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'regional', 'footer', 'global_includes', 'create_universe', 'news'));
$variables['title'] = $langvars['l_cu_title'];

$local_table_timer = new Tki\Timer();
$z = 0;
$i = 0;
$language_files = new DirectoryIterator("languages/");
$lang_file_import_results = array();

$catch_results = array();
foreach ($language_files as $language_filename)
{
    if ($language_filename->isFile() && $language_filename->getExtension() == 'ini')
    {
        $lang_name = substr($language_filename->getFilename(), 0, -4);

        // Import Languages
        $local_table_timer->start(); // Start benchmarking
        $lang_result = Tki\File::iniToDb($pdo_db, "languages/" . $language_filename->getFilename(), "languages", $lang_name, $tkireg);
        $local_table_timer->stop();
        $variables['import_lang_results'][$i]['time'] = $local_table_timer->elapsed();
        $variables['import_lang_results'][$i]['name'] = ucwords($lang_name);
        $variables['import_lang_results'][$i]['result'] = $lang_result;
        $catch_results[$z] = $lang_result;
        $z++;
        $i++;
    }
}

$variables['language_count'] = ($i - 1);
$local_table_timer->start(); // Start benchmarking
$gameconfig_result = Tki\File::iniToDb($pdo_db, "config/classic_config.ini", "gameconfig", "game", $tkireg);
$local_table_timer->stop();
if ($gameconfig_result === true)
{
    $variables['import_config_results']['result'] = true;
    $variables['import_config_results']['time'] = $local_table_timer->elapsed();
}
else
{
    $variables['import_config_results']['result'] = $gameconfig_result;
    $variables['import_config_results']['time'] = $local_table_timer->elapsed();
}

$catch_results[$z] = $gameconfig_result;
$z++;

for ($t = 0; $t < $z; $t++)
{
    if ($catch_results[$t] !== true)
    {
        $variables['autorun'] = false; // We disable autorun if any errors occur in processing
    }
}

// Write the number of sectors chosen during CU to the database
$local_table_timer->start(); // Start benchmarking
$sql = "UPDATE ::prefix::gameconfig SET value = :sectors WHERE name='max_sectors'";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':sectors', $variables['max_sectors'], \PDO::PARAM_INT);
$result = $stmt->execute();
$local_table_timer->stop();
$variables['update_config_results']['result'] = Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);
$variables['update_config_results']['time'] = $local_table_timer->elapsed();

$lang = $tkireg->default_lang;

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);
$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('templates/classic/create_universe/50.tpl');

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
