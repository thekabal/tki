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
// File: classes/Ibank.php
//
// FUTURE: These are horribly bad. They should be broken out of classes, and turned mostly into template
// behaviors. But in the interest of saying goodbye to the includes directory, and raw functions, this
// will at least allow us to auto-load and use classes instead. Plenty to do in the future, though!

namespace Tki;

class Ibank
{
    public static function ibankBorrow(\PDO $pdo_db, string $lang, array $langvars, Reg $tkireg, array $playerinfo, string $account, $amount, Smarty $template)
    {
        $amount = preg_replace("/[^0-9]/", '', $amount);
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

        $langvars['l_ibank_loanreminder'] = str_replace("[hours]", $hours, $langvars['l_ibank_loanreminder']);
        $langvars['l_ibank_loanreminder'] = str_replace("[mins]", $mins, $langvars['l_ibank_loanreminder']);

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

        $sql = "UPDATE ::prefix::ibank_accounts SET loan = :amount, loantime=NOW() WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':amount', $amount3);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        $sql = "UPDATE ::prefix::ships SET credits = credits + :amount WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    }

    public static function ibankLogin(array $langvars, array $playerinfo, string $account)
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

    public static function ibankWithdraw(array $langvars, string $account)
    {
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

    public static function ibankTransfer(\PDO $pdo_db, array $langvars, array $playerinfo, Reg $tkireg)
    {
        $sql = "SELECT * FROM ::prefix::ships WHERE email not like '%@xenobe' AND ship_destroyed ='N' AND turns_used > :ibank_min_turns ORDER BY character_name ASC";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ibank_min_turns', $tkireg->ibank_min_turns);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        $ships = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $sql = "SELECT name, planet_id, sector_id FROM ::prefix::planets WHERE owner=:owner ORDER BY sector_id ASC";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':owner', $playerinfo['ship_id']);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        $planets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_transfertype'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top>" .
             "<form accept-charset='utf-8' action='ibank.php?command=transfer2' method=post>" .
             "<td>" . $langvars['l_ibank_toanothership'] . " :<br><br>" .
             "<select class=term name=ship_id style='width:200px;'>";

        foreach ($ships as $ship)
        {
            echo "<option value='" . $ship['ship_id'] . "'>" . $ship['character_name'] . "</option>";
        }

        echo "</select></td><td valign=center align=right>" .
             "<input class=term type=submit name=shipt value='" . $langvars['l_ibank_shiptransfer'] . "'>" .
             "</form></td></tr>" .
             "<tr valign=top>" .
             "<td><br>" . $langvars['l_ibank_fromplanet'] . " :<br><br>" .
             "<form accept-charset='utf-8' action='ibank.php?command=transfer2' method=post>" .
             $langvars['l_ibank_source'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select class=term name=splanet_id>";

        if ($planets !== null)
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

        if ($planets !== null)
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

        echo "</select></td><td valign=center align=right>" .
             "<br><input class=term type=submit name=planett value='" . $langvars['l_ibank_planettransfer'] . "'>" .
             "</td></tr></form>";

        // Begin consolidate credits form
        echo "<tr valign=top><td><br>" . $langvars['l_ibank_conspl'] . " :<br><br>" .
             "<form accept-charset='utf-8' action='ibank.php?command=consolidate' method=post>" .
             $langvars['l_ibank_destination'] . " <select class=term name=dplanet_id>";

        if ($planets !== null)
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

        echo "</select></td><td valign=top align=right>" .
             "<br><input class=term type=submit name=planetc value='" . $langvars['l_ibank_consolidate'] . "'>" .
             "</td></tr></form>";
        // End consolidate credits form

        echo "</form><tr valign=bottom>" .
             "<td><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td></tr>";
    }

    public static function ibankLoans(\PDO $pdo_db, array $langvars, Reg $tkireg, array $playerinfo, string $account)
    {
        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_loanstatus'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top><td>" . $langvars['l_ibank_shipaccount'] . " :</td><td align=right>" . number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</td></tr>" .
             "<tr valign=top><td>" . $langvars['l_ibank_currentloan'] . " :</td><td align=right>" . number_format($account['loan'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</td></tr>";

        if ($account['loan'] != 0)
        {
            $curtime = time();

            $sql = "SELECT UNIX_TIMESTAMP(loantime) as time FROM ::prefix::ibank_accounts WHERE ship_id = :ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            $time = $stmt->fetch(\PDO::FETCH_COLUMN);

            $difftime = ($curtime - $time) / 60;

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

            $langvars['l_ibank_loanrates'] = str_replace("[factor]", $factor, $langvars['l_ibank_loanrates']);
            $langvars['l_ibank_loanrates'] = str_replace("[interest]", $interest, $langvars['l_ibank_loanrates']);

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

            $langvars['l_ibank_maxloanpercent'] = str_replace("[ibank_percent]", $percent, $langvars['l_ibank_maxloanpercent']);
            echo "<tr valign=top><td nowrap>" . $langvars['l_ibank_maxloanpercent'] . " :</td><td align=right>" . number_format($maxloan, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</td></tr>";

            $factor = $tkireg->ibank_loanfactor *= 100;
            $interest = $tkireg->ibank_loaninterest *= 100;

            $langvars['l_ibank_loanrates'] = str_replace("[factor]", $factor, $langvars['l_ibank_loanrates']);
            $langvars['l_ibank_loanrates'] = str_replace("[interest]", $interest, $langvars['l_ibank_loanrates']);

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

    public static function ibankRepay(\PDO $pdo_db, string $lang, array $langvars, array $playerinfo, string $account, $amount, Reg $tkireg, Smarty $template)
    {
        $amount = preg_replace("/[^0-9]/", '', $amount);
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

        $sql = "UPDATE ::prefix::ibank_accounts SET loan = loan - :loanamount, loantime=:loantime WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':loanamount', $amount);
        $stmt->bindParam(':loantime', $account['loantime']);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        $sql = "UPDATE ::prefix::ships SET credits = credits - :amount WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    }

    public static function ibankConsolidate(array $langvars, Reg $tkireg, int $dplanet_id)
    {
        $percent = $tkireg->ibank_paymentfee * 100;

        $langvars['l_ibank_transferrate3'] = str_replace("[ibank_num_percent]", number_format($percent, 1, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_ibank_transferrate3']);
        $langvars['l_ibank_transferrate3'] = str_replace("[nbplanets]", $tkireg->ibank_tconsolidate, $langvars['l_ibank_transferrate3']);

        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_planetconsolidate'] . "<br>---------------------------------</td></tr>" .
             "<form accept-charset='utf-8' action='ibank.php?command=consolidate2' method=post>" .
             "<tr valign=top>" .
             "<td colspan=2>" . $langvars['l_ibank_consolrates'] . " :</td>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_minimum'] . " :<br>" .
             "<br>" . $langvars['l_ibank_maximum'] . " :</td>" .
             "<td align=right>" .
             "<input class=term type=text size=15 maxlength=20 name=minimum value=0><br><br>" .
             "<input class=term type=text size=15 maxlength=20 name=maximum value=0><br><br>" .
             "<input class=term type=submit value=\"" . $langvars['l_ibank_compute'] . "\"></td>" .
             "<input type=hidden name=dplanet_id value=" . $dplanet_id . ">" .
             "</form>" .
             "<tr><td colspan=2 align=center>" .
             $langvars['l_ibank_transferrate3'] .
             "<tr valign=bottom>" .
             "<td><a href='ibank.php?command=transfer'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
             "</tr>";
    }

    public static function ibankError(\PDO $pdo_db, array $langvars, string $errmsg, string $backlink, string $lang, Reg $tkireg, Smarty $template)
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
             "<img width=600 height=21 src=" . $template . "/images/div2.png>" .
             "</center>";

        \Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
        die();
    }

    public static function ibankDeposit(\PDO $pdo_db, string $lang, string $account, array $playerinfo)
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
             "    <span style='color:\"#00ff00\";'>You can deposit only ". number_format($credit_space, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " credits.</span><br>" .
             "  </td>" .
             "</tr>" .
             "<tr valign=bottom>" .
             "<td><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
             "</tr>";
    }
}
