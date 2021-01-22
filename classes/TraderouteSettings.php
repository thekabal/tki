<?php declare(strict_types = 1);
/**
 * classes/TraderouteSettings.php from The Kabal Invasion.
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

class TraderouteSettings
{
    public static function before(\PDO $pdo_db, string $lang, Reg $tkireg, Smarty $template, array $playerinfo): void
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('common',
                                         'footer', 'insignias', 'regional',
                                         'traderoutes', 'universal'));
        echo "<p><font size=3 color=blue><strong>" . $langvars['l_tdr_globalset'] . "</strong></font><p>";
        echo "<font color=white size=2><strong>" . $langvars['l_tdr_sportsrc'] . " :</strong></font><p>" .
             "<form accept-charset='utf-8' action=traderoute.php?command=setsettings method=post>" .
             "<table border=0><tr>" .
             "<td><font size=2 color=white> - " . $langvars['l_tdr_colonists'] . " :</font></td>" .
             "<td><input type=checkbox name=colonists";

        if ($playerinfo['trade_colonists'] == 'Y')
        {
            echo " checked";
        }

        echo "></tr><tr>" .
            "<td><font size=2 color=white> - " . $langvars['l_tdr_fighters'] . " :</font></td>" .
            "<td><input type=checkbox name=fighters";

        if ($playerinfo['trade_fighters'] == 'Y')
        {
            echo " checked";
        }

        echo "></tr><tr>" .
            "<td><font size=2 color=white> - " . $langvars['l_tdr_torps'] . " :</font></td>" .
            "<td><input type=checkbox name=torps";

        if ($playerinfo['trade_torps'] == 'Y')
        {
            echo " checked";
        }

        echo "></tr>" .
            "</table>" .
            "<p>" .
            "<font color=white size=2><strong>" . $langvars['l_tdr_tdrescooped'] . " :</strong></font><p>" .
            "<table border=0><tr>" .
            "<td><font size=2 color=white>&nbsp;&nbsp;&nbsp;" . $langvars['l_tdr_trade'] . "</font></td>" .
            "<td><input type=radio name=energy value=\"Y\"";

        if ($playerinfo['trade_energy'] == 'Y')
        {
            echo " checked";
        }

        echo "></td></tr><tr>" .
            "<td><font size=2 color=white>&nbsp;&nbsp;&nbsp;" . $langvars['l_tdr_keep'] . "</font></td>" .
            "<td><input type=radio name=energy value=\"N\"";

        if ($playerinfo['trade_energy'] == 'N')
        {
            echo " checked";
        }

        echo "></td></tr><tr><td>&nbsp;</td></tr><tr><td>" .
            "<td><input type=submit value=\"" . $langvars['l_tdr_save'] . "\"></td>" .
            "</tr></table>" .
            "</form>";

        $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);
        echo $langvars['l_tdr_returnmenu'];
        \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $template, null);
    }

    public static function after(\PDO $pdo_db, array $playerinfo, int $colonists, int $fighters, int $torps, int $energy): void
    {
        empty($colonists) ? $colonists = 'N' : $colonists = 'Y';
        empty($fighters) ? $fighters = 'N' : $fighters = 'Y';
        empty($torps) ? $torps = 'N' : $torps = 'Y';

        $sql = "UPDATE ::prefix::ships SET trade_colonists = :colonists, trade_fighters = :fighters, trade_torps = :torps, trade_energy = :energy WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':colonists', $colonists, \PDO::PARAM_INT);
        $stmt->bindParam(':fighters', $fighters, \PDO::PARAM_INT);
        $stmt->bindParam(':torps', $torps, \PDO::PARAM_INT);
        $stmt->bindParam(':energy', $energy, \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $resa = $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $resa, __LINE__, __FILE__);
    }

    public static function afterOutput(\PDO $pdo_db, string $lang, Reg $tkireg, Smarty $template): void
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('common',
                                         'footer', 'insignias', 'regional',
                                         'traderoutes', 'universal'));
        $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" .
                                        $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);
        echo $langvars['l_tdr_globalsetsaved'] . " " . $langvars['l_tdr_returnmenu'];
        \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $template, null);
    }
}
