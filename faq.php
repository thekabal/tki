<?php declare(strict_types = 1);
/**
 * faq.php from The Kabal Invasion.
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

$lang = $tkireg->default_lang;
$link = null;

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'faq',
                                'universal'));

$variables = null;
$variables['lang'] = $lang;
$variables['link'] = $link;
$variables['body_class'] = 'faq';
$variables['title'] = $langvars['l_faq_title'];

if (empty($_SESSION['username']))
{
    $variables['linkback'] = array("fulltext" => $langvars['l_universal_main_login'], "link" => "index.php");
}
else
{
    $variables['linkback'] = array("fulltext" => $langvars['l_universal_main_menu'], "link" => "index.php");
}

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);

$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('faq.tpl');


$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
