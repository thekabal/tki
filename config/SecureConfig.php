<?php declare(strict_types = 1);
/**
 * config/SecureConfig.php from The Kabal Invasion.
 * The Kabal Invasion is a Free & Opensource (FOSS), web-based 4X space/strategy game.
 *
 * @copyright 2020 The Kabal Invasion development team, Ron Harwood, and the BNT development team
 *
 * @license GNU AGPL version 3.0 or (at your option) any later version.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

// Class for managing the secure settings inside TKI that are accessed across the entire codebase.

namespace Tki;

class SecureConfig
{
    // NOTES: The Adodb db module is currently required to run TKI.
    // Adodb is automatically configured to be run from vendor/adodb.
    // We are migrating away from adodb, switching to pure PDO instead.

    // Port to connect to database on.
    // If you do not know the port, set this to null for default.
    // The default for MySQL is 3306, The default for PgSQL is 5432
    public const DB_PORT = null;

    // Hostname of the database server.
    public const DB_HOST = '127.0.0.1';

    // Username connect to the database.
    public const DB_USER = 'tki';

    // Password to connect to the database.
    public const DB_PASS = 'tki';

    // Name of the SQL database.
    public const DB_NAME = 'tki';

    // Type of the SQL database.
    // "mysqli" -  required for transaction support.
    // "postgres9" - Version 9+.
    // Only mysqli works as of this release.
    public const DB_TYPE = 'mysqli';

     // Table prefix for the database.
     // If you want to run more than one game of TKI on the same database, or if the current table names
     // conflict with tables you already have in your db, you will need to change this.
    public const DB_TABLE_PREFIX = 'tki_';

    //Define the admin password, used for accessing "create_universe", "scheduler", and the admin control panel.
    public const ADMIN_PASS = 'secret';
}
