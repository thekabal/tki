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
// File: classes/Players/PlayersGateway.php

namespace Tki\Players; // Domain Entity organization pattern, Players objects

class PlayersGateway // Gateway for SQL calls related to Players
{
    protected $pdo_db; // This will hold a protected version of the pdo_db variable

    public function __construct(\PDO $pdo_db) // Create the this->pdo_db object
    {
        $this->pdo_db = $pdo_db;
    }

    /**
     * @param string $since_stamp
     * @param string $stamp
     */
    public function selectPlayersLoggedIn($since_stamp, $stamp)
    {
        // SQL call that selected the number (count) of logged in ships (should be players)
        // where last login time is between the since_stamp, and the current timestamp ($stamp)
        // But it excludes xenobes.
        $sql = "SELECT COUNT(*) AS loggedin FROM {$this->pdo_db->prefix}ships " .
               "WHERE {$this->pdo_db->prefix}ships.last_login BETWEEN timestamp '"
               . $since_stamp . "' AND timestamp '" . $stamp . "' AND email NOT LIKE '%@xenobe'";
        $stmt = $this->pdo_db->query($sql); // Query the pdo DB using this SQL call
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__); // Log any errors, if there are any
        $row = $stmt->fetchObject(); // Fetch the associated object from the select
        $online = $row->loggedin; // Set online variable to the loggedin count from SQL
        return (int) $online;
    }

    public function selectPlayerInfo($email)
    {
//        $sql = "SELECT lang, ip_address, password, ship_destroyed, ship_id, email, dev_escapepod FROM {$this->pdo_db->prefix}ships WHERE email = :email";
        $sql = "SELECT * FROM {$this->pdo_db->prefix}ships WHERE email = :email";
        $stmt = $this->pdo_db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__); // Log any errors, if there are any

        // A little magic here. If it couldn't select a user, the following call will return false - which is what we want for "no user found".
        $playerinfo = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $playerinfo; // FUTURE: Eventually we want this to return a player object instead, for now, playerinfo array or false for no user found.
    }
}
