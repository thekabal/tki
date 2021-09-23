<?php declare(strict_types = 1);
/**
 * team.php from The Kabal Invasion.
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

require_once './common.php';

$login = new Tki\Login();
$login->checkLogin($pdo_db, $lang, $tkireg, $tkitimer, $template);

$title = $langvars['l_teamm_title'];
$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('combat', 'common',
                                'footer', 'insignias', 'news', 'teams',
                                'universal'));
// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

$planet_id = (int) preg_replace('/[^0-9]/', '', (string) $_GET['planet_id']);

// Get planetinfo from database
$planets_gateway = new \Tki\Planets\PlanetsGateway($pdo_db);
$planetinfo = $planets_gateway->selectPlanetInfoByPlanet($planet_id);

if (!empty($planetinfo))
{
    if ($planetinfo['owner'] == $playerinfo['ship_id'] || ($planetinfo['team'] == $playerinfo['team'] && $playerinfo['team'] > 0))
    {
        echo "<h1>" . $title . "</h1>\n";
        if ($action == "planetteam")
        {
            echo $langvars['l_teamm_toteam'] . "<br>";
            $sql = "UPDATE ::prefix::planets SET team = :team, owner = :owner  WHERE planet_id = :planet_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':team', $playerinfo['team'], \PDO::PARAM_INT);
            $stmt->bindParam(':owner', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
            $result = $stmt->execute();
            Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            $ownership = Tki\Ownership::calc($pdo_db, $lang, $playerinfo['sector'], $tkireg);
            echo '<p>' . $ownership . '<p>';
        }

        if ($action == "planetpersonal")
        {
            echo $langvars['l_teamm_topersonal'] . "<br>";
            $sql = "UPDATE ::prefix::planets SET team='0', owner = :owner  WHERE planet_id = :planet_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
            $result = $stmt->execute();
            Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            $ownership = Tki\Ownership::calc($pdo_db, $lang, $playerinfo['sector'], $tkireg);

            // Kick other players off the planet
            $sql = "UPDATE ::prefix::ships SET on_planet='N' WHERE " .
                   "on_planet='Y' AND planet_id = :planet_id AND " .
                   "ship_id <> :ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $result = $stmt->execute();
            Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            echo '<p>' . $ownership . '<p>';
        }

        Tki\Text::gotoMain($pdo_db, $lang);
    }
    else
    {
        echo "<br>" . $langvars['l_team_exploit'] . "<br>";
        Tki\Text::gotoMain($pdo_db, $lang);
    }
}

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
