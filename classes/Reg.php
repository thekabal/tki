<?php declare(strict_types = 1);
/**
 * classes/Reg.php from The Kabal Invasion.
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

class Reg
{
    protected static array $store = array();

    public function __construct(\PDO $pdo_db)
    {
        if ($this->loadFromDb($pdo_db) === false)
        {
            $this->loadFromIni();
        }
    }

    public function __get(string $key): mixed
    {
        if (array_key_exists($key, self::$store))
        {
            return self::$store[$key];
        }

        // When the key *does not* exist, return "null".
        return null;
    }

    public function __set(string $key, mixed $value): void
    {
        self::$store[$key] = $value;
    }

    public function loadFromIni(): void
    {
        // Slurp in config variables from the ini file directly
        // This is hard-coded for now, but when we get multiple game support, we may need to change this.
        $ini_keys = parse_ini_file('config/classic_config.ini', true, INI_SCANNER_TYPED);
        if (is_array($ini_keys))
        {
            foreach ($ini_keys as $config_line)
            {
                foreach ($config_line as $key => $value)
                {
                    self::$store[$key] = $value;
                }
            }
        }
    }

    public function loadFromDb(\PDO $pdo_db): ?bool
    {
        // Get the config_values from the DB - This is a pdo operation
        $stmt = "SELECT name,value,type FROM ::prefix::gameconfig";
        $result = $pdo_db->query($stmt);
        Db::logDbErrors($pdo_db, $stmt, __LINE__, __FILE__);

        if ($result !== false) // Result is "false" during no-db status (fresh install or CU after step4/stop)
        {
            $db_keys = $result->fetchAll();
            Db::logDbErrors($pdo_db, 'fetchAll from gameconfig', __LINE__, __FILE__);

            if (!empty($db_keys))
            {
                foreach ($db_keys as $config_line)
                {
                    settype($config_line['value'], $config_line['type']);
                    self::$store[$config_line['name']] = $config_line['value'];
                }

                return null;
            }
        }

        return false;
    }
}
