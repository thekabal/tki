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
// File: classes/Bounty.php

namespace Tki;

class Bounty
{
    public static function cancel(\PDO $pdo_db, $bounty_on)
    {
        $sql = "SELECT * FROM {$pdo_db->prefix}bounty WHERE bounty_on=:bounty_on AND bounty_on=ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':bounty_on', $bounty_on);
        $stmt->execute();
        $bounty_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($bounty_present !== null)
        {
            foreach ($bounty_present as $tmp_bounty)
            {
                if ($tmp_bounty['placed_by'] != 0)
                {
                    $sql = "UPDATE {$pdo_db->prefix}ships SET credits=credits+:bounty_amount WHERE ship_id = :ship_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':bounty_amount', $tmp_bounty['amount']);
                    $stmt->bindParam(':ship_id', $tmp_bounty['placed_by']);
                    $stmt->execute();
                    PlayerLog::WriteLog($pdo_db, $tmp_bounty['placed_by'], LOG_BOUNTY_CANCELLED, "$tmp_bounty[amount]|$tmp_bounty[character_name]");
                }

                $sql = "DELETE FROM {$pdo_db->prefix}bounty WHERE bounty_id = :bounty_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':bounty_id', $tmp_bounty['bounty_id']);
                $stmt->execute();
            }
        }
    }

    public static function collect(\PDO $pdo_db, Array $langvars, $attacker, $bounty_on)
    {
        $sql = "SELECT * FROM {$pdo_db->prefix}bounty,{$pdo_db->prefix}ships WHERE bounty_on=:bounty_on AND bounty_on=ship_id AND planced_by <> 0";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':bounty_on', $bounty_on);
        $stmt->execute();
        $bounty_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($bounty_present !== null)
        {
            foreach ($bounty_present as $tmp_bounty)
            {
                if ($tmp_bounty['placed_by'] == 0)
                {
                    $placed = $langvars['l_by_thefeds'];
                }
                else
                {
                    $sql = "SELECT character_name FROM {$pdo_db->prefix}ships WHERE ship_id=:ship_id LIMIT 1";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':ship_id', $tmp_bounty['placed_by']);
                    $stmt->execute();
                    $placed = $stmt->fetch(\PDO::FETCH_ASSOC);
                }

                $sql = "UPDATE {$pdo_db->prefix}ships SET credits=credits+:bounty_amount WHERE ship_id = :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':bounty_amount', $tmp_bounty['amount']);
                $stmt->bindParam(':ship_id', $attacker);
                $stmt->execute();

                $sql = "DELETE FROM {$pdo_db->prefix}bounty WHERE bounty_id = :bounty_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':bounty_id', $tmp_bounty['bounty_id']);
                $stmt->execute();

                PlayerLog::WriteLog($pdo_db, $attacker, LOG_BOUNTY_CLAIMED, "$tmp_bounty[amount]|$tmp_bounty[character_name]|$placed");
                PlayerLog::WriteLog($pdo_db, $tmp_bounty['placed_by'], LOG_BOUNTY_PAID, "$tmp_bounty[amount]|$tmp_bounty[character_name]");
            }
        }

        $sql = "DELETE FROM {$pdo_db->prefix}bounty WHERE bounty_on = :bounty_on";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':bounty_on', $bounty_on);
        $stmt->execute();
    }
}
