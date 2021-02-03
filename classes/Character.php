<?php declare(strict_types = 1);
/**
 * classes/Character.php from The Kabal Invasion.
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

class Character
{
    public function kill(\PDO $pdo_db, string $lang, int $ship_id, Registry $tkireg): void
    {
        $langvars = Translate::load($pdo_db, $lang, array('news'));
        $sql = "UPDATE ::prefix::ships SET ship_destroyed = 'Y', " .
               "on_planet = 'N', sector = 1, cleared_defenses = ' ' WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
        $stmt->execute();

        $sql = "DELETE FROM ::prefix::bounty WHERE placed_by = :placed_by";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':placed_by', $ship_id, \PDO::PARAM_INT);
        $stmt->execute();

        $sql = "UPDATE ::prefix::planets SET owner = 0, team = 0, fighters = 0, base = 'N' WHERE owner = :owner";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':owner', $ship_id, \PDO::PARAM_INT);
        $stmt->execute();

        $sql = "SELECT DISTINCT sector_id FROM ::prefix::planets WHERE owner = :owner AND base = 'Y'";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':owner', $ship_id, \PDO::PARAM_INT);
        $stmt->execute();
        $sectors_owned = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($sectors_owned !== false)
        {
            foreach ($sectors_owned as $tmp_sector)
            {
                Ownership::calc($pdo_db, $lang, $tmp_sector, $tkireg);
            }
        }

        $sql = "DELETE FROM ::prefix::sector_defense WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
        $stmt->execute();

        $sql = "SELECT zone_id FROM ::prefix::zones WHERE team_zone = 'N' AND owner = :owner";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':owner', $ship_id, \PDO::PARAM_INT);
        $stmt->execute();
        $zone = $stmt->fetch(\PDO::FETCH_ASSOC);

        $sql = "UPDATE ::prefix::universe SET zone_id = 1 WHERE zone_id = :zone_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':zone_id', $zone['zone_id'], \PDO::PARAM_INT);
        $stmt->execute();

        $sql = "SELECT character_name FROM ::prefix::ships WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
        $stmt->execute();
        $name = $stmt->fetch(\PDO::FETCH_ASSOC);

        $headline = $name['character_name'] . ' ' . $langvars['l_killheadline'];
        $newstext = str_replace('[name]', $name['character_name'], $langvars['l_news_killed']);

        $sql = "INSERT INTO ::prefix::news (headline, newstext, user_id, date, news_type) " .
               "VALUES (:headline, :newstext, :user_id, NOW(), 'killed')";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':headline', $headline, \PDO::PARAM_STR);
        $stmt->bindParam(':newstext', $newstext, \PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $ship_id, \PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getInsignia(\PDO $pdo_db, string $language, string $a_username): string
    {
        // Lookup players score.
        $players_gateway = new Players\PlayersGateway($pdo_db);
        $playerinfo = $players_gateway->selectPlayerInfo($a_username);

        $langvars = Translate::load($pdo_db, $language, array('insignias'));

        for ($estimated_rank = 0; $estimated_rank < 20; $estimated_rank++)
        {
            $value = pow(2, $estimated_rank * 2);
            $value *= (500 * 2);
            if ($playerinfo['score'] <= $value)
            {
                // Ok we have found our Insignia, now set and break out of the for loop.
                $temp_insignia = 'l_insignia_' . $estimated_rank;
                $player_insignia = $langvars[$temp_insignia];
                break;
            }
        }

        if (!isset($player_insignia))
        {
            // Hmm, player has out ranked out highest rank, so just return that.
            $player_insignia = $langvars['l_insignia_19'];
        }

        return (string) $player_insignia;
    }
}
