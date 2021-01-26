<?php declare(strict_types = 1);
/**
 * common.php from The Kabal Invasion.
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

require_once './vendor/autoload.php';               // Load the auto-loader
mb_http_output('UTF-8');                            // Our output should be served in UTF-8 no matter what.
mb_internal_encoding('UTF-8');                      // We are explicitly UTF-8, with Unicode language variables.
ini_set('include_path', '.');                       // Set include path to avoid issues on a few platforms
ini_set('session.use_strict_mode', '1');            // Ensure that PHP will not accept uninitialized session ID
ini_set('session.use_only_cookies', '1');           // Ensure that sessions will only be stored in a cookie
ini_set('session.cookie_httponly', '1');            // Ensure that javascript cannot tamper with session cookies
ini_set('session.use_trans_sid', '0');              // Prevent session ID from being put in URLs
ini_set('session.cookie_secure', 'on');             // Cookies should only be sent over secure connections (SSL)
ini_set('url_rewriter.tags', '');                   // Do not pass Session id on the url for improved security on login
ini_set('default_charset', 'utf-8');                // Set PHP's default character set to utf-8

if (file_exists('dev'))                             // Create/touch a file named dev to activate development mode
{
    ini_set('error_reporting', '-1');               // During development, output all errors, even notices
    ini_set('display_errors', '1');                 // During development, display all errors
}
else
{
    ini_set('error_reporting', '0');                // Do not report errors
    ini_set('display_errors', '0');                 // Do not display errors
}

session_name('tki_session');                        // Change the default to defend better against session hijacking
date_default_timezone_set('UTC');                   // Set to your server's local time zone - Avoid a PHP notice
                                                    // Since header is now temlate driven, these weren't being passed
                                                    // along except on old crusty pages. Now everthing gets them!
header('Content-type: text/html; charset=utf-8');   // Set character set to utf-8, and using HTML as our content type
header('X-UA-Compatible: IE=Edge, chrome=1');       // IE - use the latest rendering engine (edge), and chrome shell
header('Cache-Control: public');                    // Tell browser and caches that it's ok to store in public caches
header('Connection: Keep-Alive');                   // Tell browser to keep going until it gets all data, please
header('Vary: Accept-Encoding, Accept-Language');   // Tell CDN's or proxies to keep a separate version of the page in
                                                    // various encodings - compressed or not, in english or french
                                                    // for example.
header('Keep-Alive: timeout=15, max=100');          // Ask for persistent HTTP connections (15sec), which give better
                                                    // per-client performance, but can be worse (for a server) for many
header('X-Frame-Options: DENY');                    // Prevent iFrames and clickjacking
header('X-XSS-Protection: 1; mode=block');          // XSS protection - block if XSS detected
header('X-Content-Type-Options: nosniff');          // Prevents MIME-sniffing away from the declared content-type.
ob_start(array('Tki\Compress', 'compress'));        // Start a buffer, and when it closes (at the end of a request),
                                                    // call the callback function 'Tki\Compress' to properly handle
                                                    // detection of compression.

$pdo_db = new Tki\Db();
try
{
    $pdo_db = $pdo_db->initPdodb();               // Connect to db using pdo
}
catch (Exception $tki_exception)
{
    echo "<html><pre>";
    die($tki_exception . "</pre></html>");
}

$old_db = new Tki\Db();
try
{
    $old_db = $old_db->initAdodb();             // Connect to db using adodb also - for now - to be eliminated!
}
catch (Exception $tki_exception)
{
    echo "<html><pre>";
    die($tki_exception . "</pre></html>");
}

$tkireg = null;
$tkireg = new Tki\Reg($pdo_db);                     // TKI Registry object -  passing config variables via classes
$tkireg->tkitimer = new Tki\Timer();                // Create a benchmark timer to get benchmarking data for everything
$tkireg->tkitimer->start();                         // Start benchmarking immediately

$langvars = null;                                   // Language variables in every page, set them to a null value first
try
{
    $template = new \Tki\Smarty();
}
catch (Exception $tki_exception)
{
    echo "<html><pre>";
    die($tki_exception . "</pre></html>");
}

$template->setTheme('classic');

if (Tki\Db::isActive($pdo_db))
{
    $tki_session = new Tki\Sessions($pdo_db);
    session_start();
}

$lang = $tkireg->default_lang;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
