<?php
declare(strict_types = 1);
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
// PDO extended to enable prefixes on existing tables - replaces ::prefix:: with table prefix
//
// File: classes/TkiPDO.php

namespace Tki;

class TkiPDO extends \PDO
{
    protected $table_prefix;

    public function __construct(string $dsn, string $user = null, string $password = null, string $prefix = null, array $driver_options = array())
    {
        $this->table_prefix = $prefix;
        parent::__construct($dsn, $user, $password, $driver_options);
    }

    public function exec($statement)
    {
        $statement = $this->tablePrefix($statement);
        $replaced_statement = parent::exec($statement);
        return $replaced_statement;
    }

    public function prepare($statement, $drive_options = array())
    {
        $statement = $this->tablePrefix($statement);
        $replaced_statement = parent::prepare($statement, $driver_options);
        return $replaced_statement;
    }

    public function query(string $statement)
    {
        $statement = $this->tablePrefix($statement);
        $args = func_get_args();
        if (count($args) > 1)
        {
            $replaced_statement = call_user_func_array(array($this, 'parent::query'), $args);
            return $replaced_statement;
        }
        else
        {
            $replaced_statement = parent::query($statement);
            return $replaced_statement;
        }
    }

    protected function tablePrefix(string $statement): string
    {
        $statement_with_prefix = str_replace('::prefix::', $this->table_prefix, $statement);
        return (string) $statement_with_prefix;
    }
}
