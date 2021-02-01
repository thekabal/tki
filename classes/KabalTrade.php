<?php declare(strict_types = 1);
/**
 * classes/KabalTrade.php from The Kabal Invasion.
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

class KabalTrade
{
    public static function trade(\PDO $pdo_db, array $playerinfo, Reg $tkireg): void
    {
        $shipore = null;
        $shiporganics = null;
        $shipgoods = null;

        // Pull sector info from database
        $sectors_gateway = new Sectors\SectorsGateway($pdo_db);
        $sectorinfo = $sectors_gateway->selectSectorInfo($playerinfo['sector']);

        // Obtain zone information
        $sql = "SELECT zone_id, allow_attack, allow_trade FROM ::prefix::zones WHERE zone_id = :zone_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $sectorinfo['zone_id'], \PDO::PARAM_INT);
        $stmt->execute();
        $zonerow = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Make sure we can trade here
        if ($zonerow['allow_trade'] == "N")
        {
            return;
        }

        // Check for a port we can use
        if ($sectorinfo['port_type'] == "none")
        {
            return;
        }

        // Kabal do not trade at energy ports since they regen energy
        if ($sectorinfo['port_type'] == "energy")
        {
            return;
        }

        // Check for negative credits or cargo
        if ($playerinfo['ship_ore'] < 0)
        {
            $playerinfo['ship_ore'] = 0;
            $shipore = 0;
        }

        if ($playerinfo['ship_organics'] < 0)
        {
            $playerinfo['ship_organics'] = 0;
            $shiporganics = 0;
        }

        if ($playerinfo['ship_goods'] < 0)
        {
            $playerinfo['ship_goods'] = 0;
            $shipgoods = 0;
        }

        if ($playerinfo['credits'] < 0)
        {
            $playerinfo['credits'] = 0;
        }

        if ($sectorinfo['port_ore'] <= 0)
        {
            return;
        }

        if ($sectorinfo['port_organics'] <= 0)
        {
            return;
        }

        if ($sectorinfo['port_goods'] <= 0)
        {
            return;
        }

        //  Check Kabal Credits & Cargo
        if ($playerinfo['ship_ore'] > 0)
        {
            $shipore = $playerinfo['ship_ore'];
        }

        if ($playerinfo['ship_organics'] > 0)
        {
            $shiporganics = $playerinfo['ship_organics'];
        }

        if ($playerinfo['ship_goods'] > 0)
        {
            $shipgoods = $playerinfo['ship_goods'];
        }

        // Make sure we have cargo or credits
        if (!$playerinfo['credits'] > 0 && !$playerinfo['ship_ore'] > 0 && !$playerinfo['ship_goods'] > 0 && !$playerinfo['ship_organics'] > 0)
        {
            return;
        }

        //  Make sure cargo is compatible
        if ($sectorinfo['port_type'] == "ore" && $shipore > 0)
        {
            return;
        }

        if ($sectorinfo['port_type'] == "organics" && $shiporganics > 0)
        {
            return;
        }

        if ($sectorinfo['port_type'] == "goods" && $shipgoods > 0)
        {
            return;
        }

        // Lets trade some cargo
        if ($sectorinfo['port_type'] == "ore") // Port ore
        {
            // Registry values can't be directly updated
            $ore_price = $tkireg->ore_price;
            $organics_price = $tkireg->organics_price;
            $goods_price = $tkireg->goods_price;

            // Set the prices
            $ore_price = $ore_price - $tkireg->ore_delta * $sectorinfo['port_ore'] / $tkireg->ore_limit * $tkireg->inventory_factor;
            $organics_price = $organics_price + $tkireg->organics_delta * $sectorinfo['port_organics'] / $tkireg->organics_limit * $tkireg->inventory_factor;
            $goods_price = $goods_price + $tkireg->goods_delta * $sectorinfo['port_goods'] / $tkireg->goods_limit * $tkireg->inventory_factor;

            // Set cargo buy/sell
            $amount_organics = $playerinfo['ship_organics'];
            $amount_goods = $playerinfo['ship_goods'];

            // Since we sell all other holds we set amount to be our total hold limit
            $amount_ore = \Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg);

            // We adjust this to make sure it does not exceed what the port has to sell
            $amount_ore = min($amount_ore, $sectorinfo['port_ore']);

            // We adjust this to make sure it does not exceed what we can afford to buy
            $amount_ore = min($amount_ore, floor(($playerinfo['credits'] + $amount_organics * $organics_price + $amount_goods * $goods_price) / $ore_price));

            // Buy / sell cargo
            $total_cost = round(($amount_ore * $ore_price) - ($amount_organics * $organics_price + $amount_goods * $goods_price));
            $newcredits = max(0, $playerinfo['credits'] - $total_cost);
            $newore = $playerinfo['ship_ore'] + $amount_ore;
            $neworganics = max(0, $playerinfo['ship_organics'] - $amount_organics);
            $newgoods = max(0, $playerinfo['ship_goods'] - $amount_goods);

            $sql = "UPDATE ::prefix::ships SET rating = rating + 1, credits = :credits, ship_ore = :ship_ore, ship_organics = :ship_organics, ship_goods = :ship_goods WHERE ship_id = :ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':credits', $newcredits, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_ore', $newore, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_organics', $neworganics, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_goods', $newgoods, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->execute();

            $sql = "UPDATE ::prefix::universe SET port_ore = port_ore - :port_ore, port_organics = port_organics + :port_organics, port_goods = port_goods + :port_goods WHERE sector_id = :sector_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':port_ore', $amount_ore, \PDO::PARAM_INT);
            $stmt->bindParam(':port_organics', $amount_organics, \PDO::PARAM_INT);
            $stmt->bindParam(':port_goods', $amount_goods, \PDO::PARAM_INT);
            $stmt->bindParam(':sector_id', $sectorinfo['sector_id'], \PDO::PARAM_INT);
            $stmt->execute();

            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Kabal Trade Results: Sold $amount_organics Organics Sold $amount_goods Goods Bought $amount_ore Ore Cost $total_cost");
        }

        if ($sectorinfo['port_type'] == "organics") // Port organics
        {
            // Registry values can't be directly updated
            $ore_price = $tkireg->ore_price;
            $organics_price = $tkireg->organics_price;
            $goods_price = $tkireg->goods_price;

            // Set the prices
            $organics_price = $organics_price - $tkireg->organics_delta * $sectorinfo['port_organics'] / $tkireg->organics_limit * $tkireg->inventory_factor;
            $ore_price = $ore_price + $tkireg->ore_delta * $sectorinfo['port_ore'] / $tkireg->ore_limit * $tkireg->inventory_factor;
            $goods_price = $goods_price + $tkireg->goods_delta * $sectorinfo['port_goods'] / $tkireg->goods_limit * $tkireg->inventory_factor;

            // Set cargo buy / sell
            $amount_ore = $playerinfo['ship_ore'];
            $amount_goods = $playerinfo['ship_goods'];

            // Since we sell all other holds we set amount to be our total hold limit
            $amount_organics = \Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg);

            // We adjust this to make sure it does not exceed what the port has to sell
            $amount_organics = min($amount_organics, $sectorinfo['port_organics']);

            // We adjust this to make sure it does not exceed what we can afford to buy
            $amount_organics = min($amount_organics, floor(($playerinfo['credits'] + $amount_ore * $ore_price + $amount_goods * $goods_price) / $organics_price));

            // Buy / sell cargo
            $total_cost = round(($amount_organics * $organics_price) - ($amount_ore * $ore_price + $amount_goods * $goods_price));
            $newcredits = max(0, $playerinfo['credits'] - $total_cost);
            $newore = max(0, $playerinfo['ship_ore'] - $amount_ore);
            $neworganics = $playerinfo['ship_organics'] + $amount_organics;
            $newgoods = max(0, $playerinfo['ship_goods'] - $amount_goods);

            $sql = "UPDATE ::prefix::ships SET rating=rating+1, credits = :credits, ship_ore = :ship_ore, ship_organics = :ship_organics, ship_goods = :ship_goods WHERE ship_id = :ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':credits', $newcredits, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_ore', $newore, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_organics', $neworganics, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_goods', $newgoods, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->execute();

            $sql = "UPDATE ::prefix::universe SET port_ore=port_ore - :port_ore, port_organics = port_organics + :port_organics, port_goods = port_goods + :port_goods WHERE sector_id = :sector_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':port_ore', $amount_ore, \PDO::PARAM_INT);
            $stmt->bindParam(':port_organics', $amount_organics, \PDO::PARAM_INT);
            $stmt->bindParam(':port_goods', $amount_goods, \PDO::PARAM_INT);
            $stmt->bindParam(':sector_id', $sectorinfo['sector_id'], \PDO::PARAM_INT);
            $stmt->execute();

            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Kabal Trade Results: Sold $amount_goods Goods Sold $amount_ore Ore Bought $amount_organics Organics Cost $total_cost");
        }

        if ($sectorinfo['port_type'] == "goods") // Port goods
        {
            // Registry values can't be directly updated
            $ore_price = $tkireg->ore_price;
            $organics_price = $tkireg->organics_price;
            $goods_price = $tkireg->goods_price;

            // Set the prices
            $goods_price = $goods_price - $tkireg->goods_delta * $sectorinfo['port_goods'] / $tkireg->goods_limit * $tkireg->inventory_factor;
            $ore_price = $ore_price + $tkireg->ore_delta * $sectorinfo['port_ore'] / $tkireg->ore_limit * $tkireg->inventory_factor;
            $organics_price = $organics_price + $tkireg->organics_delta * $sectorinfo['port_organics'] / $tkireg->organics_limit * $tkireg->inventory_factor;

            // Set cargo buy / sell
            $amount_ore = $playerinfo['ship_ore'];
            $amount_organics = $playerinfo['ship_organics'];

            // Since we sell all other holds we set amount to be our total hold limit
            $amount_goods = \Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg);

            // We adjust this to make sure it does not exceed what the port has to sell
            $amount_goods = min($amount_goods, $sectorinfo['port_goods']);

            // We adjust this to make sure it does not exceed what we can afford to buy
            $amount_goods = min($amount_goods, floor(($playerinfo['credits'] + $amount_ore * $ore_price + $amount_organics * $organics_price) / $goods_price));

            // Buy / sell cargo
            $total_cost = round(($amount_goods * $goods_price) - ($amount_organics * $organics_price + $amount_ore * $ore_price));
            $newcredits = max(0, $playerinfo['credits'] - $total_cost);
            $newore = max(0, $playerinfo['ship_ore'] - $amount_ore);
            $neworganics = max(0, $playerinfo['ship_organics'] - $amount_organics);
            $newgoods = $playerinfo['ship_goods'] + $amount_goods;

            $sql = "UPDATE ::prefix::ships SET rating=rating+1, credits = :credits, ship_ore = :ship_ore, ship_organics = :ship_organics, ship_goods = :ship_goods WHERE ship_id = :ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':credits', $newcredits, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_ore', $newore, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_organics', $neworganics, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_goods', $newgoods, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->execute();

            $sql = "UPDATE ::prefix::universe SET port_ore=port_ore - :port_ore, port_organics = port_organics + :port_organics, port_goods = port_goods + :port_goods WHERE sector_id = :sector_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':port_ore', $amount_ore, \PDO::PARAM_INT);
            $stmt->bindParam(':port_organics', $amount_organics, \PDO::PARAM_INT);
            $stmt->bindParam(':port_goods', $amount_goods, \PDO::PARAM_INT);
            $stmt->bindParam(':sector_id', $sectorinfo['sector_id'], \PDO::PARAM_INT);
            $stmt->execute();

            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Kabal Trade Results: Sold $amount_ore Ore Sold $amount_organics Organics Bought $amount_goods Goods Cost $total_cost");
        }
    }
}
