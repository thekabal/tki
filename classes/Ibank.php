<?php
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
    public static function ibankBorrow(\PDO $pdo_db, $lang, $langvars, \Tki\Reg $tkireg, $playerinfo, $active_template, $account, $amount, $template)
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

        $stmt = $pdo_db->prepare("UPDATE {$pdo_db->prefix}ibank_accounts SET loan = :amount, loantime=NOW() WHERE ship_id=:ship_id");
        $stmt->bindParam(':amount', $amount3);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
        $result = $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $pdo_db, $result, __LINE__, __FILE__);

        $stmt = $pdo_db->prepare("UPDATE {$pdo_db->prefix}ships SET credits = credits + :amount WHERE ship_id=:ship_id");
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
        $result = $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $pdo_db, $result, __LINE__, __FILE__);
    }

    public static function ibankLogin($langvars, $playerinfo, $account)
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

    public static function ibankWithdraw($langvars, $account)
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

    public static function ibankWithdraw2(\PDO $pdo_db, $lang, $langvars, $playerinfo, $amount, $account, $tkireg, $template)
    {
        $amount = preg_replace("/[^0-9]/", '', $amount);
        if (($amount * 1) != $amount)
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_invalidwithdrawinput'], "ibank.php?command=withdraw", $lang, $tkireg, $template);
        }

        if ($amount == 0)
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_nozeroamount3'], "ibank.php?command=withdraw", $lang, $tkireg, $template);
        }

        if ($amount > $account['balance'])
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_notenoughcredits'], "ibank.php?command=withdraw", $lang, $tkireg, $template);
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

        $stmt = $pdo_db->prepare("UPDATE {$pdo_db->prefix}ibank_accounts SET balance = balance - :amount WHERE ship_id=:ship_id");
        $result = $stmt->execute(array($amount, $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($pdo_db, $pdo_db, $result, __LINE__, __FILE__);

        $stmt = $pdo_db->prepare("UPDATE {$pdo_db->prefix}ships SET credits = credits + :amount WHERE ship_id=:ship_id");
        $result = $stmt->execute(array($amount, $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($pdo_db, $pdo_db, $result, __LINE__, __FILE__);
    }

    public static function ibankTransfer(\PDO $pdo_db, $langvars, $playerinfo, \Tki\Reg $tkireg)
    {
        $stmt = $pdo_db->prepare("SELECT * FROM {$pdo_db->prefix}ships WHERE email not like '%@xenobe' AND ship_destroyed ='N' AND turns_used > :ibank_min_turns ORDER BY character_name ASC");
        $stmt->bindParam(':ibank_min_turns', $tkireg->ibank_min_turns);
        $result = $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $pdo_db, $result, __LINE__, __FILE__);
        $ships = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $pdo_db->prepare("SELECT name, planet_id, sector_id FROM {$pdo_db->prefix}planets WHERE owner=:owner ORDER BY sector_id ASC");
        $stmt->bindParam(':owner', $playerinfo['ship_id']);
        $result = $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $pdo_db, $result, __LINE__, __FILE__);
        $planets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_transfertype'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top>" .
             "<form accept-charset='utf-8' action='ibank.php?command=transfer2' method=post>" .
             "<td>" . $langvars['l_ibank_toanothership'] . " :<br><br>" .
             "<select class=term name=ship_id style='width:200px;'>";

        foreach($ships as $ship)
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
            foreach($planets as $planet)
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
        echo "<tr valign=top>" .
             "<td><br>" . $langvars['l_ibank_conspl'] . " :<br><br>" .
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
             "</td></tr>" .
             "</form>";
        // End consolidate credits form

        echo "</form><tr valign=bottom>" .
             "<td><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td></tr>";
    }

    public static function ibankLoans(\PDO $pdo_db, $langvars, \Tki\Reg $tkireg, $playerinfo, $account)
    {
        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_loanstatus'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top><td>" . $langvars['l_ibank_shipaccount'] . " :</td><td align=right>" . number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</td></tr>" .
             "<tr valign=top><td>" . $langvars['l_ibank_currentloan'] . " :</td><td align=right>" . number_format($account['loan'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</td></tr>";

        if ($account['loan'] != 0)
        {
            $curtime = time();

            $sql = "SELECT UNIX_TIMESTAMP(loantime) as time FROM {$pdo_db->prefix}ibank_accounts WHERE ship_id = :ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
            $result = $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $pdo_db, $result, __LINE__, __FILE__);
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

    public static function ibankRepay(\PDO $pdo_db, $lang, $langvars, $playerinfo, $account, $amount, $active_template, $tkireg, $template)
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

        $stmt = $pdo_db->prepare("UPDATE {$pdo_db->prefix}ibank_accounts SET loan = loan - :loanamount, loantime=:loantime WHERE ship_id=:ship_id");
        $stmt->bindParam(':loanamount', $amount);
        $stmt->bindParam(':loantime', $account['loantime']);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
        $result = $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $pdo_db, $result, __LINE__, __FILE__);

        $stmt = $pdo_db->prepare("UPDATE {$pdo_db->prefix}ships SET credits = credits - :amount WHERE ship_id=:ship_id");
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
        $result = $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $pdo_db, $result, __LINE__, __FILE__);
    }

    public static function ibankConsolidate($langvars, \Tki\Reg $tkireg, $dplanet_id)
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

    public static function ibankTransfer2($db, $pdo_db, $lang, $langvars, \Tki\Reg $tkireg, $playerinfo, $account, $ship_id, $splanet_id, $dplanet_id, $template)
    {
        if ($ship_id !== null) // Ship transfer
        {
            $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id=? AND ship_destroyed ='N' AND turns_used > ?;", array($ship_id, $tkireg->ibank_min_turns));
            \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);

            if ($playerinfo['ship_id'] == $ship_id)
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_sendyourself'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if (!$res instanceof \adodb\ADORecordSet || $res->EOF)
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_unknowntargetship'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $target = $res->fields;

            if ($target['turns_used'] < $tkireg->ibank_min_turns)
            {
                $langvars['l_ibank_min_turns'] = str_replace("[ibank_min_turns]", $tkireg->ibank_min_turns, $langvars['l_ibank_min_turns']);
                $langvars['l_ibank_min_turns'] = str_replace("[ibank_target_char_name]", $target['character_name'], $langvars['l_ibank_min_turns']);
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_min_turns'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($playerinfo['turns_used'] < $tkireg->ibank_min_turns)
            {
                $langvars['l_ibank_min_turns2'] = str_replace("[ibank_min_turns]", $tkireg->ibank_min_turns, $langvars['l_ibank_min_turns2']);
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_min_turns2'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($tkireg->ibank_trate > 0)
            {
                $curtime = time();
                $curtime -= $tkireg->ibank_trate * 60;
                $res = $db->Execute("SELECT UNIX_TIMESTAMP(time) as time FROM {$db->prefix}ibank_transfers WHERE UNIX_TIMESTAMP(time) > ? AND source_id = ? AND dest_id = ?", array($curtime, $playerinfo['ship_id'], $target['ship_id']));
                \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
                if (!$res->EOF)
                {
                    $time = $res->fields;
                    $difftime = ($time['time'] - $curtime) / 60;
                    $langvars['l_ibank_mustwait'] = str_replace("[ibank_target_char_name]", $target['character_name'], $langvars['l_ibank_mustwait']);
                    $langvars['l_ibank_mustwait'] = str_replace("[ibank_trate]", number_format($tkireg->ibank_trate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_ibank_mustwait']);
                    $langvars['l_ibank_mustwait'] = str_replace("[ibank_difftime]", number_format($difftime, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_ibank_mustwait']);
                    self::ibankError($pdo_db, $langvars, $langvars['l_ibank_mustwait'], "ibank.php?command=transfer", $lang, $tkireg, $template);
                }
            }

            echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_shiptransfer'] . "<br>---------------------------------</td></tr>" .
                 "<tr valign=top><td>" . $langvars['l_ibank_ibankaccount'] . " :</td><td align=right>" . number_format($account['balance'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</td></tr>";

            if ($tkireg->ibank_svalue == 0)
            {
                echo "<tr valign=top><td>" . $langvars['l_ibank_maxtransfer'] . " :</td><td align=right>" . $langvars['l_ibank_unlimited'] . "</td></tr>";
            }
            else
            {
                $percent = $tkireg->ibank_svalue * 100;
                $score = \Tki\Score::updateScore($pdo_db, $playerinfo['ship_id'], $tkireg, $playerinfo);
                $maxtrans = $score * $score * $tkireg->ibank_svalue;

                $langvars['l_ibank_maxtransferpercent'] = str_replace("[ibank_percent]", $percent, $langvars['l_ibank_maxtransferpercent']);
                echo "<tr valign=top><td nowrap>" . $langvars['l_ibank_maxtransferpercent'] . " :</td><td align=right>" . number_format($maxtrans, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</td></tr>";
            }

            $percent = $tkireg->ibank_paymentfee * 100;

            $langvars['l_ibank_transferrate'] = str_replace("[ibank_num_percent]", number_format($percent, 1, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_ibank_transferrate']);
            echo "<tr valign=top><td>" . $langvars['l_ibank_recipient'] . " :</td><td align=right>" . $target['character_name'] . "&nbsp;&nbsp;</td></tr>" .
                 "<form accept-charset='utf-8' action='ibank.php?command=transfer3' method=post>" .
                 "<tr valign=top>" .
                 "<td><br>" . $langvars['l_ibank_seltransferamount'] . " :</td>" .
                 "<td align=right><br><input class=term type=text size=15 maxlength=20 name=amount value=0><br>" .
                 "<br><input class=term type=submit value='" . $langvars['l_ibank_transfer'] . "'></td>" .
                 "<input type=hidden name=ship_id value='" . $ship_id . "'>" .
                 "</form>" .
                 "<tr><td colspan=2 align=center>" . $langvars['l_ibank_transferrate'] .
                 "<tr valign=bottom>" .
                 "<td><a href='ibank.php?command=transfer'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
                 "</tr>";
        }
        else
        {
            if ($splanet_id == $dplanet_id)
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_errplanetsrcanddest'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $res = $db->Execute("SELECT name, credits, owner, sector_id FROM {$db->prefix}planets WHERE planet_id = ?", array($splanet_id));
            \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
            if (!$res || $res->EOF)
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $source = $res->fields;

            if (empty($source['name']))
            {
                $source['name'] = $langvars['l_ibank_unnamed'];
            }

            $res = $db->Execute("SELECT name, credits, owner, sector_id, base FROM {$db->prefix}planets WHERE planet_id = ?", array($dplanet_id));
            \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
            if (!$res || $res->EOF)
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $dest = $res->fields;

            if (empty($dest['name']))
            {
                $dest['name'] = $langvars['l_ibank_unnamed'];
            }

            if ($dest['base'] == 'N')
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnobase'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($source['owner'] != $playerinfo['ship_id'] || $dest['owner'] != $playerinfo['ship_id'])
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnotyourplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $percent = $tkireg->ibank_paymentfee * 100;

            $langvars['l_ibank_transferrate2'] = str_replace("[ibank_num_percent]", number_format($percent, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_ibank_transferrate2']);
            echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_planettransfer'] . "<br>---------------------------------</td></tr>" .
                 "<tr valign=top>" .
                 "<td>" . $langvars['l_ibank_srcplanet'] . " " . $source['name'] . " " . $langvars['l_ibank_in'] . " " . $source['sector_id'] . " :" .
                 "<td align=right>" . number_format($source['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C" .
                 "<tr valign=top>" .
                 "<td>" . $langvars['l_ibank_destplanet'] . " " . $dest['name'] . " " . $langvars['l_ibank_in'] . " " . $dest['sector_id'] . " :" .
                 "<td align=right>" . number_format($dest['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C" .
                 "<form accept-charset='utf-8' action='ibank.php?command=transfer3' method=post>" .
                 "<tr valign=top>" .
                 "<td><br>" . $langvars['l_ibank_seltransferamount'] . " :</td>" .
                 "<td align=right><br><input class=term type=text size=15 maxlength=20 name=amount value=0><br>" .
                 "<br><input class=term type=submit value='" . $langvars['l_ibank_transfer'] . "'></td>" .
                 "<input type=hidden name=splanet_id value='" . $splanet_id . "'>" .
                 "<input type=hidden name=dplanet_id value='" . $dplanet_id . "'>" .
                 "</form>" .
                 "<tr><td colspan=2 align=center>" . $langvars['l_ibank_transferrate2'] .
                 "<tr valign=bottom>" .
                 "<td><a href='ibank.php?command=transfer'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
                 "</tr>";
        }
    }

    public static function ibankTransfer3($db, $pdo_db, $lang, $langvars, $playerinfo, $account, $ship_id, $splanet_id, $dplanet_id, $amount, $tkireg, $template)
    {
        $amount = preg_replace("/[^0-9]/", '', $amount);

        if ($amount < 0)
        {
            $amount = 0;
        }

        if ($ship_id !== null) // Ship transfer
        {
            // Need to check again to prevent cheating by manual posts

            $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ? AND ship_destroyed ='N' AND turns_used > ?", array($ship_id, $tkireg->ibank_min_turns));
            \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);

            if ($playerinfo['ship_id'] == $ship_id)
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_errsendyourself'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if (!$res || $res->EOF)
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_unknowntargetship'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $target = $res->fields;

            if ($target['turns_used'] < $tkireg->ibank_min_turns)
            {
                $langvars['l_ibank_min_turns3'] = str_replace("[ibank_min_turns]", $tkireg->ibank_min_turns, $langvars['l_ibank_min_turns3']);
                $langvars['l_ibank_min_turns3'] = str_replace("[ibank_target_char_name]", $target['character_name'], $langvars['l_ibank_min_turns3']);
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_min_turns3'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($playerinfo['turns_used'] < $tkireg->ibank_min_turns)
            {
                $langvars['l_ibank_min_turns4'] = str_replace("[ibank_min_turns]", $tkireg->ibank_min_turns, $langvars['l_ibank_min_turns4']);
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_min_turns4'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($tkireg->ibank_trate > 0)
            {
                $curtime = time();
                $curtime -= $tkireg->ibank_trate * 60;
                $res = $db->Execute("SELECT UNIX_TIMESTAMP(time) as time FROM {$db->prefix}ibank_transfers WHERE UNIX_TIMESTAMP(time) > ? AND source_id = ? AND dest_id = ?", array($curtime, $playerinfo['ship_id'], $target['ship_id']));
                \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
                if (!$res->EOF)
                {
                    $time = $res->fields;
                    $difftime = ($time['time'] - $curtime) / 60;
                    $langvars['l_ibank_mustwait2'] = str_replace("[ibank_target_char_name]", $target['character_name'], $langvars['l_ibank_mustwait2']);
                    $langvars['l_ibank_mustwait2'] = str_replace("[ibank_trate]", number_format($tkireg->ibank_trate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_ibank_mustwait2']);
                    $langvars['l_ibank_mustwait2'] = str_replace("[ibank_difftime]", number_format($difftime, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_ibank_mustwait2']);
                    self::ibankError($pdo_db, $langvars, $langvars['l_ibank_mustwait2'], "ibank.php?command=transfer", $lang, $tkireg, $template);
                }
            }

            if (($amount * 1) != $amount)
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_invalidtransferinput'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($amount == 0)
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_nozeroamount'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($amount > $account['balance'])
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_notenoughcredits'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($tkireg->ibank_svalue != 0)
            {
                $score = \Tki\Score::updateScore($pdo_db, $playerinfo['ship_id'], $tkireg, $playerinfo);
                $maxtrans = $score * $score * $tkireg->ibank_svalue;

                if ($amount > $maxtrans)
                {
                    self::ibankError($pdo_db, $langvars, $langvars['l_ibank_amounttoogreat'], "ibank.php?command=transfer", $lang, $tkireg, $template);
                }
            }

            $account['balance'] -= $amount;
            $amount2 = $amount * $tkireg->ibank_paymentfee;
            $transfer = $amount - $amount2;

            echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_transfersuccessful'] . "<br>---------------------------------</td></tr>" .
                 "<tr valign=top><td colspan=2 align=center>" . number_format($transfer, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_ibank_creditsto'] . " " . $target['character_name'] . " .</tr>" .
                 "<tr valign=top>" .
                 "<td>" . $langvars['l_ibank_transferamount'] . " :</td><td align=right>" . number_format($amount, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
                 "<tr valign=top>" .
                 "<td>" . $langvars['l_ibank_transferfee'] . " :</td><td align=right>" . number_format($amount2, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
                 "<tr valign=top>" .
                 "<td>" . $langvars['l_ibank_amounttransferred'] . " :</td><td align=right>" . number_format($transfer, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
                 "<tr valign=top>" .
                 "<td>" . $langvars['l_ibank_ibankaccount'] . " :</td><td align=right>" . number_format($account['balance'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
                 "<tr valign=bottom>" .
                 "<td><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
                 "</tr>";

            $resx = $db->Execute("UPDATE {$db->prefix}ibank_accounts SET balance = balance - ? WHERE ship_id = ?", array($amount, $playerinfo['ship_id']));
            \Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
            $resx = $db->Execute("UPDATE {$db->prefix}ibank_accounts SET balance = balance + ? WHERE ship_id = ?", array($transfer, $target['ship_id']));
            \Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);

            $resx = $db->Execute("INSERT INTO {$db->prefix}ibank_transfers VALUES (NULL, ?, ?, NOW(), ?)", array($playerinfo['ship_id'], $target['ship_id'], $transfer));
            \Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
        }
        else
        {
            if ($splanet_id == $dplanet_id)
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_errplanetsrcanddest'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $res = $db->Execute("SELECT name, credits, owner, sector_id FROM {$db->prefix}planets WHERE planet_id = ?", array($splanet_id));
            \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
            if (!$res || $res->EOF)
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $source = $res->fields;

            if (empty($source['name']))
            {
                $source['name'] = $langvars['l_ibank_unnamed'];
            }

            $res = $db->Execute("SELECT name, credits, owner, sector_id FROM {$db->prefix}planets WHERE planet_id = ?", array($dplanet_id));
            \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
            if (!$res || $res->EOF)
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $dest = $res->fields;

            if (empty($dest['name']))
            {
                $dest['name'] = $langvars['l_ibank_unnamed'];
            }

            if ($source['owner'] != $playerinfo['ship_id'] || $dest['owner'] != $playerinfo['ship_id'])
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnotyourplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($amount > $source['credits'])
            {
                self::ibankError($pdo_db, $langvars, $langvars['l_ibank_notenoughcredits2'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $source['credits'] -= $amount;
            $amount2 = $amount * $tkireg->ibank_paymentfee;
            $transfer = $amount - $amount2;
            $dest['credits'] += $transfer;

            echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_transfersuccessful'] . "<br>---------------------------------</td></tr>" .
                 "<tr valign=top><td colspan=2 align=center>" . number_format($transfer, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_ibank_ctransferredfrom'] . " " . $source['name'] . " " . $langvars['l_ibank_to'] . " " . $dest['name'] . ".</tr>" .
                 "<tr valign=top>" .
                 "<td>" . $langvars['l_ibank_transferamount'] . " :</td><td align=right>" . number_format($amount, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
                 "<tr valign=top>" .
                 "<td>" . $langvars['l_ibank_transferfee'] . " :</td><td align=right>" . number_format($amount2, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
                 "<tr valign=top>" .
                 "<td>" . $langvars['l_ibank_amounttransferred'] . " :</td><td align=right>" . number_format($transfer, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
                 "<tr valign=top>" .
                 "<td>" . $langvars['l_ibank_srcplanet'] . " " . $source['name'] . " " . $langvars['l_ibank_in'] . " " . $source['sector_id'] . " :</td><td align=right>" . number_format($source['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
                 "<tr valign=top>" .
                 "<td>" . $langvars['l_ibank_destplanet'] . " " . $dest['name'] . " " . $langvars['l_ibank_in'] . " " . $dest['sector_id'] . " :</td><td align=right>" . number_format($dest['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
                 "<tr valign=bottom>" .
                 "<td><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
                 "</tr>";

            $resx = $db->Execute("UPDATE {$db->prefix}planets SET credits=credits - ? WHERE planet_id = ?", array($amount, $splanet_id));
            \Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
            $resx = $db->Execute("UPDATE {$db->prefix}planets SET credits=credits + ? WHERE planet_id = ?", array($transfer, $dplanet_id));
            \Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
        }
    }

    public static function ibankDeposit2(\PDO $pdo_db, $lang, $langvars, $playerinfo, $amount, $account, $tkireg, $template)
    {
        $max_credits_allowed = 18446744073709000000;

        $amount = preg_replace("/[^0-9]/", '', $amount);

        if (($amount * 1) != $amount)
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_invaliddepositinput'], "ibank.php?command=deposit", $lang, $tkireg);
        }

        if ($amount == 0)
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_nozeroamount2'], "ibank.php?command=deposit", $lang, $tkireg);
        }

        if ($amount > $playerinfo['credits'])
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_notenoughcredits'], "ibank.php?command=deposit", $lang, $tkireg);
        }

        $tmpcredits = $max_credits_allowed - $account['balance'];
        if ($tmpcredits < 0)
        {
            $tmpcredits = 0;
        }

        if ($amount > $tmpcredits)
        {
            self::ibankError($pdo_db, $langvars, "<center>Error You cannot deposit that much into your bank,<br> (Max Credits Reached)</center>", "ibank.php?command=deposit", $lang, $tkireg);
        }

        $account['balance'] += $amount;
        $playerinfo['credits'] -= $amount;

        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_operationsuccessful'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top>" .
             "<td colspan=2 align=center>" . number_format($amount, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_ibank_creditstoyou'] . "</td>" .
             "<tr><td colspan=2 align=center>" . $langvars['l_ibank_accounts'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_shipaccount'] . " :<br>" . $langvars['l_ibank_ibankaccount'] . " :</td>" .
             "<td align=right>" . number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" . number_format($account['balance'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</tr>" .
             "<tr valign=bottom>" .
             "<td><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
             "</tr>";

        $stmt = $pdo_db->prepare("UPDATE {$pdo_db->prefix}ibank_accounts SET balance = balance + :amount WHERE ship_id=:ship_id");
        $result = $stmt->execute(array($amount, $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($pdo_db, $pdo_db, $result, __LINE__, __FILE__);

        $stmt = $pdo_db->prepare("UPDATE {$pdo_db->prefix}ships SET credits = credits - :amount WHERE ship_id=:ship_id");
        $result = $stmt->execute(array($amount, $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($pdo_db, $pdo_db, $result, __LINE__, __FILE__);
    }

    public static function ibankConsolidate2($db, $pdo_db, $lang, $langvars, $playerinfo, \Tki\Reg $tkireg, $dplanet_id, $minimum, $maximum, $template)
    {
        $res = $db->Execute("SELECT name, credits, owner, sector_id FROM {$db->prefix}planets WHERE planet_id = ?", array($dplanet_id));
        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);

        if (!$res || $res->EOF)
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $lang, $tkireg);
        }
        $dest = $res->fields;

        if (empty($dest['name']))
        {
            $dest['name'] = $langvars['l_ibank_unnamed'];
        }

        if ($dest['owner'] != $playerinfo['ship_id'])
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnotyourplanet'], "ibank.php?command=transfer", $lang, $tkireg);
        }

        $minimum = preg_replace("/[^0-9]/", '', $minimum);
        $maximum = preg_replace("/[^0-9]/", '', $maximum);

        $query = "SELECT SUM(credits) AS total, COUNT(*) AS count FROM {$db->prefix}planets WHERE owner=? AND credits != 0 AND planet_id != ?";

        if ($minimum != 0)
        {
            $query .= " AND credits >= $minimum";
        }

        if ($maximum != 0)
        {
            $query .= " AND credits <= $maximum";
        }

        $res = $db->Execute($query, array($playerinfo['ship_id'], $dplanet_id));
        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
        $amount = $res->fields;

        $fee = $tkireg->ibank_paymentfee * $amount['total'];

        $tcost = ceil($amount['count'] / $tkireg->ibank_tconsolidate);
        $transfer = $amount['total'] - $fee;

        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_planetconsolidate'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_currentpl'] . " " . $dest['name'] . " " . $langvars['l_ibank_in'] . " " . $dest['sector_id'] . " :</td>" .
             "<td align=right>" . number_format($dest['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</td>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_transferamount'] . " :</td>" .
             "<td align=right>" . number_format($amount['total'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</td>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_transferfee'] . " :</td>" .
             "<td align=right>" . number_format($fee, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C </td>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_plaffected'] . " :</td>" .
             "<td align=right>" . number_format($amount['count'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_turncost'] . " :</td>" .
             "<td align=right>" . number_format($tcost, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_amounttransferred'] . ":</td>" .
             "<td align=right>" . number_format($transfer, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C</td>" .
             "<tr valign=top><td colspan=2 align=right>" .
             "<form accept-charset='utf-8' action='ibank.php?command=consolidate3' method=post>" .
             "<input type=hidden name=minimum value=" . $minimum . "><br>" .
             "<input type=hidden name=maximum value=" . $maximum . "><br>" .
             "<input type=hidden name=dplanet_id value=" . $dplanet_id . ">" .
             "<input class=term type=submit value=\"" . $langvars['l_ibank_consolidate'] . "\"></td>" .
             "</form>" .
             "<tr valign=bottom>" .
             "<td><a href='ibank.php?command=transfer'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
             "</tr>";
    }

    public static function ibankError($pdo_db, $langvars, $errmsg, string $backlink, $lang, $tkireg, $template)
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
             "<img width=600 height=21 src=" . $active_template . "/images/div2.png>" .
             "</center>";

        \Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
        die();
    }

    public static function isLoanPending(\PDO $pdo_db, $ship_id, \Tki\Reg $tkireg)
    {
        $stmt = $pdo_db->prepare("SELECT loan, UNIX_TIMESTAMP(loantime) AS time FROM {$pdo_db->prefix}ibank_accounts WHERE ship_id = :ship_id");
        $stmt->bindParam(':ship_id', $ship_id);
        $result = $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $pdo_db, $result, __LINE__, __FILE__);
        $account = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($account['loan'] > 0)
        {
            $curtime = time();
            $difftime = ($curtime - $account['time']) / 60;
            if ($difftime > $tkireg->ibank_lrate)
            {
                return true;
            }
        }
        else
        {
            return false;
        }
    }

    public static function ibankDeposit(\PDO $pdo_db, $lang, $account, $playerinfo)
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

    public static function ibankConsolidate3($db, \PDO $pdo_db, $langvars, $playerinfo, \Tki\Reg $tkireg, $dplanet_id, $minimum, $maximum, $template)
    {
        $res = $db->Execute("SELECT name, credits, owner, sector_id FROM {$db->prefix}planets WHERE planet_id = ?", array($dplanet_id));
        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
        if (!$res || $res->EOF)
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $tkireg, "Error");
        }

        $dest = $res->fields;

        if (empty($dest['name']))
        {
            $dest['name'] = $langvars['l_ibank_unnamed'];
        }

        if ($dest['owner'] != $playerinfo['ship_id'])
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnotyourplanet'], "ibank.php?command=transfer", $tkireg, "Error");
        }

        $minimum = preg_replace("/[^0-9]/", '', $minimum);
        $maximum = preg_replace("/[^0-9]/", '', $maximum);

        $query = "SELECT SUM(credits) as total, COUNT(*) AS count FROM {$db->prefix}planets WHERE owner=? AND credits != 0 AND planet_id != ?";

        if ($minimum != 0)
        {
            $query .= " AND credits >= $minimum";
        }

        if ($maximum != 0)
        {
            $query .= " AND credits <= $maximum";
        }

        $res = $db->Execute($query, array($playerinfo['ship_id'], $dplanet_id));
        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
        $amount = $res->fields;

        $fee = $tkireg->ibank_paymentfee * $amount['total'];

        $tcost = ceil($amount['count'] / $tkireg->ibank_tconsolidate);
        $transfer = $amount['total'] - $fee;

        $cplanet = $transfer + $dest['credits'];

        if ($tcost > $playerinfo['turns'])
        {
            self::ibankError($pdo_db, $langvars, $langvars['l_ibank_notenturns'], "ibank.php?command=transfer", $tkireg, "Error");
        }

        echo "<tr><td colspan=2 align=center valign=top>" . $langvars['l_ibank_transfersuccessful'] . "<br>---------------------------------</td></tr>" .
             "<tr valign=top>" .
             "<td>" . $langvars['l_ibank_currentpl'] . " " . $dest['name'] . " " . $langvars['l_ibank_in'] . " " . $dest['sector_id'] . " :<br><br>" .
             $langvars['l_ibank_turncost'] . " :</td>" .
             "<td align=right>" . number_format($cplanet, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br><br>" .
             number_format($tcost, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</td>" .
             "<tr valign=bottom>" .
             "<td><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout ']. "</a></td>" .
             "</tr>";

        $query = "UPDATE {$db->prefix}planets SET credits=0 WHERE owner=? AND credits != 0 AND planet_id != ?";

        if ($minimum != 0)
        {
            $query .= " AND credits >= $minimum";
        }

        if ($maximum != 0)
        {
            $query .= " AND credits <= $maximum";
        }

        $res = $db->Execute($query, array($playerinfo['ship_id'], $dplanet_id));
        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
        $res = $db->Execute("UPDATE {$db->prefix}planets SET credits=credits + ? WHERE planet_id=?", array($transfer, $dplanet_id));
        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
        $res = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns - ? WHERE ship_id=?", array($tcost, $playerinfo['ship_id']));
        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
    }
}
