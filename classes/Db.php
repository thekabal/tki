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
// File: classes/Db.php
//
// Class for managing the database inside TKI

namespace Tki;

use PDO;

class Db
{
    public static function isActive(\PDO $pdo_db)
    {
        // Get the config_values from the DB
        $results = $pdo_db->query("SELECT * FROM {$pdo_db->prefix}gameconfig LIMIT 1");
        if (!$results)
        {
            return false;
        }
        else
        {
            $returnvalue = ($results->rowCount() > 0);
            return $returnvalue;
        }
    }

    public function initDb($db_layer)
    {
        $db_port = \Tki\SecureConfig::PORT;
        $db_host = \Tki\SecureConfig::HOST;
        $db_user = \Tki\SecureConfig::USER;
        $db_pwd = \Tki\SecureConfig::PASS;
        $db_name = \Tki\SecureConfig::NAME;
        $db_type = \Tki\SecureConfig::TYPE;
        $db_prefix = \Tki\SecureConfig::PREFIX;

        if ($db_layer == 'adodb')
        {
            // Add MD5 encryption for sessions, and then compress it before storing it in the database
            //ADODB_Session::filter (new ADODB_Encrypt_Mcrypt ());
            //ADODB_Session::filter (new ADODB_Compress_Gzip ());

            // If there is a $db_port variable set, use it in the connection method
            if ($db_port !== null)
            {
                $db_host .= ":$db_port";
            }

            // Attempt to connect to the database
            try
            {
                if ($db_type === 'postgres9')
                {
                    $db = ADONewConnection('postgres9');
                }
                else
                {
                    $db = ADONewConnection('mysqli');
                }

                $db_init_result = @@@@$db->Connect($db_host, $db_user, $db_pwd, $db_name);

                // Returns Boolean true or false.
                // However ADOdb's postgres driver returns null if postgres insn't installed.
                if ($db_init_result === false || $db_init_result === 0)
                {
                    throw new \Exception;
                }
                else
                {
                    // We have connected successfully. Now set our character set to utf-8
                    $db->Execute("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");

                    // Set the fetch mode for database calls to be associative by default
                    $db->SetFetchMode(ADODB_FETCH_ASSOC);
                }
            }
            catch (\Exception $e)
            {
                // We need to display the error message onto the screen.
                $err_msg = 'The Kabal Invasion - General error: Unable to connect to the ' . $db_type .
                            ' Database.<br> Database Error: '. $db->ErrorNo() .
                            ': '. $db->ErrorMsg() .'<br>\n';
                die ($err_msg);
            }

            if ($db_type === 'postgres9')
            {
                $db->type = 'pgsql';
            }
            else
            {
                $db->type = 'mysql';
            }

            $db->prefix = $db_prefix;
            // End of database work
            return $db;
        }
        else
        {
            // Connect to database with pdo
            try
            {
                if ($db_type === 'postgres9')
                {
                    if ($db_port === null)
                    {
                        $db_port = '5432';
                    }

                    $pdo_db = new PDO("pgsql:host=$db_host; port=$db_port; dbname=$db_name;", $db_user, $db_pwd);
                }
                else
                {
                    // Include the charset when connecting
                    $pdo_db = new PDO("mysql:host=$db_host; port=$db_port; dbname=$db_name; charset=utf8mb4", $db_user, $db_pwd);
                }
            }
            catch (\PDOException $e)
            {
                $err_msg = 'The Kabal Invasion - General error: Unable to connect to the ' . $db_type .
                            ' Database.<br> Database Error: '.
                            $e->getMessage() . "<br>\n";
                die ($err_msg);
            }

            // Disable emulated prepares so that we get true prepared statements
            // These are slightly slower, but also far safer in a number of cases that matter
            $pdo_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            if ($db_type === 'postgres9')
            {
                $pdo_db->type = 'pgsql';
            }
            else
            {
                $pdo_db->type = 'mysql';
            }

            $pdo_db->prefix = $db_prefix;
            return $pdo_db;
        }
    }

    /**
     * @param integer $served_line
     * @param string $served_page
     */
    public static function logDbErrors(\PDO $pdo_db, $query, $served_line, $served_page)
    {
        // Convert the content of PHP_SELF (in case it has been tainted) to the correct html entities
        $safe_script_name = htmlentities($_SERVER['PHP_SELF'], ENT_HTML5, 'UTF-8');
        $db_log = false;
        $error = null;
        $db_error = null;
        if ($pdo_db instanceof PDO)
        {
            $error = $pdo_db->errorInfo()[1];
            $db_error = $pdo_db->errorInfo()[2];
            $db_log = true; // We need to create a method for disabling db logging on PDO
        }

        if ($error === 'null' || $error == '')
        {
            return true;
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
                            'and the query was ' . $query;

            if (!self::isActive($pdo_db))
            {
                if ($db_log)
                {
                    AdminLog::writeLog($pdo_db, LOG_RAW, $text_error);
                }
            }

            return $db_error;
        }
    }
}
