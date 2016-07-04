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
// File: classes/Mines.php

namespace Tki;

class Mines
{
    public static function explode(\PDO $pdo_db, $sector, $num_mines)
    {
        $sql = "SELECT * FROM {$pdo_db->prefix}sector_defence WHERE sector_id=:sector_id AND defence_type ='M' ORDER BY QUANTITY ASC";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $sector);
        $stmt->execute();
        $defence_present = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($defence_present !== null)
        {
            foreach ($defence_present as $tmp_defence)
            {
                if ($num_mines > 0)
                {
                    // Put the defence information into the array "defenceinfo"
                    if ($tmp_defence['quantity'] > $num_mines)
                    {
                        $sql = "UPDATE {$pdo_db->prefix}sector_defence SET quantity = quantity - :num_mines WHERE defence_id=:defence_id";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':num_mines', $num_mines);
                        $stmt->bindParam(':defence_id', $tmp_defence['defence_id']);
                        $stmt->execute();
                        $num_mines = 0;
                    }
                    else
                    {
                        $sql = "DELETE FROM {$pdo_db->prefix}sector_defence WHERE defence_id=:defence_id";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':defence_id', $tmp_defence['defence_id']);
                        $stmt->execute();
                        $num_mines -= $tmp_defence['quantity'];
                    }
                }
            }
        }
    }
}
