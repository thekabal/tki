<?php declare(strict_types = 1);
/**
 * classes/TkiPDO.php from The Kabal Invasion.
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

// PDO extended to enable prefixes on existing tables - replaces ::prefix:: with table prefix

namespace Tki;

class TkiPDO extends \PDO
{
    private ?string $tablePrefix;

    public function __construct(
        string $dsn,
        ?string $user = null,
        ?string $password = null,
        ?string $prefix = null,
        array $driver_options = [ \PDO::ATTR_EMULATE_PREPARES => false ]
    )
    {
        $this->tablePrefix = $prefix;
        parent::__construct($dsn, $user, $password, $driver_options);
        parent::setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
    }

    public function exec(string $query): int | false
    {
        $query = $this->tablePrefix($query);
        $rows_affected = parent::exec($query);
        return $rows_affected;
    }

    public function prepare(string $statement, array $options = array()): \PDOStatement | false
    {
        $statement = $this->tablePrefix($statement);
        $replaced_statement = parent::prepare($statement, $options);
        if ($this->errorCode() !== '00000')
        {
            return false;
        }

        return $replaced_statement;
    }

    public function query(string $statement, ?int $fetchMode = null, mixed ...$fetchModeArgs): \PDOStatement | false
    {
        $statement = $this->tablePrefix($statement);
        $args = func_get_args();
        if (count($args) > 1)
        {
            $replaced_statement = call_user_func('PDO::query', $args);
        }
        else
        {
            $replaced_statement = parent::query($statement);
        }

        if ($this->errorCode() !== '00000')
        {
            return false;
        }

        return $replaced_statement;
    }

    protected function tablePrefix(string $statement): string
    {
        $stmt_with_prefix = str_replace('::prefix::', (string) $this->tablePrefix, $statement);
        return $stmt_with_prefix;
    }
}
