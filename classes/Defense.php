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
// File: classes/Defense.php

namespace Tki;

class Defense
{
    public static function defenseVsDefense(\PDO $pdo_db, int $ship_id, Array $langvars)
    {
        $sql = "SELECT * FROM ::prefix::sector_defense WHERE ship_id=:ship_d";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $ship_id);
        $stmt->execute();
        $secdef_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($secdef_present !== null)
        {
            foreach ($secdef_present as $tmp_defense)
            {
                $deftype = $tmp_defense['defense_type'] == 'F' ? 'Fighters' : 'Mines';
                $qty = $tmp_defense['quantity'];

                $sql = "SELECT * FROM ::prefix::sector_defense WHERE sector_id=:sector_id AND ship_id<>:ship_d ORDER BY quantity DESC";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':sector_id', $tmp_defense['sector_id']);
                $stmt->bindParam(':ship_id', $ship_id);
                $stmt->execute();
                $other_secdef_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                if ($other_secdef_present !== null && $qty > 0)
                {
                    foreach ($other_secdef_present as $tmp_other_defense)
                    {
                        $targetdeftype = $tmp_other_defense['defense_type'] == 'F' ? $langvars['l_fighters'] : $langvars['l_mines'];
                        if ($qty > $tmp_other_defense['quantity'])
                        {
                            $sql = "DELETE FROM ::prefix::sector_defense WHERE defense_id = :defense_id";
                            $stmt = $pdo_db->prepare($sql);
                            $stmt->bindParam(':defense_id', $tmp_other_defense['sector_id']);
                            $stmt->execute();
                            $qty -= $tmp_other_defense['quantity'];

                            $sql = "UPDATE ::prefix::sector_defense SET quantity = :quantity_id WHERE defense_id = :defense_id";
                            $stmt = $pdo_db->prepare($sql);
                            $stmt->bindParam(':quantity_id', $qty);
                            $stmt->bindParam(':defense_id', $tmp_defense['sector_id']);
                            $stmt->execute();

                            PlayerLog::WriteLog($pdo_db, $tmp_other_defense['ship_id'], LOG_DEFS_DESTROYED, $tmp_other_defense['quantity'] .'|'. $targetdeftype .'|'. $tmp_defense['sector_id']);
                            PlayerLog::WriteLog($pdo_db, $tmp_defense['ship_id'], LOG_DEFS_DESTROYED, $tmp_other_defense['quantity'] .'|'. $deftype .'|'. $tmp_defense['sector_id']);
                        }
                        else
                        {
                            $sql = "DELETE FROM ::prefix::sector_defense WHERE defense_id = :defense_id";
                            $stmt = $pdo_db->prepare($sql);
                            $stmt->bindParam(':defense_id', $tmp_defense['defense_id']);
                            $stmt->execute();

                            $sql = "UPDATE ::prefix::sector_defense SET quantity = quantity - :quantity_id WHERE defense_id = :defense_id";
                            $stmt = $pdo_db->prepare($sql);
                            $stmt->bindParam(':quantity_id', $qty);
                            $stmt->bindParam(':defense_id', $tmp_other_defense['defense_id']);
                            $stmt->execute();

                            PlayerLog::WriteLog($pdo_db, $tmp_other_defense['ship_id'], LOG_DEFS_DESTROYED, $qty .'|'. $targetdeftype .'|'. $tmp_defense['sector_id']);
                            PlayerLog::WriteLog($pdo_db, $tmp_defense['ship_id'], LOG_DEFS_DESTROYED, $qty .'|'. $deftype .'|'. $tmp_defense['sector_id']);
                            $qty = 0;
                        }
                    }
                }
            }

            $sql = "DELETE FROM ::prefix::sector_defense WHERE quantity <= 0";
            $stmt = $pdo_db->prepare($sql);
            $stmt->execute();
        }
    }
}
