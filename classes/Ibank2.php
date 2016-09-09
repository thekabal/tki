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
// File: classes/Ibank2.php

namespace Tki;

class Ibank2
{
    public static function ibankWithdraw2(\PDO $pdo_db, $lang, Array $langvars, Array $playerinfo, $amount, $account, Reg $tkireg, $template)
    {
        $amount = preg_replace("/[^0-9]/", '', $amount);
        if (($amount * 1) != $amount)
        {
            \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_invalidwithdrawinput'], "ibank.php?command=withdraw", $lang, $tkireg, $template);
        }

        if ($amount == 0)
        {
            \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_nozeroamount3'], "ibank.php?command=withdraw", $lang, $tkireg, $template);
        }

        if ($amount > $account['balance'])
        {
            \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_notenoughcredits'], "ibank.php?command=withdraw", $lang, $tkireg, $template);
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

        $sql = "UPDATE ::prefix::ibank_accounts SET balance = balance - :amount WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->execute(array($amount, $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        $sql = "UPDATE ::prefix::ships SET credits = credits + :amount WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->execute(array($amount, $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    }

    public static function ibankTransfer2($db, \PDO $pdo_db, $lang, Array $langvars, Reg $tkireg, Array $playerinfo, $account, int $ship_id, int $splanet_id, int $dplanet_id, $template)
    {
        if ($ship_id !== null) // Ship transfer
        {
            if ($playerinfo['ship_id'] == $ship_id)
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_sendyourself'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $stmt = $pdo_db->prepare("SELECT * FROM ::prefix::ships WHERE ship_id=:ship_id AND ship_destroyed = 'N' AND turns_used > :turns_used");
            $stmt->bindParam(':ship_id', $ship_id);
            $stmt->bindParam(':turns_used', $tkireg->ibank_min_turns);
            $target = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$target)
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_unknowntargetship'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($target['turns_used'] < $tkireg->ibank_min_turns)
            {
                $langvars['l_ibank_min_turns'] = str_replace("[ibank_min_turns]", $tkireg->ibank_min_turns, $langvars['l_ibank_min_turns']);
                $langvars['l_ibank_min_turns'] = str_replace("[ibank_target_char_name]", $target['character_name'], $langvars['l_ibank_min_turns']);
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_min_turns'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($playerinfo['turns_used'] < $tkireg->ibank_min_turns)
            {
                $langvars['l_ibank_min_turns2'] = str_replace("[ibank_min_turns]", $tkireg->ibank_min_turns, $langvars['l_ibank_min_turns2']);
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_min_turns2'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($tkireg->ibank_trate > 0)
            {
                $curtime = time();
                $curtime -= $tkireg->ibank_trate * 60;
                $sql = "SELECT UNIX_TIMESTAMP(time) as time FROM {$db->prefix}ibank_transfers WHERE UNIX_TIMESTAMP(time) > ? AND source_id = ? AND dest_id = ?";
                $res = $db->Execute($sql, array($curtime, $playerinfo['ship_id'], $target['ship_id']));
                \Tki\Db::LogDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                if (!$res->EOF)
                {
                    $time = $res->fields;
                    $difftime = ($time['time'] - $curtime) / 60;
                    $langvars['l_ibank_mustwait'] = str_replace("[ibank_target_char_name]", $target['character_name'], $langvars['l_ibank_mustwait']);
                    $langvars['l_ibank_mustwait'] = str_replace("[ibank_trate]", number_format($tkireg->ibank_trate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_ibank_mustwait']);
                    $langvars['l_ibank_mustwait'] = str_replace("[ibank_difftime]", number_format($difftime, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_ibank_mustwait']);
                    \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_mustwait'], "ibank.php?command=transfer", $lang, $tkireg, $template);
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
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_errplanetsrcanddest'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $sql = "SELECT name, credits, owner, sector_id FROM {$db->prefix}planets WHERE planet_id = ?";
            $res = $db->Execute($sql, array($splanet_id));
            \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
            if (!$res || $res->EOF)
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $source = $res->fields;

            if (empty($source['name']))
            {
                $source['name'] = $langvars['l_ibank_unnamed'];
            }

            $sql = "SELECT name, credits, owner, sector_id, base FROM {$db->prefix}planets WHERE planet_id = ?";
            $res = $db->Execute($sql, array($dplanet_id));
            \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
            if (!$res || $res->EOF)
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $dest = $res->fields;

            if (empty($dest['name']))
            {
                $dest['name'] = $langvars['l_ibank_unnamed'];
            }

            if ($dest['base'] == 'N')
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnobase'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($source['owner'] != $playerinfo['ship_id'] || $dest['owner'] != $playerinfo['ship_id'])
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnotyourplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
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

    public static function ibankTransfer3($db, \PDO $pdo_db, $lang, Array $langvars, Array $playerinfo, $account, int $ship_id, int $splanet_id, int $dplanet_id, $amount, Reg $tkireg, $template)
    {
        $amount = preg_replace("/[^0-9]/", '', $amount);

        if ($amount < 0)
        {
            $amount = 0;
        }

        if ($ship_id !== null) // Ship transfer
        {
            // Need to check again to prevent cheating by manual posts

            $sql = "SELECT * FROM {$db->prefix}ships WHERE ship_id = ? AND ship_destroyed ='N' AND turns_used > ?";
            $res = $db->Execute($sql, array($ship_id, $tkireg->ibank_min_turns));
            \Tki\Db::LogDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            if ($playerinfo['ship_id'] == $ship_id)
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_errsendyourself'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if (!$res || $res->EOF)
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_unknowntargetship'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $target = $res->fields;

            if ($target['turns_used'] < $tkireg->ibank_min_turns)
            {
                $langvars['l_ibank_min_turns3'] = str_replace("[ibank_min_turns]", $tkireg->ibank_min_turns, $langvars['l_ibank_min_turns3']);
                $langvars['l_ibank_min_turns3'] = str_replace("[ibank_target_char_name]", $target['character_name'], $langvars['l_ibank_min_turns3']);
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_min_turns3'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($playerinfo['turns_used'] < $tkireg->ibank_min_turns)
            {
                $langvars['l_ibank_min_turns4'] = str_replace("[ibank_min_turns]", $tkireg->ibank_min_turns, $langvars['l_ibank_min_turns4']);
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_min_turns4'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($tkireg->ibank_trate > 0)
            {
                $curtime = time();
                $curtime -= $tkireg->ibank_trate * 60;
                $sql = "SELECT UNIX_TIMESTAMP(time) as time FROM {$db->prefix}ibank_transfers WHERE UNIX_TIMESTAMP(time) > ? AND source_id = ? AND dest_id = ?";
                $res = $db->Execute($sql, array($curtime, $playerinfo['ship_id'], $target['ship_id']));
                \Tki\Db::LogDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                if (!$res->EOF)
                {
                    $time = $res->fields;
                    $difftime = ($time['time'] - $curtime) / 60;
                    $langvars['l_ibank_mustwait2'] = str_replace("[ibank_target_char_name]", $target['character_name'], $langvars['l_ibank_mustwait2']);
                    $langvars['l_ibank_mustwait2'] = str_replace("[ibank_trate]", number_format($tkireg->ibank_trate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_ibank_mustwait2']);
                    $langvars['l_ibank_mustwait2'] = str_replace("[ibank_difftime]", number_format($difftime, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_ibank_mustwait2']);
                    \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_mustwait2'], "ibank.php?command=transfer", $lang, $tkireg, $template);
                }
            }

            if (($amount * 1) != $amount)
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_invalidtransferinput'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($amount == 0)
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_nozeroamount'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($amount > $account['balance'])
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_notenoughcredits'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($tkireg->ibank_svalue != 0)
            {
                $score = \Tki\Score::updateScore($pdo_db, $playerinfo['ship_id'], $tkireg, $playerinfo);
                $maxtrans = $score * $score * $tkireg->ibank_svalue;

                if ($amount > $maxtrans)
                {
                    \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_amounttoogreat'], "ibank.php?command=transfer", $lang, $tkireg, $template);
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

            $sql = "UPDATE {$db->prefix}ibank_accounts SET balance = balance - ? WHERE ship_id = ?";
            $db->Execute($sql, array($amount, $playerinfo['ship_id']));
            \Tki\Db::LogDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            $sql = "UPDATE {$db->prefix}ibank_accounts SET balance = balance + ? WHERE ship_id = ?";
            $db->Execute($sql, array($transfer, $target['ship_id']));
            \Tki\Db::LogDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            $sql = "INSERT INTO {$db->prefix}ibank_transfers VALUES (NULL, ?, ?, NOW(), ?)";
            $db->Execute($sql, array($playerinfo['ship_id'], $target['ship_id'], $transfer));
            \Tki\Db::LogDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        }
        else
        {
            if ($splanet_id == $dplanet_id)
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_errplanetsrcanddest'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $sql = "SELECT name, credits, owner, sector_id FROM {$db->prefix}planets WHERE planet_id = ?";
            $res = $db->Execute($sql, array($splanet_id));
            \Tki\Db::LogDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            if (!$res || $res->EOF)
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $source = $res->fields;

            if (empty($source['name']))
            {
                $source['name'] = $langvars['l_ibank_unnamed'];
            }

            $sql = "SELECT name, credits, owner, sector_id FROM {$db->prefix}planets WHERE planet_id = ?";
            $res = $db->Execute($sql, array($dplanet_id));
            \Tki\Db::LogDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            if (!$res || $res->EOF)
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $dest = $res->fields;

            if (empty($dest['name']))
            {
                $dest['name'] = $langvars['l_ibank_unnamed'];
            }

            if ($source['owner'] != $playerinfo['ship_id'] || $dest['owner'] != $playerinfo['ship_id'])
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnotyourplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($amount > $source['credits'])
            {
                \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_notenoughcredits2'], "ibank.php?command=transfer", $lang, $tkireg, $template);
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

            $sql = "UPDATE {$db->prefix}planets SET credits=credits - ? WHERE planet_id = ?";
            $db->Execute($sql, array($amount, $splanet_id));
            \Tki\Db::LogDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            $sql = "UPDATE {$db->prefix}planets SET credits=credits + ? WHERE planet_id = ?";
            $db->Execute($sql, array($transfer, $dplanet_id));
            \Tki\Db::LogDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        }
    }

    public static function ibankDeposit2(\PDO $pdo_db, $lang, Array $langvars, Array $playerinfo, $amount, $account, Reg $tkireg, $template)
    {
        $max_credits_allowed = 18446744073709000000;

        $amount = preg_replace("/[^0-9]/", '', $amount);

        if (($amount * 1) != $amount)
        {
            \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_invaliddepositinput'], "ibank.php?command=deposit", $lang, $tkireg, $template);
        }

        if ($amount == 0)
        {
            \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_nozeroamount2'], "ibank.php?command=deposit", $lang, $tkireg, $template);
        }

        if ($amount > $playerinfo['credits'])
        {
            \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_notenoughcredits'], "ibank.php?command=deposit", $lang, $tkireg, $template);
        }

        $tmpcredits = $max_credits_allowed - $account['balance'];
        if ($tmpcredits < 0)
        {
            $tmpcredits = 0;
        }

        if ($amount > $tmpcredits)
        {
            \Tki\Ibank3::ibankError($pdo_db, $langvars, "<center>Error You cannot deposit that much into your bank,<br> (Max Credits Reached)</center>", "ibank.php?command=deposit", $lang, $tkireg, $template);
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

        $sql = "UPDATE ::prefix::ibank_accounts SET balance = balance + :amount WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->execute(array($amount, $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        $sql = "UPDATE ::prefix::ships SET credits = credits - :amount WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->execute(array($amount, $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    }

    public static function ibankConsolidate2(\PDO $pdo_db, $lang, Array $langvars, Array $playerinfo, Reg $tkireg, int $dplanet_id, int $minimum, int $maximum, $template)
    {
        $sql = "SELECT name, credits, owner, sector_id FROM ::prefix::planets WHERE planet_id=:planet_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':planet_id', $dplanet_id);
        $stmt->execute();
        $dest = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($dest === null)
        {
            \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
        }

        if (empty($dest['name']))
        {
            $dest['name'] = $langvars['l_ibank_unnamed'];
        }

        if ($dest['owner'] != $playerinfo['ship_id'])
        {
            \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnotyourplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
        }

        if ($minimum != 0)
        {
            $sql = "SELECT SUM(credits) as total, COUNT(*) AS count FROM ::prefix::planets WHERE owner = :owner_id AND credits != 0 AND planet_id != :planet_id AND credits >= :minimum";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner_id', $playerinfo['ship_id']);
            $stmt->bindParam(':dplanet_id', $dplanet_id);
            $stmt->bindParam(':minimum', $minimum);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            $amount = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if ($maximum != 0)
        {
            $sql = "UPDATE ::prefix::planets SET credits = 0 WHERE owner = :owner_id AND credits != 0 AND planet_id != :dplanet_id AND credxits <= :maximum";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner_id', $playerinfo['ship_id']);
            $stmt->bindParam(':dplanet_id', $dplanet_id);
            $stmt->bindParam(':maximum', $maximum);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        }

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

    public static function ibankConsolidate3(\PDO $pdo_db, Array $langvars, Array $playerinfo, Reg $tkireg, int $dplanet_id, int $minimum, int $maximum, $lang, $template)
    {
        $sql = "SELECT name, credits, owner, sector_id FROM ::prefix::planets WHERE planet_id=:planet_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':planet_id', $dplanet_id);
        $stmt->execute();
        $dest = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($dest === null)
        {
            \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
        }

        if (empty($dest['name']))
        {
            $dest['name'] = $langvars['l_ibank_unnamed'];
        }

        if ($dest['owner'] != $playerinfo['ship_id'])
        {
            \Tki\Ibank3::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnotyourplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
        }

        if ($minimum != 0)
        {
            $sql = "SELECT SUM(credits) as total, COUNT(*) AS count FROM ::prefix::planets WHERE owner = :owner_id AND credits != 0 AND planet_id != :planet_id AND credits >= :minimum";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner_id', $playerinfo['ship_id']);
            $stmt->bindParam(':dplanet_id', $dplanet_id);
            $stmt->bindParam(':minimum', $minimum);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            $amount = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if ($maximum != 0)
        {
            $sql = "SELECT SUM(credits) as total, COUNT(*) AS count FROM ::prefix::planets WHERE owner = :owner_id AND credits != 0 AND planet_id != :planet_id AND credits <= :maximum";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner_id', $playerinfo['ship_id']);
            $stmt->bindParam(':dplanet_id', $dplanet_id);
            $stmt->bindParam(':maximum', $maximum);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            $amount = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        $fee = $tkireg->ibank_paymentfee * $amount['total'];
        $tcost = ceil($amount['count'] / $tkireg->ibank_tconsolidate);
        $transfer = $amount['total'] - $fee;
        $cplanet = $transfer + $dest['credits'];

        if ($tcost > $playerinfo['turns'])
        {
            \Tki\Ibank2::ibankError($pdo_db, $langvars, $langvars['l_ibank_notenturns'], "ibank.php?command=transfer", $lang, $tkireg, $template);
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

        if ($minimum != 0)
        {
            $sql = "UPDATE ::prefix::planets SET credits = 0 WHERE owner = :owner_id AND credits != 0 AND planet_id != :dplanet_id AND credits >= :minimum";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner_id', $playerinfo['ship_id']);
            $stmt->bindParam(':dplanet_id', $dplanet_id);
            $stmt->bindParam(':minimum', $minimum);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        }

        if ($maximum != 0)
        {
            $sql = "UPDATE ::prefix::planets SET credits = 0 WHERE owner = :owner_id AND credits != 0 AND planet_id != :dplanet_id AND credxits <= :maximum";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner_id', $playerinfo['ship_id']);
            $stmt->bindParam(':dplanet_id', $dplanet_id);
            $stmt->bindParam(':maximum', $maximum);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        }

        $sql = "UPDATE ::prefix::planets SET credits = :credits WHERE planet_id = :planet_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':credits', $cplanet);
        $stmt->bindParam(':planet_id', $dplanet_id);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        $sql = "UPDATE ::prefix::ships SET turns = turns - :turns WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':turns', $tcost);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    }
}
