<?php
declare(strict_types = 1);
// The Kabal Invasion - A web-based 4X space game
// Copyright © 2014 The Kabal Invasion development team, Ron Harwood, and the BNT development team
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero General Public License as
//  published by the Free Software Foundation, either version 3 of the
//  License, or (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero General Public License for more details.
//
//  You should have received a copy of the GNU Affero General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// File: classes/Traderoute3.php

namespace Tki;

class Traderoute3
{
    public static function traderouteDistance(\PDO $pdo_db, string $type1, string $type2, $start, $dest, $circuit, array $playerinfo, Reg $tkireg, $sells = 'N')
    {
        $retvalue = array();
        $retvalue['triptime'] = 0;
        $retvalue['scooped1'] = 0;
        $retvalue['scooped2'] = 0;
        $retvalue['scooped'] = 0;

        if ($type1 == 'L')
        {
            $sql = "SELECT * FROM ::prefix::universe WHERE sector_id=:sector_id LIMIT 1";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $start);
            $stmt->execute();
            $start = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if ($type2 == 'L')
        {
            $sql = "SELECT * FROM ::prefix::universe WHERE sector_id=:sector_id LIMIT 1";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':dest', $dest);
            $stmt->execute();
            $dest = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if ($start['sector_id'] == $dest['sector_id'])
        {
            if ($circuit == '1')
            {
                $retvalue['triptime'] = '1';
            }
            else
            {
                $retvalue['triptime'] = '2';
            }

            return $retvalue;
        }

        $deg = pi() / 180;

        $sa1 = $start['angle1'] * $deg;
        $sa2 = $start['angle2'] * $deg;
        $fa1 = $dest['angle1'] * $deg;
        $fa2 = $dest['angle2'] * $deg;
        $x = $start['distance'] * sin($sa1) * cos($sa2) - $dest['distance'] * sin($fa1) * cos($fa2);
        $y = $start['distance'] * sin($sa1) * sin($sa2) - $dest['distance'] * sin($fa1) * sin($fa2);
        $z = $start['distance'] * cos($sa1) - $dest['distance'] * cos($fa1);
        $distance = round(sqrt(pow($x, 2) + pow($y, 2) + pow($z, 2)));
        $shipspeed = pow($tkireg->level_factor, $playerinfo['engines']);
        $triptime = round($distance / $shipspeed);

        if (!$triptime && $dest['sector_id'] != $playerinfo['sector'])
        {
            $triptime = 1;
        }

        if ($playerinfo['dev_fuelscoop'] == "Y")
        {
            $energyscooped = $distance * 100;
        }
        else
        {
            $energyscooped = 0;
        }

        if ($playerinfo['dev_fuelscoop'] == "Y" && !$energyscooped && $triptime == 1)
        {
            $energyscooped = 100;
        }

        $free_power = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg) - $playerinfo['ship_energy'];

        if ($free_power < $energyscooped)
        {
            $energyscooped = $free_power;
        }

        if ($energyscooped < 1)
        {
            $energyscooped = 0;
        }

        $retvalue['scooped1'] = $energyscooped;

        if ($circuit == '2')
        {
            if ($sells == 'Y' && $playerinfo['dev_fuelscoop'] == 'Y' && $type2 == 'P' && $dest['port_type'] != 'energy')
            {
                $energyscooped = $distance * 100;
                $free_power = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg);

                if ($free_power < $energyscooped)
                {
                    $energyscooped = $free_power;
                }

                $retvalue['scooped2'] = $energyscooped;
            }
            elseif ($playerinfo['dev_fuelscoop'] == 'Y')
            {
                $energyscooped = $distance * 100;
                $free_power = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg) - $retvalue['scooped1'] - $playerinfo['ship_energy'];

                if ($free_power < $energyscooped)
                {
                    $energyscooped = $free_power;
                }

                $retvalue['scooped2'] = $energyscooped;
            }
        }

        if ($circuit == '2')
        {
            $triptime *= 2;
            $triptime += 2;
        }
        else
        {
            $triptime++;
        }

        $retvalue['triptime'] = $triptime;
        $retvalue['scooped'] = $retvalue['scooped1'] + $retvalue['scooped2'];

        return $retvalue;
    }

    public static function traderouteCreate(\ADODB_mysqli $db, \PDO $pdo_db, string $lang, Reg $tkireg, Smarty $template, array $playerinfo, $num_traderoutes, $ptype1, $ptype2, $port_id1, $port_id2, int $planet_id1, int $planet_id2, $team_planet_id1, $team_planet_id2, $move_type, $circuit_type, $editing)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        $src_id = null;
        $dest_id = null;
        $src_type = null;
        $dest_type = null;

        if ($num_traderoutes >= $tkireg->max_traderoutes_player && empty ($editing))
        { // Dont let them exceed max traderoutes
            \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_maxtdr'], $template);
        }

        // Database sanity check for source
        if ($ptype1 == 'port')
        {
            // Check for valid Source Port
            if ($port_id1 >= $tkireg->max_sectors)
            {
                \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_invalidspoint'], $template);
            }

            $query = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id = ?;", array($port_id1));
            \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);
            if (!$query || $query->EOF)
            {
                $langvars['l_tdr_errnotvalidport'] = str_replace("[tdr_port_id]", $port_id1, $langvars['l_tdr_errnotvalidport']);
                \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnotvalidport'], $template);
            }

            // OK we definitely have a port here
            $source = $query->fields;
            if ($source['port_type'] == 'none')
            {
                $langvars['l_tdr_errnoport'] = str_replace("[tdr_port_id]", $port_id1, $langvars['l_tdr_errnoport']);
                \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnoport'], $template);
            }
        }
        else
        {
            $query = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id = ?;", array($planet_id1));
            \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);
            $source = $query->fields;
            if (!$query || $query->EOF)
            {
                \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnosrc'], $template);
            }

            // Check for valid Source Planet
            if ($source['sector_id'] >= $tkireg->max_sectors)
            {
                \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_invalidsrc'], $template);
            }

            if ($source['owner'] != $playerinfo['ship_id'])
            {
                if (($playerinfo['team'] == 0 || $playerinfo['team'] != $source['team']) && $source['sells'] == 'N')
                {
                    // $langvars['l_tdr_errnotownnotsell'] = str_replace("[tdr_source_name]", $source[name], $langvars['l_tdr_errnotownnotsell']);
                    // $langvars['l_tdr_errnotownnotsell'] = str_replace("[tdr_source_sector_id]", $source[sector_id], $langvars['l_tdr_errnotownnotsell']);
                    // \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnotownnotsell'], $template);

                    // Check for valid Owned Source Planet
                    \Tki\AdminLog::writeLog($pdo_db, 902, "{$playerinfo['ship_id']}|Tried to find someones planet: {$planet_id1} as source.");
                    \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_invalidsrc'], $template);
                }
            }
        }

        // OK we have $source, *probably* now lets see if we have ever been there
        // Attempting to fix the map the universe via traderoute bug

        $pl1query = $db->Execute("SELECT * FROM {$db->prefix}movement_log WHERE sector_id = ? AND ship_id = ?;", array($source['sector_id'], $playerinfo['ship_id']));
        \Tki\Db::LogDbErrors($pdo_db, $pl1query, __LINE__, __FILE__);
        $num_res1 = $pl1query->numRows();
        if ($num_res1 == 0)
        {
            \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, "You cannot create a traderoute from a sector you have not visited!", $template);
        }

        // Note: shouldnt we, more realistically, require a ship to be *IN* the source sector to create the traderoute?
        // Database sanity check for dest
        if ($ptype2 == 'port')
        {
            // Check for valid Dest Port
            if ($port_id2 >= $tkireg->max_sectors)
            {
                \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_invaliddport'], $template);
            }

            $query = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id = ?;", array($port_id2));
            \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);
            if (!$query || $query->EOF)
            {
                $langvars['l_tdr_errnotvaliddestport'] = str_replace("[tdr_port_id]", $port_id2, $langvars['l_tdr_errnotvaliddestport']);
                \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnotvaliddestport'], $template);
            }

            $destination = $query->fields;

            if ($destination['port_type'] == 'none')
            {
                $langvars['l_tdr_errnoport2'] = str_replace("[tdr_port_id]", $port_id2, $langvars['l_tdr_errnoport2']);
                \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnoport2'], $template);
            }
        }
        else
        {
            $query = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id = ?;", array($planet_id2));
            \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);
            $destination = $query->fields;
            if (!$query || $query->EOF)
            {
                \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnodestplanet'], $template);
            }

            // Check for valid Dest Planet
            if ($destination['sector_id'] >= $tkireg->max_sectors)
            {
                \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_invaliddplanet'], $template);
            }

            if ($destination['owner'] != $playerinfo['ship_id'] && $destination['sells'] == 'N')
            {
                // $langvars['l_tdr_errnotownnotsell2'] = str_replace("[tdr_dest_name]", $destination['name'], $langvars['l_tdr_errnotownnotsell2']);
                // $langvars['l_tdr_errnotownnotsell2'] = str_replace("[tdr_dest_sector_id]", $destination['sector_id'], $langvars['l_tdr_errnotownnotsell2']);
                // \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnotownnotsell2'], $template);

                // Check for valid Owned Source Planet
                \Tki\AdminLog::writeLog($pdo_db, 902, "{$playerinfo['ship_id']}|Tried to find someones planet: {$planet_id2} as dest.");
                \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_invaliddplanet'], $template);
            }
        }

        // OK now we have $destination lets see if we've been there.
        $pl2query = $db->Execute("SELECT * FROM {$db->prefix}movement_log WHERE sector_id = ? AND ship_id = ?;", array($destination['sector_id'], $playerinfo['ship_id']));
        \Tki\Db::LogDbErrors($pdo_db, $pl2query, __LINE__, __FILE__);
        $num_res2 = $pl2query->numRows();
        if ($num_res2 == 0)
        {
            \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, "You cannot create a traderoute into a sector you have not visited!", $template);
        }

        // Check destination - we cannot trade INTO a special port
        if (array_key_exists('port_type', $destination) === true && $destination['port_type'] == 'special')
        {
            \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, "You cannot create a traderoute into a special port!", $template);
        }

        // Check traderoute for src => dest
        \Tki\Traderoute2::traderouteCheckCompatible($db, $pdo_db, $lang, $ptype1, $ptype2, $move_type, $circuit_type, $source, $destination, $playerinfo, $tkireg, $template);

        if ($ptype1 == 'port')
        {
            $src_id = $port_id1;
        }
        elseif ($ptype1 == 'planet')
        {
            $src_id = $planet_id1;
        }
        elseif ($ptype1 == 'team_planet')
        {
            $src_id = $team_planet_id1;
        }

        if ($ptype2 == 'port')
        {
            $dest_id = $port_id2;
        }
        elseif ($ptype2 == 'planet')
        {
            $dest_id = $planet_id2;
        }
        elseif ($ptype2 == 'team_planet')
        {
            $dest_id = $team_planet_id2;
        }

        if ($ptype1 == 'port')
        {
            $src_type = 'P';
        }
        elseif ($ptype1 == 'planet')
        {
            $src_type = 'L';
        }
        elseif ($ptype1 == 'team_planet')
        {
            $src_type = 'C';
        }

        if ($ptype2 == 'port')
        {
            $dest_type = 'P';
        }
        elseif ($ptype2 == 'planet')
        {
            $dest_type = 'L';
        }
        elseif ($ptype2 == 'team_planet')
        {
            $dest_type = 'C';
        }

        if ($move_type == 'realspace')
        {
            $mtype = 'R';
        }
        else
        {
            $mtype = 'W';
        }

        if (empty ($editing))
        {
            $query = $db->Execute("INSERT INTO {$db->prefix}traderoutes VALUES(NULL, ?, ?, ?, ?, ?, ?, ?);", array($src_id, $dest_id, $src_type, $dest_type, $mtype, $playerinfo['ship_id'], $circuit_type));
            \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);
            echo "<p>" . $langvars['l_tdr_newtdrcreated'];
        }
        else
        {
            $query = $db->Execute("UPDATE {$db->prefix}traderoutes SET source_id = ?, dest_id = ?, source_type = ?, dest_type = ?, move_type = ?, owner = ?, circuit = ? WHERE traderoute_id = ?;", array($src_id, $dest_id, $src_type, $dest_type, $mtype, $playerinfo['ship_id'], $circuit_type, $editing));
            \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);
            echo "<p>" . $langvars['l_tdr_modified'];
        }

        $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);
        echo " " . $langvars['l_tdr_returnmenu'];
        \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, null, $template);
    }

    public static function traderouteDelete(\PDO $pdo_db, \ADODB_mysqli $db, string $lang, array $langvars, Reg $tkireg, Smarty $template, array $playerinfo, $confirm, int $traderoute_id)
    {
        $query = $db->Execute("SELECT * FROM {$db->prefix}traderoutes WHERE traderoute_id = ?;", array($traderoute_id));
        \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);

        if (!$query || $query->EOF)
        {
            \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_doesntexist'], $template);
        }

        $delroute = $query->fields;

        if ($delroute['owner'] != $playerinfo['ship_id'])
        {
            \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_notowntdr'], $template);
        }

        if (!empty ($confirm))
        {
            $query = $db->Execute("DELETE FROM {$db->prefix}traderoutes WHERE traderoute_id = ?;", array($traderoute_id));
            \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);
            $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);
            echo $langvars['l_tdr_deleted'] . " " . $langvars['l_tdr_returnmenu'];
            \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, null, $template);
        }
    }

    public static function traderouteSettings(\PDO $pdo_db, string $lang, Reg $tkireg, Smarty $template, array $playerinfo)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        echo "<p><font size=3 color=blue><strong>" . $langvars['l_tdr_globalset'] . "</strong></font><p>";
        echo "<font color=white size=2><strong>" . $langvars['l_tdr_sportsrc'] . " :</strong></font><p>".
             "<form accept-charset='utf-8' action=traderoute.php?command=setsettings method=post>".
             "<table border=0><tr>".
             "<td><font size=2 color=white> - " . $langvars['l_tdr_colonists'] . " :</font></td>".
             "<td><input type=checkbox name=colonists";

        if ($playerinfo['trade_colonists'] == 'Y')
        {
            echo " checked";
        }

        echo "></tr><tr>".
            "<td><font size=2 color=white> - " . $langvars['l_tdr_fighters'] . " :</font></td>".
            "<td><input type=checkbox name=fighters";

        if ($playerinfo['trade_fighters'] == 'Y')
        {
            echo " checked";
        }

        echo "></tr><tr>".
            "<td><font size=2 color=white> - " . $langvars['l_tdr_torps'] . " :</font></td>".
            "<td><input type=checkbox name=torps";

        if ($playerinfo['trade_torps'] == 'Y')
        {
            echo " checked";
        }

        echo "></tr>".
            "</table>".
            "<p>".
            "<font color=white size=2><strong>" . $langvars['l_tdr_tdrescooped'] . " :</strong></font><p>".
            "<table border=0><tr>".
            "<td><font size=2 color=white>&nbsp;&nbsp;&nbsp;" . $langvars['l_tdr_trade'] . "</font></td>".
            "<td><input type=radio name=energy value=\"Y\"";

        if ($playerinfo['trade_energy'] == 'Y')
        {
            echo " checked";
        }

        echo "></td></tr><tr>".
            "<td><font size=2 color=white>&nbsp;&nbsp;&nbsp;" . $langvars['l_tdr_keep'] . "</font></td>".
            "<td><input type=radio name=energy value=\"N\"";

        if ($playerinfo['trade_energy'] == 'N')
        {
            echo " checked";
        }

        echo "></td></tr><tr><td>&nbsp;</td></tr><tr><td>".
            "<td><input type=submit value=\"" . $langvars['l_tdr_save'] . "\"></td>".
            "</tr></table>".
            "</form>";

        $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);
        echo $langvars['l_tdr_returnmenu'];
        \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, null, $template);
    }

    public static function traderouteSetsettings(\ADODB_mysqli $db, \PDO $pdo_db, string $lang, Reg $tkireg, Smarty $template, array $playerinfo, $colonists, $fighters, $torps, $energy)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        empty ($colonists) ? $colonists = 'N' : $colonists = 'Y';
        empty ($fighters) ? $fighters = 'N' : $fighters = 'Y';
        empty ($torps) ? $torps = 'N' : $torps = 'Y';

        $resa = $db->Execute("UPDATE {$db->prefix}ships SET trade_colonists = ?, trade_fighters = ?, trade_torps = ?, trade_energy = ? WHERE ship_id = ?;", array($colonists, $fighters, $torps, $energy, $playerinfo['ship_id']));
        \Tki\Db::LogDbErrors($pdo_db, $resa, __LINE__, __FILE__);

        $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);
        echo $langvars['l_tdr_globalsetsaved'] . " " . $langvars['l_tdr_returnmenu'];
        \Tki\Traderoute2::traderouteDie($pdo_db, $lang, $tkireg, null, $template);
    }

    public static function traderouteResultsTableTop(\PDO $pdo_db, string $lang, Reg $tkireg)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        echo "<table border='1' cellspacing='1' cellpadding='2' width='65%' align='center'>\n";
        echo "  <tr bgcolor='" . $tkireg->color_line2 . "'>\n";
        echo "    <td align='center' colspan='7'><strong><font color='white'>" . $langvars['l_tdr_res'] . "</font></strong></td>\n";
        echo "  </tr>\n";
        echo "  <tr align='center' bgcolor='" . $tkireg->color_line2 . "'>\n";
        echo "    <td width='50%'><font size='2' color='white'><strong>";
    }

    public static function traderouteResultsSource()
    {
        echo "</strong></font></td>\n";
        echo "    <td width='50%'><font size='2' color='white'><strong>";
    }

    public static function traderouteResultsDestination(Reg $tkireg)
    {
        echo "</strong></font></td>\n";
        echo "  </tr>\n";
        echo "  <tr bgcolor='" . $tkireg->color_line1 . "'>\n";
        echo "    <td align='center'><font size='2' color='white'>";
    }

    public static function traderouteResultsCloseCell()
    {
        echo "</font></td>\n";
        echo "    <td align='center'><font size='2' color='white'>";
    }

    public static function traderouteResultsShowCost(Reg $tkireg)
    {
        echo "</font></td>\n";
        echo "  </tr>\n";
        echo "  <tr bgcolor='" . $tkireg->color_line2 . "'>\n";
        echo "    <td align='center'><font size='2' color='white'>";
    }

    public static function traderouteResultsCloseCost()
    {
        echo "</font></td>\n";
        echo "    <td align='center'><font size='2' color='white'>";
    }

    public static function traderouteResultsCloseTable()
    {
        echo "</font></td>\n";
        echo "  </tr>\n";
        echo "</table>\n";
        // echo "<p><center><font size=3 color=white><strong>\n";
    }

    /**
     * @param double $total_profit
     */
    public static function traderouteResultsDisplayTotals(\PDO $pdo_db, string $lang, $total_profit)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        if ($total_profit > 0)
        {
            echo "<p><center><font size=3 color=white><strong>" . $langvars['l_tdr_totalprofit'] . " : <font style='color:#0f0;'><strong>".number_format(abs($total_profit), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</strong></font><br>\n";
        }
        else
        {
            echo "<p><center><font size=3 color=white><strong>" . $langvars['l_tdr_totalcost'] . " : <font style='color:#f00;'><strong>".number_format(abs($total_profit), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</strong></font><br>\n";
        }
    }

    /**
     * @param string $tdr_display_creds
     */
    public static function traderouteResultsDisplaySummary(\PDO $pdo_db, string $lang, $tdr_display_creds, $dist, array $playerinfo)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        echo "\n<font size='3' color='white'><strong>" . $langvars['l_tdr_turnsused'] . " : <font style='color:#f00;'>$dist[triptime]</font></strong></font><br>";
        echo "\n<font size='3' color='white'><strong>" . $langvars['l_tdr_turnsleft'] . " : <font style='color:#0f0;'>$playerinfo[turns]</font></strong></font><br>";

        echo "\n<font size='3' color='white'><strong>" . $langvars['l_tdr_credits'] . " : <font style='color:#0f0;'> $tdr_display_creds\n</font></strong></font><br> </strong></font></center>\n";
        //echo "<font size='2'>\n";
    }

    public static function traderouteResultsShowRepeat($engage)
    {
        echo "<form accept-charset='utf-8' action='traderoute.php?engage=" . $engage . "' method='post'>\n";
        echo "<br>Enter times to repeat <input type='text' name='tr_repeat' value='1' size='5'> <input type='submit' value='submit'>\n";
        echo "</form>\n";
        // echo "<p>";
    }
}
