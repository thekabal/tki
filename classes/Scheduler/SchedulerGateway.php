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
// File: classes/Scheduler/SchedulerGateway.php

namespace Tki\Scheduler; // Domain Entity organization pattern, Players objects

class SchedulerGateway // Gateway for SQL calls related to Players
{
    /** @var \PDO **/
    protected $pdo_db; // This will hold a protected version of the pdo_db variable

    public function __construct(\PDO $pdo_db) // Create the this->pdo_db object
    {
        $this->pdo_db = $pdo_db;
    }

    public function selectSchedulerLastRun(): ?int
    {
        // It is possible to have this call run before the game is setup, so we need to test to ensure the db is active
        if (\Tki\Db::isActive($this->pdo_db))
        {
            // SQL call that selects the last run of the scheduler, and only one record
            $sql = "SELECT last_run FROM ::prefix::scheduler LIMIT 1";
            $stmt = $this->pdo_db->query($sql); // Query the pdo DB using this SQL call

            // Future: Handle a bad return (aka false) as it is causing problems for the fetchObject call
            $row = $stmt->fetchObject();
            \Tki\Db::logDbErrors($this->pdo_db, $sql, __LINE__, __FILE__); // Log any errors, if there are any

            if (is_object($row) && (property_exists($row, 'last_run')))
            {
                return (int) $row->last_run; // Return the int value of the last scheduler run
            }
        }

        return null; // If anything goes wrong, db not active, etc, return null
    }
}
