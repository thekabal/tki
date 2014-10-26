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
// File: classes/File.php
//
// This class handles direct file functions for TKI. Included is iniToDb, a function
// for importing values from an INI file into the database.

namespace Tki;

class File
{
    public static function iniToDb($db, $ini_file, $ini_table, $section, Reg $tkireg)
    {
        // This is a loop, that reads a ini file, of the type variable = value.
        // It will loop thru the list of the ini variables, and push them into the db.

        $ini_keys = File::betterParseIni($ini_file);
        // We need a way to deal with errors in parse_ini_file here #fixit #todo

        $status_array = array();
        $j = 0;
        $start_tran_res = $db->beginTransaction(); // We enclose the inserts in a transaction as it is roughly 30 times faster
        Db::logDbErrors($db, $start_tran_res, __LINE__, __FILE__);

        $insert_sql = 'INSERT into ' . $db->prefix. $ini_table . ' (name, category, value, section, type) VALUES (:config_key, :config_category, :config_value, :section, :type)';
        $stmt = $db->prepare($insert_sql);

        foreach($ini_keys as $config_category => $config_line)
        {
            foreach($config_line as $config_key => $type_n_value)
            {
                $j++;
                if (mb_strpos($ini_file, '_config') !== false)
                {
                    // Import all the variables into the registry
                    settype($type_n_value['value'], $type_n_value['type']);
                    $tkireg->$config_key = $type_n_value['value'];
                }

                $stmt->bindParam(':config_key', $config_key);
                $stmt->bindParam(':config_category', $config_category);
                $stmt->bindParam(':config_value', $type_n_value['value']);
                $stmt->bindParam(':section', $section);
                $stmt->bindParam(':type', $type_n_value['type']);
                $result = $stmt->execute();
                $status_array[$j] = Db::logDbErrors($db, $result, __LINE__, __FILE__);
            }
        }

        for($k = 1; $k < $j; $k++)
        {
            // Status Array will continue the results of individual executes. It should be === true unless something went horribly wrong.
            if ($status_array[$k] !== true)
            {
                $final_result = false;
            }
            else
            {
                $final_result = true;
            }
        }

        unset ($ini_keys);
        if ($final_result !== true) // If the final result is not true, rollback our transaction, and return false.
        {
            $db->rollBack();
            Db::logDbErrors($db, 'Rollback transaction on File::initodb', __LINE__, __FILE__);

            return false;
        }
        else // Else we process the transaction, and return true
        {
            $db->commit(); // Complete the transaction
            Db::logDbErrors($db, 'Complete transaction on File::initodb', __LINE__, __FILE__);

            return true;
        }
    }

    // Very close to a drop-in replacement for parse_ini_file, although without the second parameter
    // This defaults to the equivalent of "true" for the second param of parse_ini, ie, process sections
    public static function betterParseIni($file)
    {
        $ini = file($file, FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);

        $container = null;
        foreach($ini as $line)
        {
            if (mb_substr(trim($line), 0, 1) === '[' && mb_substr(trim($line), -1, 1) === ']')
            {
                $container = trim(mb_substr($line, 1, -1));
                continue;
            }
            elseif (mb_substr(trim($line), 0, 1) !== ';' && mb_substr(trim($line), 0, 2) !== '//')
            {
                list($name, $data) = explode('=', $line, 2);
                $name = trim($name);
                $data = trim($data);
                $comment = null;
                if (mb_strpos($data, '//') != 0)
                {
                    list($value, $comment) = explode('//', $data, 2);
                }
                else
                {
                    $value = trim($data);
                }

                // Remove any semicolons from the end of the value.
                if (mb_substr(trim($value), -1, 1) === ';')
                {
                    $value = mb_substr(trim($value), 0, -1);
                }

                // Remove Quote Tags from the start and end.
                if (mb_substr(trim($value), 0, 1) === '\'' || mb_substr(trim($value), 0, 1) === '"')
                {
                    $value = mb_substr(trim($value), 1);
                }
                if (mb_substr(trim($value), -1, 1) === '\'' || mb_substr(trim($value), -1, 1) === '"')
                {
                    $value = mb_substr(trim($value), 0, -1);
                }

                $value = trim($value);
                $comment = trim($comment);

                // Check for Numeric types (int/long, double/float)
                if (is_numeric($value))
                {
                    $value +=0;
                }
                elseif (mb_strtolower($value) === 'true' || mb_strtolower($value) === 'false')
                {
                    $value =(mb_strtolower($value) == 'true' ? true : false);
                    settype($value, 'bool');
                }
                elseif (is_string($value))
                {
                    if (mb_strlen(trim($value)) == 0)
                    {
                        $value = null;
                    }
                }
                if (!is_null($container))
                {
                    $out[$container][$name] = array('value' => $value, 'type' => gettype($value), 'comment' => $comment);
                }
            }
        }
        return $out;
    }
}

