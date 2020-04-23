<?php declare(strict_types = 1);
/**
 * classes/Bounty.php from The Kabal Invasion.
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

class Bounty
{
    public function cancel(\PDO $pdo_db, int $bounty_on): void
    {
        // $sql = "SELECT * FROM ::prefix::bounty WHERE bounty_on=:bounty_on AND bounty_on=ship_id";
        $sql = "SELECT * FROM ::prefix::bounty WHERE bounty_on=:bounty_on";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':bounty_on', $bounty_on, \PDO::PARAM_INT);
        $stmt->execute();
        $bounty_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($bounty_present !== false)
        {
            foreach ($bounty_present as $tmp_bounty)
            {
                if ($tmp_bounty['placed_by'] != 0)
                {
                    $sql = "UPDATE ::prefix::ships SET credits=credits+:bounty_amount WHERE ship_id = :ship_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':bounty_amount', $tmp_bounty['amount'], \PDO::PARAM_INT);
                    $stmt->bindParam(':ship_id', $tmp_bounty['placed_by'], \PDO::PARAM_INT);
                    $stmt->execute();
                    PlayerLog::writeLog($pdo_db, $tmp_bounty['placed_by'], LogEnums::BOUNTY_CANCELLED, "$tmp_bounty[amount]|$tmp_bounty[character_name]");
                }

                $sql = "DELETE FROM ::prefix::bounty WHERE bounty_id = :bounty_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':bounty_id', $tmp_bounty['bounty_id'], \PDO::PARAM_INT);
                $stmt->execute();
            }
        }
    }

    public static function collect(\PDO $pdo_db, array $langvars, int $attacker, int $bounty_on): void
    {
        $sql = "SELECT * FROM ::prefix::bounty,::prefix::ships WHERE " .
               "bounty_on=:bounty_on AND bounty_on=ship_id AND planced_by <> 0";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':bounty_on', $bounty_on, \PDO::PARAM_INT);
        $stmt->execute();
        $bounty_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($bounty_present !== false)
        {
            foreach ($bounty_present as $tmp_bounty)
            {
                if ($tmp_bounty['placed_by'] == 0)
                {
                    $placed = $langvars['l_by_thefeds'];
                }
                else
                {
                    $players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
                    $tmp_return = $players_gateway->selectPlayerInfoById($tmp_bounty['placed_by']);
                    $placed = $tmp_return['character_name'];
                }

                $sql = "UPDATE ::prefix::ships SET credits=credits+:bounty_amount WHERE ship_id = :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':bounty_amount', $tmp_bounty['amount'], \PDO::PARAM_INT);
                $stmt->bindParam(':ship_id', $attacker, \PDO::PARAM_INT);
                $stmt->execute();

                $sql = "DELETE FROM ::prefix::bounty WHERE bounty_id = :bounty_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':bounty_id', $tmp_bounty['bounty_id'], \PDO::PARAM_INT);
                $stmt->execute();

                PlayerLog::writeLog($pdo_db, $attacker, LogEnums::BOUNTY_CLAIMED, "$tmp_bounty[amount]|$tmp_bounty[character_name]|$placed");
                PlayerLog::writeLog($pdo_db, $tmp_bounty['placed_by'], LogEnums::BOUNTY_PAID, "$tmp_bounty[amount]|$tmp_bounty[character_name]");
            }
        }

        $sql = "DELETE FROM ::prefix::bounty WHERE bounty_on = :bounty_on";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':bounty_on', $bounty_on, \PDO::PARAM_INT);
        $stmt->execute();
    }
}
