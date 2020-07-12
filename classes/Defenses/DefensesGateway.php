<?php declare(strict_types = 1);
/**
 * classes/Defenses/DefensesGateway.php from The Kabal Invasion.
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

namespace Tki\Defenses; // Domain Entity organization pattern, Defenses objects

class DefensesGateway // Gateway for SQL calls related to Defenses
{
    protected \PDO $pdo_db; // This will hold a protected version of the pdo_db variable

    public function __construct(\PDO $pdo_db) // Create the this->pdo_db object
    {
        $this->pdo_db = $pdo_db;
    }

    public function selectDefenses(int $sector_id): ?array
    {
        $sql = "SELECT * FROM ::prefix::sector_defense WHERE sector_id = :sector_id";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $sector_id, \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__); // Log any errors, if there are any
        // A little magic here. If it couldn't select a defense in the sector, the following call will return false - which is what we want for "no defenses found".
        $defense_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($defense_present !== false)
        {
            return $defense_present; // FUTURE: Eventually we want this to return a defense object instead, for now, defensesinfo array or false for no defense found.
        }
        else
        {
            return null;
        }
    }
}
