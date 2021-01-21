<?php declare(strict_types = 1);
/**
 * classes/TraderouteResults.php from The Kabal Invasion.
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

class TraderouteResults
{
    public static function tableTop(\PDO $pdo_db, string $lang, Reg $tkireg): void
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('common',
                                         'footer', 'global_funcs',
                                         'global_includes', 'regional',
                                         'traderoutes'));
        echo "<table border='1' cellspacing='1' cellpadding='2' width='65%' align='center'>\n";
        echo "  <tr bgcolor='" . $tkireg->color_line2 . "'>\n";
        echo "    <td align='center' colspan='7'><strong><font color='white'>" . $langvars['l_tdr_res'] . "</font></strong></td>\n";
        echo "  </tr>\n";
        echo "  <tr align='center' bgcolor='" . $tkireg->color_line2 . "'>\n";
        echo "    <td width='50%'><font size='2' color='white'><strong>";
    }

    public static function source(): void
    {
        echo "</strong></font></td>\n";
        echo "    <td width='50%'><font size='2' color='white'><strong>";
    }

    public static function destination(Reg $tkireg): void
    {
        echo "</strong></font></td>\n";
        echo "  </tr>\n";
        echo "  <tr bgcolor='" . $tkireg->color_line1 . "'>\n";
        echo "    <td align='center'><font size='2' color='white'>";
    }

    public static function closeCell(): void
    {
        echo "</font></td>\n";
        echo "    <td align='center'><font size='2' color='white'>";
    }

    public static function showCost(Reg $tkireg): void
    {
        echo "</font></td>\n";
        echo "  </tr>\n";
        echo "  <tr bgcolor='" . $tkireg->color_line2 . "'>\n";
        echo "    <td align='center'><font size='2' color='white'>";
    }

    public static function closeCost(): void
    {
        echo "</font></td>\n";
        echo "    <td align='center'><font size='2' color='white'>";
    }

    public static function closeTable(): void
    {
        echo "</font></td>\n";
        echo "  </tr>\n";
        echo "</table>\n";
        // echo "<p><center><font size=3 color=white><strong>\n";
    }

    public static function displayTotals(\PDO $pdo_db, string $lang, int $total_profit): void
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('common',
                                         'footer', 'global_funcs',
                                         'global_includes', 'regional',
                                         'traderoutes'));
        if ($total_profit > 0)
        {
            echo "<p><center><font size=3 color=white><strong>" . $langvars['l_tdr_totalprofit'] . " : <font style='color:#0f0;'><strong>" . number_format(abs($total_profit), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</strong></font><br>\n";
        }
        else
        {
            echo "<p><center><font size=3 color=white><strong>" . $langvars['l_tdr_totalcost'] . " : <font style='color:#f00;'><strong>" . number_format(abs($total_profit), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</strong></font><br>\n";
        }
    }

    public static function displaySummary(\PDO $pdo_db, string $lang, string $tdr_display_creds, array $dist, array $playerinfo): void
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('common',
                                         'footer', 'global_funcs',
                                         'global_includes', 'regional',
                                         'traderoutes'));
        echo "\n<font size='3' color='white'><strong>" . $langvars['l_tdr_turnsused'] . " : <font style='color:#f00;'>$dist[triptime]</font></strong></font><br>";
        echo "\n<font size='3' color='white'><strong>" . $langvars['l_tdr_turnsleft'] . " : <font style='color:#0f0;'>$playerinfo[turns]</font></strong></font><br>";
        echo "\n<font size='3' color='white'><strong>" . $langvars['l_tdr_credits'] . " : <font style='color:#0f0;'> $tdr_display_creds\n</font></strong></font><br> </strong></font></center>\n";
        //echo "<font size='2'>\n";
    }

    public static function showRepeat(int $engage): void
    {
        echo "<form accept-charset='utf-8' action='traderoute.php?engage=" . $engage . "' method='post'>\n";
        echo "<br>Enter times to repeat <input type='text' name='tr_repeat' value='1' size='5'> <input type='submit' value='submit'>\n";
        echo "</form>\n";
        // echo "<p>";
    }
}
