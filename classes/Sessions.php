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
// File: classes/Sessions.php

namespace Tki;

class Sessions
{
    /** @var int */
    private $maxlifetime = 1800; // 30 mins

    /** @var \PDO|null */
    private $pdo_db = null;

    /** @var string|null */
    private $expiry = null;

    /** @var string|null */
    private $currenttime = null;

    public function __construct(\PDO $pdo_db)
    {
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'garbageCollection')
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
        session_register_shutdown();
    }

    public function __destruct()
    {
        session_write_close();
    }

    public function open(): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $sesskey): string
    {
        if ($this->pdo_db !== null)
        {
            $qry = "SELECT sessdata FROM ::prefix::sessions where sesskey=:sesskey and expiry>=:expiry";
            $stmt = $this->pdo_db->prepare($qry);
            $stmt->bindParam(':sesskey', $sesskey, \PDO::PARAM_STR);
            $stmt->bindParam(':expiry', $this->currenttime, \PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            // PHP7 change requires return to be string:
            // https://github.com/Inchoo/Inchoo_PHP7/issues/4#issuecomment-165618172
            return (string) $result['sessdata'];
        }
        else
        {
            return '';
        }
    }

    /** @return boolean */
    public function write(string $sesskey, string $sessdata)
    {
        if (($this->pdo_db !== null) && (Db::isActive($this->pdo_db)))
        {
            $err_mode = $this->pdo_db->getAttribute(\PDO::ATTR_ERRMODE);
            // Set the error mode to be exceptions,
            // so that we can catch them -- This breaks everything in game except for sessions
            $this->pdo_db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            try
            {
                // Try to insert the record. This will fail if the record already exists,
                // which will trigger catch below..
                $qry = "INSERT into ::prefix::sessions (" .
                       "sesskey, sessdata, expiry) values (:sesskey, :sessdata, :expiry)";
                $stmt = $this->pdo_db->prepare($qry);
                $stmt->bindParam(':sesskey', $sesskey, \PDO::PARAM_STR);
                $stmt->bindParam(':sessdata', $sessdata, \PDO::PARAM_STR);
                $stmt->bindParam(':expiry', $this->expiry, \PDO::PARAM_STR);
                $result = $stmt->execute();
            }
            catch (\PDOException $e)
            {
                // Insert didn't work, use update instead
                $qry = "UPDATE ::prefix::sessions SET sessdata=:sessdata, expiry=:expiry " .
                       "where sesskey=:sesskey";
                $stmt = $this->pdo_db->prepare($qry);
                $stmt->bindParam(':sesskey', $sesskey, \PDO::PARAM_STR);
                $stmt->bindParam(':sessdata', $sessdata, \PDO::PARAM_STR);
                $stmt->bindParam(':expiry', $this->expiry, \PDO::PARAM_STR);
                $result = $stmt->execute();
            }

            $this->pdo_db->setAttribute(\PDO::ATTR_ERRMODE, $err_mode);
            return $result;
        }
        else
        {
            // If you run create universe on an existing universe, at step 30,
            // you would get an error - this prevents it by returning false to
            // note that we didn't successfully write the session.
            return false;
            // The error was Session callback expects true/false return
            // value in Unknown on line 0, and is triggered because
            // the DB tables have been dropped in step 30 prior to the call.
        }
    }

    public function destroy(string $sesskey): bool
    {
        if ($this->pdo_db !== null)
        {
            $qry = "DELETE from ::prefix::sessions where sesskey=:sesskey";
            $stmt = $this->pdo_db->prepare($qry);
            $stmt->bindParam(':sesskey', $sesskey, \PDO::PARAM_STR);
            $result = $stmt->execute();
            return $result;
        }
        else
        {
            return false;
        }
    }

    public function garbageCollection(): bool
    {
        if ($this->pdo_db !== null)
        {
            $qry = "DELETE from ::prefix::sessions where expiry>:expiry";
            $stmt = $this->pdo_db->prepare($qry);
            $stmt->bindParam(':expiry', $this->expiry, \PDO::PARAM_STR);
            $result = $stmt->execute();
            return $result;
        }
        else
        {
            return false;
        }
    }

    public function regen(): void
    {
        if ($this->pdo_db !== null)
        {
            $old_id = session_id();
            session_regenerate_id();
            $new_id = session_id();
            $qry = "UPDATE ::prefix::sessions SET sesskey=:newkey where sesskey=:sesskey";
            $stmt = $this->pdo_db->prepare($qry);
            $stmt->bindParam(':newkey', $new_id, \PDO::PARAM_STR);
            $stmt->bindParam(':sesskey', $old_id, \PDO::PARAM_STR);
            $stmt->execute();
        }
    }
}
