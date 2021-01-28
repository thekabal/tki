<?php declare(strict_types = 1);
/**
 * create_univserse/80.php from The Kabal Invasion.
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
$variables['autorun']                = filter_input(INPUT_POST, 'autorun', FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common',
                                'create_universe', 'footer', 'insignias',
                                'news', 'regional'));
$variables['title'] = $langvars['l_cu_title'];
$variables['update_ticks_results']['sched'] = $tkireg->sched_ticks;
$local_table_timer = new Tki\Timer();

$now = time();
$local_table_timer->start(); // Start benchmarking for turns scheduler
$sched_file = 'sched_turns.php';
$sql = "INSERT INTO ::prefix::scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_turns, \PDO::PARAM_INT);
$stmt->bindParam(':sched_file', $sched_file, \PDO::PARAM_STR);
$stmt->bindParam(':last_run', $now, \PDO::PARAM_INT);
$resxx = $stmt->execute();
$variables['update_turns_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_turns_results']['sched'] = $tkireg->sched_turns;
$local_table_timer->stop();
$variables['update_turns_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for Kabal
$sql = "INSERT INTO ::prefix::scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_turns, \PDO::PARAM_INT);
$stmt->bindValue(':sched_file', 'sched_kabal.php', \PDO::PARAM_STR);
$stmt->bindParam(':last_run', $now, \PDO::PARAM_INT);
$resxx = $stmt->execute();
$variables['update_kabal_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_kabal_results']['sched'] = $tkireg->sched_turns;
$local_table_timer->stop();
$variables['update_kabal_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for Ibank scheduler
$sched_file = 'sched_ibank.php';
$sql = "INSERT INTO ::prefix::scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_ibank, \PDO::PARAM_INT);
$stmt->bindParam(':sched_file', $sched_file, \PDO::PARAM_STR);
$stmt->bindParam(':last_run', $now, \PDO::PARAM_INT);
$resxx = $stmt->execute();
$variables['update_ibank_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_ibank_results']['sched'] = $tkireg->sched_ibank;
$local_table_timer->stop();
$variables['update_ibank_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for news scheduler
$sched_file = 'sched_news.php';
$sql = "INSERT INTO ::prefix::scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_news, \PDO::PARAM_INT);
$stmt->bindParam(':sched_file', $sched_file, \PDO::PARAM_STR);
$stmt->bindParam(':last_run', $now, \PDO::PARAM_INT);
$resxx = $stmt->execute();
$variables['update_news_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_news_results']['sched'] = $tkireg->sched_news;
$local_table_timer->stop();
$variables['update_news_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for planets scheduler
$sched_file = 'sched_planets.php';
$sql = "INSERT INTO ::prefix::scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_planets, \PDO::PARAM_INT);
$stmt->bindParam(':sched_file', $sched_file, \PDO::PARAM_STR);
$stmt->bindParam(':last_run', $now, \PDO::PARAM_INT);
$resxx = $stmt->execute();
$variables['update_planets_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_planets_results']['sched'] = $tkireg->sched_planets;
$local_table_timer->stop();
$variables['update_planets_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for ports scheduler
$sched_file = 'sched_ports.php';
$sql = "INSERT INTO ::prefix::scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_ports, \PDO::PARAM_INT);
$stmt->bindParam(':sched_file', $sched_file, \PDO::PARAM_STR);
$stmt->bindParam(':last_run', $now, \PDO::PARAM_INT);
$resxx = $stmt->execute();
$variables['update_ports_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_ports_results']['sched'] = $tkireg->sched_ports;
$local_table_timer->stop();
$variables['update_ports_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for tow scheduler
$sched_file = 'sched_tow.php';
$sql = "INSERT INTO ::prefix::scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_turns, \PDO::PARAM_INT);
$stmt->bindParam(':sched_file', $sched_file, \PDO::PARAM_STR);
$stmt->bindParam(':last_run', $now, \PDO::PARAM_INT);
$resxx = $stmt->execute();
$variables['update_tow_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_tow_results']['sched'] = $tkireg->sched_turns; // Towing occurs at the same time as turns
$local_table_timer->stop();
$variables['update_tow_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for ranking scheduler
$sched_file = 'sched_ranking.php';
$sql = "INSERT INTO ::prefix::scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_ranking, \PDO::PARAM_INT);
$stmt->bindParam(':sched_file', $sched_file, \PDO::PARAM_STR);
$stmt->bindParam(':last_run', $now, \PDO::PARAM_INT);
$resxx = $stmt->execute();
$variables['update_ranking_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_ranking_results']['sched'] = $tkireg->sched_ranking;
$local_table_timer->stop();
$variables['update_ranking_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for degrade scheduler
$sched_file = 'sched_degrade.php';
$sql = "INSERT INTO ::prefix::scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_degrade, \PDO::PARAM_INT);
$stmt->bindParam(':sched_file', $sched_file, \PDO::PARAM_STR);
$stmt->bindParam(':last_run', $now, \PDO::PARAM_INT);
$resxx = $stmt->execute();
$variables['update_degrade_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_degrade_results']['sched'] = $tkireg->sched_degrade;
$local_table_timer->stop();
$variables['update_degrade_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for apocalypse scheduler
$sched_file = 'sched_apocalypse.php';
$sql = "INSERT INTO ::prefix::scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_apocalypse, \PDO::PARAM_INT);
$stmt->bindParam(':sched_file', $sched_file, \PDO::PARAM_STR);
$stmt->bindParam(':last_run', $now, \PDO::PARAM_INT);
$resxx = $stmt->execute();
$variables['update_apoc_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_apoc_results']['sched'] = $tkireg->sched_apocalypse;
$local_table_timer->stop();
$variables['update_apoc_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for the governor scheduler
$sched_file = 'sched_thegovernor.php';
$sql = "INSERT INTO ::prefix::scheduler (run_once, ticks_full, sched_file, last_run) VALUES ('N', :ticks_full, :sched_file, :last_run)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ticks_full', $tkireg->sched_thegovernor, \PDO::PARAM_INT);
$stmt->bindParam(':sched_file', $sched_file, \PDO::PARAM_STR);
$stmt->bindParam(':last_run', $now, \PDO::PARAM_INT);
$resxx = $stmt->execute();
$variables['update_gov_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['update_gov_results']['sched'] = $tkireg->sched_thegovernor;
$local_table_timer->stop();
$variables['update_gov_results']['elapsed'] = $local_table_timer->elapsed();

// This adds a news item into the newly created news table
$local_table_timer->start(); // Start benchmarking for big bang news event
$headline = 'Big Bang';
$newstext = $langvars['l_cu_bigbang'];
$news_type = 'col25';
$sql = "INSERT INTO ::prefix::news (headline, newstext, date, news_type) VALUES (:headline, :newstext, :date, :news_type)";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':headline', $headline, \PDO::PARAM_STR);
$stmt->bindParam(':newstext', $newstext, \PDO::PARAM_STR);
$stmt->bindParam(':news_type', $news_type, \PDO::PARAM_STR);
$today = date("Y-m-d H:i:s");
$stmt->bindParam(':date', $today, \PDO::PARAM_STR);
$resxx = $stmt->execute();
$variables['first_news_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$local_table_timer->stop();
$variables['first_news_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for ibank accounts for admin
$update = $pdo_db->exec("INSERT INTO ::prefix::ibank_accounts (ship_id,balance,loan) VALUES (1,0,0)");
$variables['ibank_results']['result'] = Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);
$local_table_timer->stop();
$variables['ibank_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for add admin account

$sql = "INSERT INTO ::prefix::ships " .
       "(ship_name, ship_destroyed, character_name, password, " .
       "recovery_time, " .
       "email, turns, armor_pts, credits, sector, ship_energy, " .
       "ship_fighters, last_login, " .
       "ip_address, lang) VALUES " .
       "(:ship_name, :ship_destroyed, :character_name, :password, " .
       ":recovery_time, " .
       ":email, 1200, 10, :credits, :sector, 100, " .
       "10, :last_login, " .
       ":ip_address, :lang)";
$stmt = $pdo_db->prepare($sql);

$admin_ship_destr = 'N';
$admin_ip = '1.1.1.1';
$admin_recovery_time = null;
$admin_sector = 1;
$admin_last_login = date("Y-m-d H:i:s");
$admin_hashed_pw = password_hash(\Tki\SecureConfig::ADMIN_PASS, PASSWORD_DEFAULT);
$admin_credits = 200000000;

$stmt->bindParam(':ship_name', $tkireg->admin_ship_name, \PDO::PARAM_STR);
$stmt->bindParam(':ship_destroyed', $admin_ship_destr, \PDO::PARAM_STR);
$stmt->bindParam(':character_name', $tkireg->admin_name, \PDO::PARAM_STR);
$stmt->bindParam(':password', $admin_hashed_pw, \PDO::PARAM_STR);
$stmt->bindParam(':recovery_time', $admin_recovery_time, \PDO::PARAM_NULL);
$stmt->bindParam(':email', $tkireg->admin_mail, \PDO::PARAM_STR);
$stmt->bindParam(':credits', $admin_credits, \PDO::PARAM_INT);
$stmt->bindParam(':sector', $admin_sector, \PDO::PARAM_INT);
$stmt->bindParam(':last_login', $admin_last_login, \PDO::PARAM_STR);
$stmt->bindParam(':ip_address', $admin_ip, \PDO::PARAM_INT);
$stmt->bindParam(':lang', $tkireg->default_lang, \PDO::PARAM_STR);

$resxx = $stmt->execute();
$variables['admin_account_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$variables['admin_mail'] = $tkireg->admin_mail;
$variables['admin_name'] = $tkireg->admin_name;
$variables['admin_pass'] = \Tki\SecureConfig::ADMIN_PASS;
$local_table_timer->stop();
$variables['admin_account_results']['elapsed'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking for set password on admin account
$sql2 = "INSERT INTO ::prefix::players " .
        "(password) VALUES" .
        "(:password)";
$stmt2 = $pdo_db->prepare($sql2);
$admin_hashed_pw = password_hash(\Tki\SecureConfig::ADMIN_PASS, PASSWORD_DEFAULT);
$stmt2->bindParam(':password', $admin_hashed_pw, \PDO::PARAM_STR);
$resxx = $stmt2->execute();
$variables['admin_password_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$local_table_timer->stop();
$variables['admin_password_results']['elapsed'] = $local_table_timer->elapsed();

for ($zz = 0; $zz < $tkireg->max_presets; $zz++)
{
    $local_table_timer->start(); // Start benchmarking for admin preset #$zz
    $sql = "INSERT INTO ::prefix::presets (ship_id, preset, type) " .
           "VALUES (:ship_id, :preset, :type)";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindValue(':ship_id', 1, \PDO::PARAM_INT);
    $stmt->bindValue(':preset', 1, \PDO::PARAM_INT);
    $stmt->bindValue(':type', 'R', \PDO::PARAM_STR);
    $resxx = $stmt->execute();
    $variables['admin_preset_results'][$zz]['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
    $local_table_timer->stop(); // Stop benchmarking for admin preset #$zz
    $variables['admin_preset_results'][$zz]['elapsed'] = $local_table_timer->elapsed();
}

$local_table_timer->start(); // Start benchmarking for admin zone ownership
$sql = "INSERT INTO ::prefix::zones (zone_name, owner, team_zone, allow_beacon, allow_attack, allow_planetattack, allow_warpedit, allow_planet, allow_trade, allow_defenses, max_hull) " .
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
$stmt->bindParam(':zone_name', $tkireg->admin_zone_name, \PDO::PARAM_STR);
$stmt->bindValue(':owner', 0, \PDO::PARAM_INT);
$stmt->bindParam(':team_zone', $team_zone, \PDO::PARAM_STR);
$stmt->bindParam(':allow_beacon', $allow_beacon, \PDO::PARAM_STR);
$stmt->bindParam(':allow_attack', $allow_attack, \PDO::PARAM_STR);
$stmt->bindParam(':allow_planetattack', $allow_planetattack, \PDO::PARAM_STR);
$stmt->bindParam(':allow_warpedit', $allow_warpedit, \PDO::PARAM_STR);
$stmt->bindParam(':allow_planet', $allow_planet, \PDO::PARAM_STR);
$stmt->bindParam(':allow_trade', $allow_trade, \PDO::PARAM_STR);
$stmt->bindParam(':allow_defenses', $allow_defenses, \PDO::PARAM_STR);
$stmt->bindParam(':max_hull', $max_hull, \PDO::PARAM_INT);
$resxx = $stmt->execute();
$variables['admin_zone_results']['result'] = Tki\Db::logDbErrors($pdo_db, $resxx, __LINE__, __FILE__);
$local_table_timer->stop();
$variables['admin_zone_results']['elapsed'] = $local_table_timer->elapsed();

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);
$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('templates/classic/create_universe/80.tpl');

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
