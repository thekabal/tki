<?php declare(strict_types = 1);
/**
 * classes/Ships/ShipsGateway.php from The Kabal Invasion.
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

namespace Tki\Ships; // Domain Entity organization pattern, Ships objects

class ShipsGateway // Gateway for SQL calls related to Ships
{
    protected \PDO $pdo_db; // This will hold a protected version of the pdo_db variable

    public function __construct(\PDO $pdo_db) // Create the this->pdo_db object
    {
        $this->pdo_db = $pdo_db;
    }

    public function updateDestroyedShip(int $ship_id, int $rating = 0): void
    {
        $sql = "UPDATE ::prefix::ships SET hull=0," .
               "engines=0, power=0, computer=0, sensors=0," .
               "beams=0, torp_launchers=0, torps=0, armor=0," .
               "armor_pts=100, cloak=0, shields=0, sector=1," .
               "rating=0, cleared_defense=' ', " .
               "ship_ore=0, ship_organics=0, ship_energy=1000," .
               "ship_colonists=0, ship_goods=0," .
               "ship_fighters=100, ship_damage=0, credits=1000," .
               "on_planet='N', dev_warpedit=0, dev_genesis=0," .
               "dev_beacon=0, dev_emerwarp=0, dev_escapepod='N'," .
               "dev_fuelscoop='N', dev_minedeflector=0," .
               "ship_destroyed='N', dev_lssd='N' " .
               "WHERE ship_id = :ship_id";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
        $stmt->bindParam(':rating', $rating, \PDO::PARAM_INT);
        $result = $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__);
    }
}
