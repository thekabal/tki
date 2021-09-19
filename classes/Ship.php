<?php declare(strict_types = 1);
/**
 * classes/Ship.php from The Kabal Invasion.
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

class Ship
{
    public static function isDestroyed(\PDO $pdo_db, string $lang, Registry $tkireg, Timer $tkitimer, Smarty $template, array $playerinfo): bool
    {
        // Check for destroyed ship
        if ($playerinfo['ship_destroyed'] === 'Y')
        {
            // if the player has an escapepod, set the player up with a new ship
            if ($playerinfo['dev_escapepod'] === 'Y')
            {
                $rating = round($playerinfo['rating'] / 2);
                $ships_gateway = new \Tki\Players\ShipsGateway($pdo_db);
                $shipinfo = $ships_gateway->updateDestroyedShip($_SESSION['username'], $rating);
                return true;

                // $error_status = str_replace('[here]', "<a href='main.php'>" . $langvars['l_here'] . '</a>', $langvars['l_login_died']); Error status is not used anywhere
            }
            else
            {
                $langvars = Translate::load($pdo_db, $lang, array('common',
                                            'login', 'self_destruct', 'universal'));
                // If the player doesn't have an escapepod - they're dead, delete them.
                // But we can't delete them yet. (This prevents the self-distruct inherit bug)
                $error_status = str_replace('[here]', "<a href='log.php'>" .
                                 ucfirst($langvars['l_here']) . '</a>', $langvars['l_universal_died']) .
                                 '<br><br>' . $langvars['l_universal_died2'];
                $error_status .= str_replace('[logout]', "<a href='logout.php'>" .
                                 $langvars['l_logout'] . '</a>', $langvars['l_die_please']);
                $title = $langvars['l_error'];

                $header = new \Tki\Header();
                $header->display($pdo_db, $lang, $template, $title);

                echo $error_status;

                $footer = new \Tki\Footer();
                $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
                return true;
            }
        }
        else
        {
            return false;
        }
    }

    // FUTURE: Reduce the number of SQL calls needed to accomplish this. Maybe do the update without two selects?
    public static function leavePlanet(\PDO $pdo_db, int $ship_id): void
    {
        // Get planetinfo from database
        $planets_gateway = new \Tki\Planets\PlanetsGateway($pdo_db);
        $planetinfo = $planets_gateway->selectAllPlanetInfoByOwner($ship_id);

        if (is_array($planetinfo))
        {
            foreach ($planetinfo as $tmp_planet)
            {
                $sql = "SELECT * FROM ::prefix::ships WHERE on_planet='Y' AND planet_id = :planet_id AND ship_id <> :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':planet_id', $tmp_planet['planet_id'], \PDO::PARAM_INT);
                $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
                $stmt->execute();
                $ships_on_planet = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                if (is_array($ships_on_planet))
                {
                    foreach ($ships_on_planet as $tmp_ship)
                    {
                        $sql = "UPDATE ::prefix::ships SET on_planet='N', planet_id = '0' WHERE ship_id = :ship_id";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':ship_id', $tmp_ship['ship_id'], \PDO::PARAM_INT);
                        $stmt->execute();
                        PlayerLog::writeLog($pdo_db, $tmp_ship['ship_id'], LogEnums::PLANET_EJECT, $tmp_ship['sector'] . '|' . $tmp_ship['character_name']);
                    }
                }
            }
        }
    }
}
