<?php declare(strict_types = 1);
/**
 * classes/IbankTransferFinal.php from The Kabal Invasion.
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

class IbankTransferFinal
{
    public static function final(\PDO $pdo_db, string $lang, array $playerinfo, $ship_id, int $splanet_id, int $dplanet_id, int $amount, Reg $tkireg, Timer $tkitimer, Smarty $template): void
    {
        $langvars = Translate::load($pdo_db, $lang, array('ibank', 'regional'));
        $ibank_gateway = new Ibank\IbankGateway($pdo_db);
        $bank_account = $ibank_gateway->selectIbankAccount($playerinfo['ship_id']);
        $amount = preg_replace("/[^0-9]/", '', (string) $amount);
        $amount = (int) $amount;
        $source = null;
        $dest = null;

        if ($amount < 0)
        {
            $amount = 0;
        }

        if ($ship_id !== null) // Ship transfer
        {
            if ($playerinfo['ship_id'] == $ship_id)
            {
                \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_errsendyourself'], "ibank.php?command=transfer", $tkireg, $tkitimer, $template);
            }

            // Need to check again to prevent cheating by manual posts
            $stmt = $pdo_db->prepare("SELECT * FROM ::prefix::ships WHERE ship_id = :ship_id AND ship_destroyed = 'N' AND turns_used > :turns_used");
            $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
            $stmt->bindParam(':turns_used', $tkireg->ibank_min_turns, \PDO::PARAM_INT);
            $target = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($target === null)
            {
                \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_unknowntargetship'], "ibank.php?command=transfer", $tkireg, $tkitimer, $template);
            }

            if ($target['turns_used'] < $tkireg->ibank_min_turns)
            {
                $langvars['l_ibank_min_turns3'] = str_replace("[ibank_min_turns]", $tkireg->ibank_min_turns, $langvars['l_ibank_min_turns3']);
                $langvars['l_ibank_min_turns3'] = str_replace("[ibank_target_char_name]", $target['character_name'], $langvars['l_ibank_min_turns3']);
                \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_min_turns3'], "ibank.php?command=transfer", $tkireg, $tkitimer, $template);
            }

            if ($playerinfo['turns_used'] < $tkireg->ibank_min_turns)
            {
                $langvars['l_ibank_min_turns4'] = str_replace("[ibank_min_turns]", $tkireg->ibank_min_turns, $langvars['l_ibank_min_turns4']);
                \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_min_turns4'], "ibank.php?command=transfer", $tkireg, $tkitimer, $template);
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
                    \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_mustwait2'], "ibank.php?command=transfer", $tkireg, $tkitimer, $template);
                }
            }

            if (($amount * 1) != $amount)
            {
                \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_invalidtransferinput'], "ibank.php?command=transfer", $tkireg, $tkitimer, $template);
            }

            if ($amount == 0)
            {
                \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_nozeroamount'], "ibank.php?command=transfer", $tkireg, $tkitimer, $template);
            }

            if ($amount > $bank_account['balance'])
            {
                \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_notenoughcredits'], "ibank.php?command=transfer", $tkireg, $tkitimer, $template);
            }

            if ($tkireg->ibank_svalue != 0)
            {
                $score = \Tki\Score::updateScore($pdo_db, $playerinfo['ship_id'], $tkireg, $playerinfo);
                $maxtrans = $score * $score * $tkireg->ibank_svalue;

                if ($amount > $maxtrans)
                {
                    \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_amounttoogreat'], "ibank.php?command=transfer", $tkireg, $tkitimer, $template);
                }
            }

            $bank_account['balance'] -= $amount;
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
                 "<td>" . $langvars['l_ibank_ibankaccount'] . " :</td><td align=right>" . number_format($bank_account['balance'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " C<br>" .
                 "<tr valign=bottom>" .
                 "<td><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] . "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout'] . "</a></td>" .
                 "</tr>";

            $sql = "UPDATE ::prefix::ibank_accounts SET balance = balance - :amount WHERE ship_id = :ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':amount', $amount, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            $sql = "UPDATE ::prefix::ibank_accounts SET balance = balance + :amount WHERE ship_id = :ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':amount', $transfer, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $target['ship_id'], \PDO::PARAM_INT);
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
                \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_errplanetsrcanddest'], "ibank.php?command=transfer", $tkireg, $tkitimer, $template);
            }

            $sql = "SELECT name, credits, owner, sector_id FROM ::prefix::planets WHERE planet_id = :planet_id";

            $stmt = $pdo_db->prepare($sql);
            $sql_test = \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            if ($sql_test === true)
            {
                $stmt->bindParam(':planet_id', $splanet_id, \PDO::PARAM_INT);
                $stmt->execute();
                $source = $stmt->fetch(\PDO::FETCH_ASSOC);
            }

            if (empty($source))
            {
                \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $tkireg, $tkitimer, $template);
            }

            if (empty($source['name']))
            {
                $source['name'] = $langvars['l_ibank_unnamed'];
            }

            $sql = "SELECT name, credits, owner, sector_id FROM ::prefix::planets WHERE planet_id = :planet_id";
            $stmt = $pdo_db->prepare($sql);
            $sql_test = \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            if ($sql_test === true)
            {
                $stmt->bindParam(':planet_id', $dplanet_id, \PDO::PARAM_INT);
                $stmt->execute();
                $dest = $stmt->fetch(\PDO::FETCH_ASSOC);
            }

            if (empty($dest))
            {
                \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_errunknownplanet'], "ibank.php?command=transfer", $tkireg, $tkitimer, $template);
            }

            if (empty($dest['name']))
            {
                $dest['name'] = $langvars['l_ibank_unnamed'];
            }

            if ($source['owner'] != $playerinfo['ship_id'] || $dest['owner'] != $playerinfo['ship_id'])
            {
                \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_errnotyourplanet'], "ibank.php?command=transfer", $tkireg, $tkitimer, $template);
            }

            if ($amount > $source['credits'])
            {
                \Tki\Ibank::ibankError($pdo_db, $lang, $langvars['l_ibank_notenoughcredits2'], "ibank.php?command=transfer", $tkireg, $tkitimer, $template);
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

            $sql = "UPDATE ::prefix::planets SET credits = credits - :amount WHERE planet_id = :planet_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':amount', $amount, \PDO::PARAM_INT);
            $stmt->bindParam(':planet_id', $splanet_id, \PDO::PARAM_INT);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            $sql = "UPDATE ::prefix::planets SET credits = credits + :amount WHERE planet_id = :planet_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':amount', $transfer, \PDO::PARAM_INT);
            $stmt->bindParam(':planet_id', $dplanet_id, \PDO::PARAM_INT);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        }
    }
}
