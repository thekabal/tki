<?php declare(strict_types = 1);
/**
 * classes/AdminLog.php from The Kabal Invasion.
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

// FUTURE: Recode adminlog to be smart about whether there is a db
// and if not, log to a file that will be slurped into the db when there is.

namespace Tki;

use PDO;

class AdminLog
{
    public function writeLog(\PDO $pdo_db, int $log_type, string $data): void
    {
        $query = "INSERT INTO ::prefix::logs VALUES (null, 0, :logtype, NOW(), :data)";
        $prep = $pdo_db->prepare($query);
        $prep->bindParam(':logtype', $log_type, \PDO::PARAM_STR);
        $prep->bindParam(':data', $data, \PDO::PARAM_STR);
        $prep->execute();
        Db::logDbErrors($pdo_db, $query, __LINE__, __FILE__);
    }
}
