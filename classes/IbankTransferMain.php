<?php declare(strict_types = 1);
/**
 * classes/IbankTransferMain.php from The Kabal Invasion.
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

class IbankTransferMain
{
    public static function main(\PDO $pdo_db, string $lang, array $playerinfo, Registry $tkireg): void
    {
        // Registry values can't be directly used in a PDO bind parameter call
        $ibank_min_turns = $tkireg->ibank_min_turns;

        $langvars = Translate::load($pdo_db, $lang, array('ibank', 'regional'));
        $sql = "SELECT * FROM ::prefix::ships WHERE email not like '%@kabal' AND ship_destroyed ='N' AND turns_used > :ibank_min_turns ORDER BY character_name ASC";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ibank_min_turns', $ibank_min_turns, \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        $ships = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $planets_gateway = new \Tki\Planets\PlanetsGateway($pdo_db);
        $planets = $planets_gateway->selectSomePlanetInfoByOwner($playerinfo['ship_id']);
        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_transfertype'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top>" .
             "<form accept-charset='utf-8' action='ibank.php?command=transfer2' method=post>" .
             "<td>" . $langvars['l_ibank_toanothership'] . " :<br><br>" .
             "<select class=term name=ship_id style='width:200px;'>";

        if (!empty($ships))
        {
            foreach ($ships as $ship)
            {
                echo "<option value='" . $ship['ship_id'] . "'>" . $ship['character_name'] . "</option>";
            }
        }

        echo "</select></td><td valign=center align=right>" .
             "<input class=term type=submit name=shipt value='" . $langvars['l_ibank_shiptransfer'] . "'>" .
             "</form></td></tr><tr valign=top><td><br>" . $langvars['l_ibank_fromplanet'] . " :<br><br>" .
             "<form accept-charset='utf-8' action='ibank.php?command=transfer2' method=post>" .
             $langvars['l_ibank_source'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select class=term name=splanet_id>";

        if (!empty($planets))
        {
            foreach ($planets as $planet)
            {
                if (empty($planet['name']))
                {
                    $planet['name'] = $langvars['l_ibank_unnamed'];
                }

                echo "<option value=" . $planet['planet_id'] . ">" . $planet['name'] . " " . $langvars['l_ibank_in'] . " " . $planet['sector_id'] . "</option>";
            }
        }
        else
        {
            echo "<option value=none>" . $langvars['l_ibank_none'] . "</option>";
        }

        echo "</select><br>" . $langvars['l_ibank_destination'] . "<select class=term name=dplanet_id>";

        if (!empty($planets))
        {
            foreach ($planets as $planet)
            {
                if (empty($planet['name']))
                {
                    $planet['name'] = $langvars['l_ibank_unnamed'];
                }

                echo "<option value=" . $planet['planet_id'] . ">" . $planet['name'] . " " . $langvars['l_ibank_in'] . " " . $planet['sector_id'] . "</option>";
            }
        }
        else
        {
            echo "<option value=none>" . $langvars['l_ibank_none'] . "</option>";
        }

        echo "</select></td><td valign=center align=right><br>" .
             "<input class=term type=submit name=planett value='" . $langvars['l_ibank_planettransfer'] . "'>" .
             "</td></tr></form>";

        // Begin consolidate credits form
        echo "<tr valign=top><td><br>" . $langvars['l_ibank_conspl'] . " :<br><br>" .
             "<form accept-charset='utf-8' action='ibank.php?command=consolidate' method=post>" .
             $langvars['l_ibank_destination'] . " <select class=term name=dplanet_id>";

        if (!empty($planets))
        {
            foreach ($planets as $planet)
            {
                if (empty($planet['name']))
                {
                    $planet['name'] = $langvars['l_ibank_unnamed'];
                }

                echo "<option value=" . $planet['planet_id'] . ">" . $planet['name'] . " " . $langvars['l_ibank_in'] . " " . $planet['sector_id'] . "</option>";
            }
        }
        else
        {
            echo "<option value=none>" . $langvars['l_ibank_none'] . "</option>";
        }

        echo "</select></td><td valign=top align=right><br>" .
             "<input class=term type=submit name=planetc value='" . $langvars['l_ibank_consolidate'] . "'>" .
             "</td></tr></form>";
        // End consolidate credits form

        echo "</form><tr valign=bottom>" .
             "<td><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td></tr>";
    }
}
