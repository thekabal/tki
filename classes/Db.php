<?php declare(strict_types = 1);
/**
 * classes/Db.php from The Kabal Invasion.
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

// FUTURE: Add Session filters for Mcrypt & gzip, like we once had in Adodb

namespace Tki;

use PDO;
use Symfony\Component\HttpFoundation\Request;

class Db
{
    public static function isActive(\PDO $pdo_db): bool
    {
        // Get the config_values from the DB
        $results = $pdo_db->query("SELECT * FROM ::prefix::gameconfig LIMIT 1");
        if (!$results)
        {
            return false;
        }
        else
        {
            $are_there_results = ($results->rowCount() > 0);
            return $are_there_results; // Will be either true or false
        }
    }

    public function initDb(string $db_layer)
    {
        $db_port = \Tki\SecureConfig::DB_PORT;
        $db_host = \Tki\SecureConfig::DB_HOST;
        $db_user = \Tki\SecureConfig::DB_USER;
        $db_pwd = \Tki\SecureConfig::DB_PASS;
        $db_name = \Tki\SecureConfig::DB_NAME;
        $db_type = \Tki\SecureConfig::DB_TYPE;
        $db_prefix = \Tki\SecureConfig::DB_TABLE_PREFIX;

        if ($db_layer == 'adodb')
        {
            // Add MD5 encryption for sessions, and then compress it before storing it in the database

            // If there is a $db_port variable set, use it in the connection method
            if ($db_port !== null)
            {
                $db_host .= ":$db_port";
            }

            $old_db = null;
            // Attempt to connect to the database
            try
            {
                if (SecureConfig::DB_TYPE === 'postgres9')
                {
                    $old_db = \ADONewConnection('postgres9');
                }
                else
                {
                    $old_db = \ADONewConnection('mysqli');
                }

                $db_init_result = $old_db->Connect($db_host, $db_user, $db_pwd, $db_name);

                // Returns Bool true or false.
                // However ADOdb's postgres driver returns null if postgres insn't installed.
                if ($db_init_result === false || $db_init_result === 0)
                {
                    throw new \Exception();
                }
                else
                {
                    // We have connected successfully. Now set our character set to utf-8
                    $old_db->Execute("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");

                    // Set the fetch mode for database calls to be associative by default
                    $old_db->SetFetchMode(ADODB_FETCH_ASSOC);
                }
            }
            catch (\Exception $e)
            {
                // We need to display the error message onto the screen.
                $err_msg = 'The Kabal Invasion - General error: Unable to connect to the ' . $db_type .
                           ' Database. <br>Database Error: ' . $db->ErrorNo();
                throw new \Exception($err_msg);
            }

            $db->prefix = $db_prefix;
            // End of database work
            return $old_db;
        }
        else
        {
            // Connect to database with pdo
            try
            {
                $charset = "charset=utf8mb4";
                if ($db_type === 'postgres9')
                {
                    $charset = null;
                    if ($db_port === null)
                    {
                        $db_port = '5432';
                    }
                }

                // Include the charset when connecting
                $pdo_db = new \Tki\TkiPDO("mysql:host=$db_host; port=$db_port; dbname=$db_name; " . $charset,
                                          $db_user, $db_pwd, \Tki\SecureConfig::DB_TABLE_PREFIX);
            }
            catch (\PDOException $e)
            {
                $err_msg = 'The Kabal Invasion - General error: Unable to connect to the ' . $db_type .
                           ' Database. <br>Database Error: ' . $e->getMessage();
                throw new \Exception($err_msg);
            }

            // Disable emulated prepares so that we get true prepared statements
            // These are slightly slower, but also far safer in a number of cases that matter
            $pdo_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $pdo_db;
        }
    }

    /**
     * @param \PDOStatement|bool|string $query
     */
    public static function logDbErrors(\PDO $pdo_db, $query, int $served_line, string $served_page)
    {
        $request = Request::createFromGlobals();

        // Convert the content of SCRIPT_NAME (in case it has been tainted) to the correct html entities
        $safe_script_name = htmlentities($request->server->get('SCRIPT_NAME'), ENT_HTML5, 'UTF-8');
        $db_log = false;
        $error = null;
        $db_error = null;
        if ($pdo_db instanceof \PDO)
        {
            $error = $pdo_db->errorInfo()[1];
            $db_error = $pdo_db->errorInfo()[2];
            $db_log = true; // We need to create a method for disabling db logging on PDO
        }

        if ($error === null || $error == '')
        {
            return (bool) true;
        }
        else
        {
            if ($served_line > 0)
            {
                $served_line--; // Unless it is line 1 of the file, it is generally one lower than where it is reported.
            }

            $text_error = 'A Database error occurred in ' . $served_page .
                            ' on line ' . $served_line .
                            ' (called from: ' . $safe_script_name . ' the error message was: ' . $db_error .
                            ' and the query was ' . $query;

            if (self::isActive($pdo_db))
            {
                if ($db_log)
                {
                    $admin_log = new \Tki\AdminLog();
                    $admin_log->writeLog($pdo_db, LogEnums::RAW, $text_error);
                }
            }

            return (string) $db_error;
        }
    }
}
