<?php
declare(strict_types=1);
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
// File: classes/PlayerLog.php

namespace Tki;

class PlayerLog
{
    public static function writeLog(\PDO $pdo_db, int $ship_id, $log_type, $data = null)
    {
        $data = addslashes($data);
        $stamp = date('Y-m-d H:i:s'); // Now (as seen by PHP)

        // Write log_entry to the player's log - identified by player's ship_id.
        if ($ship_id !== null && $log_type !== null)
        {
            $sql = "INSERT INTO {$pdo_db->prefix}logs (ship_id, type, time, data) " .
                   "VALUES (:ship_id, :type, :time, :data)";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':ship_id', $ship_id);
            $stmt->bindParam(':type', $log_type);
            $stmt->bindParam(':time', $stamp);
            $stmt->bindParam(':data', $data);
            $res = $stmt->execute();
            Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
        }
    }
}
