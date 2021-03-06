<?php declare(strict_types = 1);
/**
 * classes/Schema.php from The Kabal Invasion.
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

namespace Tki;

class Schema
{
    private const PDO_SUCCESS = '00000'; // PDO gives an error code of string 00000 if successful. Not extremely helpful.

    public function dropTables(\PDO $pdo_db, string $db_prefix, string $dbtype): array
    {
        $counter = 0;
        $destroy_results = array();

        $schema_files = new \DirectoryIterator('schema/' . $dbtype);
        foreach ($schema_files as $schema_filename)
        {
            $table_timer = new Timer();
            $table_timer->start(); // Start benchmarking

            if ($schema_filename->isFile() && $schema_filename->getExtension() == 'sql')
            {
                // Since we are using strict types, the Directory Iterator returns an object, and we want a string to pass to substr.
                $simple_filename = $schema_filename->getFilename();

                // Routine to handle persistent database tables. If a SQL schema file starts with persist-, then it is a persistent table. Fix the name.
                $persist_file = (substr($simple_filename, 0, 8) === 'persist-');
                if ($persist_file)
                {
                    $tablename = substr($simple_filename, 8, -4);
                }
                else
                {
                    $tablename = substr($simple_filename, 0, -4);
                }

                if (!$persist_file)
                {
                    $drop_res = $pdo_db->exec('DROP TABLE ' . $db_prefix . $tablename);
                    // Db::logDbErrors($pdo_db, $drop_res, __LINE__, __FILE__); // Triggers errors because there is no DB

                    if ($drop_res === 0)
                    {
                        $destroy_results[$counter]['result'] = true;
                    }
                    else
                    {
                        $errorinfo = $pdo_db->errorInfo();
                        $destroy_results[$counter]['result'] = $errorinfo[1] . ': ' . $errorinfo[2];
                    }
                }
                else
                {
                    $destroy_results[$counter]['result'] = 'Skipped - Persistent table';
                }

                $destroy_results[$counter]['name'] = $db_prefix . $tablename;
                $table_timer->stop();
                $destroy_results[$counter]['time'] = $table_timer->elapsed();
                $counter++;
            }
        }

        return $destroy_results;
    }

    public function dropSequences(\PDO $pdo_db, string $db_prefix, string $dbtype): ?array
    {
        $counter = 0;
        $destroy_results = array();

        if ($dbtype == 'postgres9')
        {
            $seq_files = new \DirectoryIterator('schema/' . $dbtype . '/seq/');
            foreach ($seq_files as $seq_filename)
            {
                $table_timer = new Timer();
                $table_timer->start(); // Start benchmarking

                if ($seq_filename->isFile() && $seq_filename->getExtension() == 'sql')
                {
                    $seqname = substr((string) $seq_filename, 0, -4);
                    $drop_res = $pdo_db->exec('DROP SEQUENCE ' . $db_prefix . $seqname);
                    // Db::logDbErrors($pdo_db, $drop_res, __LINE__, __FILE__); // Triggers errors because there is no DB

                    if ($drop_res === 0)
                    {
                        $destroy_results[$counter]['result'] = true;
                    }
                    else
                    {
                         $errorinfo = $pdo_db->errorInfo();
                         $destroy_results[$counter]['result'] = $errorinfo[1] . ': ' . $errorinfo[2];
                    }

                    $destroy_results[$counter]['name'] = $db_prefix . $seqname;
                    $table_timer->stop();
                    $destroy_results[$counter]['time'] = $table_timer->elapsed();
                    $counter++;
                }
            }

            return $destroy_results;
        }
        else
        {
            return null;
        }
    }

    public function createSequences(\PDO $pdo_db, string $db_prefix, string $dbtype): ?array
    {
        if ($dbtype == 'postgres9')
        {
            $create_table_results = array();
            $counter = 0;

            $seq_files = new \DirectoryIterator('schema/' . $dbtype . '/seq/');
            foreach ($seq_files as $seq_filename)
            {
                $table_timer = new Timer();
                $table_timer->start(); // Start benchmarking

                if ($seq_filename->isFile() && $seq_filename->getExtension() == 'sql')
                {
                    $seqname = substr((string) $seq_filename, 0, -4);
                    $drop_res = $pdo_db->exec('CREATE SEQUENCE ' . $db_prefix . $seqname);
                    // Db::logDbErrors($pdo_db, $drop_res, __LINE__, __FILE__); // Triggers errors because there is no DB

                    if ($drop_res === 0)
                    {
                        $create_table_results[$counter]['result'] = true;
                    }
                    else
                    {
                        $errorinfo = $pdo_db->errorInfo();
                        $create_table_results[$counter]['result'] = $errorinfo[1] . ': ' . $errorinfo[2];
                    }

                    $create_table_results[$counter]['name'] = $db_prefix . $seqname;
                    $table_timer->stop();
                    $create_table_results[$counter]['time'] = $table_timer->elapsed();
                    $counter++;
                }
            }

            return $create_table_results;
        }
        else
        {
            return null;
        }
    }

    public function createTables(\PDO $pdo_db, string $db_prefix, string $dbtype): array
    {
        $create_table_results = array();
        $counter = 0;

        $schema_files = new \DirectoryIterator('schema/' . $dbtype);
        foreach ($schema_files as $schema_filename)
        {
            $table_timer = new Timer();
            $table_timer->start(); // Start benchmarking

            if ($schema_filename->isFile() && $schema_filename->getExtension() == 'sql')
            {
                // Since we are using strict types, the Directory Iterator returns an object, and we want a string to pass to substr.
                $simple_filename = $schema_filename->getFilename();

                // Routine to handle persistent database tables. If a SQL schema file starts with persist-, then it is a persistent table
                $persist_file = (substr($simple_filename, 0, 8) === 'persist-');
                if ($persist_file)
                {
                    $tablename = substr($simple_filename, 8, -4);
                }
                else
                {
                    $tablename = substr($simple_filename, 0, -4);
                }

                // Slurp the SQL call from schema, and turn it into an SQL string
                $sql_query = file_get_contents('schema/' . $dbtype . '/' . $schema_filename);

                // Replace the default prefix (tki_) with the chosen table prefix from the game
                $sql_query = preg_replace('/tki_/', $db_prefix, (string) $sql_query);

                // Remove comments from SQL
                $RXSQLComments = '@(--[^\r\n]*)|(\#[^\r\n]*)|(/\*[\w\W]*?(?=\*/)\*/)@ms';
                $sql_query = (($sql_query == '') ? '' : preg_replace($RXSQLComments, '', (string) $sql_query));

                if ($sql_query === null)
                {
                        $create_table_results[$counter]['result'] = 'Please report this bug to the developers. No SQL remaining in create tables.';
                }
                else
                {
                    $sth = $pdo_db->prepare($sql_query);
                    $sth->execute();

                    if ($pdo_db->errorCode() !== self::PDO_SUCCESS)
                    {
                        $errorinfo = $pdo_db->errorInfo();
                        $create_table_results[$counter]['result'] = $errorinfo[1] . ': ' . $errorinfo[2];
                    }
                    else
                    {
                        $create_table_results[$counter]['result'] = true;
                    }
                }

                // Db::logDbErrors($pdo_db, $execute_res, __LINE__, __FILE__); // Triggers errors because there is no DB
                $create_table_results[$counter]['name'] = $db_prefix . $tablename;
                $table_timer->stop();
                $create_table_results[$counter]['time'] = $table_timer->elapsed();
                $counter++;
            }
        }

        return $create_table_results;
    }
}
