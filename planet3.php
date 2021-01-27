<?php declare(strict_types = 1);
/**
 * planet3.php from The Kabal Invasion.
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
$login->checkLogin($pdo_db, $lang, $tkireg, $template);

$title = $langvars['l_planet3_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'footer',
                                'insignias', 'main', 'news', 'planet',
                                'planet2', 'planet3', 'port', 'universal'));
// Fixed The Phantom Planet Transfer Bug
// Needs to be validated and type cast into their correct types.
// [GET]
// (int) planet_id

// [POST]
// (int) trade_ore
// (int) trade_organics
// (int) trade_goods
// (int) trade_energy

// Empty out Planet and Ship vars
$planetinfo = null;
$playerinfo = null;

// Validate and set the type of $_POST vars
$trade_ore = (int) $_POST['trade_ore'];
$trade_organics = (int) $_POST['trade_organics'];
$trade_goods = (int) $_POST['trade_goods'];
$trade_energy = (int) $_POST['trade_energy'];

// Validate and set the type of $_GET vars;
$planet_id = (int) $_GET['planet_id'];

echo "<h1>" . $title . "</h1>\n";

// Check if planet_id is valid.
if ($planet_id <= 0)
{
    echo $langvars['l_planet2_invalid_planet'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $template);
    die();
}

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

// Get planetinfo from database
$planets_gateway = new \Tki\Planets\PlanetsGateway($pdo_db); 
$planetinfo = $planets_gateway->selectPlanetInfoByPlanet($planet_id);

// Check to see if it returned valid planet info.
if (empty($planetinfo))
{
    echo $langvars['l_planet2_invalid_planet'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);
    die();
}

if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_trade_turnneed'] . '<br><br>';
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $template);
    die();
}

if ($planetinfo['sector_id'] != $playerinfo['sector'])
{
    echo $langvars['l_planet2_sector'] . '<br><br>';
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $template);
    die();
}

if (empty($planetinfo))
{
    echo $langvars['l_planet_none'] . "<br>";
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $template);
    die();
}

$trade_ore = round(abs($trade_ore));
$trade_organics = round(abs($trade_organics));
$trade_goods = round(abs($trade_goods));
$trade_energy = round(abs($trade_energy));
$ore_price = ($ore_price + $ore_delta / 4);
$organics_price = ($organics_price + $organics_delta / 4);
$goods_price = ($goods_price + $goods_delta / 4);
$energy_price = ($energy_price + $energy_delta / 4);

if ($planetinfo['sells'] == 'Y')
{
    $cargo_exchanged = $trade_ore + $trade_organics + $trade_goods;

    $free_holds = Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
    $free_power = Tki\CalcLevels::energy($playerinfo['power'], $tkireg) - $playerinfo['ship_energy'];
    $total_cost = ($trade_ore * $ore_price) + ($trade_organics * $organics_price) + ($trade_goods * $goods_price) + ($trade_energy * $energy_price);

    if ($free_holds < $cargo_exchanged)
    {
        echo $langvars['l_notenough_cargo'] . "  <a href=planet.php?planet_id=$planet_id>" . $langvars['l_clickme'] . "</a> " . $langvars['l_toplanetmenu'] . "<br><br>";
    }
    elseif ($trade_energy > $free_power)
    {
        echo $langvars['l_notenough_power'] . " <a href=planet.php?planet_id=$planet_id>" . $langvars['l_clickme'] . "</a> " . $langvars['l_toplanetmenu'] . "<br><br>";
    }
    elseif ($playerinfo['turns'] < 1)
    {
        echo $langvars['l_notenough_turns'] . "<br><br>";
    }
    elseif ($playerinfo['credits'] < $total_cost)
    {
        echo $langvars['l_notenough_credits'] . "<br><br>";
    }
    elseif ($trade_organics > $planetinfo['organics'])
    {
        echo $langvars['l_exceed_organics'] . "  ";
    }
    elseif ($trade_ore > $planetinfo['ore'])
    {
        echo $langvars['l_exceed_ore'] . "  ";
    }
    elseif ($trade_goods > $planetinfo['goods'])
    {
        echo $langvars['l_exceed_goods'] . "  ";
    }
    elseif ($trade_energy > $planetinfo['energy'])
    {
        echo $langvars['l_exceed_energy'] . "  ";
    }
    else
    {
        echo $langvars['l_totalcost'] . ": $total_cost<br>" . $langvars['l_traded_ore'] . ": $trade_ore<br>" . $langvars['l_traded_organics'] . ": $trade_organics<br>" . $langvars['l_traded_goods'] . ": $trade_goods<br>" . $langvars['l_traded_energy'] . ": $trade_energy<br><br>";

        // Update ship cargo, credits and turns
        $sql = "UPDATE ::prefix::ships SET turns = turns - 1, turns_used = turns_used + 1, credits = credits - :total_cost, ";
        $sql = $sql . "ship_ore = ship_ore + :trade_ore, ship_organics = ship_organics + :trade_organics, ship_goods = ship_goods + :trade_goods, ";
        $sql = $sql . "ship_energy = ship_energy + :trade_energy WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':total_cost', $total_cost, \PDO::PARAM_INT);
        $stmt->bindParam(':trade_ore', $trade_ore, \PDO::PARAM_INT);
        $stmt->bindParam(':trade_organics', $trade_organics, \PDO::PARAM_INT);
        $stmt->bindParam(':trade_goods', $trade_goods, \PDO::PARAM_INT);
        $stmt->bindParam(':trade_energy', $trade_energy, \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
        $update = $stmt->execute();
        Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);

        $sql = "UPDATE ::prefix::planets SET ore = ore - :trade_ore, organics = organics - :trade_organics, goods = goods - :trade_goods, ";
        $sql = $sql . "energy = energy - :trade_energy, credits = credits + :total_cost WHERE planet_id = :planet_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':total_cost', $total_cost, \PDO::PARAM_INT);
        $stmt->bindParam(':trade_ore', $trade_ore, \PDO::PARAM_INT);
        $stmt->bindParam(':trade_organics', $trade_organics, \PDO::PARAM_INT);
        $stmt->bindParam(':trade_goods', $trade_goods, \PDO::PARAM_INT);
        $stmt->bindParam(':trade_energy', $trade_energy, \PDO::PARAM_INT);
        $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
        $update = $stmt->execute();
        Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);

        echo $langvars['l_trade_complete'] . "<br><br>";
    }
}

Tki\Score::updateScore($pdo_db, $playerinfo['ship_id'], $tkireg, $playerinfo);
Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
