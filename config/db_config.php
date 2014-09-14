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
// File: db_config.php

if (strpos($_SERVER['PHP_SELF'], 'db_config.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

// The ADOdb db module is currently required to run TKI. You can find it at http://php.weblogs.com/ADODB.
// Adodb is automatically configured to be run from vendor/adodb.
// We are migrating away from adodb, switching to pure PDO instead.

// Port to connect to database on. Note : if you do not know the port, set this to '' for default. Ex, MySQL default is 3306
$db_port = null;

// Hostname of the database server:
$db_host = '127.0.0.1';

// Username and password to connect to the database:
$db_user = 'tki';
$db_pwd = 'tki';

// Name of the SQL database:
$db_name = 'tki';

// Type of the SQL database.
// "mysqli" for MySQLi - needed for transaction support or "postgres9" for PostgreSQL ver 9 and up
// NOTE: only mysqli works as of this release.
// $db_type = 'postgres9';
$db_type = 'mysqli';

// Table prefix for the database. If you want to run more than
// one game of TKI on the same database, or if the current table
// names conflict with tables you already have in your db, you will
// need to change this
$db_prefix = 'tki_';

