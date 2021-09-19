<?php declare(strict_types = 1);
/**
 * port.php from The Kabal Invasion.
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

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('bounty', 'combat',
                                'common', 'device', 'footer', 'insignias',
                                'news', 'port', 'regional', 'report',
                                'universal'));
$title = $langvars['l_title_port'];
$body_class = 'port';

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title, $body_class);

echo "<body class=" . $body_class . "><br>";

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

// Fix negative quantities. How do the quantities acutally get negative?

if ($playerinfo['ship_ore'] < 0)
{
    $sql = "UPDATE ::prefix::ships SET ship_ore = :ship_ore WHERE email = :email";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindValue(':ship_ore', 0, PDO::PARAM_INT);
    $stmt->bindParam(':email', $_SESSION['username'], PDO::PARAM_STR);
    $stmt->execute();
    Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    $playerinfo['ship_ore'] = 0;
}

if ($playerinfo['ship_organics'] < 0)
{
    $sql = "UPDATE ::prefix::ships SET ship_organics = :ship_organics WHERE email = :email";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindValue(':ship_organics', 0, PDO::PARAM_INT);
    $stmt->bindParam(':email', $_SESSION['username'], PDO::PARAM_STR);
    $stmt->execute();
    Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    $playerinfo['ship_organics'] = 0;
}

if ($playerinfo['ship_energy'] < 0)
{
    $sql = "UPDATE ::prefix::ships SET ship_energy = :ship_energy WHERE email = :email";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindValue(':ship_energy', 0, PDO::PARAM_INT);
    $stmt->bindParam(':email', $_SESSION['username'], PDO::PARAM_STR);
    $stmt->execute();
    Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    $playerinfo['ship_energy'] = 0;
}

if ($playerinfo['ship_goods'] < 0)
{
    $sql = "UPDATE ::prefix::ships SET ship_goods = :ship_goods WHERE email = :email";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindValue(':ship_goods', 0, PDO::PARAM_INT);
    $stmt->bindParam(':email', $_SESSION['username'], PDO::PARAM_STR);
    $stmt->execute();
    Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    $playerinfo['ship_goods'] = 0;
}

// Get sectorinfo from database
$sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db);
$sectorinfo = $sectors_gateway->selectSectorInfo($playerinfo['sector']);

if ($sectorinfo['port_ore'] < 0)
{
    $sql = "UPDATE ::prefix::ships SET port_ore = :port_ore WHERE sector_id = :sector";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindValue(':port_ore', 0, PDO::PARAM_INT);
    $stmt->bindParam(':sector_id', $playerinfo['sector'], PDO::PARAM_STR);
    $stmt->execute();
    Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    $sectorinfo['port_ore'] = 0;
}

if ($sectorinfo['port_goods'] < 0)
{
    $sql = "UPDATE ::prefix::ships SET port_goods = :port_goods WHERE sector_id = :sector";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindValue(':port_goods', 0, PDO::PARAM_INT);
    $stmt->bindParam(':sector_id', $playerinfo['sector'], PDO::PARAM_STR);
    $stmt->execute();
    Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    $sectorinfo['port_goods'] = 0;
}

if ($sectorinfo['port_organics'] < 0)
{
    $sql = "UPDATE ::prefix::ships SET port_organics = :port_organics WHERE sector_id = :sector";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindValue(':port_organics', 0, PDO::PARAM_INT);
    $stmt->bindParam(':sector_id', $playerinfo['sector'], PDO::PARAM_STR);
    $stmt->execute();
    Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    $sectorinfo['port_organics'] = 0;
}

if ($sectorinfo['port_energy'] < 0)
{
    $sql = "UPDATE ::prefix::ships SET port_energy = :port_energy WHERE sector_id = :sector";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindValue(':port_energy', 0, PDO::PARAM_INT);
    $stmt->bindParam(':sector_id', $playerinfo['sector'], PDO::PARAM_STR);
    $stmt->execute();
    Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    $sectorinfo['port_energy'] = 0;
}

// Get zoneinfo from database
$zones_gateway = new \Tki\Zones\ZonesGateway($pdo_db);
$zoneinfo = $zones_gateway->selectZoneInfo($sectorinfo['zone_id']);

// Create ibank gateway where multiple codepaths can get to it
$ibank_gateway = new Tki\Ibank\IbankGateway($pdo_db);

if (!empty($zoneinfo))
{
    if ($zoneinfo['zone_id'] == 4)
    {
        $title = $langvars['l_sector_war'];
        echo "<h1>" . $title . "</h1>\n";
        echo $langvars['l_war_info'] . "<p>";
        Tki\Text::gotoMain($pdo_db, $lang);

        $footer = new Tki\Footer();
        $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
        die();
    }
    elseif ($zoneinfo['allow_trade'] == 'N')
    {
        // Translation needed
        $title = "Trade forbidden";
        echo "<h1>" . $title . "</h1>\n";
        echo $langvars['l_no_trade_info'] . "<p>";
        Tki\Text::gotoMain($pdo_db, $lang);

        $footer = new Tki\Footer();
        $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
        die();
    }
    elseif ($zoneinfo['allow_trade'] == 'L')
    {
        if ($zoneinfo['team_zone'] == 'N')
        {
            // Get playerinfo from database
            $sql = "SELECT team FROM ::prefix::ships WHERE ship_id = :ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':ship_id', $zoneinfo['owner'], PDO::PARAM_INT);
            $stmt->execute();
            $ownerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (($playerinfo['ship_id'] != $zoneinfo['owner']) && $playerinfo['team'] == 0 || ($playerinfo['team'] != $ownerinfo['team']))
            {
                // Translation needed
                $title = "Trade forbidden";
                echo "<h1>" . $title . "</h1>\n";
                echo "Trading at this port is not allowed for outsiders<p>";
                Tki\Text::gotoMain($pdo_db, $lang);

                $footer = new Tki\Footer();
                $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
                die();
            }
        }
        else
        {
            if ($playerinfo['team'] != $zoneinfo['owner'])
            {
                $title = $langvars['l_no_trade'];
                echo "<h1>" . $title . "</h1>\n";
                echo $langvars['l_no_trade_out'] . "<p>";
                Tki\Text::gotoMain($pdo_db, $lang);

                $footer = new Tki\Footer();
                $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
                die();
            }
        }
    }
}

if ($sectorinfo['port_type'] != "none" && $sectorinfo['port_type'] != "special")
{
    $title = $langvars['l_title_trade'];
    echo "<h1>" . $title . "</h1>\n";

    if ($sectorinfo['port_type'] == "ore")
    {
        $tkireg->ore_price = $tkireg->ore_price - $tkireg->ore_delta * $sectorinfo['port_ore'] / $tkireg->ore_limit * $tkireg->inventory_factor;
        $sb_ore = $langvars['l_selling'];
    }
    else
    {
        $tkireg->ore_price = $tkireg->ore_price + $tkireg->ore_delta * $sectorinfo['port_ore'] / $tkireg->ore_limit * $tkireg->inventory_factor;
        $sb_ore = $langvars['l_buying'];
    }

    if ($sectorinfo['port_type'] == "organics")
    {
        $tkireg->organics_price = $tkireg->organics_price - $tkireg->organics_delta * $sectorinfo['port_organics'] / $tkireg->organics_limit * $tkireg->inventory_factor;
        $sb_organics = $langvars['l_selling'];
    }
    else
    {
        $tkireg->organics_price = $tkireg->organics_price + $tkireg->organics_delta * $sectorinfo['port_organics'] / $tkireg->organics_limit * $tkireg->inventory_factor;
        $sb_organics = $langvars['l_buying'];
    }

    if ($sectorinfo['port_type'] == "goods")
    {
        $tkireg->goods_price = $tkireg->goods_price - $tkireg->goods_delta * $sectorinfo['port_goods'] / $tkireg->goods_limit * $tkireg->inventory_factor;
        $sb_goods = $langvars['l_selling'];
    }
    else
    {
        $tkireg->goods_price = $tkireg->goods_price + $tkireg->goods_delta * $sectorinfo['port_goods'] / $tkireg->goods_limit * $tkireg->inventory_factor;
        $sb_goods = $langvars['l_buying'];
    }

    if ($sectorinfo['port_type'] == "energy")
    {
        $tkireg->energy_price = $tkireg->energy_price - $tkireg->energy_delta * $sectorinfo['port_energy'] / $tkireg->energy_limit * $tkireg->inventory_factor;
        $sb_energy = $langvars['l_selling'];
    }
    else
    {
        $tkireg->energy_price = $tkireg->energy_price + $tkireg->energy_delta * $sectorinfo['port_energy'] / $tkireg->energy_limit * $tkireg->inventory_factor;
        $sb_energy = $langvars['l_buying'];
    }

    // Establish default amounts for each commodity
    if ($sb_ore == $langvars['l_buying'])
    {
        $amount_ore = $playerinfo['ship_ore'];
    }
    else
    {
        $amount_ore = Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_colonists'];
    }

    if ($sb_organics == $langvars['l_buying'])
    {
        $amount_organics = $playerinfo['ship_organics'];
    }
    else
    {
        $amount_organics = Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_organics'] - $playerinfo['ship_colonists'];
    }

    if ($sb_goods == $langvars['l_buying'])
    {
        $amount_goods = $playerinfo['ship_goods'];
    }
    else
    {
        $amount_goods = Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
    }

    if ($sb_energy == $langvars['l_buying'])
    {
        $amount_energy = $playerinfo['ship_energy'];
    }
    else
    {
        $amount_energy = Tki\CalcLevels::energy($playerinfo['power'], $tkireg) - $playerinfo['ship_energy'];
    }

    // Limit amounts to port quantities
    $amount_ore = min($amount_ore, $sectorinfo['port_ore']);
    $amount_organics = min($amount_organics, $sectorinfo['port_organics']);
    $amount_goods = min($amount_goods, $sectorinfo['port_goods']);
    $amount_energy = min($amount_energy, $sectorinfo['port_energy']);

    // Limit amounts to what the player can afford
    if ($sb_ore == $langvars['l_selling'])
    {
        $amount_ore = min($amount_ore, floor(($playerinfo['credits'] + $amount_organics * $tkireg->organics_price + $amount_goods * $tkireg->goods_price + $amount_energy * $tkireg->energy_price) / $tkireg->ore_price));
    }

    if ($sb_organics == $langvars['l_selling'])
    {
        $amount_organics = min($amount_organics, floor(($playerinfo['credits'] + $amount_ore * $tkireg->ore_price + $amount_goods * $tkireg->goods_price + $amount_energy * $tkireg->energy_price) / $tkireg->organics_price));
    }

    if ($sb_goods == $langvars['l_selling'])
    {
        $amount_goods = min($amount_goods, floor(($playerinfo['credits'] + $amount_ore * $tkireg->ore_price + $amount_organics * $tkireg->organics_price + $amount_energy * $tkireg->energy_price) / $tkireg->goods_price));
    }

    if ($sb_energy == $langvars['l_selling'])
    {
        $amount_energy = min($amount_energy, floor(($playerinfo['credits'] + $amount_ore * $tkireg->ore_price + $amount_organics * $tkireg->organics_price + $amount_goods * $tkireg->goods_price) / $tkireg->energy_price));
    }

    echo "<form accept-charset='utf-8' action=port2.php method=post>";
    echo "<table>";
    echo "<tr><td><strong>" . $langvars['l_commodity'] . "</strong></td><td><strong>" . $langvars['l_buying'] . "/" . $langvars['l_selling'] . "</strong></td><td><strong>" . $langvars['l_amount'] . "</strong></td><td><strong>" . $langvars['l_price'] . "</strong></td><td><strong>" . $langvars['l_buy'] . "/" . $langvars['l_sell'] . "</strong></td><td><strong>" . $langvars['l_cargo'] . "</strong></td></tr>";
    echo "<tr><td>" . $langvars['l_ore'] . "</td><td>$sb_ore</td><td>" . number_format($sectorinfo['port_ore'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>$tkireg->ore_price</td><td><input type=text name=trade_ore size=10 maxlength=20 value=$amount_ore></td><td>" . number_format($playerinfo['ship_ore'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td></tr>";
    echo "<tr><td>" . $langvars['l_organics'] . "</td><td>$sb_organics</td><td>" . number_format($sectorinfo['port_organics'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>$tkireg->organics_price</td><td><input type=text name=trade_organics size=10 maxlength=20 value=$amount_organics></td><td>" . number_format($playerinfo['ship_organics'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td></tr>";
    echo "<tr><td>" . $langvars['l_goods'] . "</td><td>$sb_goods</td><td>" . number_format($sectorinfo['port_goods'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>$tkireg->goods_price</td><td><input type=text name=trade_goods size=10 maxlength=20 value=$amount_goods></td><td>" . number_format($playerinfo['ship_goods'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td></tr>";
    echo "<tr><td>" . $langvars['l_energy'] . "</td><td>$sb_energy</td><td>" . number_format($sectorinfo['port_energy'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>$tkireg->energy_price</td><td><input type=text name=trade_energy size=10 maxlength=20 value=$amount_energy></td><td>" . number_format($playerinfo['ship_energy'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td></tr>";
    echo "</table><br>";
    echo "<input type=submit value=" . $langvars['l_trade'] . ">";
    echo "</form>";

    $free_holds = Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
    $free_power = Tki\CalcLevels::energy($playerinfo['power'], $tkireg) - $playerinfo['ship_energy'];

    $langvars['l_trade_st_info'] = str_replace("[free_holds]", number_format($free_holds, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_trade_st_info']);
    $langvars['l_trade_st_info'] = str_replace("[free_power]", number_format($free_power, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_trade_st_info']);
    $langvars['l_trade_st_info'] = str_replace("[credits]", number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_trade_st_info']);
    echo $langvars['l_trade_st_info'];
}
elseif ($sectorinfo['port_type'] == "special")
{
    $title = $langvars['l_special_port'];
    echo "<h1>" . $title . "</h1>\n";

    // Kami Multi-browser window upgrade fix
    $_SESSION['port_shopping'] = true;

    if (Tki\Loan::isPending($pdo_db, $tkireg))
    {
        echo $langvars['l_port_loannotrade'] . "<p>";
        echo "<a href=ibank.php>" . $langvars['l_ibank_term'] . "</a><p>";
        Tki\Text::gotoMain($pdo_db, $lang);

        $footer = new Tki\Footer();
        $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
        die();
    }

    if ($tkireg->bounty_all_special)
    {
        $res2 = $old_db->Execute("SELECT SUM(amount) as total_bounty FROM {$old_db->prefix}bounty WHERE placed_by = 0 AND bounty_on = ?;", array($playerinfo['ship_id']));
        Tki\Db::logDbErrors($pdo_db, $res2, __LINE__, __FILE__);
    }
    else
    {
        $res2 = $old_db->Execute("SELECT SUM(amount) as total_bounty FROM {$old_db->prefix}bounty WHERE placed_by = 0 AND bounty_on = ? AND ?=2;", array($playerinfo['ship_id'], $sectorinfo['zone_id']));
        Tki\Db::logDbErrors($pdo_db, $res2, __LINE__, __FILE__);
    }

    if ($res2)
    {
        $bty = $res2->fields;
        if ($bty['total_bounty'] > 0)
        {
            $pay = (int) filter_input(INPUT_POST, 'pay', FILTER_SANITIZE_NUMBER_INT);
            if ($pay === 1)
            {
                if ($playerinfo['credits'] < $bty['total_bounty'])
                {
                    $langvars['l_port_btynotenough'] = str_replace("[amount]", number_format($bty['total_bounty'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_port_btynotenough']);
                    echo $langvars['l_port_btynotenough'] . "<br>";
                    Tki\Text::gotoMain($pdo_db, $lang);
                    die();
                }
                else
                {
                    $ibank_gateway->reduceIbankCredits($playerinfo, $bty['total_bounty']);

                    $sql = "DELETE FROM ::prefix::bounty WHERE bounty_on = :ship_id AND placed_by = :placed_by";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':ship_id', $playerinfo['ship_id'], PDO::PARAM_STR);
                    $stmt->bindValue(':placed_by', 0, PDO::PARAM_INT);
                    $stmt->execute();
                    Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                    $langvars['l_port_bountypaid'] = str_replace("[here]", "<a href='port.php'>" . $langvars['l_here'] . "</a>", $langvars['l_port_bountypaid']);
                    echo $langvars['l_port_bountypaid'] . "<br>";
                    die();
                }
            }
            elseif ($pay === 2)
            {
                $bank_account = $ibank_gateway->selectIbankAccount($playerinfo['ship_id']);
                $bounty_payment = $bank_account['balance'];
                if ($bounty_payment > 1000)
                {
                    $bounty_payment -= 1000;

                    if ($bank_account['balance'] >= $bty['total_bounty'])
                    {
                        $bounty_payment = $bty['total_bounty'];
                        $ibank_gateway->reduceIbankCredits($playerinfo, $bounty_payment);

                        $sql = "DELETE FROM ::prefix::bounty WHERE bounty_on = :ship_id AND placed_by = :placed_by";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], PDO::PARAM_STR);
                        $stmt->bindValue(':placed_by', 0, PDO::PARAM_INT);
                        $stmt->execute();
                        Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                        $langvars['l_port_fullbountypaid'] = str_replace("[here]", "<a href='port.php'>" . $langvars['l_here'] . "</a>", $langvars['l_port_fullbountypaid']);
                        echo $langvars['l_port_fullbountypaid'] . "<br>";
                        die();
                    }
                    else
                    {
                        // Translation needed
                        echo "Partial Payment Mode<br>\n";
                        echo "You don't have enough Credits within your Intergalactic Bank Account to pay your entire bounty.<br>\n";
                        echo "However you can pay your bounty off in instalments.<br>\n";
                        echo "And your first instalment will be " . number_format($bounty_payment, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " credits.<br>\n";
                        echo "<br>\n";

                        $sql = "UPDATE ::prefix::ibank_accounts SET balance = balance - :credits WHERE ship_id = :ship_id";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':credits', $bounty_payment, PDO::PARAM_INT);
                        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], PDO::PARAM_INT);
                        $stmt->execute();
                        Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                        $sql = "UPDATE ::prefix::bounty SET amount = amount - :credits WHERE bounty_on = :ship_id AND placed_by = :placed_by";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':credits', $bounty_payment, PDO::PARAM_INT);
                        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], PDO::PARAM_INT);
                        $stmt->bindValue(':placed_by', 0, PDO::PARAM_INT);
                        $stmt->execute();
                        Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                        echo $langvars['l_port_part_bounty'] . ".<br>\n";
                        echo "<br>\n";

                        $bounty_left = $bty['total_bounty'] - $bounty_payment;
                        Tki\Text::gotoMain($pdo_db, $lang);
                        die();
                    }
                }
                else
                {
                    // Translation needed
                    echo $langvars['l_port_funds_bank'] . ".<br>\n";
                    echo "Try doing some trading then transfer your funds over to the <a href='ibank.php'>Intergalactic Bank</a><br>\n";
                    echo "<br>\n";

                    Tki\Text::gotoMain($pdo_db, $lang);
                    die();
                }
            }
            else
            {
                echo $langvars['l_port_bounty'] . "<br>";
                echo "<br>\n";

                echo $langvars['l_port_pay_from_ship'] . "<br>\n";
                $langvars['l_port_bounty2'] = str_replace("[amount]", number_format($bty['total_bounty'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_port_bounty2']);
                $langvars['l_port_bounty2'] = str_replace("[here]", "<a href='port.php?pay=1'>" . $langvars['l_here'] . "</a>", $langvars['l_port_bounty2']);
                echo $langvars['l_port_bounty2'] . "<br>";
                echo "<br>\n";

                echo $langvars['l_port_pay_from_bank'] . "<br>\n";
                $langvars['l_port_bounty3'] = "Click <a href='port.php?pay=2'>here</a> to pay the bounty of [amount] Credits from your Intergalactic Bank Account.";
                $langvars['l_port_bounty3'] = str_replace("[amount]", number_format($bty['total_bounty'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_port_bounty3']);
                echo $langvars['l_port_bounty3'] . "<br>\n";
                echo "<br>\n";

                echo "<a href=\"bounty.php\">" . $langvars['l_by_placebounty'] . "</a><br><br>";
                Tki\Text::gotoMain($pdo_db, $lang);
                die();
            }
        }
    }

    $genesis_free = $tkireg->max_genesis - $playerinfo['dev_genesis'];
    $beacon_free = $tkireg->max_beacons - $playerinfo['dev_beacon'];
    $emerwarp_free = $tkireg->max_emerwarp - $playerinfo['dev_emerwarp'];
    $warpedit_free = $tkireg->max_warpedit - $playerinfo['dev_warpedit'];
    $fighter_max = Tki\CalcLevels::abstractLevels($playerinfo['computer'], $tkireg);
    $fighter_free = $fighter_max - $playerinfo['ship_fighters'];
    $torpedo_max = Tki\CalcLevels::abstractLevels($playerinfo['torp_launchers'], $tkireg);
    $torpedo_free = $torpedo_max - $playerinfo['torps'];
    $armor_max = Tki\CalcLevels::abstractLevels($playerinfo['armor'], $tkireg);
    $armor_free = $armor_max - $playerinfo['armor_pts'];
    $colonist_max = Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'];

    if ($colonist_max < 0)
    {
        $colonist_max = 0;
    }

    $colonist_free = $colonist_max - $playerinfo['ship_colonists'];
    echo "\n<script>\n<!--\n";

    echo "function make_max(name, val)\n";
    echo "{\n";
    echo " if (document.forms[0].elements[name].value != val)\n";
    echo " {\n";
    echo "  if (val != 0)\n";
    echo "  {\n";
    echo "  document.forms[0].elements[name].value = val;\n";
    echo "  }\n";
    echo " }\n";
    echo "}\n";

    // change_delta function //
    echo "function change_delta(desiredvalue,currentvalue)\n";
    echo "{\n";
    echo "  Delta=0; DeltaCost=0;\n";
    echo "  Delta = desiredvalue - currentvalue;\n";
    echo "\n";
    echo "    while (Delta>0) \n";
    echo "    {\n";
    echo "     DeltaCost=DeltaCost + Math.pow(2,desiredvalue-Delta); \n";
    echo "     Delta=Delta-1;\n";
    echo "    }\n";
    echo "\n";
    echo "  DeltaCost=DeltaCost * " . $tkireg->upgrade_cost . "\n";
    echo "  return DeltaCost;\n";
    echo "}\n";

    echo "function count_total()\n";
    echo "{\n";
    echo "// Here we cycle through all form values (other than buy, or full), and regexp out all non-numerics. (1,000 = 1000)\n";
    echo "// Then, if its become a null value (type in just a, it would be a blank value. blank is bad.) we set it to zero.\n";
    echo "var form = document.forms[0];\n";
    echo "var i = form.elements.length;\n";
    echo "while (i > 0)\n";
    echo " {\n";
    echo " if ((form.elements[i-1].value != 'Buy') && (form.elements[i-1].value != 'Full'))\n";
    echo "  {\n";
    echo "  var tmpval = form.elements[i-1].value.replace(/\D+/g, \"\");\n";
    echo "  if (tmpval != form.elements[i-1].value)\n";
    echo "   {\n";
    echo "   form.elements[i-1].value = form.elements[i-1].value.replace(/\D+/g, \"\");\n";
    echo "   }\n";
    echo "  }\n";
    echo " if (form.elements[i-1].value == '')\n";
    echo "  {\n";
    echo "  form.elements[i-1].value ='0';\n";
    echo "  }\n";
    echo " i--;\n";
    echo "}\n";
    echo "// Here we set all 'Max' items to 0 if they are over max - player amt.\n";

    echo "if (($genesis_free < form.dev_genesis_number.value) && (form.dev_genesis_number.value != 'Full'))\n";
    echo " {\n";
    echo " form.dev_genesis_number.value=0\n";
    echo " }\n";

    echo "if (($beacon_free < form.dev_beacon_number.value) && (form.dev_beacon_number.value != 'Full'))\n";
    echo " {\n";
    echo " form.dev_beacon_number.value=0\n";
    echo " }\n";

    echo "if (($emerwarp_free < form.dev_emerwarp_number.value) && (form.dev_emerwarp_number.value != 'Full'))\n";
    echo " {\n";
    echo " form.dev_emerwarp_number.value=0\n";
    echo " }\n";

    echo "if (($warpedit_free < form.dev_warpedit_number.value) && (form.dev_warpedit_number.value != 'Full'))\n";
    echo " {\n";
    echo " form.dev_warpedit_number.value=0\n";
    echo " }\n";

    echo "if (($fighter_free < form.fighter_number.value) && (form.fighter_number.value != 'Full'))\n";
    echo " {\n";
    echo " form.fighter_number.value=0\n";
    echo " }\n";

    echo "if (($torpedo_free < form.torpedo_number.value) && (form.torpedo_number.value != 'Full'))\n";
    echo "  {\n";
    echo "  form.torpedo_number.value=0\n";
    echo "  }\n";

    echo "if (($armor_free < form.armor_number.value) && (form.armor_number.value != 'Full'))\n";
    echo "  {\n";
    echo "  form.armor_number.value=0\n";
    echo "  }\n";

    echo "if (($colonist_free < form.colonist_number.value) && (form.colonist_number.value != 'Full'))\n";
    echo "  {\n";
    echo "  form.colonist_number.value=0\n";
    echo "  }\n";

    echo "// Done with the bounds checking\n";
    echo "// Pluses must be first, or if empty will produce a javascript error\n";
    echo "form.total_cost.value = 0\n";

    // NaN Fix :: Needed to be put in an if statment to check for Full.
    if ($genesis_free > 0)
    {
        echo "+ form.dev_genesis_number.value * " . $tkireg->dev_genesis_price . "\n";
    }

    // NaN Fix :: Needed to be put in an if statment to check for Full.
    if ($beacon_free > 0)
    {
        echo "+ form.dev_beacon_number.value * " . $tkireg->dev_beacon_price . "\n";
    }

    if ($emerwarp_free > 0)
    {
        echo "+ form.dev_emerwarp_number.value * " . $tkireg->dev_emerwarp_price . "\n";
    }

    // NaN Fix :: Needed to be put in an if statment to check for Full.
    if ($warpedit_free > 0)
    {
        echo "+ form.dev_warpedit_number.value * " . $tkireg->dev_warpedit_price . "\n";
    }

    echo "+ form.elements['dev_minedeflector_number'].value * " . $tkireg->dev_minedeflector_price . "\n";

    if ($playerinfo['dev_escapepod'] == 'N')
    {
        echo "+ (form.escapepod_purchase.checked ?  " . $tkireg->dev_escapepod_price . " : 0)\n";
    }

    if ($playerinfo['dev_fuelscoop'] == 'N')
    {
        echo "+ (form.fuelscoop_purchase.checked ?  " . $tkireg->dev_fuelscoop_price . ": 0)\n";
    }

    if ($playerinfo['dev_lssd'] == 'N')
    {
        echo "+ (form.lssd_purchase.checked ? " . $tkireg->dev_lssd_price . " : 0)\n";
    }

    echo "+ change_delta(form.hull_upgrade.value, $playerinfo[hull])\n";
    echo "+ change_delta(form.engine_upgrade.value, $playerinfo[engines])\n";
    echo "+ change_delta(form.power_upgrade.value, $playerinfo[power])\n";
    echo "+ change_delta(form.computer_upgrade.value, $playerinfo[computer])\n";
    echo "+ change_delta(form.sensors_upgrade.value, $playerinfo[sensors])\n";
    echo "+ change_delta(form.beams_upgrade.value, $playerinfo[beams])\n";
    echo "+ change_delta(form.armor_upgrade.value, $playerinfo[armor])\n";
    echo "+ change_delta(form.cloak_upgrade.value, $playerinfo[cloak])\n";
    echo "+ change_delta(form.torp_launchers_upgrade.value, $playerinfo[torp_launchers])\n";
    echo "+ change_delta(form.shields_upgrade.value, $playerinfo[shields])\n";

    if ($playerinfo['ship_fighters'] != $fighter_max)
    {
        echo "+ form.fighter_number.value * " . $tkireg->fighter_price . " ";
    }

    if ($playerinfo['torps'] != $torpedo_max)
    {
        echo "+ form.torpedo_number.value * " . $tkireg->torpedo_price . " ";
    }

    if ($playerinfo['armor_pts'] != $armor_max)
    {
        echo "+ form.armor_number.value * " . $tkireg->armor_price . " ";
    }

    if ($playerinfo['ship_colonists'] != $colonist_max)
    {
        echo "+ form.colonist_number.value * " . $tkireg->colonist_price . " ";
    }

    echo ";\n";
    echo "  if (form.total_cost.value > $playerinfo[credits])\n";
    echo "  {\n";
    echo "    form.total_cost.value = '" . $langvars['l_no_credits'] . "';\n";
    // echo "    form.total_cost.value = 'You are short '+(form.total_cost.value - $playerinfo[credits]) +' credits';\n";
    echo "  }\n";
    echo "  form.total_cost.length = form.total_cost.value.length;\n";
    echo "\n";
    echo "form.engine_costper.value=change_delta(form.engine_upgrade.value, $playerinfo[engines]);\n";
    echo "form.power_costper.value=change_delta(form.power_upgrade.value, $playerinfo[power]);\n";
    echo "form.computer_costper.value=change_delta(form.computer_upgrade.value, $playerinfo[computer]);\n";
    echo "form.sensors_costper.value=change_delta(form.sensors_upgrade.value, $playerinfo[sensors]);\n";
    echo "form.beams_costper.value=change_delta(form.beams_upgrade.value, $playerinfo[beams]);\n";
    echo "form.armor_costper.value=change_delta(form.armor_upgrade.value, $playerinfo[armor]);\n";
    echo "form.cloak_costper.value=change_delta(form.cloak_upgrade.value, $playerinfo[cloak]);\n";
    echo "form.torp_launchers_costper.value=change_delta(form.torp_launchers_upgrade.value, $playerinfo[torp_launchers]);\n";
    echo "form.hull_costper.value=change_delta(form.hull_upgrade.value, $playerinfo[hull]);\n";
    echo "form.shields_costper.value=change_delta(form.shields_upgrade.value, $playerinfo[shields]);\n";
    echo "}";
    echo "\n// -->\n</script>\n";

    $onblur = "onblur=\"count_total()\"";
    $onfocus = "onfocus=\"count_total()\"";
    $onchange = "onchange=\"count_total()\"";
    $onclick = "onclick=\"count_total()\"";

    echo "<p>\n";
    $langvars['l_creds_to_spend'] = str_replace("[credits]", number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_creds_to_spend']);
    echo $langvars['l_creds_to_spend'] . "<br>\n";
    if ($tkireg->allow_ibank)
    {
        $ibanklink = "\n<a href=ibank.php>" . $langvars['l_ibank_term'] . "</a>";
        $langvars['l_ifyouneedmore'] = str_replace("[ibank]", $ibanklink, $langvars['l_ifyouneedmore']);
        echo $langvars['l_ifyouneedmore'] . "<br>";
    }

    echo "\n";
    echo "<a href=\"bounty.php\">" . $langvars['l_by_placebounty'] . "</a><br>\n";
    echo " <form accept-charset='utf-8' action=port2.php method=post>\n";
    echo "  <table>\n";
    echo "   <tr>\n";
    echo "    <th><strong>" . $langvars['l_device'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_cost'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_current'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_max'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_qty'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_ship_levels'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_cost'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_current'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_upgrade'] . "</strong></th>\n";
    echo "   </tr>\n";
    echo "   <tr>\n";
    echo "    <td>" . $langvars['l_genesis'] . "</td>\n";
    echo "    <td>" . number_format($tkireg->dev_genesis_price, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>" . number_format($playerinfo['dev_genesis'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>";
    if ($playerinfo['dev_genesis'] != $tkireg->max_genesis)
    {
        echo "<a href='#' onClick=\"make_max('dev_genesis_number', $genesis_free);count_total();return false;\">";
        echo number_format($genesis_free, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</a></td>\n";
        echo "    <td><input type=text name=dev_genesis_number size=4 maxlength=4 value=0 $onblur>";
    }
    else
    {
        echo "0</td>\n";
        echo "    <td><input type=text readonly class='portcosts1' name=dev_genesis_number maxlength=10 value=" . $langvars['l_full'] . " " . $onblur . " tabindex='0'>";
    }

    echo "</td>\n";
    echo "    <td>" . $langvars['l_hull'] . "</td>\n";
    echo "    <td><input type=text readonly class='portcosts1' name=hull_costper value='0' tabindex='0' $onblur></td>\n";
    echo "    <td>" . number_format($playerinfo['hull'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>\n       ";
    echo Tki\Ports::dropdown("hull_upgrade", $playerinfo['hull'], $onchange, $tkireg->max_upgrades_devices);
    echo "    </td>\n";
    echo "   </tr>\n";
    echo "   <tr>\n";
    echo "    <td>" . $langvars['l_beacons'] . "</td>\n";
    echo "    <td>" . number_format($tkireg->dev_beacon_price, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>" . number_format($playerinfo['dev_beacon'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>";
    if ($playerinfo['dev_beacon'] != $tkireg->max_beacons)
    {
        echo "<a href='#' onClick=\"make_max('dev_beacon_number', $beacon_free);count_total();return false;\">";
        echo number_format($beacon_free, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</a></td>\n";
        echo "    <td><input type=text name=dev_beacon_number size=4 maxlength=4 value=0 $onblur>";
    }
    else
    {
        echo "0</td>\n";
        echo "    <td><input type=text readonly class='portcosts2' name=dev_beacon_number maxlength=10 value=" . $langvars['l_full'] . " " . $onblur . " tabindex='0'>";
    }

    echo "</td>\n";
    echo "    <td>" . $langvars['l_engines'] . "</td>\n";
    echo "    <td><input type=text readonly class='portcosts2' size=10 name=engine_costper value='0' tabindex='0' $onblur></td>\n";
    echo "    <td>" . number_format($playerinfo['engines'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>\n       ";
    echo Tki\Ports::dropdown("engine_upgrade", $playerinfo['engines'], $onchange, $tkireg->max_upgrades_devices);
    echo "    </td>\n";
    echo "   </tr>\n";
    echo "   <tr>\n";
    echo "    <td>" . $langvars['l_ewd'] . "</td>\n";
    echo "    <td>" . number_format($tkireg->dev_emerwarp_price, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>" . number_format($playerinfo['dev_emerwarp'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>";
    if ($playerinfo['dev_emerwarp'] != $tkireg->max_emerwarp)
    {
        echo "<a href='#' onClick=\"make_max('dev_emerwarp_number', $emerwarp_free);count_total();return false;\">";
        echo number_format($emerwarp_free, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</a></td>\n";
        echo "    <td><input type=text name=dev_emerwarp_number size=4 maxlength=4 value=0 $onblur>";
    }
    else
    {
        echo "0</td>\n";
        echo "    <td><input type=text readonly class='portcosts1' name=dev_emerwarp_number maxlength=10 value=" . $langvars['l_full'] . " " . $onblur . " tabindex='0'>";
    }

    echo "</td>\n";
    echo "    <td>" . $langvars['l_power'] . "</td>\n";
    echo "    <td><input type=text readonly class='portcosts1' name=power_costper value='0' tabindex='0' $onblur></td>\n";
    echo "    <td>" . number_format($playerinfo['power'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>\n       ";
    echo Tki\Ports::dropdown("power_upgrade", $playerinfo['power'], $onchange, $tkireg->max_upgrades_devices);
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td>" . $langvars['l_warpedit'] . "</td>\n";
    echo "    <td>" . number_format($tkireg->dev_warpedit_price, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>" . number_format($playerinfo['dev_warpedit'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>";
    if ($playerinfo['dev_warpedit'] != $tkireg->max_warpedit)
    {
        echo "<a href='#' onClick=\"make_max('dev_warpedit_number', $warpedit_free);count_total();return false;\">";
        echo number_format($warpedit_free, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</a></td>\n";
        echo "    <td><input type=text name=dev_warpedit_number size=4 maxlength=4 value=0 $onblur>";
    }
    else
    {
        echo "0</td>\n";
        echo "    <td><input type=text readonly class='portcosts2' name=dev_warpedit_number maxlength=10 value=" . $langvars['l_full'] . " " . $onblur . " tabindex='0'>";
    }

    echo "</td>\n";

    echo "    <td>" . $langvars['l_computer'] . "</td>\n";
    echo "    <td><input type=text readonly class='portcosts2' name=computer_costper value='0' tabindex='0' $onblur></td>\n";
    echo "    <td>" . number_format($playerinfo['computer'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>\n       ";
    echo Tki\Ports::dropdown("computer_upgrade", $playerinfo['computer'], $onchange, $tkireg->max_upgrades_devices);
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td>&nbsp;</td>\n";
    echo "    <td>&nbsp;</td>\n";
    echo "    <td>&nbsp;</td>\n";
    echo "    <td>&nbsp;</td>\n";
    echo "    <td>&nbsp;</td>\n";
    echo "    <td>" . $langvars['l_sensors'] . "</td>\n";
    echo "    <td><input type=text readonly class='portcosts1' name=sensors_costper value='0' tabindex='0' $onblur></td>\n";
    echo "    <td>" . number_format($playerinfo['sensors'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>\n       ";
    echo Tki\Ports::dropdown("sensors_upgrade", $playerinfo['sensors'], $onchange, $tkireg->max_upgrades_devices);
    echo "    </td>\n";
    echo "  </tr>";
    echo "  <tr>\n";
    echo "    <td>" . $langvars['l_deflect'] . "</td>\n";
    echo "    <td>" . number_format($tkireg->dev_minedeflector_price, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>" . number_format($playerinfo['dev_minedeflector'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>" . $langvars['l_unlimited'] . "</td>\n";
    echo "    <td><input type=text name=dev_minedeflector_number size=4 maxlength=10 value=0 $onblur></td>\n";
    echo "    <td>" . $langvars['l_beams'] . "</td>\n";
    echo "    <td><input type=text readonly class='portcosts2' name=beams_costper value='0' tabindex='0' $onblur></td>";
    echo "    <td>" . number_format($playerinfo['beams'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>\n       ";
    echo Tki\Ports::dropdown("beams_upgrade", $playerinfo['beams'], $onchange, $tkireg->max_upgrades_devices);
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td>" . $langvars['l_escape_pod'] . "</td>\n";
    echo "    <td>" . number_format($tkireg->dev_escapepod_price, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    if ($playerinfo['dev_escapepod'] == "N")
    {
        echo "    <td>" . $langvars['l_none'] . "</td>\n";
        echo "    <td>&nbsp;</td>\n";
        echo "    <td><input type=checkbox name=escapepod_purchase value=1 $onclick></td>\n";
    }
    else
    {
        echo "    <td>" . $langvars['l_equipped'] . "</td>\n";
        echo "    <td></td>\n";
        echo "    <td>" . $langvars['l_n_a'] . "</td>\n";
    }

    echo "    <td>" . $langvars['l_armor'] . "</td>\n";
    echo "    <td><input type=text readonly class='portcosts1' name=armor_costper value='0' tabindex='0' $onblur></td>\n";
    echo "    <td>" . number_format($playerinfo['armor'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>\n       ";
    echo Tki\Ports::dropdown("armor_upgrade", $playerinfo['armor'], $onchange, $tkireg->max_upgrades_devices);
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td>" . $langvars['l_fuel_scoop'] . "</td>\n";
    echo "    <td>" . number_format($tkireg->dev_fuelscoop_price, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    if ($playerinfo['dev_fuelscoop'] == "N")
    {
        echo "    <td>" . $langvars['l_none'] . "</td>\n";
        echo "    <td>&nbsp;</td>\n";
        echo "    <td><input type=checkbox name=fuelscoop_purchase value=1 $onclick></td>\n";
    }
    else
    {
        echo "    <td>" . $langvars['l_equipped'] . "</td>\n";
        echo "    <td></td>\n";
        echo "    <td>" . $langvars['l_n_a'] . "</td>\n";
    }

    echo "    <td>" . $langvars['l_cloak'] . "</td>\n";
    echo "    <td><input type=text readonly class='portcosts2' name=cloak_costper value='0' tabindex='0' $onblur $onfocus></td>\n";
    echo "    <td>" . number_format($playerinfo['cloak'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>\n       ";
    echo Tki\Ports::dropdown("cloak_upgrade", $playerinfo['cloak'], $onchange, $tkireg->max_upgrades_devices);
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td>" . $langvars['l_lssd'] . "</td>\n";
    echo "    <td>" . number_format($tkireg->dev_lssd_price, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    if ($playerinfo['dev_lssd'] == "N")
    {
        echo "    <td>" . $langvars['l_none'] . "</td>\n";
        echo "    <td>&nbsp;</td>\n";
        echo "    <td><input type=checkbox name=lssd_purchase value=1 $onclick></td>\n";
    }
    else
    {
        echo "    <td>" . $langvars['l_equipped'] . "</td>\n";
        echo "    <td></td>\n";
        echo "    <td>" . $langvars['l_n_a'] . "</td>\n";
    }

    echo "    <td>" . $langvars['l_torp_launch'] . "</td>\n";
    echo "    <td><input type=text readonly class='portcosts1' name=torp_launchers_costper value='0' tabindex='0' $onblur></td>\n";
    echo "    <td>" . number_format($playerinfo['torp_launchers'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>\n       ";
    echo Tki\Ports::dropdown("torp_launchers_upgrade", $playerinfo['torp_launchers'], $onchange, $tkireg->max_upgrades_devices);
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td>&nbsp;</td>\n";
    echo "    <td>&nbsp;</td>\n";
    echo "    <td>&nbsp;</td>\n";
    echo "    <td>&nbsp;</td>\n";
    echo "    <td>&nbsp;</td>\n";
    echo "    <td>" . $langvars['l_shields'] . "</td>\n";
    echo "    <td><input type=text readonly class='portcosts2' name=shields_costper value='0' tabindex='0' $onblur></td>\n";
    echo "    <td>" . number_format($playerinfo['shields'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>\n       ";
    echo Tki\Ports::dropdown("shields_upgrade", $playerinfo['shields'], $onchange, $tkireg->max_upgrades_devices);
    echo "    </td>\n";
    echo "  </tr>\n";
    echo " </table>\n";
    echo " <br>\n";
    echo " <table>\n";
    echo "  <tr>\n";
    echo "    <th><strong>" . $langvars['l_item'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_cost'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_current'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_max'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_qty'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_item'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_cost'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_current'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_max'] . "</strong></th>\n";
    echo "    <th><strong>" . $langvars['l_qty'] . "</strong></th>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td>" . $langvars['l_fighters'] . "</td>\n";
    echo "    <td>" . number_format($tkireg->fighter_price, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>" . number_format($playerinfo['ship_fighters'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " / " . number_format($fighter_max, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>";
    if ($playerinfo['ship_fighters'] != $fighter_max)
    {
        echo "<a href='#' onClick=\"make_max('fighter_number', $fighter_free);count_total();return false;\" $onblur>" . number_format($fighter_free, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</a></td>\n";
        echo "    <td><input type=text name=fighter_number size=6 maxlength=10 value=0 $onblur>";
    }
    else
    {
        echo "0<td><input type=text readonly class='portcosts1' name=fighter_number maxlength=10 value=" . $langvars['l_full'] . " " . $onblur . " tabindex='0'>";
    }

    echo "    </td>\n";
    echo "    <td>" . $langvars['l_torps'] . "</td>\n";
    echo "    <td>" . number_format($tkireg->torpedo_price, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>" . number_format($playerinfo['torps'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " / " . number_format($torpedo_max, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>";
    if ($playerinfo['torps'] != $torpedo_max)
    {
        echo "<a href='#' onClick=\"make_max('torpedo_number', $torpedo_free);count_total();return false;\" $onblur>" . number_format($torpedo_free, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</a></td>\n";
        echo "    <td><input type=text name=torpedo_number size=6 maxlength=10 value=0 $onblur>";
    }
    else
    {
        echo "0<td><input type=text readonly class='portcosts1' name=torpedo_number maxlength=10 value=" . $langvars['l_full'] . " " . $onblur . " tabindex='0'>";
    }

    echo "</td>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td>" . $langvars['l_armorpts'] . "</td>\n";
    echo "    <td>" . number_format($tkireg->armor_price, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>" . number_format($playerinfo['armor_pts'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " / " . number_format($armor_max, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>";
    if ($playerinfo['armor_pts'] != $armor_max)
    {
        echo "<a href='#' onClick=\"make_max('armor_number', $armor_free);count_total();return false;\" $onblur>" . number_format($armor_free, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</a></td>\n";
        echo "    <td><input type=text name=armor_number size=6 maxlength=10 value=0 $onblur>";
    }
    else
    {
        echo "0<td><input type=text readonly class='portcosts2' name=armor_number maxlength=10 value=" . $langvars['l_full'] . " tabindex='0' " . $onblur . ">";
    }

    echo "</td>\n";
    echo "    <td>" . $langvars['l_colonists'] . "</td>\n";
    echo "    <td>" . number_format($tkireg->colonist_price, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>" . number_format($playerinfo['ship_colonists'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " / " . number_format($colonist_max, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>\n";
    echo "    <td>";
    if ($playerinfo['ship_colonists'] != $colonist_max)
    {
        echo "<a href='#' onClick=\"make_max('colonist_number', $colonist_free);count_total();return false;\" $onblur>" . number_format($colonist_free, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</a></td>\n";
        echo "    <td><input type=text name=colonist_number size=6 maxlength=10 value=0 $onblur>";
    }
    else
    {
        echo "0<td><input type=text readonly class='portcosts2' name=colonist_number maxlength=10 value=" . $langvars['l_full'] . " tabindex='0' " . $onblur . ">";
    }

    echo "    </td>\n";
    echo "  </tr>\n";
    echo " </table><br>\n";
    echo " <table>\n";
    echo "  <tr style=\"background-color: transparent;\">\n";
    echo "    <td><input type=submit value=" . $langvars['l_buy'] . " " . $onclick . "></td>\n";
    echo "    <td style=\"text-align:right\">" . $langvars['l_totalcost'] . ": <input type=text style=\"text-align:right\" name=total_cost size=22 value=0 $onfocus $onblur $onchange $onclick></td>\n";
    echo "  </tr>\n";
    echo " </table>\n";
    echo "</form><br>\n";
    echo $langvars['l_would_dump'] . " <a href=dump.php>" . $langvars['l_here'] . "</a>.\n";
}
else
{
    echo $langvars['l_noport'] . "!\n";
}

echo "\n";
echo "<br><br>\n";
Tki\Text::gotoMain($pdo_db, $lang);
echo "\n";

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
