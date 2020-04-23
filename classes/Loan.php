<?php declare(strict_types = 1);
/**
 * classes/Loan.php from The Kabal Invasion.
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

class Loan
{
    public static function isPending(\PDO $pdo_db, Reg $tkireg): bool
    {
        // Get playerinfo from database
        $players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
        $playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

        $ibank_gateway = new Ibank\IbankGateway($pdo_db);
        $loan_and_time = $ibank_gateway->selectIbankLoanandTime($playerinfo['ship_id']);

        if ($loan_and_time['loan'] > 0)
        {
            $curtime = time();
            $difftime = ($curtime - $loan_and_time['time']) / 60;
            if ($difftime > $tkireg->ibank_lrate)
            {
                return true;
            }
        }

        return false;
    }
}
