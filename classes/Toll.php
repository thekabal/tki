<?php declare(strict_types = 1);
/**
 * classes/Toll.php from The Kabal Invasion.
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

class Toll
{
    public static function distribute(\PDO $pdo_db, int $sector, int $toll, int $total_fighters): void
    {
        $sql = "SELECT * FROM ::prefix::sector_defense WHERE sector_id=:sector_id AND defense_type='F'";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $sector, \PDO::PARAM_INT);
        $stmt->execute();
        $defense_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($defense_present !== false)
        {
            foreach ($defense_present as $tmp_defense)
            {
                $toll_amount = round(($tmp_defense['quantity'] / $total_fighters) * $toll);
                $sql = "UPDATE ::prefix::ships SET credits=credits + :toll_amount WHERE ship_id = :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':toll_amount', $toll_amount, \PDO::PARAM_INT);
                $stmt->bindParam(':ship_id', $tmp_defense['ship_id'], \PDO::PARAM_INT);
                $stmt->execute();
                PlayerLog::writeLog($pdo_db, $tmp_defense['ship_id'], LogEnums::TOLL_RECV, "$toll_amount|$sector");
            }
        }
    }
}
