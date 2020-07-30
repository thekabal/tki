<?php declare(strict_types = 1);
/**
 * planet.php from The Kabal Invasion.
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

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('bounty', 'port', 'ibank', 'main', 'planet', 'report', 'common', 'global_includes', 'global_funcs', 'footer', 'news', 'combat', 'regional'));

$title = $langvars['l_planet_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$destroy = null;
$destroy = (int) filter_input(INPUT_GET, 'destroy', FILTER_SANITIZE_NUMBER_INT);

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$command = null;
$command = filter_input(INPUT_GET, 'command', FILTER_SANITIZE_STRING);
if ($command === null)
{
    $command = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$planet_id = null;
$planet_id = (int) filter_input(INPUT_GET, 'planet_id', FILTER_SANITIZE_NUMBER_INT);

echo '<h1>' . $title . '</h1>';

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

// Empty out Planet and Ship vars
$planetinfo = null;

// Check if planet_id is valid.
if ($planet_id <= 0)
{
    echo $langvars['l_planet2_invalid_planet'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $template);
    die();
}

// Get sectorinfo from database
$sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db); // Build a sector gateway object to handle the SQL calls
$sectorinfo = $sectors_gateway->selectSectorInfo($playerinfo['sector']);

// Get planetinfo from database
$planets_gateway = new \Tki\Planets\PlanetsGateway($pdo_db); // Build a planet gateway object to handle the SQL calls
$planetinfo = $planets_gateway->selectPlanetInfoByPlanet($planet_id);

if (empty($planetinfo))
{
    echo $langvars['l_planet2_invalid_planet'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);
    die();
}

$admin_log = new Tki\AdminLog();

if (!empty($planetinfo))  // If there is a planet in the sector show appropriate menu
{
    if ($playerinfo['sector'] != $planetinfo['sector_id'])
    {
        if ($playerinfo['on_planet'] == 'Y')
        {
            $resx = $old_db->Execute("UPDATE {$old_db->prefix}ships SET on_planet='N' WHERE ship_id = ?;", array($playerinfo['ship_id']));
            Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
        }

        echo $langvars['l_planet_none'] . " <p>";
        Tki\Text::gotoMain($pdo_db, $lang);

        $footer = new Tki\Footer();
        $footer->display($pdo_db, $lang, $tkireg, $template);
        die();
    }

    if (($planetinfo['owner'] == 0 || $planetinfo['defeated'] == 'Y') && $command != "capture")
    {
        if ($planetinfo['owner'] == 0)
        {
            echo $langvars['l_planet_unowned'] . ".<br><br>";
        }

        $capture_link = "<a href=planet.php?planet_id=$planet_id&command=capture>" . $langvars['l_planet_capture1'] . "</a>";
        $langvars['l_planet_capture2'] = str_replace("[capture]", $capture_link, $langvars['l_planet_capture2']);
        echo $langvars['l_planet_capture2'] . ".<br><br>";
        echo "<br>";
        Tki\Text::gotoMain($pdo_db, $lang);

        $footer = new Tki\Footer();
        $footer->display($pdo_db, $lang, $tkireg, $template);
        die();
    }

    $players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
    $ownerinfo = $players_gateway->selectPlayerInfoById($planetinfo['owner']);

    if (empty($command))
    {
        // Kami Multi Browser Window Attack Fix
        $_SESSION['planet_selected'] = $planet_id;

        // If there is no planet command already
        if (empty($planetinfo['name']))
        {
            $langvars['l_planet_unnamed'] = str_replace("[name]", $ownerinfo['character_name'], $langvars['l_planet_unnamed']);
            echo $langvars['l_planet_unnamed'] . "<br><br>";
        }
        else
        {
            $langvars['l_planet_named'] = str_replace("[name]", $ownerinfo['character_name'], $langvars['l_planet_named']);
            $langvars['l_planet_named'] = str_replace("[planetname]", $planetinfo['name'], $langvars['l_planet_named']);
            echo $langvars['l_planet_named'] . "<br><br>";
        }

        if ($playerinfo['ship_id'] == $planetinfo['owner'])
        {
            if ($destroy == 1 && $tkireg->allow_genesis_destroy)
            {
                echo "<font color=red>" . $langvars['l_planet_confirm'] . "</font><br><a href=planet.php?planet_id=$planet_id&destroy=2>yes</a><br>";
                echo "<a href=planet.php?planet_id=$planet_id>no!</a><br><br>";
            }
            elseif ($destroy == 2 && $tkireg->allow_genesis_destroy)
            {
                if ($playerinfo['dev_genesis'] > 0)
                {
                    $update = $old_db->Execute("DELETE FROM {$old_db->prefix}planets WHERE planet_id = ?;", array($planet_id));
                    Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);
                    $update2 = $old_db->Execute("UPDATE {$old_db->prefix}ships SET turns_used = turns_used + 1, turns = turns - 1, dev_genesis = dev_genesis - 1 WHERE ship_id = ?", array($playerinfo['ship_id']));
                    Tki\Db::logDbErrors($pdo_db, $update2, __LINE__, __FILE__);
                    $update3 = $old_db->Execute("UPDATE {$old_db->prefix}ships SET on_planet='N' WHERE planet_id = ?;", array($planet_id));
                    Tki\Db::logDbErrors($pdo_db, $update3, __LINE__, __FILE__);
                    Tki\Ownership::calc($pdo_db, $playerinfo['sector'], $tkireg->min_bases_to_own, $langvars);
                    header("Location: main.php");
                }
                else
                {
                    echo $langvars['l_gns_nogenesis'] . "<br>";
                }
            }
            elseif ($tkireg->allow_genesis_destroy)
            {
                echo "<a onclick=\"javascript: alert ('alert:" . $langvars['l_planet_warning'] . "');\" href=planet.php?planet_id=$planet_id&destroy=1>" . $langvars['l_planet_destroyplanet'] . "</a><br>";
            }
        }

        if ($planetinfo['owner'] == $playerinfo['ship_id'] || ($planetinfo['tea,'] == $playerinfo['team'] && $playerinfo['team'] > 0))
        {
            // Owner menu
            echo $langvars['l_turns_have'] . " " . $playerinfo['turns'] . "<p>";

            $langvars['l_planet_name_link'] = "<a href=planet.php?planet_id=$planet_id&command=name>" . $langvars['l_planet_name_link'] . "</a>";
            $langvars['l_planet_name'] = str_replace("[name]", $langvars['l_planet_name_link'], $langvars['l_planet_name2']);

            echo $langvars['l_planet_name'] . "<br>";

            $langvars['l_planet_leave_link'] = "<a href=planet.php?planet_id=$planet_id&command=leave>" . $langvars['l_planet_leave_link'] . "</a>";
            $langvars['l_planet_leave'] = str_replace("[leave]", $langvars['l_planet_leave_link'], $langvars['l_planet_leave']);

            $langvars['l_planet_land_link'] = "<a href=planet.php?planet_id=$planet_id&command=land>" . $langvars['l_planet_land_link'] . "</a>";
            $langvars['l_planet_land'] = str_replace("[land]", $langvars['l_planet_land_link'], $langvars['l_planet_land']);

            if ($playerinfo['on_planet'] == 'Y' && $playerinfo['planet_id'] == $planet_id)
            {
                echo $langvars['l_planet_onsurface'] . "<br>";
                echo $langvars['l_planet_leave'] . "<br>";
                $langvars['l_planet_logout'] = str_replace("[logout]", "<a href='logout.php'>" . $langvars['l_logout'] . "</a>", $langvars['l_planet_logout']);
                echo $langvars['l_planet_logout'] . "<br>";
            }
            else
            {
                echo $langvars['l_planet_orbit'] . "<br>";
                echo $langvars['l_planet_land'] . "<br>";
            }

            $langvars['l_planet_transfer_link'] = "<a href=planet.php?planet_id=$planet_id&command=transfer>" . $langvars['l_planet_transfer_link'] . "</a>";
            $langvars['l_planet_transfer'] = str_replace("[transfer]", $langvars['l_planet_transfer_link'], $langvars['l_planet_transfer']);
            echo $langvars['l_planet_transfer'] . "<br>";
            if ($planetinfo['sells'] == "Y")
            {
                echo $langvars['l_planet_selling'];
            }
            else
            {
                echo $langvars['l_planet_not_selling'];
            }

            $langvars['l_planet_tsell_link'] = "<a href=planet.php?planet_id=$planet_id&command=sell>" . $langvars['l_planet_tsell_link'] . "</a>";
            $langvars['l_planet_tsell'] = str_replace("[selling]", $langvars['l_planet_tsell_link'], $langvars['l_planet_tsell']);
            echo $langvars['l_planet_tsell'] . "<br>";
            if ($planetinfo['base'] == "N")
            {
                $langvars['l_planet_bbase_link'] = "<a href=planet.php?planet_id=$planet_id&command=base>" . $langvars['l_planet_bbase_link'] . "</a>";
                $langvars['l_planet_bbase'] = str_replace("[build]", $langvars['l_planet_bbase_link'], $langvars['l_planet_bbase']);
                echo $langvars['l_planet_bbase'] . "<br>";
            }
            else
            {
                echo $langvars['l_planet_hasbase'] . "<br>";
            }

            $langvars['l_planet_readlog_link'] = "<a href=log.php>" . $langvars['l_planet_readlog_link'] . "</a>";
            $langvars['l_planet_readlog'] = str_replace("[View]", $langvars['l_planet_readlog_link'], $langvars['l_planet_readlog']);
            echo "<br>" . $langvars['l_planet_readlog'] . "<br>";

            if ($playerinfo['ship_id'] == $planetinfo['owner'])
            {
                if ($playerinfo['team'] != 0)
                {
                    if ($planetinfo['team'] == 0)
                    {
                        $langvars['l_planet_mteam_linkC'] = "<a href=team.php?planet_id=$planet_id&action=planetteam>" . $langvars['l_planet_mteam_linkC'] . "</a>";
                        $langvars['l_planet_mteam'] = str_replace("[planet]", $langvars['l_planet_mteam_linkC'], $langvars['l_planet_mteam']);
                        echo $langvars['l_planet_mteam'] . "<br>";
                    }
                    else
                    {
                        $langvars['l_planet_mteam_linkP'] = "<a href=team.php?planet_id=$planet_id&action=planetpersonal>" . $langvars['l_planet_mteam_linkP'] . "</a>";
                        $langvars['l_planet_mteam'] = str_replace("[planet]", $langvars['l_planet_mteam_linkP'], $langvars['l_planet_mteam']);
                        echo $langvars['l_planet_mteam'] . "<br>";
                    }
                }
            }

            // Change production rates
            echo "<form accept-charset='utf-8' action=planet.php?planet_id=$planet_id&command=productions method=post>";
            echo "<table border=0 cellspacing=0 cellpadding=2>";
            echo "<tr bgcolor=\"$tkireg->color_header\"><td></td><td><strong>" . $langvars['l_ore'] . "</strong></td><td><strong>" . $langvars['l_organics'] . "</strong></td><td><strong>" . $langvars['l_goods'] . "</strong></td><td><strong>" . $langvars['l_energy'] . "</strong></td><td><strong>" . $langvars['l_colonists'] . "</strong></td><td><strong>" . $langvars['l_credits'] . "</strong></td><td><strong>" . $langvars['l_fighters'] . "</strong></td><td><strong>" . $langvars['l_torps'] . "</td></tr>";
            echo "<tr bgcolor=\"$tkireg->color_line1\">";
            echo "<td>" . $langvars['l_current_qty'] . "</td>";
            echo "<td>" . number_format($planetinfo['ore'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
            echo "<td>" . number_format($planetinfo['organics'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
            echo "<td>" . number_format($planetinfo['goods'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
            echo "<td>" . number_format($planetinfo['energy'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
            echo "<td>" . number_format($planetinfo['colonists'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
            echo "<td>" . number_format($planetinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
            echo "<td>" . number_format($planetinfo['fighters'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
            echo "<td>" . number_format($planetinfo['torps'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>";
            echo "</tr>";
            echo "<tr bgcolor=\"$tkireg->color_line2\"><td>" . $langvars['l_planet_perc'] . "</td>";
            echo "<td><input type=text name=pore value=\"$planetinfo[prod_ore]\" size=6 maxlength=6></td>";
            echo "<td><input type=text name=porganics value=\"$planetinfo[prod_organics]\" size=6 maxlength=6></td>";
            echo "<td><input type=text name=pgoods value=\"" . round($planetinfo['prod_goods']) . "\" size=6 maxlength=6></td>";
            echo "<td><input type=text name=penergy value=\"$planetinfo[prod_energy]\" size=6 maxlength=6></td>";
            echo "<td>n/a</td><td>*</td>";
            echo "<td><input type=text name=pfighters value=\"$planetinfo[prod_fighters]\" size=6 maxlength=6></td>";
            echo "<td><input type=text name=ptorp value=\"$planetinfo[prod_torp]\" size=6 maxlength=6></td>";
            echo "</table>" . $langvars['l_planet_interest'] . "<br><br>";
            echo "<input type=submit value=" . $langvars['l_planet_update'] . ">";
            echo "</form>";
        }
        else
        {
            // Visitor menu
            if ($planetinfo['sells'] == "Y")
            {
                $langvars['l_planet_buy_link'] = "<a href=planet.php?planet_id=$planet_id&command=buy>" . $langvars['l_planet_buy_link'] . "</a>";
                $langvars['l_planet_buy'] = str_replace("[buy]", $langvars['l_planet_buy_link'], $langvars['l_planet_buy']);
                echo $langvars['l_planet_buy'] . "<br>";
            }
            else
            {
                echo $langvars['l_planet_not_selling'] . ".<br>";
            }

            // Fix for team member leaving a non team planet
            if (($planetinfo['planet_id'] == $playerinfo['planet_id'] && $playerinfo['on_planet'] == "Y") && $planetinfo['team'] == 0)
            {
                $langvars['l_planet_leave_link'] = "<a href=planet.php?planet_id=$planet_id&command=leave>Leave Friendly Planet</a>";
                echo "<p>" . $langvars['l_planet_leave_link'] . "</p>\n";
            }

            $retOwnerInfo = null;

            $owner_found = Tki\Planet::getOwner($pdo_db, $planetinfo['planet_id'], $retOwnerInfo);
            if ($owner_found === true && $retOwnerInfo !== null)
            {
                if ($retOwnerInfo['team'] == $playerinfo['team'] && ($playerinfo['team'] != 0 || $retOwnerInfo['team'] != 0))
                {
                    echo "<div style='color:#ff0;'>Sorry, no Options available for Friendly Owned Private Planets.</div>\n";
                }
                else
                {
                    $langvars['l_planet_att_link'] = "<a href=planet.php?planet_id=$planet_id&command=attac>" . $langvars['l_planet_att_link'] . "</a>";
                    $langvars['l_planet_att'] = str_replace("[attack]", $langvars['l_planet_att_link'], $langvars['l_planet_att']);
                    $langvars['l_planet_scn_link'] = "<a href=planet.php?planet_id=$planet_id&command=scan>" . $langvars['l_planet_scn_link'] . "</a>";
                    $langvars['l_planet_scn'] = str_replace("[scan]", $langvars['l_planet_scn_link'], $langvars['l_planet_scn']);
                    echo $langvars['l_planet_att'] . "<br>";
                    echo $langvars['l_planet_scn'] . "<br>";
                    if ($tkireg->allow_sofa)
                    {
                        echo "<a href=planet.php?planet_id=$planet_id&command=bom>" . $langvars['l_sofa'] . "</a><br>";
                    }
                }
            }
        }
    }
    elseif ($planetinfo['owner'] == $playerinfo['ship_id'] || ($planetinfo['team'] == $playerinfo['team'] && $playerinfo['team'] > 0))
    {
        // Player owns planet and there is a command
        if ($command == "sell")
        {
            if ($planetinfo['sells'] == "Y")
            {
                // Set planet to not sell
                echo $langvars['l_planet_nownosell'] . "<br>";
                $result4 = $old_db->Execute("UPDATE {$old_db->prefix}planets SET sells='N' WHERE planet_id = ?;", array($planet_id));
                Tki\Db::logDbErrors($pdo_db, $result4, __LINE__, __FILE__);
            }
            else
            {
                echo $langvars['l_planet_nowsell'] . "<br>";
                $result4b = $old_db->Execute("UPDATE {$old_db->prefix}planets SET sells='Y' WHERE planet_id = ?;", array($planet_id));
                Tki\Db::logDbErrors($pdo_db, $result4b, __LINE__, __FILE__);
            }
        }
        elseif ($command == "name")
        {
            // Name menu
            echo "<form accept-charset='utf-8' action=\"planet.php?planet_id=$planet_id&command=cname\" method=\"post\">";
            echo $langvars['l_planet_iname'] . ":  ";
            echo "<input type=\"text\" name=\"new_name\" size=\"20\" maxlength=\"20\" value=\"$planetinfo[name]\"><br><br>";
            echo "<input type=\"submit\" value=\"" . $langvars['l_submit'] . "\"><input type=\"reset\" value=\"" . $langvars['l_reset'] . "\"><br><br>";
            echo "</form>";
        }
        elseif ($command == "cname")
        {
            // Name2 menu
            $new_name = trim(htmlentities($_POST['new_name'], ENT_HTML5, 'UTF-8'));
            $result5 = $old_db->Execute("UPDATE {$old_db->prefix}planets SET name = ? WHERE planet_id = ?;", array($new_name, $planet_id));
            Tki\Db::logDbErrors($pdo_db, $result5, __LINE__, __FILE__);
            echo $langvars['l_planet_cname'] . " " . $new_name . ".";
        }
        elseif ($command == "land")
        {
            // Land menu
            echo $langvars['l_planet_landed'] . "<br><br>";
            $update = $old_db->Execute("UPDATE {$old_db->prefix}ships SET on_planet='Y', planet_id = ? WHERE ship_id = ?;", array($planet_id, $playerinfo['ship_id']));
            Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);
        }
        elseif ($command == "leave")
        {
            // Leave menu
            echo $langvars['l_planet_left'] . "<br><br>";
            $update = $old_db->Execute("UPDATE {$old_db->prefix}ships SET on_planet='N' WHERE ship_id = ?;", array($playerinfo['ship_id']));
            Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);
        }
        elseif ($command == "transfer")
        {
            // Transfer menu
            $free_holds = Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
            $free_power = Tki\CalcLevels::energy($playerinfo['power'], $tkireg) - $playerinfo['ship_energy'];
            $langvars['l_planet_cinfo'] = str_replace("[cargo]", number_format($free_holds, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_planet_cinfo']);
            $langvars['l_planet_cinfo'] = str_replace("[energy]", number_format($free_power, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_planet_cinfo']);
            echo $langvars['l_planet_cinfo'] . "<br><br>";
            echo "<form accept-charset='utf-8' action=planet2.php?planet_id=$planet_id method=post>";
            echo "<table width=\"100%\" border=0 cellspacing=0 cellpadding=0>";
            echo "<tr bgcolor=\"$tkireg->color_header\"><td><strong>" . $langvars['l_commodity'] . "</strong></td><td><strong>" . $langvars['l_planet'] . "</strong></td><td><strong>" . $langvars['l_ship'] . "</strong></td><td><strong>" . $langvars['l_planet_transfer_link'] . "</strong></td><td><strong>" . $langvars['l_planet_toplanet'] . "</strong></td><td><strong>" . $langvars['l_all'] . "?</strong></td></tr>";
            echo "<tr bgcolor=\"$tkireg->color_line1\"><td>" . $langvars['l_ore'] . "</td><td>" . number_format($planetinfo['ore'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>" . number_format($playerinfo['ship_ore'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td><input type=text name=transfer_ore size=10 maxlength=20></td><td><input type=checkbox name=tpore value=-1></td><td><input type=checkbox name=allore value=-1></td></tr>";
            echo "<tr bgcolor=\"$tkireg->color_line2\"><td>" . $langvars['l_organics'] . "</td><td>" . number_format($planetinfo['organics'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>" . number_format($playerinfo['ship_organics'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td><input type=text name=transfer_organics size=10 maxlength=20></td><td><input type=checkbox name=tporganics value=-1></td><td><input type=checkbox name=allorganics value=-1></td></tr>";
            echo "<tr bgcolor=\"$tkireg->color_line1\"><td>" . $langvars['l_goods'] . "</td><td>" . number_format($planetinfo['goods'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>" . number_format($playerinfo['ship_goods'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td><input type=text name=transfer_goods size=10 maxlength=20></td><td><input type=checkbox name=tpgoods value=-1></td><td><input type=checkbox name=allgoods value=-1></td></tr>";
            echo "<tr bgcolor=\"$tkireg->color_line2\"><td>" . $langvars['l_energy'] . "</td><td>" . number_format($planetinfo['energy'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>" . number_format($playerinfo['ship_energy'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td><input type=text name=transfer_energy size=10 maxlength=20></td><td><input type=checkbox name=tpenergy value=-1></td><td><input type=checkbox name=allenergy value=-1></td></tr>";
            echo "<tr bgcolor=\"$tkireg->color_line1\"><td>" . $langvars['l_colonists'] . "</td><td>" . number_format($planetinfo['colonists'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>" . number_format($playerinfo['ship_colonists'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td><input type=text name=transfer_colonists size=10 maxlength=20></td><td><input type=checkbox name=tpcolonists value=-1></td><td><input type=checkbox name=allcolonists value=-1></td></tr>";
            echo "<tr bgcolor=\"$tkireg->color_line2\"><td>" . $langvars['l_fighters'] . "</td><td>" . number_format($planetinfo['fighters'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>" . number_format($playerinfo['ship_fighters'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td><input type=text name=transfer_fighters size=10 maxlength=20></td><td><input type=checkbox name=tpfighters value=-1></td><td><input type=checkbox name=allfighters value=-1></td></tr>";
            echo "<tr bgcolor=\"$tkireg->color_line1\"><td>" . $langvars['l_torps'] . "</td><td>" . number_format($planetinfo['torps'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>" . number_format($playerinfo['torps'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td><input type=text name=transfer_torps size=10 maxlength=20></td><td><input type=checkbox name=tptorps value=-1></td><td><input type=checkbox name=alltorps value=-1></td></tr>";
            echo "<tr bgcolor=\"$tkireg->color_line2\"><td>" . $langvars['l_credits'] . "</td><td>" . number_format($planetinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td>" . number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td><td><input type=text name=transfer_credits size=10 maxlength=20></td><td><input type=checkbox name=tpcredits value=-1></td><td><input type=checkbox name=allcredits value=-1></td></tr>";
            echo "</table><br>";
            echo "<input type=submit value=" . $langvars['l_planet_transfer_link'] . ">&nbsp;<input type=reset value=Reset>";
            echo "</form>";
        }
        elseif ($command == "base")
        {
            if (array_key_exists('planet_selected', $_SESSION) === false)
            {
                $_SESSION['planet_selected'] = null;
            }

            // Kami Multi Browser Window Attack Fix
            if ($_SESSION['planet_selected'] !== $planet_id && $_SESSION['planet_selected'] !== null)
            {
                echo "You need to Click on the planet first.<br><br>";
                Tki\Text::gotoMain($pdo_db, $lang);

                $footer = new Tki\Footer();
                $footer->display($pdo_db, $lang, $tkireg, $template);
                die();
            }

            unset($_SESSION['planet_selected']);

            // Build a base
            if ($planetinfo['ore'] >= $tkireg->base_ore && $planetinfo['organics'] >= $tkireg->base_organics && $planetinfo['goods'] >= $tkireg->base_goods && $planetinfo['credits'] >= $tkireg->base_credits)
            {
                // Check if the player has enough turns to create the base.
                if ($playerinfo['turns'] <= 0)
                {
                    echo $langvars['l_ibank_notenturns'];
                }
                else
                {
                    // Create The Base
                    $update1 = $old_db->Execute("UPDATE {$old_db->prefix}planets SET base='Y', ore = ? - ?, organics = ? - ?, goods = ? - ?, credits = ? - ? WHERE planet_id = ?;", array($planetinfo['ore'], $tkireg->base_ore, $planetinfo['organics'], $tkireg->base_organics, $planetinfo['goods'], $tkireg->base_goods, $planetinfo['credits'], $tkireg->base_credits, $planet_id));
                    Tki\Db::logDbErrors($pdo_db, $update1, __LINE__, __FILE__);

                    // Update User Turns
                    $update1b = $old_db->Execute("UPDATE {$old_db->prefix}ships SET turns = turns - 1, turns_used = turns_used + 1 WHERE ship_id = ?;", array($playerinfo['ship_id']));
                    Tki\Db::logDbErrors($pdo_db, $update1b, __LINE__, __FILE__);

                    // Refresh Planet Info
                    $planets_gateway = new \Tki\Planets\PlanetsGateway($pdo_db); // Build a planet gateway object to handle the SQL calls
                    $planetinfo = $planets_gateway->selectPlanetInfoByPlanet($planet_id);

                    // Notify User Of Base Results
                    echo $langvars['l_planet_bbuild'] . "<br><br>";

                    // Calc Ownership and Notify User Of Results
                    $ownership = Tki\Ownership::calc($pdo_db, $playerinfo['sector'], $tkireg->min_bases_to_own, $langvars);
                    echo $ownership . '<p>';
                }
            }
            else
            {
                $langvars['l_planet_baseinfo'] = str_replace("[base_credits]", $tkireg->base_credits, $langvars['l_planet_baseinfo']);
                $langvars['l_planet_baseinfo'] = str_replace("[base_ore]", $tkireg->base_ore, $langvars['l_planet_baseinfo']);
                $langvars['l_planet_baseinfo'] = str_replace("[base_organics]", $tkireg->base_organics, $langvars['l_planet_baseinfo']);
                $langvars['l_planet_baseinfo'] = str_replace("[base_goods]", $tkireg->base_goods, $langvars['l_planet_baseinfo']);
                echo $langvars['l_planet_baseinfo'] . "<br><br>";
            }
        }
        elseif ($command == "productions")
        {
            // Change production percentages
            $pore       = array_key_exists('pore', $_POST) ? $_POST['pore'] : 0;
            $pore       = (int) $pore;
            $porganics  = array_key_exists('porganics', $_POST) ? $_POST['porganics'] : 0;
            $porganics  = (int) $porganics;
            $pgoods     = array_key_exists('pgoods', $_POST) ? $_POST['pgoods'] : 0;
            $pgoods     = (int) $pgoods;
            $penergy    = array_key_exists('penergy', $_POST) ? $_POST['penergy'] : 0;
            $penergy    = (int) $penergy;
            $pfighters  = array_key_exists('pfighters', $_POST) ? $_POST['pfighters'] : 0;
            $pfighters  = (int) $pfighters;
            $ptorp      = array_key_exists('ptorp', $_POST) ? $_POST['ptorp'] : 0;
            $ptorp      = (int) $ptorp;

            if ($porganics < 0.0 || $pore < 0.0 || $pgoods < 0.0 || $penergy < 0.0 || $pfighters < 0.0 || $ptorp < 0.0)
            {
                echo $langvars['l_planet_p_under'] . "<br><br>";
            }
            elseif (($porganics + $pore + $pgoods + $penergy + $pfighters + $ptorp) > 100.0)
            {
                echo $langvars['l_planet_p_over'] . "<br><br>";
            }
            else
            {
                $resx = $old_db->Execute("UPDATE {$old_db->prefix}planets SET prod_ore= ? , prod_organics = ?, prod_goods = ?, prod_energy = ?, prod_fighters = ?, prod_torp = ? WHERE planet_id = ?;", array($pore, $porganics, $pgoods, $penergy, $pfighters, $ptorp, $planet_id));
                Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                echo $langvars['l_planet_p_changed'] . "<br><br>";
            }
        }
        else
        {
            echo $langvars['l_command_no'] . "<br>";
        }
    }
    elseif (($planetinfo['planet_id'] == $playerinfo['planet_id'] && $playerinfo['on_planet'] == "Y") && $planetinfo['team'] == 0) // Fix for team member leaving a non team planet
    {
        if ($command == "leave")
        {
            // Leave menu
            echo $langvars['l_planet_left'] . "<br><br>";
            $update = $old_db->Execute("UPDATE {$old_db->prefix}ships SET on_planet = 'N', planet_id = 0 WHERE ship_id = ?;", array($playerinfo['ship_id']));
            Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);
            $langvars['l_global_mmenu'] = str_replace("[here]", "<a href='main.php'>" . $langvars['l_here'] . "</a>", $langvars['l_global_mmenu']);
            echo $langvars['l_global_mmenu'] . "<br>\n";
            header("Location: main.php");
        }
    }
    else
    {
        // Player doesn't own planet and there is a command
        if ($command == "buy")
        {
            if ($planetinfo['sells'] == "Y")
            {
                $ore_price = ($ore_price + $ore_delta / 4);
                $organics_price = ($organics_price + $organics_delta / 4);
                $goods_price = ($goods_price + $goods_delta / 4);
                $energy_price = ($energy_price + $energy_delta / 4);
                echo "<form accept-charset='utf-8' action=planet3.php?planet_id=$planet_id method=post>";
                echo "<table>";
                echo "<tr><td>" . $langvars['l_commodity'] . "</td><td>" . $langvars['l_avail'] . "</td><td>" . $langvars['l_price'] . "</td><td>" . $langvars['l_buy'] . "</td><td>" . $langvars['l_cargo'] . "</td></tr>";
                echo "<tr><td>" . $langvars['l_ore'] . "</td><td>$planetinfo[ore]</td><td>$ore_price</td><td><input type=text name=trade_ore size=10 maxlength=20 value=0></td><td>$playerinfo[ship_ore]</td></tr>";
                echo "<tr><td>" . $langvars['l_organics'] . "</td><td>$planetinfo[organics]</td><td>$organics_price</td><td><input type=text name=trade_organics size=10 maxlength=20 value=0></td><td>$playerinfo[ship_organics]</td></tr>";
                echo "<tr><td>" . $langvars['l_goods'] . "</td><td>$planetinfo[goods]</td><td>$goods_price</td><td><input type=text name=trade_goods size=10 maxlength=20 value=0></td><td>$playerinfo[ship_goods]</td></tr>";
                echo "<tr><td>" . $langvars['l_energy'] . "</td><td>$planetinfo[energy]</td><td>$energy_price</td><td><input type=text name=trade_energy size=10 maxlength=20 value=0></td><td>$playerinfo[ship_energy]</td></tr>";
                echo "</table>";
                echo "<input type=submit value=" . $langvars['l_submit'] . "><input type=reset value=" . $langvars['l_reset'] . "><br></form>";
            }
            else
            {
                echo $langvars['l_planet_not_selling'] . "<br>";
            }
        }
        elseif ($command == "attac")
        {
            // Kami Multi Browser Window Attack Fix
            if (array_key_exists('planet_selected', $_SESSION) === false || $_SESSION['planet_selected'] != $planet_id)
            {
                echo "You need to Click on the planet first.<br><br>";
                Tki\Text::gotoMain($pdo_db, $lang);

                $footer = new Tki\Footer();
                $footer->display($pdo_db, $lang, $tkireg, $template);
                die();
            }

            // Check to see if sure
            if ($planetinfo['sells'] == "Y")
            {
                $langvars['l_planet_buy_link'] = "<a href=planet.php?planet_id=$planet_id&command=buy>" . $langvars['l_planet_buy_link'] . "</a>";
                $langvars['l_planet_buy'] = str_replace("[buy]", $langvars['l_planet_buy_link'], $langvars['l_planet_buy']);
                echo $langvars['l_planet_buy'] . "<br>";
            }
            else
            {
                echo $langvars['l_planet_not_selling'] . "<br>";
            }

            $retOwnerInfo = null;
            $owner_found = Tki\Planet::getOwner($pdo_db, $planetinfo['planet_id'], $retOwnerInfo);
            if ($owner_found === true && $retOwnerInfo !== null)
            {
                if ($retOwnerInfo['team'] == $playerinfo['team'] && ($playerinfo['team'] != 0 || $retOwnerInfo['team'] != 0))
                {
                    echo "<div style='color:#ff0;'>Sorry, You cannot attack a Friendly Owned Private Planet.</div>\n";
                }
                else
                {
                    $langvars['l_planet_att_link'] = "<a href=planet.php?planet_id=$planet_id&command=attack>" . $langvars['l_planet_att_link'] . " .</a>";
                    $langvars['l_planet_att'] = str_replace("[attack]", $langvars['l_planet_att_link'], $langvars['l_planet_att']);
                    $langvars['l_planet_scn_link'] = "<a href=planet.php?planet_id=$planet_id&command=scan>" . $langvars['l_planet_scn_link'] . "</a>";
                    $langvars['l_planet_scn'] = str_replace("[scan]", $langvars['l_planet_scn_link'], $langvars['l_planet_scn']);
                    echo $langvars['l_planet_att'] . " <strong>" . $langvars['l_planet_att_sure'] . "</strong><br>";
                    echo $langvars['l_planet_scn'] . "<br>";
                    if ($tkireg->allow_sofa)
                    {
                        echo "<a href=planet.php?planet_id=$planet_id&command=bom>" . $langvars['l_sofa'] . "</a><br>";
                    }
                }
            }
        }
        elseif ($command == "attack")
        {
            // Kami Multi Browser Window Attack Fix
            if (array_key_exists('planet_selected', $_SESSION) === false || $_SESSION['planet_selected'] != $planet_id)
            {
                echo "You need to Click on the planet first.<br><br>";
                Tki\Text::gotoMain($pdo_db, $lang);

                $footer = new Tki\Footer();
                $footer->display($pdo_db, $lang, $tkireg, $template);
                die();
            }

            unset($_SESSION['planet_selected']);
            $retOwnerInfo = null;
            $owner_found = Tki\Planet::getOwner($pdo_db, $planetinfo['planet_id'], $retOwnerInfo);
            if ($owner_found === true && $retOwnerInfo !== null)
            {
                if ($retOwnerInfo['team'] == $playerinfo['team'] && ($playerinfo['team'] != 0 || $retOwnerInfo['team'] != 0))
                {
                    echo "<div style='color:#f00;'>Look we have told you, You cannot attack a Friendly Owned Private Planet!</div>\n";
                }
                else
                {
                    if (\Tki\PlanetCombat::prime($pdo_db, $old_db, $lang, $langvars, $tkireg, $template, $playerinfo, $ownerinfo, $planetinfo))
                    {
                        die();
                    }
                }
            }
        }
        elseif ($command == "bom")
        {
            // Check to see if sure...
            if ($planetinfo['sells'] == "Y" && $tkireg->allow_sofa)
            {
                $langvars['l_planet_buy_link'] = "<a href=planet.php?planet_id=$planet_id&command=buy>" . $langvars['l_planet_buy_link'] . "</a>";
                $langvars['l_planet_buy'] = str_replace("[buy]", $langvars['l_planet_buy_link'], $langvars['l_planet_buy']);
                echo $langvars['l_planet_buy'] . "<br>";
            }
            else
            {
                echo $langvars['l_planet_not_selling'] . "<br>";
            }

            $langvars['l_planet_att_link'] = "<a href=planet.php?planet_id=$planet_id&command=attac>" . $langvars['l_planet_att_link'] . "</a>";
            $langvars['l_planet_att'] = str_replace("[attack]", $langvars['l_planet_att_link'], $langvars['l_planet_att']);
            $langvars['l_planet_scn_link'] = "<a href=planet.php?planet_id=$planet_id&command=scan>" . $langvars['l_planet_scn_link'] . "</a>";
            $langvars['l_planet_scn'] = str_replace("[scan]", $langvars['l_planet_scn_link'], $langvars['l_planet_scn']);
            echo $langvars['l_planet_att'] . "<br>";
            echo $langvars['l_planet_scn'] . "<br>";
            echo "<a href=planet.php?planet_id=$planet_id&command=bomb>" . $langvars['l_sofa'] . "</a><strong>" . $langvars['l_planet_att_sure'] . "</strong><br>";
        }
        elseif ($command == "bomb" && $tkireg->allow_sofa)
        {
            try
            {
                \Tki\Planet::bombing($pdo_db, $lang, $langvars, $tkireg, $playerinfo, $ownerinfo, $planetinfo, $template);
            }
            catch (Exception $e)
            {
                die($e);
            }
        }
        elseif ($command == "scan")
        {
            // Kami Multi Browser Window Attack Fix
            if (array_key_exists('planet_selected', $_SESSION) === false || $_SESSION['planet_selected'] != $planet_id)
            {
                echo "You need to Click on the planet first.<br><br>";
                Tki\Text::gotoMain($pdo_db, $lang);

                $footer = new Tki\Footer();
                $footer->display($pdo_db, $lang, $tkireg, $template);
                die();
            }

            unset($_SESSION['planet_selected']);
            // Scan menu
            if ($playerinfo['turns'] < 1)
            {
                echo $langvars['l_plant_scn_turn'] . "<br><br>";
                Tki\Text::gotoMain($pdo_db, $lang);

                $footer = new Tki\Footer();
                $footer->display($pdo_db, $lang, $tkireg, $template);
                die();
            }

            // Determine per cent chance of success in scanning target ship - based on player's sensors and opponent's cloak
            $success = (10 - $ownerinfo['cloak'] / 2 + $playerinfo['sensors']) * 5;
            if ($success < 5)
            {
                $success = 5;
            }

            if ($success > 95)
            {
                $success = 95;
            }

            $roll = random_int(1, 100);
            if ($roll > $success)
            {
                // If scan fails - inform both player and target.
                echo $langvars['l_planet_noscan'] . "<br><br>";
                Tki\Text::gotoMain($pdo_db, $lang);
                Tki\PlayerLog::writeLog($pdo_db, $ownerinfo['ship_id'], \Tki\LogEnums::PLANET_SCAN_FAIL, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");

                $footer = new Tki\Footer();
                $footer->display($pdo_db, $lang, $tkireg, $template);
                die();
            }
            else
            {
                Tki\PlayerLog::writeLog($pdo_db, $ownerinfo['ship_id'], \Tki\LogEnums::PLANET_SCAN, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");
                // Scramble results by scan error factor.
                $sc_error = Tki\Scan::error($playerinfo['sensors'], $ownerinfo['cloak'], $scan_error_factor);
                if (empty($planetinfo['name']))
                {
                    $planetinfo['name'] = $langvars['l_unnamed'];
                }

                $langvars['l_planet_scn_report'] = str_replace("[name]", $planetinfo['name'], $langvars['l_planet_scn_report']);
                $langvars['l_planet_scn_report'] = str_replace("[owner]", $ownerinfo['character_name'], $langvars['l_planet_scn_report']);
                echo $langvars['l_planet_scn_report'] . "<br><br>";
                echo "<table>";
                echo "<tr><td>" . $langvars['l_commodities'] . ":</td><td></td>";
                echo "<tr><td>" . $langvars['l_organics'] . ":</td>";
                $roll = random_int(1, 100);
                if ($roll < $success)
                {
                    $sc_planet_organics = number_format(round($planetinfo['organics'] * $sc_error / 100), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
                    echo "<td>$sc_planet_organics</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>" . $langvars['l_ore'] . ":</td>";
                $roll = random_int(1, 100);
                if ($roll < $success)
                {
                    $sc_planet_ore = number_format(round($planetinfo['ore'] * $sc_error / 100), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
                    echo "<td>$sc_planet_ore</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>" . $langvars['l_goods'] . ":</td>";
                $roll = random_int(1, 100);
                if ($roll < $success)
                {
                    $sc_planet_goods = number_format(round($planetinfo['goods'] * $sc_error / 100), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
                    echo "<td>$sc_planet_goods</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>" . $langvars['l_energy'] . ":</td>";
                $roll = random_int(1, 100);

                if ($roll < $success)
                {
                    $sc_planet_energy = number_format(round($planetinfo['energy'] * $sc_error / 100), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
                    echo "<td>$sc_planet_energy</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>" . $langvars['l_colonists'] . ":</td>";
                $roll = random_int(1, 100);
                if ($roll < $success)
                {
                    $sc_planet_colonists = number_format(round($planetinfo['colonists'] * $sc_error / 100), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
                    echo "<td>$sc_planet_colonists</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>" . $langvars['l_credits'] . ":</td>";
                $roll = random_int(1, 100);
                if ($roll < $success)
                {
                    $sc_planet_credits = number_format(round($planetinfo['credits'] * $sc_error / 100), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
                    echo "<td>$sc_planet_credits</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>" . $langvars['l_defense'] . ":</td><td></td>";
                echo "<tr><td>" . $langvars['l_base'] . ":</td>";
                $roll = random_int(1, 100);
                if ($roll < $success)
                {
                    echo "<td>$planetinfo[base]</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>" . $langvars['l_base'] . " " .  $langvars['l_torps'] . ":</td>";
                $roll = random_int(1, 100);
                if ($roll < $success)
                {
                    $sc_base_torp = number_format(round($planetinfo['torps'] * $sc_error / 100), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
                    echo "<td>$sc_base_torp</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>" . $langvars['l_fighters'] . ":</td>";
                $roll = random_int(1, 100);
                if ($roll < $success)
                {
                    $sc_planet_fighters = number_format(round($planetinfo['fighters'] * $sc_error / 100), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
                    echo "<td>$sc_planet_fighters</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>" . $langvars['l_beams'] . ":</td>";
                $roll = random_int(1, 100);
                if ($roll < $success)
                {
                    $sc_beams = number_format(round($ownerinfo['beams'] * $sc_error / 100), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
                    echo "<td>$sc_beams</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>" . $langvars['l_torp_launch'] . ":</td>";
                $roll = random_int(1, 100);
                if ($roll < $success)
                {
                    $sc_torp_launchers = number_format(round($ownerinfo['torp_launchers'] * $sc_error / 100), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
                    echo "<td>$sc_torp_launchers</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>" . $langvars['l_shields'] . "</td>";
                $roll = random_int(1, 100);
                if ($roll < $success)
                {
                    $sc_shields = number_format(round($ownerinfo['shields'] * $sc_error / 100), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
                    echo "<td>$sc_shields</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "</table><br>";
                // $roll=random_int(1, 100);
                // if ($ownerinfo[sector] == $playerinfo[sector] && $ownerinfo[on_planet] == 'Y' && $roll < $success)
                // {
                       // echo "<strong>" . $ownerinfo['character_name'] . " " . $langvars['l_planet_ison'] . "</strong><br>";
                // }

                $res = $old_db->Execute("SELECT * FROM {$old_db->prefix}ships WHERE on_planet = 'Y' and planet_id = ?;", array($planet_id));
                Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);

                while (!$res->EOF)
                {
                    $row = $res->fields;
                    $success = Tki\Scan::success($playerinfo['sensors'], $row['cloak']);
                    if ($success < 5)
                    {
                        $success = 5;
                    }

                    if ($success > 95)
                    {
                        $success = 95;
                    }

                    $roll = random_int(1, 100);

                    if ($roll < $success)
                    {
                        echo "<strong>" . $row['character_name'] . " " . $langvars['l_planet_ison'] . "</strong><br>";
                    }

                    $res->MoveNext();
                }
            }

            $update = $old_db->Execute("UPDATE {$old_db->prefix}ships SET turns = turns - 1, turns_used = turns_used + 1 WHERE ship_id = ?;", array($playerinfo['ship_id']));
            Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);
        }
        elseif ($command == "capture" && $planetinfo['owner'] == 0)
        {
            echo $langvars['l_planet_captured'] . "<br>";
            $update = $old_db->Execute("UPDATE {$old_db->prefix}planets SET team = 0, owner = ?, base = 'N', defeated = 'N' WHERE planet_id = ?;", array($playerinfo['ship_id'], $planet_id));
            Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);
            $ownership = Tki\Ownership::calc($pdo_db, $playerinfo['sector'], $tkireg, $langvars);
            echo $ownership . '<p>';

            if ($planetinfo['owner'] != 0)
            {
                Tki\Score::updateScore($pdo_db, $planetinfo['owner'], $tkireg, $playerinfo);
            }

            if ($planetinfo['owner'] != 0)
            {
                $players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
                $planetowner = $players_gateway->selectPlayerInfoById($planetinfo['owner']);
            }
            else
            {
                $planetowner = $langvars['l_planet_noone'];
            }

            Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::PLANET_CAPTURED, "$planetinfo[colonists]|$planetinfo[credits]|$planetowner");
        }
        elseif ($command == "capture" && ($planetinfo['owner'] == 0 || $planetinfo['defeated'] == 'Y'))
        {
            echo $langvars['l_planet_notdef'] . "<br>";
            $resx = $old_db->Execute("UPDATE {$old_db->prefix}planets SET defeated='N' WHERE planet_id = ?;", array($planetinfo['planet_id']));
            Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
        }
        else
        {
            echo $langvars['l_command_no'] . "<br>";
        }
    }
}
else
{
    echo $langvars['l_planet_none'] . "<p>";
}

if ($command !== null)
{
    echo "<br><a href=planet.php?planet_id=$planet_id>" . $langvars['l_clickme'] . "</a> " . $langvars['l_toplanetmenu'] . "<br><br>";
}

if ($tkireg->allow_ibank)
{
    echo $langvars['l_ifyouneedplan'] . " <a href=\"ibank.php?planet_id=$planet_id\">" . $langvars['l_ibank_term'] . "</a>.<br><br>";
}

echo "<a href =\"bounty.php\">" . $langvars['l_by_placebounty'] . "</a><p>";

Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
