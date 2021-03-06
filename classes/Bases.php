<?php declare(strict_types = 1);
/**
 * classes/Bases.php from The Kabal Invasion.
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

class Bases
{
    public function buildBase(\PDO $pdo_db, string $lang, int $planet_id, int $sector_id, Registry $tkireg): void
    {
        $langvars = Translate::load($pdo_db, $lang, array('planet', 'planet_report', 'common'));
        echo "<br>";
        echo str_replace("[here]", "<a href='planet_report.php?preptype=1'>" .
             $langvars['l_here'] . "</a>", $langvars['l_pr_click_return_status']);
        echo "<br><br>";

        $players_gateway = new Players\PlayersGateway($pdo_db);
        $playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);
        $planets_gateway = new \Tki\Planets\PlanetsGateway($pdo_db);
        $planetinfo = $planets_gateway->selectPlanetInfoByPlanet($planet_id);

        if (!empty($planetinfo))
        {
            // Error out and return if the player isn't the owner of the planet
            // Verify player owns the planet which is to have the base created on.
            if ($planetinfo['owner'] != $playerinfo['ship_id'])
            {
                echo "<div style='color:#f00; font-size:16px;'>" . $langvars['l_pr_make_base_failed'] . "</div>\n";
                echo "<div style='color:#f00; font-size:16px;'>" . $langvars['l_pr_invalid_info'] . "</div>\n";
                return;
            }

            // Build a base
            $rs_move = new \Tki\Realspace();
            $rs_move->realSpaceMove($pdo_db, $lang, $sector_id, $tkireg);
            echo "<br>";
            echo str_replace("[here]", "<a href='planet.php?planet_id=$planet_id'>" .
                 $langvars['l_here'] . "</a>", $langvars['l_pr_click_return_planet']);
            echo "<br><br>";

            if ($planetinfo['ore'] >= $tkireg->base_ore && $planetinfo['organics'] >= $tkireg->base_organics && $planetinfo['goods'] >= $tkireg->base_goods && $planetinfo['credits'] >= $tkireg->base_credits)
            {
                // Registry values can't be directly used in a PDO bind parameter call
                $base_ore = $tkireg->base_ore;
                $base_organics = $tkireg->base_organics;
                $base_goods = $tkireg->base_goods;
                $base_credits = $tkireg->base_credits;

                // Create the base
                $stmt = $pdo_db->prepare("UPDATE ::prefix::planets SET base='Y', " .
                        "ore = :planetore - :baseore, organics = :planetorg - :baseorg," .
                        "goods = :planetgoods - :basegoods, " .
                        "credits = :planetcredits - :basecredits WHERE planet_id = :planet_id");
                $stmt->bindParam(':planetore', $planetinfo['ore'], \PDO::PARAM_INT);
                $stmt->bindParam(':baseore', $base_ore, \PDO::PARAM_INT);
                $stmt->bindParam(':planetorg', $planetinfo['organics'], \PDO::PARAM_INT);
                $stmt->bindParam(':baseorg', $base_organics, \PDO::PARAM_INT);
                $stmt->bindParam(':planetgoods', $planetinfo['goods'], \PDO::PARAM_INT);
                $stmt->bindParam(':basegoods', $base_goods, \PDO::PARAM_INT);
                $stmt->bindParam(':planetcredits', $planetinfo['credits'], \PDO::PARAM_INT);
                $stmt->bindParam(':basecredits', $base_credits, \PDO::PARAM_INT);
                $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
                $result = $stmt->execute();
                \Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);

                // Update user turns
                $stmt = $pdo_db->prepare("UPDATE ::prefix::ships SET turns = turns - 1, " .
                        "turns_used = turns_used + 1 WHERE ship_id = :ship_id");
                $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                $result = $stmt->execute();
                \Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);

                // Notify user of base building results
                echo $langvars['l_planet_bbuild'] . "<br><br>";

                // Calculate ownership and notify user of results
                $ownership = \Tki\Ownership::calc($pdo_db, $lang, $playerinfo['sector'], $tkireg);
                echo $ownership . "<p>";
                return;
            }
        }
    }
}
