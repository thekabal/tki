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
    public static function ibankDeposit2(\PDO $pdo_db, string $lang, array $langvars, array $playerinfo, $amount, string $account, Reg $tkireg, Smarty $template): void
    {
        $max_credits_allowed = 18446744073709000000;

        $amount = preg_replace("/[^0-9]/", '', $amount);

        if (($amount * 1) != $amount)
        {
            \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_invaliddepositinput'], "ibank.php?command=deposit", $lang, $tkireg, $template);
        }

        if ($amount == 0)
        {
            \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_nozeroamount2'], "ibank.php?command=deposit", $lang, $tkireg, $template);
        }

        if ($amount > $playerinfo['credits'])
        {
            \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_notenoughcredits'], "ibank.php?command=deposit", $lang, $tkireg, $template);
        }

        $tmpcredits = $max_credits_allowed - $account['balance'];
        if ($tmpcredits < 0)
        {
            $tmpcredits = 0;
        }

        if ($amount > $tmpcredits)
        {
            \Tki\Ibank::ibankError($pdo_db, $langvars, "<center>Error You cannot deposit that much into your bank,<br> (Max Credits Reached)</center>", "ibank.php?command=deposit", $lang, $tkireg, $template);
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

    public static function ibankConsolidate2(\PDO $pdo_db, string $lang, array $langvars, array $playerinfo, Reg $tkireg, int $dplanet_id, int $minimum, int $maximum, Smarty $template): void
    {
        $sql = "SELECT name, credits, owner, sector_id FROM ::prefix::planets WHERE planet_id=:planet_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':planet_id', $dplanet_id);
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

        if ($dest['owner'] != $playerinfo['ship_id'])
        {
            \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnotyourplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
        }

        if ($minimum != 0)
        {
            $sql = "SELECT SUM(credits) as total, COUNT(*) AS count FROM ::prefix::planets WHERE owner = :owner_id AND credits <> 0 AND planet_id <> :planet_id AND credits >= :minimum";
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
            $sql = "UPDATE ::prefix::planets SET credits = 0 WHERE owner = :owner_id AND credits <> 0 AND planet_id <> :dplanet_id AND credxits <= :maximum";
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

    public static function ibankConsolidate3(\PDO $pdo_db, array $langvars, array $playerinfo, Reg $tkireg, int $dplanet_id, int $minimum, int $maximum, string $lang, Smarty $template): void
    {
        $sql = "SELECT name, credits, owner, sector_id FROM ::prefix::planets WHERE planet_id=:planet_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':planet_id', $dplanet_id);
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

        if ($dest['owner'] != $playerinfo['ship_id'])
        {
            \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnotyourplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
        }

        if ($minimum != 0)
        {
            $sql = "SELECT SUM(credits) as total, COUNT(*) AS count FROM ::prefix::planets WHERE owner = :owner_id AND credits <> 0 AND planet_id <> :planet_id AND credits >= :minimum";
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
            $sql = "SELECT SUM(credits) as total, COUNT(*) AS count FROM ::prefix::planets WHERE owner = :owner_id AND credits <> 0 AND planet_id <> :planet_id AND credits <= :maximum";
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
            \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_notenturns'], "ibank.php?command=transfer", $lang, $tkireg, $template);
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
            $sql = "UPDATE ::prefix::planets SET credits = 0 WHERE owner = :owner_id AND credits <> 0 AND planet_id <> :dplanet_id AND credits >= :minimum";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner_id', $playerinfo['ship_id']);
            $stmt->bindParam(':dplanet_id', $dplanet_id);
            $stmt->bindParam(':minimum', $minimum);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        }

        if ($maximum != 0)
        {
            $sql = "UPDATE ::prefix::planets SET credits = 0 WHERE owner = :owner_id AND credits <> 0 AND planet_id <> :dplanet_id AND credxits <= :maximum";
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
