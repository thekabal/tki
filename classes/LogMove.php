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
// File: classes/LogMove.php

namespace Tki;

class LogMove
{
    public static function writeLog(\PDO $pdo_db, int $ship_id, int $sector_id): void
    {
        $stmt = $pdo_db->prepare("INSERT INTO ::prefix::movement_log " .
                                 "(ship_id, sector_id, time) VALUES (:ship_id, :sector_id, NOW())");
        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
        $stmt->bindParam(':sector_id', $sector_id, \PDO::PARAM_INT);
        $result = $stmt->execute();
        Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);
    }
}
