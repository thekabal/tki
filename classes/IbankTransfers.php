<?php declare(strict_types = 1);
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
// File: classes/IbankTransfers.php

namespace Tki;

class IbankTransfers
{
    public static function ibankTransferPrime(\PDO $pdo_db, array $langvars, array $playerinfo, Reg $tkireg): void
    {
        $sql = "SELECT * FROM ::prefix::ships WHERE email not like '%@kabal' AND ship_destroyed ='N' AND turns_used > :ibank_min_turns ORDER BY character_name ASC";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ibank_min_turns', $tkireg->ibank_min_turns, \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        $ships = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $sql = "SELECT name, planet_id, sector_id FROM ::prefix::planets WHERE owner=:owner ORDER BY sector_id ASC";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':owner', $playerinfo['ship_id'], \PDO::PARAM_INT);
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

    public static function ibankTransfer2(\PDO $pdo_db, string $lang, array $langvars, Reg $tkireg, array $playerinfo, array $account, int $ship_id, int $splanet_id, int $dplanet_id, Smarty $template): void
    {
        if ($ship_id !== null) // Ship transfer
        {
            if ($playerinfo['ship_id'] == $ship_id)
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_sendyourself'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $stmt = $pdo_db->prepare("SELECT * FROM ::prefix::ships WHERE ship_id=:ship_id AND ship_destroyed = 'N' AND turns_used > :turns_used");
            $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
            $stmt->bindParam(':turns_used', $tkireg->ibank_min_turns, \PDO::PARAM_INT);
            $target = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$target)
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_unknowntargetship'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($target['turns_used'] < $tkireg->ibank_min_turns)
            {
                $langvars['l_ibank_min_turns'] = str_replace("[ibank_min_turns]", $tkireg->ibank_min_turns, $langvars['l_ibank_min_turns']);
                $langvars['l_ibank_min_turns'] = str_replace("[ibank_target_char_name]", $target['character_name'], $langvars['l_ibank_min_turns']);
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_min_turns'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($playerinfo['turns_used'] < $tkireg->ibank_min_turns)
            {
                $langvars['l_ibank_min_turns2'] = str_replace("[ibank_min_turns]", $tkireg->ibank_min_turns, $langvars['l_ibank_min_turns2']);
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_min_turns2'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($tkireg->ibank_trate > 0)
            {
                $curtime = time();
                $curtime -= $tkireg->ibank_trate * 60;

                $stmt = $pdo_db->prepare("SELECT UNIX_TIMESTAMP(time) as time FROM ::prefix::ibank_transfers WHERE UNIX_TIMESTAMP(time) > :curtime AND source_id = :source_id AND dest_id = :dest_id");
                $stmt->bindParam(':curtime', $curtime, \PDO::PARAM_INT);
                $stmt->bindParam(':source_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                $stmt->bindParam(':dest_id', $target['ship_id'], \PDO::PARAM_INT);
                $time = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($time !== null)
                {
                    $difftime = ($time['time'] - $curtime) / 60;
                    $langvars['l_ibank_mustwait'] = str_replace("[ibank_target_char_name]", $target['character_name'], $langvars['l_ibank_mustwait']);
                    $langvars['l_ibank_mustwait'] = str_replace("[ibank_trate]", number_format($tkireg->ibank_trate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_ibank_mustwait']);
                    $langvars['l_ibank_mustwait'] = str_replace("[ibank_difftime]", number_format($difftime, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_ibank_mustwait']);
                    \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_mustwait'], "ibank.php?command=transfer", $lang, $tkireg, $template);
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
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_errplanetsrcanddest'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $sql = "SELECT name, credits, owner, sector_id FROM ::prefix::planets WHERE planet_id=:planet_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':planet_id', $splanet_id, \PDO::PARAM_INT);
            $stmt->execute();
            $source = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($source === null)
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if (empty($source['name']))
            {
                $source['name'] = $langvars['l_ibank_unnamed'];
            }

            $sql = "SELECT name, credits, owner, sector_id, base FROM ::prefix::planets WHERE planet_id=:planet_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':planet_id', $dplanet_id, \PDO::PARAM_INT);
            $stmt->execute();
            $dest = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($dest === null)
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if (empty($dest['name']))
            {
                $dest['name'] = $langvars['l_ibank_unnamed'];
            }

            if ($dest['base'] == 'N')
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnobase'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($source['owner'] != $playerinfo['ship_id'] || $dest['owner'] != $playerinfo['ship_id'])
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnotyourplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
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

    public static function ibankTransfer3(\PDO $pdo_db, $db, string $lang, array $langvars, array $playerinfo, array $account, int $ship_id, int $splanet_id, int $dplanet_id, int $amount, Reg $tkireg, Smarty $template): void
    {
        $amount = preg_replace("/[^0-9]/", '', $amount);

        if ($amount < 0)
        {
            $amount = 0;
        }

        if ($ship_id !== null) // Ship transfer
        {
            if ($playerinfo['ship_id'] == $ship_id)
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_errsendyourself'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            // Need to check again to prevent cheating by manual posts
            $stmt = $pdo_db->prepare("SELECT * FROM ::prefix::ships WHERE ship_id=:ship_id AND ship_destroyed = 'N' AND turns_used > :turns_used");
            $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
            $stmt->bindParam(':turns_used', $tkireg->ibank_min_turns, \PDO::PARAM_INT);
            $target = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($target === null)
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_unknowntargetship'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($target['turns_used'] < $tkireg->ibank_min_turns)
            {
                $langvars['l_ibank_min_turns3'] = str_replace("[ibank_min_turns]", $tkireg->ibank_min_turns, $langvars['l_ibank_min_turns3']);
                $langvars['l_ibank_min_turns3'] = str_replace("[ibank_target_char_name]", $target['character_name'], $langvars['l_ibank_min_turns3']);
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_min_turns3'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($playerinfo['turns_used'] < $tkireg->ibank_min_turns)
            {
                $langvars['l_ibank_min_turns4'] = str_replace("[ibank_min_turns]", $tkireg->ibank_min_turns, $langvars['l_ibank_min_turns4']);
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_min_turns4'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($tkireg->ibank_trate > 0)
            {
                $curtime = time();
                $curtime -= $tkireg->ibank_trate * 60;

                $sql = "SELECT UNIX_TIMESTAMP(time) as time FROM ::prefix::ibank_transfers WHERE " .
                       "UNIX_TIMESTAMP(time) > :curtime AND source_id = :source_id AND dest_id = :dest_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':curtime', $curtime, \PDO::PARAM_INT);
                $stmt->bindParam(':source_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                $stmt->bindParam(':dest_id', $target['ship_id'], \PDO::PARAM_INT);
                $time = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($time !== null)
                {
                    $difftime = ($time['time'] - $curtime) / 60;
                    $langvars['l_ibank_mustwait2'] = str_replace("[ibank_target_char_name]", $target['character_name'], $langvars['l_ibank_mustwait2']);
                    $langvars['l_ibank_mustwait2'] = str_replace("[ibank_trate]", number_format($tkireg->ibank_trate, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_ibank_mustwait2']);
                    $langvars['l_ibank_mustwait2'] = str_replace("[ibank_difftime]", number_format($difftime, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_ibank_mustwait2']);
                    \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_mustwait2'], "ibank.php?command=transfer", $lang, $tkireg, $template);
                }
            }

            if (($amount * 1) != $amount)
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_invalidtransferinput'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($amount == 0)
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_nozeroamount'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($amount > $account['balance'])
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_notenoughcredits'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($tkireg->ibank_svalue != 0)
            {
                $score = \Tki\Score::updateScore($pdo_db, $playerinfo['ship_id'], $tkireg, $playerinfo);
                $maxtrans = $score * $score * $tkireg->ibank_svalue;

                if ($amount > $maxtrans)
                {
                    \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_amounttoogreat'], "ibank.php?command=transfer", $lang, $tkireg, $template);
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

            $sql = "UPDATE ::prefix::ibank_accounts SET balance=balance-:amount WHERE ship_id=:ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':amount', $amount, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            $sql = "UPDATE ::prefix::ibank_accounts SET balance=balance+:amount WHERE ship_id=:ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':amount', $transfer, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', target['ship_id'], \PDO::PARAM_INT);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            $sql = "INSERT ::prefix::ibank_transfers VALUES (null, :ship_id, :target_id, NOW(), :transfer)";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':target_id', $target['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':transfer', $transfer, \PDO::PARAM_INT);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        }
        else
        {
            if ($splanet_id == $dplanet_id)
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_errplanetsrcanddest'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $sql = "SELECT name, credits, owner, sector_id FROM {$db->prefix}planets WHERE planet_id = ?";
            $res = $db->Execute($sql, array($splanet_id));
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            if (!$res || $res->EOF)
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $source = $res->fields;

            if (empty($source['name']))
            {
                $source['name'] = $langvars['l_ibank_unnamed'];
            }

            $sql = "SELECT name, credits, owner, sector_id FROM {$db->prefix}planets WHERE planet_id = ?";
            $res = $db->Execute($sql, array($dplanet_id));
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            if (!$res || $res->EOF)
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            $dest = $res->fields;

            if (empty($dest['name']))
            {
                $dest['name'] = $langvars['l_ibank_unnamed'];
            }

            if ($source['owner'] != $playerinfo['ship_id'] || $dest['owner'] != $playerinfo['ship_id'])
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnotyourplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
            }

            if ($amount > $source['credits'])
            {
                \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_notenoughcredits2'], "ibank.php?command=transfer", $lang, $tkireg, $template);
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

            $sql = "UPDATE ::prefix::planets SET credits=credits-:amount  WHERE planet_id=:planet_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':amount', $amount, \PDO::PARAM_INT);
            $stmt->bindParam(':planet_id', $splanet_id, \PDO::PARAM_INT);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            $sql = "UPDATE ::prefix::planets SET credits=credits+:amount  WHERE planet_id=:planet_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':amount', $transfer, \PDO::PARAM_INT);
            $stmt->bindParam(':planet_id', $dplanet_id, \PDO::PARAM_INT);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        }
    }
}
