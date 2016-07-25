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
// File: classes/AdminLog.php
// FUTURE: Recode adminlog to be smart about whether there is a db
// and if not, log to a file that will be slurped into the db when there is.

namespace Tki;

use PDO;

class AdminLog
{
    public static function writeLog(\PDO $pdo_db, int $log_type, $data = null)
    {
        $result = false;
        $query = "INSERT INTO {$pdo_db->prefix}logs VALUES (NULL, 0, :logtype, NOW(), :data)";
        $prep = $pdo_db->prepare($query);
        if ($prep !== false) // If the database is not live, this will return false
        {                      // so we should not attempt to write (or it will fail silently)
            $prep->bindParam(':logtype', $log_type, PDO::PARAM_STR);
            $prep->bindParam(':data', $data, PDO::PARAM_STR);
            $res = $prep->execute();
            Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
        }
        else
        {
            $result = false;
        }

        return $result;
    }
}
