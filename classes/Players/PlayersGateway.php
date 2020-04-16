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
// File: classes/Players/PlayersGateway.php

namespace Tki\Players; // Domain Entity organization pattern, Players objects

class PlayersGateway // Gateway for SQL calls related to Players
{
    /** @var \PDO */
    protected $pdo_db; // This will hold a protected version of the pdo_db variable

    public function __construct(\PDO $pdo_db) // Create the this->pdo_db object
    {
        $this->pdo_db = $pdo_db;
    }

    public function selectPlayersLoggedIn(string $since_stamp, string $cur_time_stamp): int
    {
        // SQL call that selected the number (count) of logged in ships (should be players)
        // where last login time is between the since_stamp, and the current timestamp ($cur_time_stamp)
        // But it excludes kabal.
        $sql = "SELECT COUNT(*) AS loggedin FROM ::prefix::ships " .
               "WHERE ::prefix::ships.last_login BETWEEN timestamp '"
               . $since_stamp . "' AND timestamp '" . $cur_time_stamp . "' AND email NOT LIKE '%@kabal'";
        $stmt = $this->pdo_db->query($sql); // Query the pdo DB using this SQL call
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__); // Log any errors, if there are any
        // Future: Correctly handle a false condition, which will not work for fetchObject

        $row = $stmt->fetchObject(); // Fetch the associated object from the select
        $online = $row->loggedin; // Set online variable to the loggedin count from SQL
        return (int) $online;
    }

    public function selectPlayerInfo(string $email): array
    {
        $sql = "SELECT * FROM ::prefix::ships WHERE email = :email LIMIT 1";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__); // Log any errors, if there are any

        // A little magic here. If it couldn't select a user, the following call will return false - which is what we want for "no user found".
        $playerinfo = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $playerinfo; // FUTURE: Eventually we want this to return a player object instead, for now, playerinfo array or false for no user found.
    }

    public function selectPlayerInfoById(?int $user_id): array
    {
        $sql = "SELECT * FROM ::prefix::ships WHERE ship_id = :user_id LIMIT 1";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_STR);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__); // Log any errors, if there are any

        // A little magic here. If it couldn't select a user, the following call will return false - which is what we want for "no user found".
        $playerinfo = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $playerinfo; // FUTURE: Eventually we want this to return a player object instead, for now, playerinfo array or false for no user found.
    }
}
