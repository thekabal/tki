<?php declare(strict_types = 1);
/**
 * classes/Ibank.php from The Kabal Invasion.
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

class Ibank
{
    public static function ibankBorrow(\PDO $pdo_db, string $lang, array $langvars, Reg $tkireg, array $playerinfo, array $account, int $amount, Smarty $template): void
    {
        $playerinfo['ship_id'] = (int) $playerinfo['ship_id'];
        $amount = preg_replace("/[^0-9]/", '', (string) $amount);
        $amount = (int) $amount;

        if (($amount * 1) != $amount)
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_invalidamount'], "ibank.php?command=loans", $lang, $tkireg, $template);
        }

        if ($amount <= 0)
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_invalidamount'], "ibank.php?command=loans", $lang, $tkireg, $template);
        }

        if ($account['loan'] != 0)
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_notwoloans'], "ibank.php?command=loans", $lang, $tkireg, $template);
        }

        $score = \Tki\Score::updateScore($pdo_db, $playerinfo['ship_id'], $tkireg, $playerinfo);
        $maxtrans = $score * $score * $tkireg->ibank_loanlimit;

        if ($amount > $maxtrans)
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_loantoobig'], "ibank.php?command=loans", $lang, $tkireg, $template);
        }

        $amount2 = $amount * $tkireg->ibank_loanfactor;
        $amount3 = $amount + $amount2;

        $hours = $tkireg->ibank_lrate / 60;
        $mins = $tkireg->ibank_lrate % 60;

        $langvars['l_ibank_loanreminder'] = str_replace("[hours]", (string) $hours, $langvars['l_ibank_loanreminder']);
        $langvars['l_ibank_loanreminder'] = str_replace("[mins]", (string) $mins, $langvars['l_ibank_loanreminder']);

        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_takenaloan'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top><td colspan=2 align=center>" . $langvars['l_ibank_loancongrats'] . "<br><br></tr>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_loantransferred'] . " :</td><td nowrap align=right>" . number_format($amount, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_loanfee'] . " :</td><td nowrap align=right>" . number_format($amount2, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_amountowned'] . " :</td><td nowrap align=right>" . number_format($amount3, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
             "<tr valign=top>" .
             "<td colspan=2 align=center>---------------------------------<br><br>" . $langvars['l_ibank_loanreminder'] . "<br><br>\"" . $langvars['l_ibank_loanreminder2'] . "\"</td>" .
             "<tr valign=top>" .
             "<td nowrap><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td nowrap align=right>&nbsp;<a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
             "</tr>";

        $sql = "UPDATE ::prefix::ibank_accounts SET loan = :amount, loantime=NOW() WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':amount', $amount3, \PDO::PARAM_INT);
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

    public static function ibankLogin(array $langvars, array $playerinfo, array $account): void
    {
        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_welcometoibank'] . "<br>---------------------------------</td></tr>" .
            "<tr valign=top>" .
             "<td width=150 align=right>" . $langvars['l_ibank_accountholder'] . " :<br><br>" . $langvars['l_ibank_shipaccount'] . " :<br>" . $langvars['l_ibank_ibankaccount'] . "&nbsp;&nbsp;:</td>" .
             "<td style='max-width:550px; padding-right:4px;' align=right>" . $playerinfo['character_name'] . "&nbsp;&nbsp;<br><br>" . number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_ibank_credit_symbol'] . "<br>" . number_format($account['balance'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_ibank_credit_symbol'] . "<br></td>" .
             "</tr>" .
             "<tr><td colspan=2 align=center>" . $langvars['l_ibank_operations'] . "<br>---------------------------------<br><br><a href=\"ibank.php?command=withdraw\">" . $langvars['l_ibank_withdraw'] . "</a><br><a href=\"ibank.php?command=deposit\">" . $langvars['l_ibank_deposit'] . "</a><br><a href=\"ibank.php?command=transfer\">" . $langvars['l_ibank_transfer'] . "</a><br><a href=\"ibank.php?command=loans\">" . $langvars['l_ibank_loans'] . "</a><br>&nbsp;</td></tr>" .
             "<tr valign=bottom>" .
             "<td align='left'><a href='ibank.php'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
             "</tr>";
    }

    public static function ibankLoans(\PDO $pdo_db, array $langvars, Reg $tkireg, array $playerinfo, array $account): void
    {
        $playerinfo['ship_id'] = (int) $playerinfo['ship_id'];
        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_loanstatus'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top><td>" . $langvars['l_ibank_shipaccount'] . " :</td><td align=right>" . number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</td></tr>" .
             "<tr valign=top><td>" . $langvars['l_ibank_currentloan'] . " :</td><td align=right>" . number_format($account['loan'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</td></tr>";

        if ($account['loan'] != 0)
        {
            $curtime = time();

            // Build an ibank gateway object to handle the SQL calls to retreive the iBank account for players
            $ibank_gateway = new Ibank\IbankGateway($pdo_db);
            $bank_loan_time = $ibank_gateway->selectIbankLoanTime($playerinfo['ship_id']);
            $difftime = ($curtime - $bank_loan_time) / 60;

            echo "<tr valign=top><td nowrap>" . $langvars['l_ibank_loantimeleft'] . " :</td>";

            if ($difftime > $tkireg->ibank_lrate)
            {
                echo "<td align=right>" . $langvars['l_ibank_loanlate'] . "</td></tr>";
            }
            else
            {
                $difftime = $tkireg->ibank_lrate - $difftime;
                $hours = $difftime / 60;
                $hours = (int) $hours;
                $mins = $difftime % 60;
                echo "<td align=right>{$hours}h {$mins}m</td></tr>";
            }

            $factor = $tkireg->ibank_loanfactor *= 100;
            $interest = $tkireg->ibank_loaninterest *= 100;

            $langvars['l_ibank_loanrates'] = str_replace("[factor]", (string) $factor, $langvars['l_ibank_loanrates']);
            $langvars['l_ibank_loanrates'] = str_replace("[interest]", (string) $interest, $langvars['l_ibank_loanrates']);

            echo "<form accept-charset='utf-8' action='ibank.php?command=repay' method=post>" .
                 "<tr valign=top>" .
                 "<td><br>" . $langvars['l_ibank_repayamount'] . " :</td>" .
                 "<td align=right><br><input class=term type=text size=15 maxlength=20 name=amount value=0><br>" .
                 "<br><input class=term type=submit value='" . $langvars['l_ibank_repay'] . "'></td>" .
                 "</form>" .
                 "<tr><td colspan=2 align=center>" . $langvars['l_ibank_loanrates'];
        }
        else
        {
            $percent = $tkireg->ibank_loanlimit * 100;
            $score = \Tki\Score::updateScore($pdo_db, $playerinfo['ship_id'], $tkireg, $playerinfo);
            $maxloan = $score * $score * $tkireg->ibank_loanlimit;

            $langvars['l_ibank_maxloanpercent'] = str_replace("[ibank_percent]", (string) $percent, $langvars['l_ibank_maxloanpercent']);
            echo "<tr valign=top><td nowrap>" . $langvars['l_ibank_maxloanpercent'] . " :</td><td align=right>" . number_format($maxloan, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</td></tr>";

            $factor = $tkireg->ibank_loanfactor *= 100;
            $interest = $tkireg->ibank_loaninterest *= 100;

            $langvars['l_ibank_loanrates'] = str_replace("[factor]", (string) $factor, $langvars['l_ibank_loanrates']);
            $langvars['l_ibank_loanrates'] = str_replace("[interest]", (string) $interest, $langvars['l_ibank_loanrates']);

            echo "<form accept-charset='utf-8' action='ibank.php?command=borrow' method=post>" .
                 "<tr valign=top>" .
                 "<td><br>" . $langvars['l_ibank_loanamount'] . " :</td>" .
                 "<td align=right><br><input class=term type=text size=15 maxlength=20 name=amount value=0><br>" .
                 "<br><input class=term type=submit value='" . $langvars['l_ibank_borrow'] . "'></td>" .
                 "</form>" .
                 "<tr><td colspan=2 align=center>" . $langvars['l_ibank_loanrates'];
        }

        echo "<tr valign=bottom>" .
             "<td><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
             "</tr>";
    }

    public static function ibankRepay(\PDO $pdo_db, string $lang, array $langvars, array $playerinfo, array $account, int $amount, Reg $tkireg, Smarty $template): void
    {
        $amount = preg_replace("/[^0-9]/", '', (string) $amount);
        $amount = (int) $amount;

        if (($amount * 1) != $amount)
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_invalidamount'], "ibank.php?command=loans", $lang, $tkireg, $template);
        }

        if ($amount <= 0)
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_invalidamount'], "ibank.php?command=loans", $lang, $tkireg, $template);
        }

        if ($account['loan'] == 0)
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_notrepay'], "ibank.php?command=loans", $lang, $tkireg, $template);
        }

        if ($amount > $account['loan'])
        {
            $amount = $account['loan'];
        }

        if ($amount > $playerinfo['credits'])
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_notenoughrepay'], "ibank.php?command=loans", $lang, $tkireg, $template);
        }

        $playerinfo['credits'] -= $amount;
        $account['loan'] -= $amount;

        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_payloan'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top>" .
             "<td colspan=2 align=center>" . $langvars['l_ibank_loanthanks'] . "</td>" .
             "<tr valign=top>" .
             "<td colspan=2 align=center>---------------------------------</td>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_shipaccount'] . " :</td><td nowrap align=right>" . number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_payloan'] . " :</td><td nowrap align=right>" . number_format($amount, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_currentloan'] . " :</td><td nowrap align=right>" . number_format($account['loan'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
             "<tr valign=top>" .
             "<td colspan=2 align=center>---------------------------------</td>" .
             "<tr valign=top>" .
             "<td nowrap><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td nowrap align=right>&nbsp;<a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
             "</tr>";

        $sql = "UPDATE ::prefix::ibank_accounts SET loan = loan - :loanamount, loantime = :loantime WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':loanamount', $amount, \PDO::PARAM_INT);
        $stmt->bindParam(':loantime', $account['loantime'], \PDO::PARAM_INT);
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

    public static function ibankError(\PDO $pdo_db, array $langvars, string $errmsg, string $backlink, string $lang, Reg $tkireg, Smarty $template): void
    {
        $title = $langvars['l_ibank_ibankerrreport'];
        echo "<tr><td colspan=2 align=center valign=top>" . $title . "<br>---------------------------------</td></tr>" .
             "<tr valign=top>" .
             "<td colspan=2 align=center>" . $errmsg . "</td>" .
             "</tr>" .
             "<tr valign=bottom>" .
             "<td><a href=" . $backlink . ">" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
             "</tr>" .
             "</table>" .
             "</td></tr>" .
             "</table>" .
             "<img width=600 height=21 src=" . $template->getVariables('template_dir') . "/images/div2.png>" .
             "</center>";

        $footer = new \Tki\Footer();
        $footer->display($pdo_db, $lang, $tkireg, $template);
        die();
    }
}
