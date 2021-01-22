<?php declare(strict_types = 1);
/**
 * port2.php from The Kabal Invasion.
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

$title = $langvars['l_title_port'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'device',
                                'footer', 'insignias', 'news', 'port',
                                'port2', 'regional', 'report', 'universal'));
// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

// Get sectorinfo from database
$sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db); // Build a sector gateway object to handle the SQL calls
$sectorinfo = $sectors_gateway->selectSectorInfo($playerinfo['sector']);

// Get zoneinfo from database
$zones_gateway = new \Tki\Zones\ZonesGateway($pdo_db); // Build a zone gateway object to handle the SQL calls
$zoneinfo = $zones_gateway->selectZoneInfo($sectorinfo['zone_id']);

if (!empty($zoneinfo))
{
    if ($zoneinfo['allow_trade'] == 'N')
    {
        $title = $langvars['l_no_trade'];
        echo "<h1>" . $title . "</h1>\n";
        echo $langvars['l_no_trade_info'] . "<p>";
        Tki\Text::gotoMain($pdo_db, $lang);

        $footer = new Tki\Footer();
        $footer->display($pdo_db, $lang, $tkireg, $template);
        die();
    }
    elseif ($zoneinfo['allow_trade'] == 'L')
    {
        if ($zoneinfo['team_zone'] == 'N')
        {
            $players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
            $ownerinfo = $players_gateway->selectPlayerInfoById($zoneinfo['owner']);

            if ($playerinfo['ship_id'] != $zoneinfo['owner'] && $playerinfo['team'] == 0 || $playerinfo['team'] != $ownerinfo['team'])
            {
                $title = $langvars['l_no_trade'];
                echo "<h1>" . $title . "</h1>\n";
                echo $langvars['l_no_trade_out'] . "<p>";
                Tki\Text::gotoMain($pdo_db, $lang);

                $footer = new Tki\Footer();
                $footer->display($pdo_db, $lang, $tkireg, $template);
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
                $footer->display($pdo_db, $lang, $tkireg, $template);
                die();
            }
        }
    }
}

echo "<h1>" . $title . "</h1>\n";

$color_red = "red";
$color_green = "#0f0"; // Light green
$trade_deficit = $langvars['l_cost'] . " : ";
$trade_benefit = $langvars['l_profit'] . " : ";

$admin_log = new Tki\AdminLog();
if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_trade_turnneed'] . "<br><br>";
}
else
{
    if ($sectorinfo['port_type'] == "special")
    {
        // Kami multi-browser window upgrade fix
        if (array_key_exists('port_shopping', $_SESSION) === false || $_SESSION['port_shopping'] !== true)
        {
            echo "<META HTTP-EQUIV='Refresh' CONTENT='2; URL=main.php'>";
            echo "<div style='color:#f00; font-size:18px;'>" . $langvars['l_port2_already'] . "</div>\n";
            echo "<br>\n";
            echo "<div style='color:#fff; font-size:12px;'>" . $langvars['l_port2_redirect'] . "</div>\n";
            echo "<br>\n";

            Tki\Text::gotoMain($pdo_db, $lang);

            $footer = new Tki\Footer();
            $footer->display($pdo_db, $lang, $tkireg, $template);
            die();
        }

        unset($_SESSION['port_shopping']);
        if (Tki\Loan::isPending($pdo_db, $tkireg))
        {
            echo $langvars['l_port_loannotrade'] . "<p>";
            echo "<a href=ibank.php>" . $langvars['l_ibank_term'] . "</a><p>";
            Tki\Text::gotoMain($pdo_db, $lang);

            $footer = new Tki\Footer();
            $footer->display($pdo_db, $lang, $tkireg, $template);
            die();
        }

        // Clear variables that are not selected in the form, and filter them to be only the correct variable type (Int, float, and bool)
        $hull_upgrade               = (int) filter_input(INPUT_POST, 'hull_upgrade', FILTER_SANITIZE_NUMBER_INT);
        $engine_upgrade             = (int) filter_input(INPUT_POST, 'engine_upgrade', FILTER_SANITIZE_NUMBER_INT);
        $power_upgrade              = (int) filter_input(INPUT_POST, 'power_upgrade', FILTER_SANITIZE_NUMBER_INT);
        $computer_upgrade           = (int) filter_input(INPUT_POST, 'computer_upgrade', FILTER_SANITIZE_NUMBER_INT);
        $sensors_upgrade            = (int) filter_input(INPUT_POST, 'sensors_upgrade', FILTER_SANITIZE_NUMBER_INT);
        $beams_upgrade              = (int) filter_input(INPUT_POST, 'beams_upgrade', FILTER_SANITIZE_NUMBER_INT);
        $armor_upgrade              = (int) filter_input(INPUT_POST, 'armor_upgrade', FILTER_SANITIZE_NUMBER_INT);
        $cloak_upgrade              = (int) filter_input(INPUT_POST, 'cloak_upgrade', FILTER_SANITIZE_NUMBER_INT);
        $torp_launchers_upgrade     = (int) filter_input(INPUT_POST, 'torp_launchers_upgrade', FILTER_SANITIZE_NUMBER_INT);
        $shields_upgrade            = (int) filter_input(INPUT_POST, 'shields_upgrade', FILTER_SANITIZE_NUMBER_INT);

        $fighter_number             = (int) filter_input(INPUT_POST, 'fighter_number', FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_ALLOW_THOUSAND);
        $torpedo_number             = (int) filter_input(INPUT_POST, 'torpedo_number', FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_ALLOW_THOUSAND);
        $armor_number               = (int) filter_input(INPUT_POST, 'armor_number', FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_ALLOW_THOUSAND);
        $colonist_number            = (int) filter_input(INPUT_POST, 'colonist_number', FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_ALLOW_THOUSAND);
        $dev_genesis_number         = (int) filter_input(INPUT_POST, 'dev_genesis_number', FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_ALLOW_THOUSAND);
        $dev_beacon_number          = (int) filter_input(INPUT_POST, 'dev_beacon_number', FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_ALLOW_THOUSAND);
        $dev_emerwarp_number        = (int) filter_input(INPUT_POST, 'dev_emerwarp_number', FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_ALLOW_THOUSAND);
        $dev_warpedit_number        = (int) filter_input(INPUT_POST, 'dev_warpedit_number', FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_ALLOW_THOUSAND);
        $dev_minedeflector_number   = (int) filter_input(INPUT_POST, 'dev_minedeflector_number', FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_ALLOW_THOUSAND);

        $escapepod_purchase         = filter_input(INPUT_POST, 'escapepod_purchase', FILTER_VALIDATE_BOOL);
        if ($escapepod_purchase !== true)
        {
            $escapepod_purchase = false;
        }

        $fuelscoop_purchase         = filter_input(INPUT_POST, 'fuelscoop_purchase', FILTER_VALIDATE_BOOL);
        if ($fuelscoop_purchase !== true)
        {
            $fuelscoop_purchase = false;
        }

        $lssd_purchase              = filter_input(INPUT_POST, 'lssd_purchase', FILTER_VALIDATE_BOOL);
        if ($lssd_purchase !== true)
        {
            $lssd_purchase = false;
        }

        $hull_upgrade_cost = 0;
        if ($hull_upgrade > $playerinfo['hull'])
        {
            $hull_upgrade_cost = Tki\Ports::phpChangeDelta($hull_upgrade, $playerinfo['hull'], (int) $tkireg->upgrade_cost);
        }

        $engine_upgrade_cost = 0;
        if ($engine_upgrade > $playerinfo['engines'])
        {
            $engine_upgrade_cost = Tki\Ports::phpChangeDelta($engine_upgrade, $playerinfo['engines'], $tkireg->upgrade_cost);
        }

        $power_upgrade_cost = 0;
        if ($power_upgrade > $playerinfo['power'])
        {
            $power_upgrade_cost = Tki\Ports::phpChangeDelta($power_upgrade, $playerinfo['power'], $tkireg->upgrade_cost);
        }

        $computer_upgrade_cost = 0;
        if ($computer_upgrade > $playerinfo['computer'])
        {
            $computer_upgrade_cost = Tki\Ports::phpChangeDelta($computer_upgrade, $playerinfo['computer'], $tkireg->upgrade_cost);
        }

        $sensors_upgrade_cost = 0;
        if ($sensors_upgrade > $playerinfo['sensors'])
        {
            $sensors_upgrade_cost = Tki\Ports::phpChangeDelta($sensors_upgrade, $playerinfo['sensors'], $tkireg->upgrade_cost);
        }

        $beams_upgrade_cost = 0;
        if ($beams_upgrade > $playerinfo['beams'])
        {
            $beams_upgrade_cost = Tki\Ports::phpChangeDelta($beams_upgrade, $playerinfo['beams'], $tkireg->upgrade_cost);
        }

        $armor_upgrade_cost = 0;
        if ($armor_upgrade > $playerinfo['armor'])
        {
            $armor_upgrade_cost = Tki\Ports::phpChangeDelta($armor_upgrade, $playerinfo['armor'], $tkireg->upgrade_cost);
        }

        $cloak_upgrade_cost = 0;
        if ($cloak_upgrade > $playerinfo['cloak'])
        {
            $cloak_upgrade_cost = Tki\Ports::phpChangeDelta($cloak_upgrade, $playerinfo['cloak'], $tkireg->upgrade_cost);
        }

        $torp_launchers_upgrade_cost = 0;
        if ($torp_launchers_upgrade > $playerinfo['torp_launchers'])
        {
            $torp_launchers_upgrade_cost = Tki\Ports::phpChangeDelta($torp_launchers_upgrade, $playerinfo['torp_launchers'], $tkireg->upgrade_cost);
        }

        $shields_upgrade_cost = 0;
        if ($shields_upgrade > $playerinfo['shields'])
        {
            $shields_upgrade_cost = Tki\Ports::phpChangeDelta($shields_upgrade, $playerinfo['shields'], $tkireg->upgrade_cost);
        }

        if ($fighter_number < 0)
        {
            $fighter_number = 0;
        }

        $fighter_number = round(abs($fighter_number));
        $fighter_max = Tki\CalcLevels::abstractLevels($playerinfo['computer'], $tkireg) - $playerinfo['ship_fighters'];
        if ($fighter_max < 0)
        {
            $fighter_max = 0;
        }

        if ($fighter_number > $fighter_max)
        {
            $fighter_number = $fighter_max;
        }

        $fighter_cost = $fighter_number * $tkireg->fighter_price;
        if ($torpedo_number < 0)
        {
            $torpedo_number = 0;
        }

        $torpedo_number = round(abs($torpedo_number));
        $torpedo_max = Tki\CalcLevels::abstractLevels($playerinfo['torp_launchers'], $tkireg) - $playerinfo['torps'];
        if ($torpedo_max < 0)
        {
            $torpedo_max = 0;
        }

        if ($torpedo_number > $torpedo_max)
        {
            $torpedo_number = $torpedo_max;
        }

        $torpedo_cost = $torpedo_number * $tkireg->torpedo_price;
        if ($armor_number < 0)
        {
            $armor_number = 0;
        }

        $armor_number = round(abs($armor_number));
        $armor_max = Tki\CalcLevels::abstractLevels($playerinfo['armor'], $tkireg) - $playerinfo['armor_pts'];
        if ($armor_max < 0)
        {
            $armor_max = 0;
        }

        if ($armor_number > $armor_max)
        {
            $armor_number = $armor_max;
        }

        $armor_cost = $armor_number * $tkireg->armor_price;
        if ($colonist_number < 0)
        {
            $colonist_number = 0;
        }

        $colonist_number = round(abs($colonist_number));
        $colonist_max    = Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];

        if ($colonist_max < 0)
        {
            $colonist_max = 0;
        }

        if ($colonist_number > $colonist_max)
        {
            $colonist_number = $colonist_max;
        }

        $colonist_cost = $colonist_number * $tkireg->colonist_price;

        $dev_genesis_number = min(round(abs($dev_genesis_number)), $tkireg->max_genesis - $playerinfo['dev_genesis']);
        $dev_genesis_cost = $dev_genesis_number * $tkireg->dev_genesis_price;

        $dev_beacon_number = min(round(abs($dev_beacon_number)), $tkireg->max_beacons - $playerinfo['dev_beacon']);
        $dev_beacon_cost = $dev_beacon_number * $tkireg->dev_beacon_price;

        $dev_emerwarp_number = min(round(abs($dev_emerwarp_number)), $tkireg->max_emerwarp - $playerinfo['dev_emerwarp']);
        $dev_emerwarp_cost = $dev_emerwarp_number * $tkireg->dev_emerwarp_price;

        $dev_warpedit_number = min(round(abs($dev_warpedit_number)), $tkireg->max_warpedit - $playerinfo['dev_warpedit']);
        $dev_warpedit_cost = $dev_warpedit_number * $tkireg->dev_warpedit_price;

        $dev_minedeflector_number = round(abs($dev_minedeflector_number));
        $dev_minedeflector_cost = $dev_minedeflector_number * $tkireg->dev_minedeflector_price;

        $dev_escapepod_cost = 0;
        $dev_fuelscoop_cost = 0;
        $dev_lssd_cost = 0;

        if (($escapepod_purchase) && ($playerinfo['dev_escapepod'] != 'Y'))
        {
            $dev_escapepod_cost = $tkireg->dev_escapepod_price;
        }

        if (($fuelscoop_purchase) && ($playerinfo['dev_fuelscoop'] != 'Y'))
        {
            $dev_fuelscoop_cost = $tkireg->dev_fuelscoop_price;
        }

        if (($lssd_purchase) && ($playerinfo['dev_lssd'] != 'Y'))
        {
            $dev_lssd_cost = $tkireg->dev_lssd_price;
        }

        $total_cost = $hull_upgrade_cost + $engine_upgrade_cost + $power_upgrade_cost + $computer_upgrade_cost + $sensors_upgrade_cost + $beams_upgrade_cost + $armor_upgrade_cost + $cloak_upgrade_cost + $torp_launchers_upgrade_cost + $fighter_cost + $torpedo_cost + $armor_cost + $colonist_cost + $dev_genesis_cost + $dev_beacon_cost + $dev_emerwarp_cost + $dev_warpedit_cost + $dev_minedeflector_cost + $dev_escapepod_cost + $dev_fuelscoop_cost + $dev_lssd_cost + $shields_upgrade_cost;
        if ($total_cost > $playerinfo['credits'])
        {
            echo "You do not have enough credits for this transaction.  The total cost is " . number_format($total_cost, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " credits and you only have " . number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " credits.<br><br>";
            $langvars['l_port2_return'] = str_replace("[here]", "<a href='port.php'>" . $langvars['l_here'] . "</a>", $langvars['l_port2_return']);
            echo $langvars['l_port2_return'] . "<br>";
        }
        else
        {
            $trade_credits = number_format(abs($total_cost), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
            echo "<table border=2 cellspacing=2 cellpadding=2 bgcolor=#400040 width=600 align=center>
                    <tr>
                        <td colspan=99 align=center bgcolor=#300030><font size=3 color=white><strong>" . $langvars['l_trade_result'] . "</strong></font></td>
                    </tr>
                    <tr>
                        <td colspan=99 align=center><strong><font color=red>" . $langvars['l_cost'] . " : " . $trade_credits . " " . $langvars['l_credits'] . "</font></strong></td>
                    </tr>";

            //  Total cost is " . number_format(abs($total_cost), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " credits.<br><br>";
            $query = "UPDATE {$old_db->prefix}ships SET credits=credits-$total_cost";
            if ($hull_upgrade > $playerinfo['hull'])
            {
                $tempvar = 0;
                $tempvar = Tki\Ports::phpTrueDelta($hull_upgrade, $playerinfo['hull']);
                $query = $query . ", hull=hull + $tempvar";
                Tki\Ports::buildOneCol($langvars['l_hull'] . " " . $langvars['l_trade_upgraded'] . " " . $hull_upgrade);
            }

            if ($engine_upgrade > $playerinfo['engines'])
            {
                $tempvar = 0;
                $tempvar = Tki\Ports::phpTrueDelta($engine_upgrade, $playerinfo['engines']);
                $query = $query . ", engines=engines + $tempvar";
                Tki\Ports::buildOneCol($langvars['l_engines'] . " " . $langvars['l_trade_upgraded'] . " " . $engine_upgrade);
            }

            if ($power_upgrade > $playerinfo['power'])
            {
                $tempvar = 0;
                $tempvar = Tki\Ports::phpTrueDelta($power_upgrade, $playerinfo['power']);
                $query = $query . ", power=power + $tempvar";
                Tki\Ports::buildOneCol($langvars['l_power'] . " " . $langvars['l_trade_upgraded'] . " " . $power_upgrade);
            }

            if ($computer_upgrade > $playerinfo['computer'])
            {
                $tempvar = 0;
                $tempvar = Tki\Ports::phpTrueDelta($computer_upgrade, $playerinfo['computer']);
                $query = $query . ", computer=computer + $tempvar";
                Tki\Ports::buildOneCol($langvars['l_computer'] . " " . $langvars['l_trade_upgraded'] . " " . $computer_upgrade);
            }

            if ($sensors_upgrade > $playerinfo['sensors'])
            {
                $tempvar = 0;
                $tempvar = Tki\Ports::phpTrueDelta($sensors_upgrade, $playerinfo['sensors']);
                $query = $query . ", sensors=sensors + $tempvar";
                Tki\Ports::buildOneCol($langvars['l_sensors'] . " " . $langvars['l_trade_upgraded'] . " " . $sensors_upgrade);
            }

            if ($beams_upgrade > $playerinfo['beams'])
            {
                $tempvar = 0;
                $tempvar = Tki\Ports::phpTrueDelta($beams_upgrade, $playerinfo['beams']);
                $query = $query . ", beams=beams + $tempvar";
                Tki\Ports::buildOneCol($langvars['l_beams'] . " " . $langvars['l_trade_upgraded'] . " " . $beams_upgrade);
            }

            if ($armor_upgrade > $playerinfo['armor'])
            {
                $tempvar = 0;
                $tempvar = Tki\Ports::phpTrueDelta($armor_upgrade, $playerinfo['armor']);
                $query = $query . ", armor=armor + $tempvar";
                Tki\Ports::buildOneCol($langvars['l_armor'] . " " .  $langvars['l_trade_upgraded'] . " " . $armor_upgrade);
            }

            if ($cloak_upgrade > $playerinfo['cloak'])
            {
                $tempvar = 0;
                $tempvar = Tki\Ports::phpTrueDelta($cloak_upgrade, $playerinfo['cloak']);
                $query = $query . ", cloak=cloak + $tempvar";
                Tki\Ports::buildOneCol($langvars['l_cloak'] . " " . $langvars['l_trade_upgraded'] . " " . $cloak_upgrade);
            }

            if ($torp_launchers_upgrade > $playerinfo['torp_launchers'])
            {
                $tempvar = 0;
                $tempvar = Tki\Ports::phpTrueDelta($torp_launchers_upgrade, $playerinfo['torp_launchers']);
                $query = $query . ", torp_launchers=torp_launchers + $tempvar";
                Tki\Ports::buildOneCol($langvars['l_torp_launch'] . " " . $langvars['l_trade_upgraded'] . " " . $torp_launchers_upgrade);
            }

            if ($shields_upgrade > $playerinfo['shields'])
            {
                $tempvar = 0;
                $tempvar = Tki\Ports::phpTrueDelta($shields_upgrade, $playerinfo['shields']);
                $query = $query . ", shields=shields + $tempvar";
                Tki\Ports::buildOneCol($langvars['l_shields'] . " " . $langvars['l_trade_upgraded'] . " " . $shields_upgrade);
            }

            if ($fighter_number > 0)
            {
                $query = $query . ", ship_fighters = ship_fighters + $fighter_number";
                Tki\Ports::buildTwoCol($langvars['l_fighters'] . " " .  $langvars['l_trade_added'] . ":", (string) $fighter_number, "left", "right");
            }

            if ($torpedo_number > 0)
            {
                $query = $query . ", torps=torps + $torpedo_number";
                Tki\Ports::buildTwoCol($langvars['l_torps'] . " " . $langvars['l_trade_added'] . ":", (string) $torpedo_number, "left", "right");
            }

            if ($armor_number > 0)
            {
                $query = $query . ", armor_pts=armor_pts + $armor_number";
                Tki\Ports::buildTwoCol($langvars['l_armorpts'] . " " . $langvars['l_trade_added'] . ":", (string) $armor_number, "left", "right");
            }

            if ($colonist_number > 0)
            {
                $query = $query . ", ship_colonists = ship_colonists + $colonist_number";
                Tki\Ports::buildTwoCol($langvars['l_colonists'] . " " .  $langvars['l_trade_added'] . ":", (string) $colonist_number, "left", "right");
            }

            if ($dev_genesis_number > 0)
            {
                $query = $query . ", dev_genesis = dev_genesis + $dev_genesis_number";
                Tki\Ports::buildTwoCol($langvars['l_genesis'] . " " . $langvars['l_trade_added'] . ":", (string) $dev_genesis_number, "left", "right");
            }

            if ($dev_beacon_number > 0)
            {
                $query = $query . ", dev_beacon = dev_beacon + $dev_beacon_number";
                Tki\Ports::buildTwoCol($langvars['l_beacons'] . " " . $langvars['l_trade_added'] . ":", (string) $dev_beacon_number, "left", "right");
            }

            if ($dev_emerwarp_number > 0)
            {
                $query = $query . ", dev_emerwarp = dev_emerwarp + $dev_emerwarp_number";
                Tki\Ports::buildTwoCol($langvars['l_ewd'] . " " .  $langvars['l_trade_added'] . ":", (string) $dev_emerwarp_number, "left", "right");
            }

            if ($dev_warpedit_number > 0)
            {
                $query = $query . ", dev_warpedit = dev_warpedit + $dev_warpedit_number";
                Tki\Ports::buildTwoCol($langvars['l_warpedit'] . " " . $langvars['l_trade_added'] . ":", (string) $dev_warpedit_number, "left", "right");
            }

            if ($dev_minedeflector_number > 0)
            {
                $query = $query . ", dev_minedeflector = dev_minedeflector + $dev_minedeflector_number";
                Tki\Ports::buildTwoCol($langvars['l_deflect'] . " " . $langvars['l_trade_added'] . ":", (string) $dev_minedeflector_number, "left", "right");
            }

            if (($escapepod_purchase) && ($playerinfo['dev_escapepod'] != 'Y'))
            {
                $query = $query . ", dev_escapepod='Y'";
                Tki\Ports::buildOneCol($langvars['l_escape_pod'] . " " .  $langvars['l_trade_installed']);
            }

            if (($fuelscoop_purchase) && ($playerinfo['dev_fuelscoop'] != 'Y'))
            {
                $query = $query . ", dev_fuelscoop='Y'";
                Tki\Ports::buildOneCol($langvars['l_fuel_scoop'] . " " . $langvars['l_trade_installed']);
            }

            if (($lssd_purchase) && ($playerinfo['dev_lssd'] != 'Y'))
            {
                $query = $query . ", dev_lssd='Y'";
                Tki\Ports::buildOneCol($langvars['l_lssd'] . " " .  $langvars['l_trade_installed']);
            }

            $query = $query . ", turns = turns - 1, turns_used = turns_used + 1 WHERE ship_id = " . $playerinfo['ship_id'];
            $purchase = $old_db->Execute("$query");
            Tki\Db::logDbErrors($pdo_db, $purchase, __LINE__, __FILE__);

            $hull_upgrade = 0;
            echo "</table>";

            if ((Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists']) < 0)
            {
                // Tki\Ports::buildTwoCol("<span style='color:#f00;'>Detected Illegal Cargo</span>", "<span style='color:#0f0;'>Fixed</span>", "left", "right");
                echo "<div style='font-size:16px; color:#fff;'><br>[<span style='color:#0f0;'>" . $langvars['l_port2_border_patrol'] . "</span>]<br>\n";
                echo $langvars['l_port2_halt_scan'] . "<br>\n";
                echo "<span style='color:#f00; font-weight:bold;'>" . $langvars['l_port2_detected_illegal'] . "</span>\n";
                echo "</div>\n";
                $resx = $old_db->Execute("UPDATE {$old_db->prefix}ships SET ship_ore=0, ship_organics=0, ship_goods=0, ship_energy=0, ship_colonists =0 WHERE ship_id = ? LIMIT 1;", array($playerinfo['ship_id']));
                Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                $admin_log->writeLog($pdo_db, 5001, "Detected illegal cargo on shipID: {$playerinfo['ship_id']}");
            }
        }
    }
    elseif ($sectorinfo['port_type'] != "none")
    {
        $price_array = array();

        // Detect if this variable exists, and filter it. Returns false if anything wasn't right.
        $trade_ore = null;
        $trade_ore = (int) filter_input(INPUT_POST, 'trade_ore', FILTER_SANITIZE_NUMBER_INT);
        if ($trade_ore === 0)
        {
            $trade_ore = false;
        }

        // Detect if this variable exists, and filter it. Returns false if anything wasn't right.
        $trade_organics = null;
        $trade_organics = (int) filter_input(INPUT_POST, 'trade_organics', FILTER_SANITIZE_NUMBER_INT);
        if ($trade_organics === 0)
        {
            $trade_organics = false;
        }

        // Detect if this variable exists, and filter it. Returns false if anything wasn't right.
        $trade_goods = null;
        $trade_goods = (int) filter_input(INPUT_POST, 'trade_goods', FILTER_SANITIZE_NUMBER_INT);
        if ($trade_goods === 0)
        {
            $trade_goods = false;
        }

        // Detect if this variable exists, and filter it. Returns false if anything wasn't right.
        $trade_energy = null;
        $trade_energy = (int) filter_input(INPUT_POST, 'trade_energy', FILTER_SANITIZE_NUMBER_INT);
        if ($trade_energy === 0)
        {
            $trade_energy = false;
        }

        $trade_ore = (int) round(abs((int) $trade_ore), 0);
        $trade_organics = (int) round(abs((int) $trade_organics), 0);
        $trade_goods = (int) round(abs((int) $trade_goods), 0);
        $trade_energy = (int) round(abs((int) $trade_energy), 0);

        $trade_ore = Tki\Ports::trade($tkireg->ore_price, $tkireg->ore_delta, $sectorinfo['port_ore'], $tkireg->ore_limit, $tkireg->inventory_factor, "ore", $trade_ore, $price_array, $sectorinfo);
        $trade_organics = Tki\Ports::trade($tkireg->organics_price, $tkireg->organics_delta, $sectorinfo['port_organics'], $tkireg->organics_limit, $tkireg->inventory_factor, "organics", $trade_organics, $price_array, $sectorinfo);
        $trade_goods = Tki\Ports::trade($tkireg->goods_price, $tkireg->goods_delta, $sectorinfo['port_goods'], $tkireg->goods_limit, $tkireg->inventory_factor, "goods", $trade_goods, $price_array, $sectorinfo);
        $trade_energy = Tki\Ports::trade($tkireg->energy_price, $tkireg->energy_delta, $sectorinfo['port_energy'], $tkireg->energy_limit, $tkireg->inventory_factor, "energy", $trade_energy, $price_array, $sectorinfo);

        // $tkireg->ore_price       =  $price_array['ore'];
        // $tkireg->organics_price  =  $price_array['organics'];
        // $tkireg->goods_price     =  $price_array['goods'];
        // $tkireg->energy_price    =  $price_array['energy'];

        $cargo_exchanged = $trade_ore + $trade_organics + $trade_goods;

        $free_holds = Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
        $free_power = Tki\CalcLevels::energy($playerinfo['power'], $tkireg) - $playerinfo['ship_energy'];
        $total_cost = $trade_ore * $tkireg->ore_price + $trade_organics * $tkireg->organics_price + $trade_goods * $tkireg->goods_price + $trade_energy * $tkireg->energy_price;

        // Debug info
        // echo "$trade_ore * $tkireg->ore_price + $trade_organics * $tkireg->organics_price + $trade_goods * $tkireg->goods_price + $trade_energy * $tkireg->energy_price = $total_cost";

        if ($free_holds < $cargo_exchanged)
        {
            echo $langvars['l_notenough_cargo'] . " <br><br>";
        }
        elseif ($trade_energy > $free_power)
        {
            echo $langvars['l_notenough_power'] . " <br><br>";
        }
        elseif ($playerinfo['turns'] < 1)
        {
            echo $langvars['l_notenough_turns'] . ".<br><br>";
        }
        elseif ($playerinfo['credits'] < $total_cost)
        {
            echo $langvars['l_notenough_credits'] . " <br><br>";
        }
        elseif ($trade_ore < 0 && abs($playerinfo['ship_ore']) < abs($trade_ore))
        {
            echo $langvars['l_notenough_ore'] . " ";
        }
        elseif ($trade_organics < 0 && abs($playerinfo['ship_organics']) < abs($trade_organics))
        {
            echo $langvars['l_notenough_organics'] . " ";
        }
        elseif ($trade_goods < 0 && abs($playerinfo['ship_goods']) < abs($trade_goods))
        {
            echo $langvars['l_notenough_goods'] . " ";
        }
        elseif ($trade_energy < 0 && abs($playerinfo['ship_energy']) < abs($trade_energy))
        {
            echo $langvars['l_notenough_energy'] . " ";
        }
        elseif (abs($trade_organics) > $sectorinfo['port_organics'])
        {
            echo $langvars['l_exceed_organics'];
        }
        elseif (abs($trade_ore) > $sectorinfo['port_ore'])
        {
            echo $langvars['l_exceed_ore'];
        }
        elseif (abs($trade_goods) > $sectorinfo['port_goods'])
        {
            echo $langvars['l_exceed_goods'];
        }
        elseif (abs($trade_energy) > $sectorinfo['port_energy'])
        {
            echo $langvars['l_exceed_energy'];
        }
        else
        {
            if ($total_cost == 0)
            {
                $trade_color   = "#fff";
                $trade_result  = $langvars['l_cost'] . " : ";
            }
            elseif ($total_cost < 0)
            {
                $trade_color   = $color_green;
                $trade_result  = $trade_benefit;
            }
            else
            {
                $trade_color   = $color_red;
                $trade_result  = $trade_deficit;
            }

            echo "<table border=2 cellspacing=2 cellpadding=2 bgcolor=#400040 width=600 align=center>
                    <tr>
                        <td colspan=99 align=center><font size=3 color=white><strong>" . $langvars['l_trade_result'] . "</strong></font></td>
                    </tr>
                    <tr>
                        <td colspan=99 align=center><strong><font style='color:{$trade_color};'>" . $trade_result . " " . number_format(abs($total_cost), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_credits'] . "</font></strong></td>
                    </tr>
                    <tr bgcolor=$tkireg->color_line1>
                        <td><strong><font size=2 color=white>" . $langvars['l_traded_ore'] . ": </font><strong></td><td align=right><strong><font size=2 color=white>" . number_format($trade_ore, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</font></strong></td>
                    </tr>
                   <tr bgcolor=$tkireg->color_line2>
                        <td><strong><font size=2 color=white>" . $langvars['l_traded_organics'] . ": </font><strong></td><td align=right><strong><font size=2 color=white>" . number_format($trade_organics, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</font></strong></td>
                    </tr>
                    <tr bgcolor=$tkireg->color_line1>
                        <td><strong><font size=2 color=white>" . $langvars['l_traded_goods'] . ": </font><strong></td><td align=right><strong><font size=2 color=white>" . number_format($trade_goods, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</font></strong></td>
                    </tr>
                    <tr bgcolor=$tkireg->color_line2>
                        <td><strong><font size=2 color=white>" . $langvars['l_traded_energy'] . ": </font><strong></td><td align=right><strong><font size=2 color=white>" . number_format($trade_energy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</font></strong></td>
                    </tr>
                    </table>";

            // Update ship cargo, credits and turns
            $trade_result = $old_db->Execute("UPDATE {$old_db->prefix}ships SET turns = turns - 1, turns_used = turns_used + 1, rating = rating + 1, credits = credits - ?, ship_ore = ship_ore + ?, ship_organics = ship_organics + ?, ship_goods = ship_goods + ?, ship_energy = ship_energy + ? WHERE ship_id = ?;", array($total_cost, $trade_ore, $trade_organics, $trade_goods, $trade_energy, $playerinfo['ship_id']));
            Tki\Db::logDbErrors($pdo_db, $trade_result, __LINE__, __FILE__);

            // Make all trades positive to change port values
            $trade_ore        = round(abs($trade_ore));
            $trade_organics   = round(abs($trade_organics));
            $trade_goods      = round(abs($trade_goods));
            $trade_energy     = round(abs($trade_energy));

            // Decrease supply and demand on port
            $trade_result2 = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_ore = port_ore - ?, port_organics = port_organics - ?, port_goods = port_goods - ?, port_energy = port_energy - ? WHERE sector_id = ?;", array($trade_ore, $trade_organics, $trade_goods, $trade_energy, $sectorinfo['sector_id']));
            Tki\Db::logDbErrors($pdo_db, $trade_result2, __LINE__, __FILE__);

            echo $langvars['l_trade_complete'] . ".<br><br>";
        }
    }
}

echo "<br><br>";
Tki\Text::gotoMain($pdo_db, $lang);

if ($sectorinfo['port_type'] == "special")
{
    $langvars['l_port2_return'] = str_replace("[here]", "<a href='port.php'>" . $langvars['l_here'] . "</a>", $langvars['l_port2_return']);
    echo $langvars['l_port2_return'] . "<br>";
}

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
