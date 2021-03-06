<?php declare(strict_types = 1);
/**
 * classes/News/NewsGateway.php from The Kabal Invasion.
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

namespace Tki\News; // Domain Entity organization pattern, News objects

class NewsGateway // Gateway for SQL calls related to News
{
    protected \PDO $pdo_db; // This will hold a protected version of the pdo_db variable

    public function __construct(\PDO $pdo_db) // Create the this->pdo_db object
    {
        $this->pdo_db = $pdo_db;
    }

    public function selectNewsByDay(string $day): ?array
    {
        // SQL call that selects all of the news items between the start date beginning of day, and the end of day.
        $sql = "SELECT * FROM ::prefix::news WHERE date > :start AND date < :end ORDER BY news_id";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindValue(':start', $day . ' 00:00:00', \PDO::PARAM_STR);
        $stmt->bindValue(':end', $day . ' 23:59:59', \PDO::PARAM_STR);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__); // Log errors, if there are any

        $return_value = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($return_value !== false)
        {
            return $return_value;
        }
        else
        {
            return null;
        }
    }
}
