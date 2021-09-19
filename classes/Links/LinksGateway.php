<?php declare(strict_types = 1);
/**
 * classes/Links/LinksGateway.php from The Kabal Invasion.
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

namespace Tki\Links; // Domain Entity organization pattern, Links objects

class LinksGateway // Gateway for SQL calls related to Links
{
    protected \PDO $pdo_db; // This will hold a protected version of the pdo_db variable

    public function __construct(\PDO $pdo_db) // Create the this->pdo_db object
    {
        $this->pdo_db = $pdo_db;
    }

    public function selectAllLinkInfoByLinkStart(int $sector_id): ?array
    {
        $sql = "SELECT * FROM ::prefix::links WHERE link_start = :link_start ORDER BY link_dest ASC";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':link_start', $sector_id, \PDO::PARAM_INT);
        $stmt->execute();
        $linksinfo = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($linksinfo !== false)
        {
            // A little magic here. If it couldn't select a link, the following call will return false - which is what we want for "no link found".
            return $linksinfo; // FUTURE: Eventually we want this to return a link object instead, for now, linkinfo array or false for no link found.
        }
        else
        {
            return null;
        }
    }

    public function selectLinkId(int $src, int $dest): ?array
    {
        $sql = "SELECT link_id FROM ::prefix::links WHERE link_start = :link_start AND link_dest = :link_dest";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':link_start', $src, \PDO::PARAM_INT);
        $stmt->bindParam(':link_dest', $dest, \PDO::PARAM_INT);
        $stmt->execute();
        $linksinfo = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($linksinfo !== false)
        {
            // A little magic here. If it couldn't select a link, the following call will return false - which is what we want for "no link found".
            return $linksinfo; // FUTURE: Eventually we want this to return a link object instead, for now, linkinfo array or false for no link found.
        }
        else
        {
            return null;
        }
    }
}
