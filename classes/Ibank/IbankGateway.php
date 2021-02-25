<?php declare(strict_types = 1);
/**
 * classes/Ibank/IbankGateway.php from The Kabal Invasion.
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

namespace Tki\Ibank; // Domain Entity organization pattern, Ibank objects

class IbankGateway // Gateway for SQL calls related to Ibank objects
{
    protected \PDO $pdo_db; // This will hold a protected version of the pdo_db variable

    public function __construct(\PDO $pdo_db) // Create the this->pdo_db object
    {
        $this->pdo_db = $pdo_db;
    }

    public function reduceIbankCredits(array $playerinfo, int $credits): void
    {
        $sql = "UPDATE ::prefix::ships SET credits = credits - :credits WHERE ship_id = :ship_id";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':credits', $credits, \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__);
    }

    public function selectIbankScore(int $ship_id): int
    {
        $sql = "SELECT (balance-loan) AS bank_score FROM ::prefix::ibank_accounts WHERE ship_id = :ship_id";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__);
        $bank_score = $stmt->fetch(\PDO::FETCH_COLUMN);
        return $bank_score;
    }

    public function selectIbankAccount(int $ship_id): array
    {
        $sql = "SELECT * FROM ::prefix::ibank_accounts WHERE ship_id = :ship_id LIMIT 1";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__);
        $ibank_account = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $ibank_account;
    }

    public function selectIbankLoanTime(int $ship_id): int
    {
        $sql = "SELECT UNIX_TIMESTAMP(loantime) as loan_time FROM ::prefix::ibank_accounts WHERE ship_id = :ship_id";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__);
        $loan_time = $stmt->fetch(\PDO::FETCH_COLUMN);
        return $loan_time;
    }

    public function selectIbankLoanandTime(int $ship_id): array
    {
        $sql = "SELECT loan, UNIX_TIMESTAMP(loantime) as loan_time FROM ::prefix::ibank_accounts WHERE ship_id = :ship_id";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__);
        $loan_and_time = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $loan_and_time;
    }
}
