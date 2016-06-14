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
// File: classes/Schema.php

namespace Tki;

class Schema
{
    public static function dropTables(\PDO $pdo_db, $db_prefix, $dbtype)
    {
        // Need to set this or all hell breaks loose.
        $pdo_db->inactive = true;

        $i = 0;
        $destroy_table_results = array();

        $schema_files = new \DirectoryIterator('schema/' . $dbtype);
        foreach ($schema_files as $schema_filename)
        {
            $table_timer = new Timer;
            $table_timer->start(); // Start benchmarking

            if ($schema_filename->isFile() && $schema_filename->getExtension() == 'sql')
            {
                // Routine to handle persistent database tables. If a SQL schema file starts with persist-, then it is a persistent table. Fix the name.
                $persist_file = (mb_substr($schema_filename, 0, 8) === 'persist-');
                if ($persist_file)
                {
                    $tablename = mb_substr($schema_filename, 8, -4);
                }
                else
                {
                    $tablename = mb_substr($schema_filename, 0, -4);
                }

                if (!$persist_file)
                {
                    $drop_res = $pdo_db->exec('DROP TABLE ' . $db_prefix . $tablename);
//                    Db::logDbErrors($pdo_db, $pdo_db, $drop_res, __LINE__, __FILE__); // Triggers errors because there is no DB

                    if ($drop_res !== false)
                    {
                        $destroy_table_results[$i]['result'] = true;
                    }
                    else
                    {
                        $errorinfo = $pdo_db->errorInfo();
                        $destroy_table_results[$i]['result'] = $errorinfo[1] . ': ' . $errorinfo[2];
                    }
                }
                else
                {
                    $destroy_table_results[$i]['result'] = 'Skipped - Persistent table';
                }

                $destroy_table_results[$i]['name'] = $db_prefix . $tablename;
                $table_timer->stop();
                $destroy_table_results[$i]['time'] = $table_timer->elapsed();
                $i++;
            }
        }

        return $destroy_table_results;
    }

    public static function dropSequences(\PDO $pdo_db, $db_prefix, $dbtype)
    {
        // Need to set this or all hell breaks loose.
        $pdo_db->inactive = true;

        $i = 0;
        $destroy_sequence_results = array();

        if ($dbtype == 'postgres9')
        {
            $seq_files = new \DirectoryIterator('schema/' . $dbtype . '/seq/');
            foreach ($seq_files as $seq_filename)
            {
                $table_timer = new Timer;
                $table_timer->start(); // Start benchmarking

                if ($seq_filename->isFile() && $seq_filename->getExtension() == 'sql')
                {
                    $seqname = mb_substr($seq_filename, 0, -4);
                    $drop_res = $pdo_db->exec('DROP SEQUENCE ' . $db_prefix . $seqname);
//                  Db::logDbErrors($pdo_db, $pdo_db, $drop_res, __LINE__, __FILE__); // Triggers errors because there is no DB

                    if ($drop_res !== false)
                    {
                        $destroy_sequence_results[$i]['result'] = true;
                    }
                    else
                    {
                         $errorinfo = $pdo_db->errorInfo();
                         $destroy_sequence_results[$i]['result'] = $errorinfo[1] . ': ' . $errorinfo[2];
                     }

                    $destroy_sequence_results[$i]['name'] = $db_prefix . $seqname;
                    $table_timer->stop();
                    $destroy_sequence_results[$i]['time'] = $table_timer->elapsed();
                    $i++;
                }
            }

            return $destroy_sequence_results;
        }
        else
        {
            return null;
        }
    }

    public static function createSequences(\PDO $pdo_db, $db_prefix, $dbtype)
    {
        if ($dbtype == 'postgres9')
        {
            $create_table_results = array();
            $i = 0;
            define('PDO_SUCCESS', (string) '00000'); // PDO gives an error code of string 00000 if successful. Not extremely helpful.

            $seq_files = new \DirectoryIterator('schema/' . $dbtype . '/seq/');
            foreach ($seq_files as $seq_filename)
            {
                $table_timer = new Timer;
                $table_timer->start(); // Start benchmarking

                if ($seq_filename->isFile() && $seq_filename->getExtension() == 'sql')
                {
                    $seqname = mb_substr($seq_filename, 0, -4);
                    $drop_res = $pdo_db->exec('CREATE SEQUENCE ' . $db_prefix . $seqname);
//                  Db::logDbErrors($pdo_db, $pdo_db, $drop_res, __LINE__, __FILE__); // Triggers errors because there is no DB

                    if ($drop_res !== false)
                    {
                        $create_table_results[$i]['result'] = true;
                    }
                    else
                    {
                        $errorinfo = $pdo_db->errorInfo();
                        $create_table_results[$i]['result'] = $errorinfo[1] . ': ' . $errorinfo[2];
                    }

                    $create_table_results[$i]['name'] = $db_prefix . $seqname;
                    $table_timer->stop();
                    $create_table_results[$i]['time'] = $table_timer->elapsed();
                    $i++;
                }
            }

        return $create_table_results;
        }
        else
        {
            return null;
        }
    }

    public static function createTables(\PDO $pdo_db, $db_prefix, $dbtype)
    {
        $create_table_results = array();
        $i = 0;
        define('PDO_SUCCESS', (string) '00000'); // PDO gives an error code of string 00000 if successful. Not extremely helpful.

        $schema_files = new \DirectoryIterator('schema/' . $dbtype);
        foreach ($schema_files as $schema_filename)
        {
            $table_timer = new Timer;
            $table_timer->start(); // Start benchmarking

            if ($schema_filename->isFile() && $schema_filename->getExtension() == 'sql')
            {
                // Routine to handle persistent database tables. If a SQL schema file starts with persist-, then it is a persistent table
                $persist_file = (mb_substr($schema_filename, 0, 8) === 'persist-');
                if ($persist_file)
                {
                    $tablename = mb_substr($schema_filename, 8, -4);
                }
                else
                {
                    $tablename = mb_substr($schema_filename, 0, -4);
                }

                // Slurp the SQL call from schema, and turn it into an SQL string
                $sql_query = file_get_contents('schema/' . $dbtype . '/' . $schema_filename);

                // Replace the default prefix (tki_) with the chosen table prefix from the game
                $sql_query = preg_replace('/tki_/', $db_prefix, $sql_query);

                // Remove comments from SQL
                $RXSQLComments = '@(--[^\r\n]*)|(\#[^\r\n]*)|(/\*[\w\W]*?(?=\*/)\*/)@ms';
                $sql_query = (($sql_query == '') ?  '' : preg_replace($RXSQLComments, '', $sql_query));

                // FUTURE: Test handling invalid SQL to ensure it hits the error logger below AND the visible output during running
                $sth = $pdo_db->prepare($sql_query);
                $sth->execute();

                if ($pdo_db->errorCode() !== PDO_SUCCESS)
                {
                    $errorinfo = $pdo_db->errorInfo();
                    $create_table_results[$i]['result'] = $errorinfo[1] . ': ' . $errorinfo[2];
                }
                else
                {
                    $create_table_results[$i]['result'] = true;
                }

//                Db::logDbErrors($pdo_db, $pdo_db, $execute_res, __LINE__, __FILE__); // Triggers errors because there is no DB
                $create_table_results[$i]['name'] = $db_prefix . $tablename;
                $table_timer->stop();
                $create_table_results[$i]['time'] = $table_timer->elapsed();
                $i++;
            }
        }

        return $create_table_results;
    }
}
