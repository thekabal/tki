<?php declare(strict_types = 1);
/**
 * index.php from The Kabal Invasion.
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

$index_page = true;
require_once './common.php';

if (Tki\Db::isActive($pdo_db))
{
    // Database driven language entries
    $langvars = Tki\Translate::load($pdo_db, $lang, array('footer', 'index',
                                    'insignias', 'login', 'logout', 'main'));
    $template->assign('lang', $lang);
    $template->assign('title', $langvars['l_welcome_tki']);
    $template->assign('link_forums', $tkireg->link_forums);
    $template->assign('admin_mail', $tkireg->admin_mail);
    $template->assign('body_class', 'index');
    $template->assign('template', $tkireg->default_template);

    // Temporarily set the template to the default template until we have a user option
    $header = new Tki\Header();
    $header->display($pdo_db, $lang, $template, $langvars['l_welcome_tki'], 'index');
    $template->assign('langvars', $langvars);
    $template->display('index.tpl');

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
}
else
{
    // If DB is not active, redirect to create universe to run install
    header('Location: create_universe.php');
    exit;
}
