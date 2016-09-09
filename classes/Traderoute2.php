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
// File: classes/Traderoute2.php

namespace Tki;

class Traderoute2
{
    public static function traderouteNew(\PDO $pdo_db, $db, $lang, Reg $tkireg, $traderoute_id, $template, $num_traderoutes, Array $playerinfo)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer'));
        $editroute = null;

        if ($traderoute_id !== null)
        {
            $result = $db->Execute("SELECT * FROM {$db->prefix}traderoutes WHERE traderoute_id = ?;", array($traderoute_id));
            \Tki\Db::LogDbErrors($pdo_db, $result, __LINE__, __FILE__);

            if (!$result || $result->EOF)
            {
                self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_editerr'], $template);
            }

            $editroute = $result->fields;

            if ($editroute['owner'] != $playerinfo['ship_id'])
            {
                self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_notowner'], $template);
            }
        }

        if ($num_traderoutes >= $tkireg->max_traderoutes_player && ($editroute === null))
        {
            self::traderouteDie($pdo_db, $lang, $tkireg, '<p>' . $langvars['l_tdr_maxtdr'].'<p>', $template);
        }

        echo "<p><font size=3 color=blue><strong>";

        if ($editroute === null)
        {
            echo $langvars['l_tdr_createnew'];
        }
        else
        {
            echo $langvars['l_tdr_editinga'] . " ";
        }

        echo $langvars['l_tdr_traderoute'] . "</strong></font><p>";

        // Get Planet info Team and Personal

        $result = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE owner = ? ORDER BY sector_id", array($playerinfo['ship_id']));
        \Tki\Db::LogDbErrors($pdo_db, $result, __LINE__, __FILE__);

        $num_planets = $result->RecordCount();
        $i = 0;
        $planets = array();
        while (!$result->EOF)
        {
            $planets[$i] = $result->fields;

            if ($planets[$i]['name'] === null)
            {
                $planets[$i]['name'] = $langvars['l_tdr_unnamed'];
            }

            $i++;
            $result->MoveNext();
        }

        $result = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE team = ? AND team != 0 AND owner <> ? ORDER BY sector_id", array($playerinfo['team'], $playerinfo['ship_id']));
        \Tki\Db::LogDbErrors($pdo_db, $result, __LINE__, __FILE__);

        $num_team_planets = $result->RecordCount();
        $i = 0;
        $planets_team = array();
        while (!$result->EOF)
        {
            $planets_team[$i] = $result->fields;

            if ($planets_team[$i]['name'] === null)
            {
                $planets_team[$i]['name'] = $langvars['l_tdr_unnamed'];
            }

            $i++;
            $result->MoveNext();
        }

        // Display Current Sector
        echo $langvars['l_tdr_cursector'] . " " . $playerinfo['sector'] . "<br>";

        // Start of form for starting location
        echo "
            <form accept-charset='utf-8' action=traderoute.php?command=create method=post>
            <table border=0><tr>
            <td align=right><font size=2><strong>" . $langvars['l_tdr_selspoint'] . " <br>&nbsp;</strong></font></td>
            <tr>
            <td align=right><font size=2>" . $langvars['l_tdr_port'] . " : </font></td>
            <td><input type=radio name=\"ptype1\" value=\"port\"
            ";

        if (($editroute === null) || ($editroute !== null) && $editroute['source_type'] == 'P')
        {
            echo " checked";
        }

        echo "
            ></td>
            <td>&nbsp;&nbsp;<input type=text name=port_id1 size=20 align='center'
            ";

        if (($editroute !== null) && $editroute['source_type'] == 'P')
        {
            echo " value=\"$editroute[source_id]\"";
        }

        echo "
            ></td>
            </tr><tr>
            ";

        // Personal Planet
        echo "
            <td align=right><font size=2>Personal " . $langvars['l_tdr_planet'] . " : </font></td>
            <td><input type=radio name=\"ptype1\" value=\"planet\"
            ";

        if (($editroute !== null) && $editroute['source_type'] == 'L')
        {
            echo " checked";
        }

        echo '
            ></td>
            <td>&nbsp;&nbsp;<select name=planet_id1>
            ';

        if ($num_planets == 0)
        {
            echo "<option value=none>" . $langvars['l_tdr_none'] . "</option>";
        }
        else
        {
            $i = 0;
            while ($i < $num_planets)
            {
                echo "<option ";

                if ($planets[$i]['planet_id'] == $editroute['source_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets[$i]['planet_id'] . ">" . $planets[$i]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets[$i]['sector_id'] . "</option>";
                $i++;
            }
        }

        // Team Planet
        echo "
            </tr><tr>
            <td align=right><font size=2>Team " . $langvars['l_tdr_planet'] . " : </font></td>
            <td><input type=radio name=\"ptype1\" value=\"team_planet\"
            ";

        if (($editroute !== null) && $editroute['source_type'] == 'C')
        {
            echo " checked";
        }

        echo '
            ></td>
            <td>&nbsp;&nbsp;<select name=team_planet_id1>
            ';

        if ($num_team_planets == 0)
        {
            echo "<option value=none>" . $langvars['l_tdr_none'] . "</option>";
        }
        else
        {
            $i = 0;
            while ($i < $num_team_planets)
            {
                echo "<option ";

                if ($planets_team[$i]['planet_id'] == $editroute['source_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets_team[$i]['planet_id'] . ">" . $planets_team[$i]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets_team[$i]['sector_id'] . "</option>";
                $i++;
            }
        }

        echo "
            </select>
            </tr>";

        // Begin Ending point selection
        echo "
            <tr><td>&nbsp;
            </tr><tr>
            <td align=right><font size=2><strong>" . $langvars['l_tdr_selendpoint'] . " : <br>&nbsp;</strong></font></td>
            <tr>
            <td align=right><font size=2>" . $langvars['l_tdr_port'] . " : </font></td>
            <td><input type=radio name=\"ptype2\" value=\"port\"
            ";

        if (($editroute === null) || ($editroute !== null && $editroute['dest_type'] == 'P'))
        {
            echo " checked";
        }

        echo '
            ></td>
            <td>&nbsp;&nbsp;<input type=text name=port_id2 size=20 align="center"
            ';

        if ($editroute !== null && $editroute['dest_type'] == 'P')
        {
            echo " value=\"$editroute[dest_id]\"";
        }

        echo "
            ></td>
            </tr>";

        // Personal Planet
        echo "
            <tr>
            <td align=right><font size=2>Personal " . $langvars['l_tdr_planet'] . " : </font></td>
            <td><input type=radio name=\"ptype2\" value=\"planet\"
            ";

        if ($editroute !== null && $editroute['dest_type'] == 'L')
        {
            echo " checked";
        }

        echo '
            ></td>
            <td>&nbsp;&nbsp;<select name=planet_id2>
            ';

        if ($num_planets == 0)
        {
            echo "<option value=none>" . $langvars['l_tdr_none'] . "</option>";
        }
        else
        {
            $i = 0;
            while ($i < $num_planets)
            {
                echo "<option ";

                if ($planets[$i]['planet_id'] == $editroute['dest_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets[$i]['planet_id'] . ">" . $planets[$i]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets[$i]['sector_id'] . "</option>";
                $i++;
            }
        }

        // Team Planet
        echo "
            </tr><tr>
            <td align=right><font size=2>Team " . $langvars['l_tdr_planet'] . " : </font></td>
            <td><input type=radio name=\"ptype2\" value=\"team_planet\"
            ";

        if ($editroute !== null && $editroute['dest_type'] == 'C')
        {
            echo " checked";
        }

        echo '
            ></td>
            <td>&nbsp;&nbsp;<select name=team_planet_id2>
            ';

        if ($num_team_planets == 0)
        {
            echo "<option value=none>" . $langvars['l_tdr_none'] . "</option>";
        }
        else
        {
            $i = 0;
            while ($i < $num_team_planets)
            {
                echo "<option ";

                if ($planets_team[$i]['planet_id'] == $editroute['dest_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets_team[$i]['planet_id'] . ">" . $planets_team[$i]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets_team[$i]['sector_id'] . "</option>";
                $i++;
            }
        }

        echo "
            </select>
            </tr>";

        echo "
            </select>
            </tr><tr>
            <td>&nbsp;
            </tr><tr>
            <td align=right><font size=2><strong>" . $langvars['l_tdr_selmovetype'] . " : </strong></font></td>
            <td colspan=2 valign=top><font size=2><input type=radio name=\"move_type\" value=\"realspace\"
            ";

        if ($editroute === null || ($editroute !== null && $editroute['move_type'] == 'R'))
        {
            echo " checked";
        }

        echo "
            >&nbsp;" . $langvars['l_tdr_realspace'] . "&nbsp;&nbsp<font size=2><input type=radio name=\"move_type\" value=\"warp\"
            ";

        if ($editroute !== null && $editroute['move_type'] == 'W')
        {
            echo " checked";
        }

        echo "
            >&nbsp;" . $langvars['l_tdr_warp'] . "</font></td>
            </tr><tr>
            <td align=right><font size=2><strong>" . $langvars['l_tdr_selcircuit'] . " : </strong></font></td>
            <td colspan=2 valign=top><font size=2><input type=radio name=\"circuit_type\" value=\"1\"
            ";

        if (($editroute === null) || ($editroute !== null) && $editroute['circuit'] == '1')
        {
            echo " checked";
        }

        echo "
            >&nbsp;" . $langvars['l_tdr_oneway'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=\"circuit_type\" value=\"2\"
            ";

        if ($editroute !== null && $editroute['circuit'] == '2')
        {
            echo " checked";
        }

        echo "
            >&nbsp;" . $langvars['l_tdr_bothways'] . "</font></td>
            </tr><tr>
            <td>&nbsp;
            </tr><tr>
            <td><td><td align='center'>
            ";

        if ($editroute === null)
        {
            echo "<input type=submit value=\"" . $langvars['l_tdr_create'] . "\">";
        }
        else
        {
            echo "<input type=hidden name=editing value=$editroute[traderoute_id]>";
            echo "<input type=submit value=\"" . $langvars['l_tdr_modify'] . "\">";
        }

        $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);

        echo "
            </table>
            " . $langvars['l_tdr_returnmenu'] . "<br>
            </form>
            ";

        echo "<div style='text-align:left;'>\n";
        \Tki\Text::gotomain($pdo_db, $lang);
        echo "</div>\n";

        \Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
        die();
    }

    public static function traderouteDie(\PDO $pdo_db, $lang, Reg $tkireg, $error_msg, $template)
    {
        echo "<p>" . $error_msg . "<p>";
        echo "<div style='text-align:left;'>\n";
        \Tki\Text::gotomain($pdo_db, $lang);
        echo "</div>\n";
        \Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
        die();
    }

    public static function traderouteCheckCompatible($db, \PDO $pdo_db, $lang, $type1, $type2, $move, $circuit, $src, $dest, Array $playerinfo, Reg $tkireg, $template)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        // Check circuit compatibility (we only use types 1 and 2 so block anything else)
        if ($circuit != "1" && $circuit != "2")
        {
            \Tki\AdminLog::writeLog($pdo_db, LOG_RAW, "{$playerinfo['ship_id']}|Tried to use an invalid circuit_type of '{$circuit}', This is normally a result from using an external page and should be banned.");
            self::traderouteDie($pdo_db, $lang, $tkireg, "Invalid Circuit type!<br>*** Possible Exploit has been reported to the admin. ***", $template);
        }

        // Check warp links compatibility
        if ($move == 'warp')
        {
            $query = $db->Execute("SELECT link_id FROM {$db->prefix}links WHERE link_start = ? AND link_dest = ?;", array($src['sector_id'], $dest['sector_id']));
            \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);
            if ($query->EOF)
            {
                $langvars['l_tdr_nowlink1'] = str_replace("[tdr_src_sector_id]", $src['sector_id'], $langvars['l_tdr_nowlink1']);
                $langvars['l_tdr_nowlink1'] = str_replace("[tdr_dest_sector_id]", $dest['sector_id'], $langvars['l_tdr_nowlink1']);
                self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_nowlink1'], $template);
            }

            if ($circuit == '2')
            {
                $query = $db->Execute("SELECT link_id FROM {$db->prefix}links WHERE link_start = ? AND link_dest = ?;", array($dest['sector_id'], $src['sector_id']));
                \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);
                if ($query->EOF)
                {
                    $langvars['l_tdr_nowlink2'] = str_replace("[tdr_src_sector_id]", $src['sector_id'], $langvars['l_tdr_nowlink2']);
                    $langvars['l_tdr_nowlink2'] = str_replace("[tdr_dest_sector_id]", $dest['sector_id'], $langvars['l_tdr_nowlink2']);
                    self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_nowlink2'], $template);
                }
            }
        }

        // Check ports compatibility
        if ($type1 == 'port')
        {
            if ($src['port_type'] == 'special')
            {
                if (($type2 != 'planet') && ($type2 != 'team_planet'))
                {
                    self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_sportissrc'], $template);
                }

                if ($dest['owner'] != $playerinfo['ship_id'] && ($dest['team'] == 0 || ($dest['team'] != $playerinfo['team'])))
                {
                    self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_notownplanet'], $template);
                }
            }
            else
            {
                if ($type2 == 'planet')
                {
                    self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_planetisdest'], $template);
                }

                if ($src['port_type'] == $dest['port_type'])
                {
                    self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_samecom'], $template);
                }
            }
        }
        else
        {
            if (array_key_exists('port_type', $dest) === true && $dest['port_type'] == 'special')
            {
                self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_sportcom'], $template);
            }
        }
    }

    public static function traderouteDistance(\PDO $pdo_db, string $type1, string $type2, $start, $dest, $circuit, Array $playerinfo, Reg $tkireg, $sells = 'N')
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

    public static function traderouteCreate($db, \PDO $pdo_db, $lang, Reg $tkireg, $template, Array $playerinfo, $num_traderoutes, $ptype1, $ptype2, $port_id1, $port_id2, int $planet_id1, int $planet_id2, $team_planet_id1, $team_planet_id2, $move_type, $circuit_type, $editing)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        $src_id = null;
        $dest_id = null;
        $src_type = null;
        $dest_type = null;

        if ($num_traderoutes >= $tkireg->max_traderoutes_player && empty ($editing))
        { // Dont let them exceed max traderoutes
            self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_maxtdr'], $template);
        }

        // Database sanity check for source
        if ($ptype1 == 'port')
        {
            // Check for valid Source Port
            if ($port_id1 >= $tkireg->max_sectors)
            {
                self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_invalidspoint'], $template);
            }

            $query = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id = ?;", array($port_id1));
            \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);
            if (!$query || $query->EOF)
            {
                $langvars['l_tdr_errnotvalidport'] = str_replace("[tdr_port_id]", $port_id1, $langvars['l_tdr_errnotvalidport']);
                self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnotvalidport'], $template);
            }

            // OK we definitely have a port here
            $source = $query->fields;
            if ($source['port_type'] == 'none')
            {
                $langvars['l_tdr_errnoport'] = str_replace("[tdr_port_id]", $port_id1, $langvars['l_tdr_errnoport']);
                self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnoport'], $template);
            }
        }
        else
        {
            $query = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id = ?;", array($planet_id1));
            \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);
            $source = $query->fields;
            if (!$query || $query->EOF)
            {
                self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnosrc'], $template);
            }

            // Check for valid Source Planet
            if ($source['sector_id'] >= $tkireg->max_sectors)
            {
                self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_invalidsrc'], $template);
            }

            if ($source['owner'] != $playerinfo['ship_id'])
            {
                if (($playerinfo['team'] == 0 || $playerinfo['team'] != $source['team']) && $source['sells'] == 'N')
                {
                    // $langvars['l_tdr_errnotownnotsell'] = str_replace("[tdr_source_name]", $source[name], $langvars['l_tdr_errnotownnotsell']);
                    // $langvars['l_tdr_errnotownnotsell'] = str_replace("[tdr_source_sector_id]", $source[sector_id], $langvars['l_tdr_errnotownnotsell']);
                    // self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnotownnotsell'], $template);

                    // Check for valid Owned Source Planet
                    \Tki\AdminLog::writeLog($pdo_db, 902, "{$playerinfo['ship_id']}|Tried to find someones planet: {$planet_id1} as source.");
                    self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_invalidsrc'], $template);
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
            self::traderouteDie($pdo_db, $lang, $tkireg, "You cannot create a traderoute from a sector you have not visited!", $template);
        }

        // Note: shouldnt we, more realistically, require a ship to be *IN* the source sector to create the traderoute?
        // Database sanity check for dest
        if ($ptype2 == 'port')
        {
            // Check for valid Dest Port
            if ($port_id2 >= $tkireg->max_sectors)
            {
                self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_invaliddport'], $template);
            }

            $query = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id = ?;", array($port_id2));
            \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);
            if (!$query || $query->EOF)
            {
                $langvars['l_tdr_errnotvaliddestport'] = str_replace("[tdr_port_id]", $port_id2, $langvars['l_tdr_errnotvaliddestport']);
                self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnotvaliddestport'], $template);
            }

            $destination = $query->fields;

            if ($destination['port_type'] == 'none')
            {
                $langvars['l_tdr_errnoport2'] = str_replace("[tdr_port_id]", $port_id2, $langvars['l_tdr_errnoport2']);
                self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnoport2'], $template);
            }
        }
        else
        {
            $query = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id = ?;", array($planet_id2));
            \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);
            $destination = $query->fields;
            if (!$query || $query->EOF)
            {
                self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnodestplanet'], $template);
            }

            // Check for valid Dest Planet
            if ($destination['sector_id'] >= $tkireg->max_sectors)
            {
                self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_invaliddplanet'], $template);
            }

            if ($destination['owner'] != $playerinfo['ship_id'] && $destination['sells'] == 'N')
            {
                // $langvars['l_tdr_errnotownnotsell2'] = str_replace("[tdr_dest_name]", $destination['name'], $langvars['l_tdr_errnotownnotsell2']);
                // $langvars['l_tdr_errnotownnotsell2'] = str_replace("[tdr_dest_sector_id]", $destination['sector_id'], $langvars['l_tdr_errnotownnotsell2']);
                // self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_errnotownnotsell2'], $template);

                // Check for valid Owned Source Planet
                \Tki\AdminLog::writeLog($pdo_db, 902, "{$playerinfo['ship_id']}|Tried to find someones planet: {$planet_id2} as dest.");
                self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_invaliddplanet'], $template);
            }
        }

        // OK now we have $destination lets see if we've been there.
        $pl2query = $db->Execute("SELECT * FROM {$db->prefix}movement_log WHERE sector_id = ? AND ship_id = ?;", array($destination['sector_id'], $playerinfo['ship_id']));
        \Tki\Db::LogDbErrors($pdo_db, $pl2query, __LINE__, __FILE__);
        $num_res2 = $pl2query->numRows();
        if ($num_res2 == 0)
        {
            self::traderouteDie($pdo_db, $lang, $tkireg, "You cannot create a traderoute into a sector you have not visited!", $template);
        }

        // Check destination - we cannot trade INTO a special port
        if (array_key_exists('port_type', $destination) === true && $destination['port_type'] == 'special')
        {
            self::traderouteDie($pdo_db, $lang, $tkireg, "You cannot create a traderoute into a special port!", $template);
        }

        // Check traderoute for src => dest
        self::traderouteCheckCompatible($db, $pdo_db, $lang, $ptype1, $ptype2, $move_type, $circuit_type, $source, $destination, $playerinfo, $tkireg, $template);

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
        self::traderouteDie($pdo_db, $lang, $tkireg, null, $template);
    }

    public static function traderouteDelete(\PDO $pdo_db, $db, $lang, Array $langvars, Reg $tkireg, $template, Array $playerinfo, $confirm, $traderoute_id)
    {
        $query = $db->Execute("SELECT * FROM {$db->prefix}traderoutes WHERE traderoute_id = ?;", array($traderoute_id));
        \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);

        if (!$query || $query->EOF)
        {
            self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_doesntexist'], $template);
        }

        $delroute = $query->fields;

        if ($delroute['owner'] != $playerinfo['ship_id'])
        {
            self::traderouteDie($pdo_db, $lang, $tkireg, $langvars['l_tdr_notowntdr'], $template);
        }

        if (!empty ($confirm))
        {
            $query = $db->Execute("DELETE FROM {$db->prefix}traderoutes WHERE traderoute_id = ?;", array($traderoute_id));
            \Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);
            $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);
            echo $langvars['l_tdr_deleted'] . " " . $langvars['l_tdr_returnmenu'];
            self::traderouteDie($pdo_db, $lang, $tkireg, null, $template);
        }
    }

    public static function traderouteSettings(\PDO $pdo_db, $lang, Reg $tkireg, $template, Array $playerinfo)
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
        self::traderouteDie($pdo_db, $lang, $tkireg, null, $template);
    }

    public static function traderouteSetsettings($db, \PDO $pdo_db, $lang, Reg $tkireg, $template, Array $playerinfo, $colonists, $fighters, $torps, $energy)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        empty ($colonists) ? $colonists = 'N' : $colonists = 'Y';
        empty ($fighters) ? $fighters = 'N' : $fighters = 'Y';
        empty ($torps) ? $torps = 'N' : $torps = 'Y';

        $resa = $db->Execute("UPDATE {$db->prefix}ships SET trade_colonists = ?, trade_fighters = ?, trade_torps = ?, trade_energy = ? WHERE ship_id = ?;", array($colonists, $fighters, $torps, $energy, $playerinfo['ship_id']));
        \Tki\Db::LogDbErrors($pdo_db, $resa, __LINE__, __FILE__);

        $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);
        echo $langvars['l_tdr_globalsetsaved'] . " " . $langvars['l_tdr_returnmenu'];
        self::traderouteDie($pdo_db, $lang, $tkireg, null, $template);
    }

    public static function traderouteResultsTableTop(\PDO $pdo_db, $lang, Reg $tkireg)
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
    public static function traderouteResultsDisplayTotals(\PDO $pdo_db, $lang, $total_profit)
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
    public static function traderouteResultsDisplaySummary(\PDO $pdo_db, $lang, $tdr_display_creds, $dist, Array $playerinfo)
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
