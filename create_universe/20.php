<?php declare(strict_types = 1);
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
// File: create_universe/20.php

// Determine current step, next step, and number of steps
$step_finder = new Tki\BigBang();
$create_universe_info = $step_finder->findStep(__FILE__);

// Set variables
$variables['templateset']  = $tkireg->default_template;
$variables['body_class']   = 'create_universe';
$variables['title']        = $langvars['l_cu_title'];
$variables['steps']        = $create_universe_info['steps'];
$variables['current_step'] = $create_universe_info['current_step'];
$variables['next_step']    = $create_universe_info['next_step'];
$variables['max_sectors']  = (int) filter_input(INPUT_POST, 'sektors', FILTER_SANITIZE_NUMBER_INT); // Sanitize the input and typecast it to an int
$variables['spp']          = round($variables['max_sectors'] * filter_input(INPUT_POST, 'special', FILTER_SANITIZE_NUMBER_INT) / 100);
$variables['oep']          = round($variables['max_sectors'] * filter_input(INPUT_POST, 'ore', FILTER_SANITIZE_NUMBER_INT) / 100);
$variables['ogp']          = round($variables['max_sectors'] * filter_input(INPUT_POST, 'organics', FILTER_SANITIZE_NUMBER_INT) / 100);
$variables['gop']          = round($variables['max_sectors'] * filter_input(INPUT_POST, 'goods', FILTER_SANITIZE_NUMBER_INT) / 100);
$variables['enp']          = round($variables['max_sectors'] * filter_input(INPUT_POST, 'energy', FILTER_SANITIZE_NUMBER_INT) / 100);
$variables['nump']         = round($variables['max_sectors'] * filter_input(INPUT_POST, 'planets', FILTER_SANITIZE_NUMBER_INT) / 100);
$variables['empty']        = $variables['max_sectors'] - $variables['spp'] - $variables['oep'] - $variables['ogp'] - $variables['gop'] - $variables['enp'];
$variables['initscommod']  = filter_input(INPUT_POST, 'initscommod', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$variables['initbcommod']  = filter_input(INPUT_POST, 'initbcommod', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$variables['fedsecs']      = filter_input(INPUT_POST, 'fedsecs', FILTER_SANITIZE_NUMBER_INT);
$variables['loops']        = filter_input(INPUT_POST, 'loops', FILTER_SANITIZE_NUMBER_INT);
$variables['swordfish']    = filter_input(INPUT_POST, 'swordfish', FILTER_SANITIZE_URL);
$variables['autorun']      = filter_input(INPUT_POST, 'autorun', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'regional', 'footer', 'global_includes', 'create_universe', 'news'));

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);
$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('templates/classic/create_universe/20.tpl');

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
