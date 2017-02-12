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
// File: classes/Loan.php

namespace Tki;

class Loan
{
    public static function isPending(\PDO $pdo_db, int $ship_id, Reg $tkireg): bool
    {
        $sql = "SELECT loan, UNIX_TIMESTAMP(loantime) AS time FROM ::prefix::ibank_accounts WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $ship_id);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
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
}
