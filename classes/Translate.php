<?php declare(strict_types = 1);
/**
 * classes/Translate.php from The Kabal Invasion.
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

use PDO;

class Translate
{
    protected static array $langvars = array();

    public static function load(\PDO $pdo_db, ?string $language = null, ?array $categories = null): array
    {
        // Check if all supplied args are valid, if not return an empty array.
        if (($language === null) || !is_array($categories))
        {
            return self::$langvars;
        }

        if (!Db::isActive($pdo_db))
        {
            // Slurp in language variables from the ini file directly
            $ini_file = './languages/' . $language . '.ini';
            $ini_keys = parse_ini_file($ini_file, true);
            if (is_array($ini_keys))
            {
                foreach ($ini_keys as $config_line)
                {
                    foreach ($config_line as $config_key => $config_value)
                    {
                        self::$langvars[$config_key] = $config_value;
                    }
                }
            }

            return self::$langvars;
        }
        else
        {
            // Populate the $langvars array
            foreach ($categories as $category)
            {
                // Select from the DB and return the value of the language variables requested, but do not use caching
                $query = "SELECT name, value FROM ::prefix::languages WHERE " .
                         "category = :category AND section = :language;";
                $result = $pdo_db->prepare($query);
                Db::logDbErrors($pdo_db, $query, __LINE__, __FILE__);

                // We could use a single prepare, and multiple executes, but it makes this section much less clear.
                $result->bindParam(':category', $category, \PDO::PARAM_STR);
                $result->bindParam(':language', $language, \PDO::PARAM_STR);
                $result->execute();
                Db::logDbErrors($pdo_db, $query, __LINE__, __FILE__);

                $lang_entries = $result->fetchAll();
                if (is_array($lang_entries))
                {
                    foreach ($lang_entries as $row)
                    {
                        self::$langvars[$row['name']] = $row['value'];
                    }
                }
            }

            return self::$langvars;
        }
    }
}
