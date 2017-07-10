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
// File: classes/Mines.php

namespace Tki;

class Mines
{
    public static function explode(\PDO $pdo_db, int $sector, int $num_mines): void
    {
        $sql = "SELECT * FROM ::prefix::sector_defense WHERE " .
               "sector_id=:sector_id AND defense_type ='M' ORDER BY QUANTITY ASC";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $sector, \PDO::PARAM_INT);
        $stmt->execute();
        $defense_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($defense_present !== null)
        {
            foreach ($defense_present as $tmp_defense)
            {
                if ($num_mines > 0)
                {
                    // Put the defense information into the array "defenseinfo"
                    if ($tmp_defense['quantity'] > $num_mines)
                    {
                        $sql = "UPDATE ::prefix::sector_defense SET " .
                               "quantity = quantity - :num_mines WHERE defense_id=:defense_id";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':num_mines', $num_mines, \PDO::PARAM_INT);
                        $stmt->bindParam(':defense_id', $tmp_defense['defense_id'], \PDO::PARAM_INT);
                        $stmt->execute();
                        $num_mines = 0;
                    }
                    else
                    {
                        $sql = "DELETE FROM ::prefix::sector_defense WHERE defense_id=:defense_id";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':defense_id', $tmp_defense['defense_id'], \PDO::PARAM_INT);
                        $stmt->execute();
                        $num_mines -= $tmp_defense['quantity'];
                    }
                }
            }
        }
    }
}
