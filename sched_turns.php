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
// File: sched_turns.php

if (strpos($_SERVER['PHP_SELF'], 'sched_turns.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

echo "<strong>TURNS</strong><br><br>";
echo "Adding turns...";
$resa = $db->Execute("UPDATE {$db->prefix}ships SET turns = LEAST (turns + ($bntreg->turns_per_tick * $multiplier), $bntreg->max_turns) WHERE turns < $bntreg->max_turns");
//$resa = $db->Execute("UPDATE {$db->prefix}ships SET turns = LEAST (turns + (? * ?), ?) WHERE turns < ?", array($bntreg->turns_per_tick, $multiplier, $bntreg->max_turns, $bntreg->max_turns));
$debug = Bnt\Db::logDbErrors($db, $resa, __LINE__, __FILE__);
is_query_ok($db, $debug);
echo "<br>";
$multiplier = 0;
