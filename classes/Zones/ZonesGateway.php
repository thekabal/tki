<?php declare(strict_types = 1);
/**
 * classes/Zones/ZonesGateway.php from The Kabal Invasion.
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

namespace Tki\Zones; // Domain Entity organization pattern, zones objects

class ZonesGateway // Gateway for SQL calls related to Zones
{
    protected \PDO $pdo_db; // This will hold a protected version of the pdo_db variable

    public function __construct(\PDO $pdo_db) // Create the this->pdo_db object
    {
        $this->pdo_db = $pdo_db;
    }

    public function selectZoneInfo(int $sector_id): ?array
    {
        $zoneinfo = array();
        $sql = "SELECT * FROM ::prefix::zones WHERE sector_id = :sector_id";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $sector_id, \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__); // Log any errors, if there are any

        // A little magic here. If it couldn't select a zone in the sector, the following call will return false - which is what we want for "no zone found".
        $zoneinfo = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $zoneinfo; // FUTURE: Eventually we want this to return a zone object instead, for now, zoneinfo array or null array for no zone found.
    }

    public function selectZoneInfoByZone(int $zone): ?array
    {
        $sql = "SELECT * FROM ::prefix::zones WHERE zone_id = :zone_id LIMIT 1";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':zone_id', $zone, \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__); // Log any errors, if there are any

        // A little magic here. If it couldn't select a zone in the sector, the following call will return false - which is what we want for "no zone found".
        $zoneinfo = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $zoneinfo; // FUTURE: Eventually we want this to return a zone object instead, for now, zoneinfo array or false for no zone found.
    }

    public function selectMatchingZoneInfo(int $sector_id): ?array
    {
        $sql = "SELECT * FROM ::prefix::zones, ::prefix::universe WHERE ::prefix::universe.sector_id = :sector_id AND ::prefix::zones.zone_id = ::prefix::universe.zone_id";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':zone_id', $sector_id, \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__); // Log any errors, if there are any

        // A little magic here. If it couldn't select a zone in the sector, the following call will return false - which is what we want for "no zone found".
        $zoneinfo = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $zoneinfo; // FUTURE: Eventually we want this to return a zone object instead, for now, zoneinfo array or false for no zone found.
    }
}
