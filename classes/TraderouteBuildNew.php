<?php declare(strict_types = 1);
/**
 * classes/TraderouteBuildNew.php from The Kabal Invasion.
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

class TraderouteBuildNew
{
    public static function new(\PDO $pdo_db, string $lang, Reg $tkireg, Smarty $template, int $num_traderoutes, array $playerinfo, ?int $traderoute_id = null): void
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer'));
        $editroute = null;
        $planets = array();
        $num_planets = 0;

        if ($traderoute_id !== null)
        {
            $stmt = $pdo_db->prepare("SELECT * FROM ::prefix::traderoutes WHERE traderoute_id = :traderoute_id");
            $stmt->bindParam(':traderoute_id', $traderoute_id, \PDO::PARAM_INT);
            $editroute = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($editroute === null)
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $template, $langvars['l_tdr_editerr']);
            }

            if ($editroute['owner'] != $playerinfo['ship_id'])
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $template, $langvars['l_tdr_notowner']);
            }
        }

        if ($num_traderoutes >= $tkireg->max_traderoutes_player && ($editroute === null))
        {
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $template, '<p>' . $langvars['l_tdr_maxtdr'] . '<p>');
        }

        echo "<p><font size=3 color=blue><strong>";

        $tmp_output = $langvars['l_tdr_editinga'] . " ";
        if ($editroute === null)
        {
            $tmp_output = $langvars['l_tdr_createnew'];
        }

        echo $tmp_output;
        echo $langvars['l_tdr_traderoute'] . "</strong></font><p>";

        // Get Planet info Team and Personal
        $planet_loop = 0;
        $sql = "SELECT * FROM ::prefix::planets WHERE owner = :ship_id ORDER BY sector_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();
        $personal_planet_list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($personal_planet_list !== false)
        {
            foreach ($personal_planet_list as $tmp_planet)
            {
                $planets[$planet_loop] = $tmp_planet['link_dest'];

                if ($planets[$planet_loop]['name'] === null)
                {
                    $planets[$planet_loop]['name'] = $langvars['l_tdr_unnamed'];
                }

                $planet_loop++;
            }
        }

        $planet_loop = 0;
        $planets_team = array();
        $sql = "SELECT * FROM ::prefix::planets WHERE team = :player_team AND team <> 0 AND owner <> :ship_id ORDER BY sector_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':player_team', $playerinfo['team'], \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();
        $team_planet_list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($team_planet_list !== false)
        {
            foreach ($team_planet_list as $tmp_planet)
            {
                $planets_team[$planet_loop] = $tmp_planet['link_dest'];

                if ($planets_team[$planet_loop]['name'] === null)
                {
                    $planets_team[$planet_loop]['name'] = $langvars['l_tdr_unnamed'];
                }

                $planet_loop++;
            }
        }

        $num_team_planets = count ($planets_team);

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

        if (($editroute !== null) && $editroute['source_type'] == 'P')
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
            $counter = 0;
            while ($counter <= $num_planets)
            {
                echo "<option ";

                if ($planets[$counter]['planet_id'] == $editroute['source_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets[$counter]['planet_id'] . ">" . $planets[$counter]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets[$counter]['sector_id'] . "</option>";
                $counter++;
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
            $counter = 0;
            while ($counter < $num_team_planets)
            {
                echo "<option ";

                if ($planets_team[$counter]['planet_id'] == $editroute['source_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets_team[$counter]['planet_id'] . ">" . $planets_team[$counter]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets_team[$counter]['sector_id'] . "</option>";
                $counter++;
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

        if (($editroute !== null && $editroute['dest_type'] == 'P'))
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
            $counter = 0;
            while ($counter <= $num_planets)
            {
                echo "<option ";

                if ($planets[$counter]['planet_id'] == $editroute['dest_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets[$counter]['planet_id'] . ">" . $planets[$counter]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets[$counter]['sector_id'] . "</option>";
                $counter++;
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
            $counter = 0;
            while ($counter < $num_team_planets)
            {
                echo "<option ";

                if ($planets_team[$counter]['planet_id'] == $editroute['dest_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets_team[$counter]['planet_id'] . ">" . $planets_team[$counter]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets_team[$counter]['sector_id'] . "</option>";
                $counter++;
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

        if ($editroute !== null && $editroute['move_type'] == 'R')
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

        if (($editroute !== null) && $editroute['circuit'] == '1')
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

        \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $template, null);
    }
}
