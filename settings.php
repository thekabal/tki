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
// File: copyright.php

$index_page = true;
require_once './common.php';

$link = null;

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('main', 'login', 'logout', 'index', 'common', 'regional', 'settings', 'footer', 'global_includes', 'global_funcs'));

$variables = null;
$variables['lang'] = $lang;
$variables['link'] = $link;
$variables['title'] = $langvars['l_set_settings'];
$variables['link_forums'] = $tkireg->link_forums;
$variables['admin_mail'] = $tkireg->admin_mail;
$variables['body_class'] = 'settings';
$variables['release_version'] = $tkireg->release_version;
$variables['game_name'] = $tkireg->game_name;
$variables['mine_hullsize'] = $tkireg->mine_hullsize;
$variables['max_ewdhullsize'] = $tkireg->max_ewdhullsize;
$variables['max_sectors'] = number_format($tkireg->max_sectors, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
$variables['max_links'] = $tkireg->max_links;
$variables['max_fed_hull'] = $tkireg->max_fed_hull;
$variables['allow_ibank'] = (bool) $tkireg->allow_ibank;
$variables['ibank_interest'] = $tkireg->ibank_interest * 100;
$variables['ibank_loaninterest'] = $tkireg->ibank_loaninterest * 100;
$variables['base_defense'] = $tkireg->base_defense;
$variables['colonist_limit'] = number_format($tkireg->colonist_limit, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
$variables['max_turns'] = number_format($tkireg->max_turns, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
$variables['max_planets_sector'] = $tkireg->max_planets_sector;
$variables['max_traderoutes_player'] = $tkireg->max_traderoutes_player;
$variables['colonist_production_rate'] = $tkireg->colonist_production_rate;
$variables['energy_per_fighter'] = $tkireg->energy_per_fighter;
$variables['defense_degrade_rate'] = $tkireg->defense_degrade_rate * 100;
$variables['min_bases_to_own'] = $tkireg->min_bases_to_own;
$variables['interest_rate'] = number_format(($tkireg->interest_rate - 1) * 100, 3, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
$variables['sched_ticks'] = $tkireg->sched_ticks;
$variables['sched_turns'] = $tkireg->sched_turns;
$variables['sched_ibank'] = $tkireg->sched_ibank;
$variables['sched_news'] = $tkireg->sched_news;
$variables['sched_planets'] = $tkireg->sched_planets;
$variables['max_credits_without_base'] = $tkireg->max_credits_without_base;
$variables['sched_ports'] = $tkireg->sched_ports;
$variables['sched_ranking'] = $tkireg->sched_ranking;
$variables['sched_degrade'] = $tkireg->sched_degrade;
$variables['sched_apocalypse'] = $tkireg->sched_apocalypse;
$variables['port_regenrate'] = $tkireg->port_regenrate;

// Colonists needed to produce 1 Fighter each turn
$variables['cols_needed_fit'] = number_format((1 / $tkireg->colonist_production_rate) / $tkireg->fighter_prate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);

// Colonists needed to produce 1 Torpedo each turn
$variables['cols_needed_torp'] = number_format((1 / $tkireg->colonist_production_rate) / $tkireg->torpedo_prate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);

// Colonists needed to produce 1 Ore each turn
$variables['cols_needed_ore'] = number_format((1 / $tkireg->colonist_production_rate) / $tkireg->ore_prate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);

// Colonists needed to produce 1 Organics each turn
$variables['cols_needed_org'] = number_format((1 / $tkireg->colonist_production_rate) / $tkireg->organics_prate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);

// Colonists needed to produce 1 Goods each turn
$variables['cols_needed_goods'] = number_format((1 / $tkireg->colonist_production_rate) / $tkireg->goods_prate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);

// Colonists needed to produce 1 Energy each turn
$variables['cols_needed_ene'] = number_format((1 / $tkireg->colonist_production_rate) / $tkireg->energy_prate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);

// Colonists needed to produce 1 Credits each turn
$variables['cols_needed_cred'] = number_format((1 / $tkireg->colonist_production_rate) / $tkireg->credits_prate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);

// Get list of available languages
$variables['list_of_langs'] = Tki\Languages::listAvailable($pdo_db, $lang);

// Temporarily set the template to the default template until we have a user option
$variables['template'] = $tkireg->default_template;


if (empty ($_SESSION['username']))
{
    $variables['loggedin'] = (bool) true;
    $variables['linkback'] = array('caption' => $langvars['l_global_mlogin'], 'link' => 'index.php');
}
else
{
    $variables['loggedin'] = (bool) false;
    $variables['linkback'] = array('caption' => $langvars['l_global_mmenu'], 'link' => 'main.php');
}

Tki\Header::display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);

$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('settings.tpl');

Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
