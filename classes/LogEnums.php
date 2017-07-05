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
// File: LogEnums.php

namespace Tki;

// Log constants
class LogEnums
{
    const LOGIN = 1;                    // Sent when logging in
    const LOGOUT = 2;                   // Sent when logging out
    const ATTACK_OUTMAN = 3;            // Sent to target when better engines
    const ATTACK_OUTSCAN = 4;           // Sent to target when better cloak
    const ATTACK_EWD = 5;               // Sent to target when EWD engaged
    const ATTACK_EWDFAIL = 6;           // Sent to target when EWD failed
    const ATTACK_LOSE = 7;              // Sent to target when he lost
    const ATTACKED_WIN = 8;             // Sent to target when he won
    const TOLL_PAID = 9;                // Sent when paid a toll
    const HIT_MINES = 10;               // Sent when hit mines
    const SHIP_DESTROYED_MINES = 11;    // Sent when destroyed by mines
    const PLANET_DEFEATED_D = 12;       // Sent when one of your defeated planets is destroyed instead of captured
    const PLANET_DEFEATED = 13;         // Sent when a planet is defeated
    const PLANET_NOT_DEFEATED = 14;     // Sent when a planet survives
    const RAW = 15;                     // This log is sent as-is
    const TOLL_RECV = 16;               // Sent when you receive toll money
    const DEFS_DESTROYED = 17;          // Sent for destroyed sector defenses
    const PLANET_EJECT = 18;            // Sent when ejected from a planet due to team switch
    const BADLOGIN = 19;                // Sent when bad login
    const PLANET_SCAN = 20;             // Sent when a planet has been scanned
    const PLANET_SCAN_FAIL = 21;        // Sent when a planet scan failed
    const PLANET_CAPTURE = 22;          // Sent when a planet is captured
    const SHIP_SCAN = 23;               // Sent when a ship is scanned
    const SHIP_SCAN_FAIL = 24;          // Sent when a ship scan fails
    const KABAL_ATTACK = 25;            // Kabal send this to themselves
    const STARVATION = 26;              // Sent when colonists are starving... Is this actually used in the game?
    const TOW = 27;                     // Sent when a player is towed
    const DEFS_DESTROYED_F = 28;        // Sent when a player destroys fighters
    const DEFS_KABOOM = 29;             // Sent when sector fighters destroy you
    const HARAKIRI = 30;                // Sent when self-destructed
    const TEAM_REJECT = 31;             // Sent when player refuses invitation
    const TEAM_RENAME = 32;             // Sent when renaming a team
    const TEAM_M_RENAME = 33;           // Sent to members on team rename
    const TEAM_KICK = 34;               // Sent to booted player
    const TEAM_CREATE = 35;             // Sent when created a team
    const TEAM_LEAVE = 36;              // Sent when leaving a team
    const TEAM_NEWLEAD = 37;            // Sent when leaving a team, appointing a new leader
    const TEAM_LEAD = 38;               // Sent to the new team leader
    const TEAM_JOIN = 39;               // Sent when joining a team
    const TEAM_NEWMEMBER = 40;          // Sent to leader on join
    const TEAM_INVITE = 41;             // Sent to invited player
    const TEAM_NOT_LEAVE = 42;          // Sent to leader on leave
    const ADMIN_HARAKIRI = 43;          // Sent to admin on self-destruct
    const ADMIN_PLANETDEL = 44;         // Sent to admin on planet destruction instead of capture
    const DEFENSE_DEGRADE = 45;         // Sent sector fighters have no supporting planet
    const PLANET_CAPTURED = 46;         // Sent to player when he captures a planet
    const BOUNTY_CLAIMED = 47;          // Sent to player when they claim a bounty
    const BOUNTY_PAID = 48;             // Sent to player when their bounty on someone is paid
    const BOUNTY_CANCELLED = 49;        // Sent to player when their bounty is refunded
    const SPACE_PLAGUE = 50;            // Sent when space plague attacks a planet
    const PLASMA_STORM = 51;            // Sent when a plasma storm attacks a planet
    const BOUNTY_FEDBOUNTY = 52;        // Sent when the federation places a bounty on a player
    const PLANET_BOMBED = 53;           // Sent after bombing a planet
    const ADMIN_ILLEGVALUE = 54;        // Sent to admin on planet destruction instead of capture
    const ATTACK_DEBUG = 56;            // Log attack debug information
    const ATTACK_WIN = 57;              // Missing log constant - who knew?!
}
