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
// File: classes/IbankConsolidate.php

namespace Tki;

class IbankConsolidate
{
    public static function before(array $langvars, Reg $tkireg, int $dplanet_id): void
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

    public static function after(\PDO $pdo_db, string $lang, array $langvars, array $playerinfo, Reg $tkireg, int $dplanet_id, int $minimum, int $maximum, Smarty $template): void
    {
        $sql = "SELECT name, credits, owner, sector_id FROM ::prefix::planets WHERE planet_id=:planet_id";
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

        if ($dest['owner'] != $playerinfo['ship_id'])
        {
            \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnotyourplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
        }

        $amount = array();
        if ($minimum != 0)
        {
            $sql = "SELECT SUM(credits) as total, COUNT(*) AS count FROM ::prefix::planets WHERE owner = :owner_id AND credits <> 0 AND planet_id <> :planet_id AND credits >= :minimum";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':dplanet_id', $dplanet_id, \PDO::PARAM_INT);
            $stmt->bindParam(':minimum', $minimum, \PDO::PARAM_INT);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            $amount = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if ($maximum != 0)
        {
            $sql = "UPDATE ::prefix::planets SET credits = 0 WHERE owner = :owner_id AND credits <> 0 AND planet_id <> :dplanet_id AND credxits <= :maximum";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':dplanet_id', $dplanet_id, \PDO::PARAM_INT);
            $stmt->bindParam(':maximum', $maximum, \PDO::PARAM_INT);
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

    public static function third(\PDO $pdo_db, array $langvars, array $playerinfo, Reg $tkireg, int $dplanet_id, int $minimum, int $maximum, string $lang, Smarty $template): void
    {
        $sql = "SELECT name, credits, owner, sector_id FROM ::prefix::planets WHERE planet_id=:planet_id";
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

        if ($dest['owner'] != $playerinfo['ship_id'])
        {
            \Tki\Ibank::ibankError($pdo_db, $langvars, $langvars['l_ibank_errnotyourplanet'], "ibank.php?command=transfer", $lang, $tkireg, $template);
        }

        $amount = array();
        if ($minimum != 0)
        {
            $sql = "SELECT SUM(credits) as total, COUNT(*) AS count FROM ::prefix::planets WHERE owner = :owner_id AND credits <> 0 AND planet_id <> :planet_id AND credits >= :minimum";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':dplanet_id', $dplanet_id, \PDO::PARAM_INT);
            $stmt->bindParam(':minimum', $minimum, \PDO::PARAM_INT);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            $amount = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if ($maximum != 0)
        {
            $sql = "SELECT SUM(credits) as total, COUNT(*) AS count FROM ::prefix::planets WHERE owner = :owner_id AND credits <> 0 AND planet_id <> :planet_id AND credits <= :maximum";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':dplanet_id', $dplanet_id, \PDO::PARAM_INT);
            $stmt->bindParam(':maximum', $maximum, \PDO::PARAM_INT);
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
             "<td><a href='ibank.php?command=login'>" . $langvars['l_ibank_back'] .
             "</a></td><td align=right>&nbsp;<br><a href=\"main.php\">" . $langvars['l_ibank_logout '] . "</a></td>" .
             "</tr>";

        if ($minimum != 0)
        {
            $sql = "UPDATE ::prefix::planets SET credits = 0 " .
                   "WHERE owner = :owner_id AND credits <> 0 " .
                   "AND planet_id <> :dplanet_id AND credits >= :minimum";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':dplanet_id', $dplanet_id, \PDO::PARAM_INT);
            $stmt->bindParam(':minimum', $minimum, \PDO::PARAM_INT);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        }

        if ($maximum != 0)
        {
            $sql = "UPDATE ::prefix::planets SET credits = 0 " .
                   "WHERE owner = :owner_id AND credits <> 0 AND " .
                   "planet_id <> :dplanet_id AND credxits <= :maximum";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':dplanet_id', $dplanet_id, \PDO::PARAM_INT);
            $stmt->bindParam(':maximum', $maximum, \PDO::PARAM_INT);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        }

        $sql = "UPDATE ::prefix::planets SET credits = :credits WHERE planet_id = :planet_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':credits', $cplanet, \PDO::PARAM_INT);
        $stmt->bindParam(':planet_id', $dplanet_id, \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        $sql = "UPDATE ::prefix::ships SET turns = turns - :turns WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':turns', $tcost, \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    }
}
