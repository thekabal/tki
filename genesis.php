<?php declare(strict_types = 1);
/**
 * genesis.php from The Kabal Invasion.
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

// If anyone who's coded this thing is willing to update it to
// support multiple planets, go ahead. I suggest removing this
// code completely from here and putting it in the planet menu
// instead. Easier to manage, makes more sense too.

require_once './common.php';

$login = new Tki\Login();
$login->checkLogin($pdo_db, $lang, $tkireg, $tkitimer, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'footer',
                                'genesis', 'insignias', 'news', 'universal'));
$title = $langvars['l_gns_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

// Get sectorinfo from database
$sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db);
$sectorinfo = $sectors_gateway->selectSectorInfo($playerinfo['sector']);

// Get planetinfo from database
$planets_gateway = new \Tki\Planets\PlanetsGateway($pdo_db);
$planetinfo = $planets_gateway->selectPlanetInfo($playerinfo['sector']);
$num_planets = 0;
if (!empty($planetinfo))
{
    $num_planets = count($planetinfo);
}

// Generate Planetname
$planetname = substr($playerinfo['character_name'], 0, 1) . substr($playerinfo['ship_name'], 0, 1) . "-" . $playerinfo['sector'] . "-" . ($num_planets + 1);

echo "<h1>" . $title . "</h1>\n";

$destroy = null;
$prod_organics = $tkireg->default_prod_organics;
$prod_ore = $tkireg->default_prod_ore;
$prod_goods = $tkireg->default_prod_goods;
$prod_energy = $tkireg->default_prod_energy;
$prod_fighters = $tkireg->default_prod_fighters;
$prod_torp = $tkireg->default_prod_torp;

if (array_key_exists('destroy', $_GET))
{
    $destroy = $_GET['destroy'];
}

if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_gns_turn'];
}
elseif ($playerinfo['on_planet'] == 'Y')
{
    echo $langvars['l_gns_onplanet'];
}
elseif ($num_planets >= $tkireg->max_planets_sector)
{
    echo $langvars['l_gns_full'];
}
elseif ($sectorinfo['sector_id'] >= $tkireg->max_sectors)
{
    echo $langvars['l_gns_invalid_sector'] . "<br>\n";
}
elseif ($playerinfo['dev_genesis'] < 1)
{
    echo $langvars['l_gns_nogenesis'];
}
else
{
    // Get zoneinfo from database
    $zones_gateway = new \Tki\Zones\ZonesGateway($pdo_db);
    $zoneinfo = $zones_gateway->selectZoneInfo($sectorinfo['zone_id']);
    if ($zoneinfo['allow_planet'] == 'N')
    {
        echo $langvars['l_gns_forbid'];
    }
    elseif ($zoneinfo['allow_planet'] == 'L')
    {
        if ($zoneinfo['team_zone'] == 'N')
        {
            if ($playerinfo['team'] == 0 && $zoneinfo['owner'] != $playerinfo['ship_id'])
            {
                echo $langvars['l_gns_bforbid'];
            }
            else
            {
                $sql = "SELECT team FROM ::prefix::ships WHERE ship_id = :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':ship_id', $zoneinfo['owner'], PDO::PARAM_INT);
                $stmt->execute();
                $ownerinfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($ownerinfo['team'] != $playerinfo['team'])
                {
                    echo $langvars['l_gns_bforbid'];
                }
                else
                {
                    if (is_array($playerinfo))
                    {
                        $planets_gateway->genesisAddPlanet($pdo_db, $tkireg, $playerinfo, $planetname);
                    }

                    echo $langvars['l_gns_pcreate'];
                }
            }
        }
        elseif ($playerinfo['team'] != $zoneinfo['owner'])
        {
            echo $langvars['l_gns_bforbid'];
        }
        else
        {
            if (is_array($playerinfo))
            {
                $planets_gateway->genesisAddPlanet($pdo_db, $tkireg, $playerinfo, $planetname);
            }

            echo $langvars['l_gns_pcreate'];
        }
    }
    else
    {
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
        Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        $sql = "UPDATE ::prefix::ships SET turns_used = turns_used + 1, turns = turns - 1, dev_genesis = dev_genesis - 1 WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $result = $stmt->execute();
        Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        echo $langvars['l_gns_pcreate'];
    }
}

echo "<br><br>";

Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
