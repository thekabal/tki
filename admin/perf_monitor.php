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
// File: admin/perf_monitor.php

if (strpos($_SERVER['SCRIPT_NAME'], 'perf_monitor.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

//adodb_perf::table("{$db->prefix}adodb_logsql");
$perf = NewPerfMonitor($db);
$perf->UI($pollsecs=5);

echo '<style type="text/css"><!--  table { background-color: transparent; border:1px solid white}; --></style>';

echo $perf->HealthCheck();
echo $perf->SuspiciousSQL(10);
echo $perf->ExpensiveSQL(10);
echo $perf->InvalidSQL(10);
