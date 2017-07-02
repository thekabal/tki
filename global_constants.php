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
// File: global_constants.php

// Log constants
const LOG_LOGIN = 1;                    // Sent when logging in
const LOG_LOGOUT = 2;                   // Sent when logging out
const LOG_ATTACK_OUTMAN = 3;            // Sent to target when better engines
const LOG_ATTACK_OUTSCAN = 4;           // Sent to target when better cloak
const LOG_ATTACK_EWD = 5;               // Sent to target when EWD engaged
const LOG_ATTACK_EWDFAIL = 6;           // Sent to target when EWD failed
const LOG_ATTACK_LOSE = 7;              // Sent to target when he lost
const LOG_ATTACKED_WIN = 8;             // Sent to target when he won
const LOG_TOLL_PAID = 9;                // Sent when paid a toll
const LOG_HIT_MINES = 10;               // Sent when hit mines
const LOG_SHIP_DESTROYED_MINES = 11;    // Sent when destroyed by mines
const LOG_PLANET_DEFEATED_D = 12;       // Sent when one of your defeated planets is destroyed instead of captured
const LOG_PLANET_DEFEATED = 13;         // Sent when a planet is defeated
const LOG_PLANET_NOT_DEFEATED = 14;     // Sent when a planet survives
const LOG_RAW = 15;                     // This log is sent as-is
const LOG_TOLL_RECV = 16;               // Sent when you receive toll money
const LOG_DEFS_DESTROYED = 17;          // Sent for destroyed sector defenses
const LOG_PLANET_EJECT = 18;            // Sent when ejected from a planet due to team switch
const LOG_BADLOGIN = 19;                // Sent when bad login
const LOG_PLANET_SCAN = 20;             // Sent when a planet has been scanned
const LOG_PLANET_SCAN_FAIL = 21;        // Sent when a planet scan failed
const LOG_PLANET_CAPTURE = 22;          // Sent when a planet is captured
const LOG_SHIP_SCAN = 23;               // Sent when a ship is scanned
const LOG_SHIP_SCAN_FAIL = 24;          // Sent when a ship scan fails
const LOG_KABAL_ATTACK = 25;            // Kabal send this to themselves
const LOG_STARVATION = 26;              // Sent when colonists are starving... Is this actually used in the game?
const LOG_TOW = 27;                     // Sent when a player is towed
const LOG_DEFS_DESTROYED_F = 28;        // Sent when a player destroys fighters
const LOG_DEFS_KABOOM = 29;             // Sent when sector fighters destroy you
const LOG_HARAKIRI = 30;                // Sent when self-destructed
const LOG_TEAM_REJECT = 31;             // Sent when player refuses invitation
const LOG_TEAM_RENAME = 32;             // Sent when renaming a team
const LOG_TEAM_M_RENAME = 33;           // Sent to members on team rename
const LOG_TEAM_KICK = 34;               // Sent to booted player
const LOG_TEAM_CREATE = 35;             // Sent when created a team
const LOG_TEAM_LEAVE = 36;              // Sent when leaving a team
const LOG_TEAM_NEWLEAD = 37;            // Sent when leaving a team, appointing a new leader
const LOG_TEAM_LEAD = 38;               // Sent to the new team leader
const LOG_TEAM_JOIN = 39;               // Sent when joining a team
const LOG_TEAM_NEWMEMBER = 40;          // Sent to leader on join
const LOG_TEAM_INVITE = 41;             // Sent to invited player
const LOG_TEAM_NOT_LEAVE = 42;          // Sent to leader on leave
const LOG_ADMIN_HARAKIRI = 43;          // Sent to admin on self-destruct
const LOG_ADMIN_PLANETDEL = 44;         // Sent to admin on planet destruction instead of capture
const LOG_DEFENSE_DEGRADE = 45;         // Sent sector fighters have no supporting planet
const LOG_PLANET_CAPTURED = 46;         // Sent to player when he captures a planet
const LOG_BOUNTY_CLAIMED = 47;          // Sent to player when they claim a bounty
const LOG_BOUNTY_PAID = 48;             // Sent to player when their bounty on someone is paid
const LOG_BOUNTY_CANCELLED = 49;        // Sent to player when their bounty is refunded
const LOG_SPACE_PLAGUE = 50;            // Sent when space plague attacks a planet
const LOG_PLASMA_STORM = 51;            // Sent when a plasma storm attacks a planet
const LOG_BOUNTY_FEDBOUNTY = 52;        // Sent when the federation places a bounty on a player
const LOG_PLANET_BOMBED = 53;           // Sent after bombing a planet
const LOG_ADMIN_ILLEGVALUE = 54;        // Sent to admin on planet destruction instead of capture
const LOG_ATTACK_DEBUG = 56;            // Log attack debug information
const LOG_MULTI_BROWSER = 57;           // Sent when we have detected a multi-browser hack attempt

// Ban system defines
const ID_WATCH = 0x00;                  // Player flagged as being watched.
const ID_LOCKED = 0x01;                 // Player flagged as being Locked.
const IP_BAN = 0x04;                    // Player flagged as banned by IP Address.
const MULTI_BAN = 0x05;                 // Player flagged as banned by either IP or ShipID.

// Adodb specific defines
const ADODB_PERF_NO_RUN_SQL = 1;        // Do not allow SQL to be run from the performance monitor page
