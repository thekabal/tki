<?php declare(strict_types = 1);
/**
 * classes/Planets/PlanetsGateway.php from The Kabal Invasion.
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

namespace Tki\Planets; // Domain Entity organization pattern, Planets objects

class PlanetsGateway // Gateway for SQL calls related to Planets
{
    protected \PDO $pdo_db; // This will hold a protected version of the pdo_db variable

    public function __construct(\PDO $pdo_db) // Create the this->pdo_db object
    {
        $this->pdo_db = $pdo_db;
    }

    public function genesisAddPlanet(\PDO $pdo_db, \Tki\Registry $tkireg, array $playerinfo, string $planetname): void
    {
        $prod_organics = $tkireg->default_prod_organics;
        $prod_ore = $tkireg->default_prod_ore;
        $prod_goods = $tkireg->default_prod_goods;
        $prod_energy = $tkireg->default_prod_energy;
        $prod_fighters = $tkireg->default_prod_fighters;
        $prod_torp = $tkireg->default_prod_torp;

        $sql = "INSERT INTO ::prefix::planets (" .
               "planet_id, sector_id, planet_name, organics, ore, goods, " .
               "energy, colonists, credits, fighters, torps, owner, " .
               "team, base, sells, prod_organics, prod_ore, prod_goods, " .
               "prod_energy, prod_fighters, prod_torp, defeated) VALUES" .
               " (:planet_id, :sector_id, :name, :organics, :ore, " .
               ":goods, :energy, :colonists, :credits, :fighters, " .
               ":torps, :owner, :team, :base, :sells, :prod_organics, " .
               ":prod_ore, :prod_goods, :prod_energy, :prod_fighters, " .
               ":prod_torp, :defeated)";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindValue(':planet_id', null, \PDO::PARAM_NULL);
        $stmt->bindParam(':sector_id', $playerinfo['sector'], \PDO::PARAM_INT);
        $stmt->bindParam(':name', $planetname, \PDO::PARAM_STR);
        $stmt->bindValue(':organics', 0, \PDO::PARAM_INT);
        $stmt->bindValue(':ore', 0, \PDO::PARAM_INT);
        $stmt->bindValue(':goods', 0, \PDO::PARAM_INT);
        $stmt->bindValue(':energy', 0, \PDO::PARAM_INT);
        $stmt->bindValue(':colonists', 0, \PDO::PARAM_INT);
        $stmt->bindValue(':credits', 0, \PDO::PARAM_INT);
        $stmt->bindValue(':fighters', 0, \PDO::PARAM_INT);
        $stmt->bindValue(':torps', 0, \PDO::PARAM_INT);
        $stmt->bindParam(':owner', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->bindValue(':team', 0, \PDO::PARAM_INT);
        $stmt->bindValue(':base', 'N', \PDO::PARAM_STR);
        $stmt->bindValue(':sells', 'N', \PDO::PARAM_STR);
        $stmt->bindParam(':organics', $prod_organics, \PDO::PARAM_STR);
        $stmt->bindParam(':ore', $prod_ore, \PDO::PARAM_STR);
        $stmt->bindParam(':goods', $prod_goods, \PDO::PARAM_STR);
        $stmt->bindParam(':energy', $prod_energy, \PDO::PARAM_STR);
        $stmt->bindParam(':fighters', $prod_fighters, \PDO::PARAM_STR);
        $stmt->bindParam(':torp', $prod_torp, \PDO::PARAM_STR);
        $stmt->bindValue(':defeated', 'N', \PDO::PARAM_STR);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        $sql = "UPDATE ::prefix::ships SET turns_used = turns_used + 1, turns = turns - 1, dev_genesis = dev_genesis - 1 WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $result = $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    }

    public function selectPlanetInfo(int $sector_id): ?array
    {
        $sql = "SELECT * FROM ::prefix::planets WHERE sector_id = :sector_id";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $sector_id, \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__); // Log any errors, if there are any

        // A little magic here. If it couldn't select a planet in the sector, the following call will return false - which is what we want for "no planet found".
        $planetinfo = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $planetinfo; // FUTURE: Eventually we want this to return a planet object instead, for now, planetinfo array or false for no planet found.
    }

    public function selectAllPlanetInfo(int $sector_id): ?array
    {
        $sql = "SELECT * FROM ::prefix::planets WHERE sector_id = :sector_id";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $sector_id, \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__); // Log any errors, if there are any
        // A little magic here. If it couldn't select a planet in the sector, the following call will return false - which is what we want for "no planet found".
        $planetinfo = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($planetinfo !== false)
        {
            return $planetinfo; // FUTURE: Eventually we want this to return a planet object instead, for now, planetinfo array or false for no planet found.
        }
        else
        {
            return null;
        }
    }

    public function selectPlanetInfoByPlanet(int $planet_id): ?array
    {
        $sql = "SELECT * FROM ::prefix::planets WHERE planet_id = :planet_id";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__); // Log any errors, if there are any

        // A little magic here. If it couldn't select a planet in the sector, the following call will return false - which is what we want for "no planet found".
        $planetinfo = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $planetinfo; // FUTURE: Eventually we want this to return a planet object instead, for now, planetinfo array or false for no planet found.
    }

    public function selectAllPlanetInfoByOwner(int $ship_id): ?array
    {
        $sql = "SELECT * FROM ::prefix::planets WHERE owner = :owner";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':owner', $ship_id, \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__); // Log any errors, if there are any
        // A little magic here. If it couldn't select a planet in the sector, the following call will return false - which is what we want for "no planet found".
        $planetinfo = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($planetinfo !== false)
        {
            return $planetinfo; // FUTURE: Eventually we want this to return a planet object instead, for now, planetinfo array or false for no planet found.
        }
        else
        {
            return null;
        }
    }
}
