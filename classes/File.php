<?php declare(strict_types = 1);
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
// File: classes/File.php
//
// This class handles direct file methods for TKI. Included is iniToDb, a method
// for importing values from an INI file into the database.

namespace Tki;

class File
{
    public static function iniToDb(\PDO $pdo_db, string $ini_file, string $ini_table, string $section, Reg $tkireg) : bool
    {
        // This is a loop, that reads a ini file, of the type variable = value.
        // It will loop thru the list of the ini variables, and push them into the db.

        $ini_keys = self::betterParseIni($ini_file);
        // We need a way to deal with errors in parse_ini_file here #fixit #future

        $status_array = array();
        $array_item = 0;
        $final_result = null;
        $pdo_db->beginTransaction(); // We enclose the inserts in a transaction as it is roughly 30 times faster

        $insert_sql = "INSERT into ::prefix::" . $ini_table .
                      " (name, category, value, section, type) VALUES " .
                      "(:config_key, :config_category, :config_value, :section, :type)";
        $stmt = $pdo_db->prepare($insert_sql);
        Db::logDbErrors($pdo_db, $insert_sql, __LINE__, __FILE__);

        foreach ($ini_keys as $config_category => $config_line)
        {
            foreach ($config_line as $config_key => $type_n_value)
            {
                if (strpos($ini_file, '_config'))
                {
                    // Import all the variables into the registry
                    settype($type_n_value['value'], $type_n_value['type']);
                    $tkireg->$config_key = $type_n_value['value'];
                }

                $stmt->bindParam(':config_key', $config_key, \PDO::PARAM_STR);
                $stmt->bindParam(':config_category', $config_category, \PDO::PARAM_STR);
                $stmt->bindParam(':section', $section, \PDO::PARAM_STR);
                $stmt->bindParam(':type', $type_n_value['type'], \PDO::PARAM_STR);
                if (is_int($type_n_value['value']))
                {
                    $stmt->bindParam(':config_value', $type_n_value['value'], \PDO::PARAM_INT);
                }
                elseif ($type_n_value['value'] === null)
                {
                    // Not currently used - but this should handle it correctly if we add it
                    $stmt->bindParam(':config_value', $type_n_value['value'], \PDO::PARAM_NULL);
                }
                elseif (is_bool($type_n_value['value']))
                {
                    // Boolean true/false are stored temporarily as 1 for true and 0 for false
                    if ($type_n_value['value'])
                    {
                        $stmt->bindValue(':config_value', '1', \PDO::PARAM_INT);
                    }
                    else
                    {
                        $stmt->bindValue(':config_value', '0', \PDO::PARAM_INT);
                    }
                }
                else
                {
                    $stmt->bindParam(':config_value', $type_n_value['value'], \PDO::PARAM_STR);
                }

                $result = $stmt->execute();
                $status_array[$array_item++] = Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);
            }
        }

        for ($k = 1; $k < $array_item; $k++)
        {
            // Status array will continue the results of individual executes.
            // It should be === true unless something went horribly wrong.
            $final_result = true;
            if ($status_array[$k] !== true)
            {
                $final_result = false;
            }
        }

        unset($ini_keys);
        if ($final_result !== true) // If the final result is not true, rollback our transaction, and return false.
        {
            $pdo_db->rollBack();
            Db::logDbErrors($pdo_db, 'Rollback transaction on File::initodb', __LINE__, __FILE__);

            return false;
        }
        else // Else we process the transaction, and return true
        {
            $pdo_db->commit(); // Complete the transaction
            Db::logDbErrors($pdo_db, 'Complete transaction on File::initodb', __LINE__, __FILE__);

            return true;
        }
    }

    // Very close to a drop-in replacement for parse_ini_file, although without the second parameter
    // This defaults to the equivalent of "true" for the second param of parse_ini, ie, process sections
    public static function betterParseIni(string $file) : array
    {
        $ini = file($file, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

        $container = null;
        $out = array();
        foreach ($ini as $line)
        {
            if (substr(trim($line), 0, 1) === '[' && substr(trim($line), -1, 1) === ']')
            {
                $container = trim(substr($line, 1, -1));
                continue;
            }
            elseif (substr(trim($line), 0, 1) !== ';' && substr(trim($line), 0, 2) !== '//')
            {
                list($name, $data) = explode('=', $line, 2);
                $name = trim($name);
                $data = trim($data);
                $comment = null;
                if (strpos($data, '//') != 0)
                {
                    list($value, $comment) = explode('//', $data, 2);
                }
                else
                {
                    $value = trim($data);
                }

                // Remove any semicolons from the end of the value.
                if (substr(trim($value), -1, 1) === ';')
                {
                    $value = substr(trim($value), 0, -1);
                }

                // Remove Quote Tags from the start and end.
                if (substr(trim($value), 0, 1) === '\'' || substr(trim($value), 0, 1) === '"')
                {
                    $value = substr(trim($value), 1);
                }

                if (substr(trim($value), -1, 1) === '\'' || substr(trim($value), -1, 1) === '"')
                {
                    $value = substr(trim($value), 0, -1);
                }

                $value = trim($value);
                if ($comment !== null)
                {
                    $comment = trim($comment);
                }

                // Check for Numeric types (int/long, double/float)
                if (is_numeric($value))
                {
                    $value += 0;
                }
                elseif (strtolower($value) === 'true' || strtolower($value) === 'false')
                {
                    $value = (strtolower($value) == 'true' ? true : false);
                    settype($value, 'bool');
                }
                elseif (is_string($value))
                {
                    if (strlen(trim($value)) == 0)
                    {
                        $value = null;
                    }
                }

                if ($container !== null)
                {
                    $out[$container][$name] = array('value' => $value,
                                                    'type' => gettype($value),
                                                    'comment' => $comment);
                }
            }
        }

        return $out;
    }
}
