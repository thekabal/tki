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
// File: classes/CheckBan.php
//
// Returns a Boolean false when no account info or no ban found.
// Returns an array which contains the ban information when it has found something.
// Calling code needs to act on the returned information (boolean false or array of ban info).

namespace Tki;

class CheckBan
{
    public static function isBanned(\PDO $pdo_db, array $playerinfo)
    {
        // Check for IP Ban
        $sql = "SELECT * FROM {$pdo_db->prefix}bans WHERE (ban_type = :ban_type AND ban_mask = :ban_mask1) OR (ban_mask = :ban_mask2)";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindValue(':ban_type', IP_BAN);
        $stmt->bindParam(':ban_mask1', $playerinfo['ip_address']);
        $stmt->bindParam(':ban_mask2', $playerinfo['ip_address']);
        $stmt->execute();
        $ipban_count = $stmt->rowCount();
        $ipbans_res = $stmt->fetch();
        Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        if ($ipban_count > 0)
        {
            // Ok, we have a ban record matching the players current IP Address, so return the BanType.
            return (array) $ipbans_res->fields;
        }

        // Check for ID Watch, Ban, Lock, 24H Ban etc linked to the platyers ShipID.
        $sql = "SELECT * FROM {$pdo_db->prefix}bans WHERE ban_ship = :ban_ship";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ban_ship', $playerinfo['ship_id']);
        $stmt->execute();
        $idban_count = $stmt->rowCount();
        $idbans_res = $stmt->fetch();
        Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        if ($idban_count > 0)
        {
            // Now return the highest ban type (i.e. worst type of ban)
            $ban_type = array('ban_type' => 0);
            while (!$idbans_res->EOF)
            {
                if ($idbans_res->fields['ban_type'] > $ban_type['ban_type'])
                {
                    $ban_type = $idbans_res->fields;
                }
                $idbans_res->MoveNext();
            }

            return (array) $ban_type;
        }

        // Check for Multi Ban (IP, ID)
        $sql = "SELECT * FROM {$pdo_db->prefix}bans WHERE ban_type = :ban_type AND (ban_mask = :ban_mask1 OR ban_mask = :ban_mask2 OR ban_ship = :ban_ship)";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindValue(':ban_type', MULTI_BAN);
        $stmt->bindParam(':ban_mask1', $playerinfo['ip_address']);
        $stmt->bindParam(':ban_mask2', $_SERVER['REMOTE_ADDR']);
        $stmt->bindParam(':ban_ship', $playerinfo['ship_id']);
        $stmt->execute();
        $multiban_count = $stmt->rowCount();
        $multiban_res = $stmt->fetch();
        Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        if ($multiban_count > 0)
        {
            // Ok, we have a ban record matching the players current IP Address or their ShipID, so return the BanType.
            return (array) $multiban_res->fields;
        }

        // Well we got here, so we haven't found anything, so we return a Boolean false.
        return (boolean) false;
    }
}
