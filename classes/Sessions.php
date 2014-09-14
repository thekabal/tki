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
// File: classes/Sessions.php

namespace Tki;

class Sessions
{
    public $maxlifetime = 1800; // 30 mins

    public function __construct($pdo_db)
    {
        session_set_save_handler(
        array($this, 'open'),
        array($this, 'close'),
        array($this, 'read'),
        array($this, 'write'),
        array($this, 'destroy'),
        array($this, 'gc')
        );

        // Set the database variable for this class
        $this->pdo_db = $pdo_db;

        // Select the current time from the database, NOT PHP
        $stmt = $this->pdo_db->prepare('SELECT now() as currenttime');
        $stmt->execute();
        $row = $stmt->fetch();

        // Set the current time for comparison to sessions to be the current database time
        $this->currenttime = $row['currenttime'];

        // Set the expiry time for sessions to be the current database time plus the maxlifetime set at top of class
        $this->expiry = gmdate('Y-m-d H:i:s', strtotime($row['currenttime']) + $this->maxlifetime);

        // This line prevents unexpected effects when using objects as save handlers.
        register_shutdown_function('session_write_close');
    }

    public function __destruct()
    {
        session_write_close();
    }

    public function open($path, $name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($sesskey)
    {
        $table = $this->pdo_db->prefix . 'sessions';
        $qry = 'SELECT sessdata FROM ' . $table . ' where sesskey=:sesskey and expiry>=:expiry';
        $stmt = $this->pdo_db->prepare($qry);
        $stmt->bindParam(':sesskey', $sesskey);
        $stmt->bindParam(':expiry', $this->currenttime);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['sessdata'];
    }

    public function write($sesskey, $sessdata)
    {
        $err_mode = $this->pdo_db->getAttribute(\PDO::ATTR_ERRMODE);
        // Set the error mode to be exceptions, so that we can catch them -- This fucks everything in game except for sessions
        $this->pdo_db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $table = $this->pdo_db->prefix . 'sessions';
        try
        {
            // Try to insert the record. This will fail if the record already exists, which will trigger catch below..
            $qry = 'INSERT into ' . $table . ' (sesskey, sessdata, expiry) values (:sesskey, :sessdata, :expiry)';
            $stmt = $this->pdo_db->prepare($qry);
            $stmt->bindParam(':sesskey', $sesskey);
            $stmt->bindParam(':sessdata', $sessdata);
            $stmt->bindParam(':expiry', $this->expiry);
            $result = $stmt->execute();
        }
        catch (\PDOException $e)
        {
            // Insert didn't work, use update instead
            $qry = 'UPDATE ' . $table . ' SET sessdata=:sessdata, expiry=:expiry where sesskey=:sesskey';
            $stmt = $this->pdo_db->prepare($qry);
            $stmt->bindParam(':sesskey', $sesskey);
            $stmt->bindParam(':sessdata', $sessdata);
            $stmt->bindParam(':expiry', $this->expiry);
            $result = $stmt->execute();
        }
        $this->pdo_db->setAttribute(\PDO::ATTR_ERRMODE, $err_mode);
        return $result;
    }

    public function destroy($sesskey)
    {
        $table = $this->pdo_db->prefix . 'sessions';
        $qry = 'DELETE from ' . $table . ' where sesskey=:sesskey';
        $stmt = $this->pdo_db->prepare($qry);
        $stmt->bindParam(':sesskey', $sesskey);
        $result = $stmt->execute();
        return $result;
    }

    public function gc($maxlifetime)
    {
        $table = $this->pdo_db->prefix . 'sessions';
        $qry = 'DELETE from ' . $table . ' where expiry>:expiry';
        $stmt = $this->pdo_db->prepare($qry);
        $stmt->bindParam(':expiry', $this->expiry);
        $result = $stmt->execute();
        return $result;
    }

    public function regen()
    {
        $old_id = session_id();
        session_regenerate_id();
        $new_id = session_id();
        $table = $this->pdo_db->prefix . 'sessions';
        $qry = 'UPDATE ' . $table . ' SET sesskey=:newkey where sesskey=:sesskey';
        $stmt = $this->pdo_db->prepare($qry);
        $stmt->bindParam(':newkey', $new_id);
        $stmt->bindParam(':sesskey', $old_id);
        $result = $stmt->execute();
    }
}
