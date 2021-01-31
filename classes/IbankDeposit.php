<?php declare(strict_types = 1);
/**
 * classes/IbankDeposit.php from The Kabal Invasion.
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

class IbankDeposit
{
    public static function before(\PDO $pdo_db, string $lang, array $account, array $playerinfo): void
    {
        // Database driven language entries
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('ibank'));

        $max_credits_allowed = 18446744073709000000;
        $credit_space = ($max_credits_allowed - $account['balance']);

        if ($credit_space > $playerinfo['credits'])
        {
            $credit_space = ($playerinfo['credits']);
        }

        if ($credit_space < 0)
        {
            $credit_space = 0;
        }

        echo "<tr><td height=53 colspan=2 align=center valign=top>" . $langvars['l_ibank_depositfunds'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top>" .
             "<td height=30>" . $langvars['l_ibank_fundsavailable'] . " :</td>" .
             "<td align=right>" . number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br></td>" .
             "</tr><tr valign=top>" .
             "<td height=90>" . $langvars['l_ibank_seldepositamount'] . " :</td><td align=right>" .
             "<form accept-charset='utf-8' action='ibank.php?command=deposit2' method=post>" .
             "<input class=term type=text size=15 maxlength=20 name=amount value=0>" .
             "<br><br><input class=term type=submit value=" . $langvars['l_ibank_deposit'] . ">" .
             "</form>" .
             "</td></tr>" .
             "<tr>" .
             "  <td height=30  colspan=2 align=left>" .
             "    <span style='color:\"#00ff00\";'>You can deposit only " . number_format($credit_space, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " credits.</span><br>" .
             "  </td>" .
             "</tr>" .
             "<tr valign=bottom>" .
             "<td><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
             "</tr>";
    }

    public static function after(\PDO $pdo_db, string $lang, array $langvars, array $playerinfo, int $amount, array $account, Reg $tkireg, Timer $tkitimer, Smarty $template): void
    {
        $max_credits_allowed = 18446744073709000000;

        $amount = preg_replace("/[^0-9]/", '', (string) $amount);
        $amount = (int) $amount;

        if (($amount * 1) != $amount)
        {
            \Tki\Ibank::ibankError($pdo_db, $langvars['l_ibank_invaliddepositinput'], "ibank.php?command=deposit", $lang, $tkireg, $tkitimer, $template);
        }

        if ($amount == 0)
        {
            \Tki\Ibank::ibankError($pdo_db, $langvars['l_ibank_nozeroamount2'], "ibank.php?command=deposit", $lang, $tkireg, $tkitimer, $template);
        }

        if ($amount > $playerinfo['credits'])
        {
            \Tki\Ibank::ibankError($pdo_db, $langvars['l_ibank_notenoughcredits'], "ibank.php?command=deposit", $lang, $tkireg, $tkitimer, $template);
        }

        $tmpcredits = $max_credits_allowed - $account['balance'];
        if ($tmpcredits < 0)
        {
            $tmpcredits = 0;
        }

        if ($amount > $tmpcredits)
        {
            \Tki\Ibank::ibankError($pdo_db, "<center>" . $langvars['l_ibank_deposit_max'] . "</center>", "ibank.php?command=deposit", $lang, $tkireg, $tkitimer, $template);
        }

        $account['balance'] += $amount;
        $playerinfo['credits'] -= $amount;

        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_operationsuccessful'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top>" .
             "<td colspan=2 align=center>" . number_format((float) $amount, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_ibank_creditstoyou'] . "</td>" .
             "<tr><td colspan=2 align=center>" . $langvars['l_ibank_accounts'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_shipaccount'] . " :<br>" . $langvars['l_ibank_ibankaccount'] . " :</td>" .
             "<td align=right>" . number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" . number_format($account['balance'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</tr>" .
             "<tr valign=bottom>" .
             "<td><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
             "</tr>";

        $sql = "UPDATE ::prefix::ibank_accounts SET balance = balance + :amount WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':amount', $amount, \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        $sql = "UPDATE ::prefix::ships SET credits = credits - :amount WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':amount', $amount, \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    }
}
