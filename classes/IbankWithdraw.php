<?php declare(strict_types = 1);
/**
 * classes/IbankWithdraw.php from The Kabal Invasion.
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

class IbankWithdraw
{
    public static function before(\PDO $pdo_db, string $lang, array $account): void
    {
        $langvars = Translate::load($pdo_db, $lang, array('ibank', 'regional'));
        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_withdrawfunds'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_fundsavailable'] . ":</td>" .
             "<td align=right>" . number_format($account['balance'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br></td>" .
             "</tr><tr valign=top>" .
             "<td>" . $langvars['l_ibank_selwithdrawamount'] . ":</td><td align=right>" .
             "<form accept-charset='utf-8' action='ibank.php?command=withdraw2' method=post>" .
             "<input class=term type=text size=15 maxlength=20 name=amount value=0>" .
             "<br><br><input class=term type=submit value='" . $langvars['l_ibank_withdraw'] . "'>" .
             "</form></td></tr>" .
             "<tr valign=bottom>" .
             "<td><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
             "</tr>";
    }

    public static function after(\PDO $pdo_db, string $lang, array $playerinfo, int $amount, array $account, Registry $tkireg, Timer $tkitimer, Smarty $template): void
    {
        $langvars = Translate::load($pdo_db, $lang, array('ibank', 'regional'));
        $amount = preg_replace("/[^0-9]/", '', (string) $amount);
        $amount = (int) $amount;

        if (($amount * 1) != $amount)
        {
            \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_invalidwithdrawinput'], "ibank.php?command=withdraw", $tkireg, $tkitimer, $template);
        }

        if ($amount == 0)
        {
            \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_nozeroamount3'], "ibank.php?command=withdraw", $tkireg, $tkitimer, $template);
        }

        if ($amount > $account['balance'])
        {
            \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_notenoughcredits'], "ibank.php?command=withdraw", $tkireg, $tkitimer, $template);
        }

        $account['balance'] -= $amount;
        $playerinfo['credits'] += $amount;

        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_operationsuccessful'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top>" .
             "<td colspan=2 align=center>" . number_format($amount, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_ibank_creditstoyourship'] . "</td>" .
             "<tr><td colspan=2 align=center>" . $langvars['l_ibank_accounts'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top>" .
             "<td>Ship Account :<br>" . $langvars['l_ibank_ibankaccount'] . " :</td>" .
             "<td align=right>" . number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" . number_format($account['balance'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</tr>" .
             "<tr valign=bottom>" .
             "<td><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
             "</tr>";

        $sql = "UPDATE ::prefix::ibank_accounts SET balance = balance - :amount WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':amount', $amount, \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        $sql = "UPDATE ::prefix::ships SET credits = credits + :amount WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':amount', $amount, \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    }
}
