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
// File: setup_info.php

require_once './common.php';
require_once './config/SecureConfig.php';

// Set headers
header('Content-type: text/html; charset=utf-8');  // Set character set to utf-8, and using HTML as our content type
header('X-UA-Compatible: IE=Edge, chrome=1');      // Tell IE to use the latest version of the rendering engine, and to use chrome if it is available. This is not needed after IE11.
header('Cache-Control: public');                   // Tell the browser (and any caches) that this information can be stored in public caches.
header('Connection: Keep-Alive');                  // Tell the browser to keep going until it gets all data, please.
header('Vary: Accept-Encoding, Accept-Language');  // Tell CDN's or proxies to keep a separate version of the page in various encodings - compressed or not, in english or french for example.
header('Keep-Alive: timeout=15, max=100');         // Ask for persistent HTTP connections (15sec), which give better per-client performance, but can be worse (for a server) for many.

// Set cookies for cookie test
setcookie('TestCookie', '', 0);
setcookie('TestCookie', 'Shuzbutt', time() + 3600, Tki\SetPaths::setGamepath(), $request->server->get('HTTP_HOST'));

// Database configuration.
$db_host = \Tki\SecureConfig::DB_HOST;
$db_port = \Tki\SecureConfig::DB_PORT;
$db_user = \Tki\SecureConfig::DB_USER;
$db_pwd = \Tki\SecureConfig::DB_PASS;
$db_type = \Tki\SecureConfig::DB_TYPE;
$db_name = \Tki\SecureConfig::DB_NAME;
$db_prefix = \Tki\SecureConfig::DB_TABLE_PREFIX;

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('new', 'login', 'common', 'global_includes', 'global_funcs', 'footer', 'news', 'index', 'options', 'setup_info'));

$variables = null;
$variables['lang'] = $lang;
$variables['link'] = 'https://kabal-invasion.com/forums/';
$variables['admin_mail'] = $tkireg->admin_mail;
$variables['body_class'] = 'tki';
$variables['template'] = $tkireg->default_template; // Temporarily set the template to the default template until we have a user option

// Get the webserver version.
$sapi = php_sapi_name();
$serverType = '';
$serverVersion = '';

if($sapi === 'apache')
{
    $serverType = $sapi;
    $serverVersion = apache_get_version();
}
else
{
    // This logic presumes nginx is being used.
    $nameVersionPair = explode('/', $request->server->get('SERVER_SOFTWARE'));
    $serverType = $nameVersionPair[0];
    $serverVersion = array_pop($nameVersionPair);
}

$variables['selected_lang'] = null;
$variables['system'] = php_uname();
$variables['remote_addr'] = $request->server->get('REMOTE_ADDR');
$variables['server_addr'] = $request->server->get('HTTP_HOST') . ':' . $request->server->get('SERVER_PORT');
$variables['zend_version'] = zend_version();
$variables['php_version'] = PHP_VERSION;
$variables['php_sapi_name'] = php_sapi_name();
$variables['server_type'] = $serverType;
$variables['server_version'] = $serverVersion;
$variables['game_path'] = Tki\SetPaths::setGamepath();
$variables['db_type'] = $db_type;
$variables['db_name'] = $db_name;
$variables['db_prefix'] = $db_prefix;
$variables['admin_name'] = $tkireg->admin_name;
$variables['admin_email'] = str_replace('@', ' AT ', $tkireg->admin_mail);
$variables['release_version'] = $tkireg->release_version;
$variables['turns_per_tick'] = $tkireg->turns_per_tick;
$variables['sched_ticks'] = $tkireg->sched_ticks;
$variables['sched_turns'] = $tkireg->sched_turns;
$variables['sched_ports'] = $tkireg->sched_ports;
$variables['sched_planets'] = $tkireg->sched_planets;
$variables['sched_ibank'] = $tkireg->sched_ibank;
$variables['sched_ranking'] = $tkireg->sched_ranking;
$variables['sched_news'] = $tkireg->sched_news;
$variables['sched_degrade'] = $tkireg->sched_degrade;
$variables['sched_apocalypse'] = $tkireg->sched_apocalypse;
$variables['sched_thegovernor'] = $tkireg->sched_thegovernor;
$variables['hash'] = mb_strtoupper(md5_file(__FILE__));
$variables['updated_on'] = date("l, F d, Y", filemtime(basename(__FILE__)));
$variables['cookie_test'] = ($_COOKIE['TestCookie'] !== null);
$variables['dev_mode'] = file_exists('dev');
$variables['php_module_pdo'] = extension_loaded('pdo_mysql');
$variables['php_module_mysqli'] = extension_loaded('mysqli');
$variables['adodb_path_test'] = file_exists(realpath("vendor/adodb/adodb-php/adodb.inc.php"));
$variables['smarty_path_test'] = file_exists(realpath("vendor/smarty/smarty/distribution/libs/Smarty.class.php"));
$variables['title'] = $langvars['l_setup_info_title'];

// Test Smarty
$test_smarty = new \Smarty();
$test_smarty->setCompileDir('templates/_compile/');
$test_smarty->setCacheDir('templates/_cache/');
$test_smarty->setConfigDir('templates/_configs/');

// Smarty outputs directly (yuck), so we output buffer it instead
ob_start();
$test_smarty->testInstall();
$variables['smarty_test_err'] = ob_get_contents();
ob_end_clean();
if (mb_strpos($variables['smarty_test_err'], 'FAILED'))
{
    $variables['smarty_test'] = false;
}
else
{
    $variables['smarty_test'] = true;
}

if ($db_port !== null)
{
    $db_host .= ":$db_port";
}

// Attempt to connect to the database via adodb
$test_db = ADONewConnection('mysqli');
$variables['adodb_conn_test'] = $test_db->Connect($db_host, $db_user, $db_pwd, $db_name);
if (!($variables['adodb_conn_test']))
{
    $variables['adodb_conn_err'] = "Error message";
}

// Attempt to connect to the database via PDO
try
{
    $test_pdo_db = new PDO("mysql:host=$db_host; port=$db_port; dbname=$db_name; charset=utf8mb4", $db_user, $db_pwd);
    $variables['pdo_conn_test'] = true;
    $variables['pdo_server_ver'] = $test_pdo_db->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
}
catch (\PDOException $e)
{
    $err_msg = "Unable to connect to the " . $db_type .
               " Database.<br>\n Database Error: ".
               $e->getMessage() . "<br>\n";
    $variables['pdo_conn_test'] = false;
    $variables['pdo_conn_err'] = $err_msg;
}

// Get environment variables
$id = 0;
ksort($_SERVER);
reset($_SERVER);
foreach ($_SERVER as $name => $value)
{
    $array_var = explode(";", "$value");
    $value = implode("; ", $array_var);
    $variables['env_vars'][$id]['name'] = trim($name);
    $variables['env_vars'][$id]['value'] = trim($value);
    $id++;
}

// Properly format the database host/port
if ($db_port !== null)
{
    $variables['db_addr'] = $db_host . ":" . $db_port;
}
else
{
    $variables['db_addr'] = $db_host;
}

Tki\Header::display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);

$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('templates/classic/setup_info.tpl');

Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
