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

$link = null;

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('new', 'login', 'common', 'global_includes', 'global_funcs', 'footer', 'news', 'index', 'options'));

$variables = null;
$variables['lang'] = $lang;
$variables['link'] = $link;
$variables['admin_mail'] = $tkireg->admin_mail;
$variables['body_class'] = 'index';
$variables['title'] = $langvars['l_new_title'];
$variables['template'] = $tkireg->default_template; // Temporarily set the template to the default template until we have a user option

Tki\Header::display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);
$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('new.tpl');

Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
