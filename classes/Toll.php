<?php
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
// File: classes/Toll.php

namespace Tki;

class Toll
{
    public static function distribute(\PDO $pdo_db, int $sector, $toll, $total_fighters)
    {
        $sql = "SELECT * FROM {$pdo_db->prefix}sector_defence WHERE sector_id=:sector_id AND defence_type='F'";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $sector);
        $stmt->execute();
        $defence_present = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($defence_present !== null)
        {
            foreach ($defence_present as $tmp_defence)
            {
                $toll_amount = round(($tmp_defence['quantity'] / $total_fighters) * $toll);
                $sql = "UPDATE {$pdo_db->prefix}ships SET credits=credits + :toll_amount WHERE ship_id = :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':toll_amount', $toll_amount);
                $stmt->bindParam(':ship_id', $tmp_defence['ship_id']);
                $stmt->execute();
                PlayerLog::WriteLog($pdo_db, $tmp_defence['ship_id'], LOG_TOLL_RECV, "$toll_amount|$sector");
            }
        }
    }
}
