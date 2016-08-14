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
// File: classes/Reg.php

namespace Tki;

class Reg
{
    protected $vars = array();

    public function __construct(\PDO $pdo_db)
    {
        // Get the config_values from the DB - This is a pdo operation
        $stmt = "SELECT name,value,type FROM ::prefix::gameconfig";
        $result = $pdo_db->query($stmt);
        Db::logDbErrors($pdo_db, $stmt, __LINE__, __FILE__);

        if ($result !== false) // If the database is not live, this will give false, and db calls will fail silently
        {
            $big_array = $result->fetchAll();
            Db::logDbErrors($pdo_db, 'fetchAll from gameconfig', __LINE__, __FILE__);
            if (!empty($big_array))
            {
                foreach ($big_array as $row)
                {
                    settype($row['value'], $row['type']);
                    $this->vars[$row['name']] = $row['value'];
                }
            }
            else
            {
                // Slurp in config variables from the ini file directly
                $ini_file = 'config/classic_config.ini'; // This is hard-coded for now, but when we get multiple game support, we may need to change this.
                $ini_keys = parse_ini_file($ini_file, true);
                foreach ($ini_keys as $config_category => $config_line)
                {
                    foreach ($config_line as $config_key => $config_value)
                    {
                        $this->$config_key = $config_value;
                    }
                }
            }
        }
        else
        {
            // Slurp in config variables from the ini file directly
            $ini_file = 'config/classic_config.ini'; // This is hard-coded for now, but when we get multiple game support, we may need to change this.
            $ini_keys = parse_ini_file($ini_file, true);
            foreach ($ini_keys as $config_category => $config_line)
            {
                foreach ($config_line as $config_key => $config_value)
                {
                    $this->$config_key = $config_value;
                }
            }
        }
    }

    public function __set($key, $value)
    {
        $this->vars[$key] = $value;
    }

    public function &__get($key)
    {
        if (array_key_exists($key, $this->vars))
        {
            return $this->vars[$key];
        }
        else
        {
            return null;
        }
    }
}
