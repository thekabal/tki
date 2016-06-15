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
// File: create_universe/80.php

if (strpos($_SERVER['PHP_SELF'], '/80.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

// Determine current step, next step, and number of steps
$create_universe_info = Tki\BigBang::findStep(__FILE__);

// Set variables
$variables['templateset']            = $tkireg->default_template;
$variables['body_class']             = 'create_universe';
$variables['title']                  = $langvars['l_cu_title'];
$variables['steps']                  = $create_universe_info['steps'];
$variables['current_step']           = $create_universe_info['current_step'];
$variables['next_step']              = $create_universe_info['next_step'];
$variables['max_sectors']            = (int) filter_input(INPUT_POST, 'sektors', FILTER_SANITIZE_NUMBER_INT); // Sanitize the input and typecast it to an int
$variables['spp']                    = round($variables['max_sectors'] * filter_input(INPUT_POST, 'special', FILTER_SANITIZE_NUMBER_INT) / 100);
$variables['oep']                    = round($variables['max_sectors'] * filter_input(INPUT_POST, 'ore', FILTER_SANITIZE_NUMBER_INT) / 100);
$variables['ogp']                    = round($variables['max_sectors'] * filter_input(INPUT_POST, 'organics', FILTER_SANITIZE_NUMBER_INT) / 100);
$variables['gop']                    = round($variables['max_sectors'] * filter_input(INPUT_POST, 'goods', FILTER_SANITIZE_NUMBER_INT) / 100);
$variables['enp']                    = round($variables['max_sectors'] * filter_input(INPUT_POST, 'energy', FILTER_SANITIZE_NUMBER_INT) / 100);
$variables['nump']                   = round($variables['max_sectors'] * filter_input(INPUT_POST, 'planets', FILTER_SANITIZE_NUMBER_INT) / 100);
$variables['empty']                  = $variables['max_sectors'] - $variables['spp'] - $variables['oep'] - $variables['ogp'] - $variables['gop'] - $variables['enp'];
$variables['initscommod']            = filter_input(INPUT_POST, 'initscommod', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$variables['initbcommod']            = filter_input(INPUT_POST, 'initbcommod', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$variables['fedsecs']                = filter_input(INPUT_POST, 'fedsecs', FILTER_SANITIZE_NUMBER_INT);
$variables['loops']                  = filter_input(INPUT_POST, 'loops', FILTER_SANITIZE_NUMBER_INT);
$variables['swordfish']              = filter_input(INPUT_POST, 'swordfish', FILTER_SANITIZE_URL);
$variables['autorun']                = filter_input(INPUT_POST, 'autorun', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'regional', 'footer', 'global_includes', 'create_universe', 'news'));
$variables['update_ticks_results']['sched'] = $tkireg->sched_ticks;
$local_table_timer = new Tki\Timer;

$now = time();
$local_table_timer->start(); // Start benchmarking for turns scheduler
$sched_file = 'sched_turns.php';
$sql = "INSERT INTO {$pdo_db->prefix}scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_turns);
$stmt->bindParam(':sched_file', $sched_file);
$stmt->bindParam(':last_run', $now);
$resxx = $stmt->execute();
$variables['update_turns_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_turns_results']['sched'] = $tkireg->sched_turns;
$local_table_timer->stop();
$variables['update_turns_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking
$resxx = $db->execute("INSERT INTO {$db->prefix}scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', $tkireg->sched_turns, 'sched_xenobe.php', ?)", array(time()));
$variables['update_xenobe_results']['result'] = Tki\Db::LogDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_xenobe_results']['sched'] = $tkireg->sched_turns;
$local_table_timer->stop();
$variables['update_xenobe_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for Ibank scheduler
$sched_file = 'sched_ibank.php';
$sql = "INSERT INTO {$pdo_db->prefix}scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_ibank);
$stmt->bindParam(':sched_file', $sched_file);
$stmt->bindParam(':last_run', $now);
$resxx = $stmt->execute();
$variables['update_ibank_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_ibank_results']['sched'] = $tkireg->sched_ibank;
$local_table_timer->stop();
$variables['update_ibank_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for news scheduler
$sched_file = 'sched_news.php';
$sql = "INSERT INTO {$pdo_db->prefix}scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_news);
$stmt->bindParam(':sched_file', $sched_file);
$stmt->bindParam(':last_run', $now);
$resxx = $stmt->execute();
$variables['update_news_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_news_results']['sched'] = $tkireg->sched_news;
$local_table_timer->stop();
$variables['update_news_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for planets scheduler
$sched_file = 'sched_planets.php';
$sql = "INSERT INTO {$pdo_db->prefix}scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_planets);
$stmt->bindParam(':sched_file', $sched_file);
$stmt->bindParam(':last_run', $now);
$resxx = $stmt->execute();
$variables['update_planets_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_planets_results']['sched'] = $tkireg->sched_planets;
$local_table_timer->stop();
$variables['update_planets_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for ports scheduler
$sched_file = 'sched_ports.php';
$sql = "INSERT INTO {$pdo_db->prefix}scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_ports);
$stmt->bindParam(':sched_file', $sched_file);
$stmt->bindParam(':last_run', $now);
$resxx = $stmt->execute();
$variables['update_ports_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_ports_results']['sched'] = $tkireg->sched_ports;
$local_table_timer->stop();
$variables['update_ports_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for tow scheduler
$sched_file = 'sched_tow.php';
$sql = "INSERT INTO {$pdo_db->prefix}scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_turns);
$stmt->bindParam(':sched_file', $sched_file);
$stmt->bindParam(':last_run', $now);
$resxx = $stmt->execute();
$variables['update_tow_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_tow_results']['sched'] = $tkireg->sched_turns; // Towing occurs at the same time as turns
$local_table_timer->stop();
$variables['update_tow_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for ranking scheduler
$sched_file = 'sched_ranking.php';
$sql = "INSERT INTO {$pdo_db->prefix}scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_ranking);
$stmt->bindParam(':sched_file', $sched_file);
$stmt->bindParam(':last_run', $now);
$resxx = $stmt->execute();
$variables['update_ranking_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_ranking_results']['sched'] = $tkireg->sched_ranking;
$local_table_timer->stop();
$variables['update_ranking_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for degrade scheduler
$sched_file = 'sched_degrade.php';
$sql = "INSERT INTO {$pdo_db->prefix}scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_degrade);
$stmt->bindParam(':sched_file', $sched_file);
$stmt->bindParam(':last_run', $now);
$resxx = $stmt->execute();
$variables['update_degrade_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_degrade_results']['sched'] = $tkireg->sched_degrade;
$local_table_timer->stop();
$variables['update_degrade_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for apocalypse scheduler
$sched_file = 'sched_apocalypse.php';
$sql = "INSERT INTO {$pdo_db->prefix}scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_apocalypse);
$stmt->bindParam(':sched_file', $sched_file);
$stmt->bindParam(':last_run', $now);
$resxx = $stmt->execute();
$variables['update_apoc_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_apoc_results']['sched'] = $tkireg->sched_apocalypse;
$local_table_timer->stop();
$variables['update_apoc_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for the governor scheduler
$sched_file = 'sched_thegovernor.php';
$sql = "INSERT INTO {$pdo_db->prefix}scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_thegovernor);
$stmt->bindParam(':sched_file', $sched_file);
$stmt->bindParam(':last_run', $now);
$resxx = $stmt->execute();
$variables['update_gov_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_gov_results']['sched'] = $tkireg->sched_thegovernor;
$local_table_timer->stop();
$variables['update_gov_results']['elapsed'] = $local_table_timer->elapsed();

// This adds a news item into the newly created news table
$local_table_timer->start(); // Start benchmarking for big bang news event
$headline = 'Big Bang';
$newstext = 'Scientists have just discovered the Universe exists!';
$news_type = 'col25';
$sql = "INSERT INTO {$pdo_db->prefix}news (headline, newstext, date, news_type) VALUES (:headline, :newstext, :date, :news_type)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':headline', $headline);
$stmt->bindParam(':newstext', $newstext);
$stmt->bindParam(':news_type', $news_type);
$today = date("Y-m-d H:i:s");
$stmt->bindParam(':date', $today);
$resxx = $stmt->execute();
$variables['first_news_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$local_table_timer->stop();
$variables['first_news_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for ibank accounts for admin
$update = $pdo_db->exec("INSERT INTO {$pdo_db->prefix}ibank_accounts (ship_id,balance,loan) VALUES (1,0,0)");
$variables['ibank_results']['result'] = Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);
$local_table_timer->stop();
$variables['ibank_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for add admin account

$sql = "INSERT INTO {$pdo_db->prefix}ships " .
       "(ship_name, ship_destroyed, character_name, password, " .
       "recovery_time, " .
       "email, turns, armor_pts, credits, sector, ship_energy, " .
       "ship_fighters, last_login, " .
       "ip_address, lang) VALUES " .
       "(:ship_name, :ship_destroyed, :character_name, :password, " .
       ":recovery_time, " .
       ":email, :turns, :armor_pts, :credits, :sector, :ship_energy, " .
       ":ship_fighters, :last_login, " .
       ":ip_address, :lang)";
$stmt = $pdo_db->prepare($sql);

$admin_ship_destr = 'N';
$admin_ip = '1.1.1.1';
$admin_recovery_time = null;
$admin_sector = 1;
$admin_last_login = date("Y-m-d H:i:s");
$admin_hashed_password = password_hash(\Tki\SecureConfig::ADMINPW, PASSWORD_DEFAULT);

$stmt->bindParam(':ship_name', $tkireg->admin_ship_name);
$stmt->bindParam(':ship_destroyed', $admin_ship_destr);
$stmt->bindParam(':character_name', $tkireg->admin_name);
$stmt->bindParam(':password', $admin_hashed_password);
$stmt->bindParam(':recovery_time', $admin_recovery_time);
$stmt->bindParam(':email', $tkireg->admin_mail);
$stmt->bindParam(':turns', $tkireg->start_turns);
$stmt->bindParam(':armor_pts', $tkireg->start_armor);
$stmt->bindParam(':credits', $tkireg->start_credits);
$stmt->bindParam(':sector', $admin_sector);
$stmt->bindParam(':ship_energy', $tkireg->start_energy);
$stmt->bindParam(':ship_fighters', $tkireg->start_fighters);
$stmt->bindParam(':last_login', $admin_last_login);
$stmt->bindParam(':ip_address', $admin_ip);
$stmt->bindParam(':lang', $tkireg->default_lang);
$resxx = $stmt->execute();
$variables['admin_account_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['admin_mail'] = $tkireg->admin_mail;
$variables['admin_name'] = $tkireg->admin_name;
$variables['admin_pass'] = \Tki\SecureConfig::ADMINPW;
$local_table_timer->stop();
$variables['admin_account_results']['elapsed'] = $local_table_timer->elapsed();

for ($zz=0; $zz<$tkireg->max_presets; $zz++)
{
    $local_table_timer->start(); // Start benchmarking for admin preset #$zz
    $sql = "INSERT INTO {$pdo_db->prefix}presets (ship_id, preset, type) " .
           "VALUES (:ship_id, :preset, :type)";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindValue(':ship_id', 1);
    $stmt->bindValue(':preset', 1);
    $stmt->bindValue(':type', 'R');
    $resxx = $stmt->execute();
    $variables['admin_preset_results'][$zz]['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
    $local_table_timer->stop(); // Stop benchmarking for admin preset #$zz
    $variables['admin_preset_results'][$zz]['elapsed'] = $local_table_timer->elapsed();
}

$local_table_timer->start(); // Start benchmarking for admin zone ownership
$sql = "INSERT INTO {$pdo_db->prefix}zones (zone_name, owner, team_zone, allow_beacon, allow_attack, allow_planetattack, allow_warpedit, allow_planet, allow_trade, allow_defenses, max_hull) " .
       "VALUES (:zone_name, :owner, :team_zone, :allow_beacon, :allow_attack, :allow_planetattack, :allow_warpedit, :allow_planet, :allow_trade, :allow_defenses, :max_hull)";
$owner = 1;
$team_zone = 'N';
$allow_beacon = 'Y';
$allow_attack = 'Y';
$allow_planetattack = 'Y';
$allow_warpedit = 'Y';
$allow_planet = 'Y';
$allow_trade = 'Y';
$allow_defenses = 'Y';
$max_hull = '0';
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':zone_name', $tkireg->admin_zone_name);
$stmt->bindValue(':owner', 0);
$stmt->bindParam(':team_zone', $team_zone);
$stmt->bindParam(':allow_beacon', $allow_beacon);
$stmt->bindParam(':allow_attack', $allow_attack);
$stmt->bindParam(':allow_planetattack', $allow_planetattack);
$stmt->bindParam(':allow_warpedit', $allow_warpedit);
$stmt->bindParam(':allow_planet', $allow_planet);
$stmt->bindParam(':allow_trade', $allow_trade);
$stmt->bindParam(':allow_defenses', $allow_defenses);
$stmt->bindParam(':max_hull', $max_hull);
$resxx = $stmt->execute();
$variables['admin_zone_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$local_table_timer->stop();
$variables['admin_zone_results']['elapsed'] = $local_table_timer->elapsed();

Tki\Header::display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);
$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('templates/classic/create_universe/80.tpl');
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
