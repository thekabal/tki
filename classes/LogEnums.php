<?php declare(strict_types = 1);
/**
 * classes/LogEnums.php from The Kabal Invasion.
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

namespace Tki;

// Log constants
class LogEnums
{
    public const LOGIN = 1;                    // Sent when logging in
    public const LOGOUT = 2;                   // Sent when logging out
    public const ATTACK_OUTMAN = 3;            // Sent to target when better engines
    public const ATTACK_OUTSCAN = 4;           // Sent to target when better cloak
    public const ATTACK_EWD = 5;               // Sent to target when EWD engaged
    public const ATTACK_EWDFAIL = 6;           // Sent to target when EWD failed
    public const ATTACK_LOSE = 7;              // Sent to target when he lost
    public const ATTACKED_WIN = 8;             // Sent to target when he won
    public const TOLL_PAID = 9;                // Sent when paid a toll
    public const HIT_MINES = 10;               // Sent when hit mines
    public const SHIP_DESTROYED_MINES = 11;    // Sent when destroyed by mines
    public const PLANET_DEFEATED_D = 12;       // Sent when one of your defeated planets is destroyed instead of captured
    public const PLANET_DEFEATED = 13;         // Sent when a planet is defeated
    public const PLANET_NOT_DEFEATED = 14;     // Sent when a planet survives
    public const RAW = 15;                     // This log is sent as-is
    public const TOLL_RECV = 16;               // Sent when you receive toll money
    public const DEFS_DESTROYED = 17;          // Sent for destroyed sector defenses
    public const PLANET_EJECT = 18;            // Sent when ejected from a planet due to team switch
    public const BADLOGIN = 19;                // Sent when bad login
    public const PLANET_SCAN = 20;             // Sent when a planet has been scanned
    public const PLANET_SCAN_FAIL = 21;        // Sent when a planet scan failed
    public const PLANET_CAPTURE = 22;          // Sent when a planet is captured
    public const SHIP_SCAN = 23;               // Sent when a ship is scanned
    public const SHIP_SCAN_FAIL = 24;          // Sent when a ship scan fails
    public const KABAL_ATTACK = 25;            // Kabal send this to themselves
    public const STARVATION = 26;              // Sent when colonists are starving... Is this actually used in the game?
    public const TOW = 27;                     // Sent when a player is towed
    public const DEFS_DESTROYED_F = 28;        // Sent when a player destroys fighters
    public const DEFS_KABOOM = 29;             // Sent when sector fighters destroy you
    public const HARAKIRI = 30;                // Sent when self-destructed
    public const TEAM_REJECT = 31;             // Sent when player refuses invitation
    public const TEAM_RENAME = 32;             // Sent when renaming a team
    public const TEAM_M_RENAME = 33;           // Sent to members on team rename
    public const TEAM_KICK = 34;               // Sent to booted player
    public const TEAM_CREATE = 35;             // Sent when created a team
    public const TEAM_LEAVE = 36;              // Sent when leaving a team
    public const TEAM_NEWLEAD = 37;            // Sent when leaving a team, appointing a new leader
    public const TEAM_LEAD = 38;               // Sent to the new team leader
    public const TEAM_JOIN = 39;               // Sent when joining a team
    public const TEAM_NEWMEMBER = 40;          // Sent to leader on join
    public const TEAM_INVITE = 41;             // Sent to invited player
    public const TEAM_NOT_LEAVE = 42;          // Sent to leader on leave
    public const ADMIN_HARAKIRI = 43;          // Sent to admin on self-destruct
    public const ADMIN_PLANETDEL = 44;         // Sent to admin on planet destruction instead of capture
    public const DEFENSE_DEGRADE = 45;         // Sent sector fighters have no supporting planet
    public const PLANET_CAPTURED = 46;         // Sent to player when he captures a planet
    public const BOUNTY_CLAIMED = 47;          // Sent to player when they claim a bounty
    public const BOUNTY_PAID = 48;             // Sent to player when their bounty on someone is paid
    public const BOUNTY_CANCELLED = 49;        // Sent to player when their bounty is refunded
    public const SPACE_PLAGUE = 50;            // Sent when space plague attacks a planet
    public const PLASMA_STORM = 51;            // Sent when a plasma storm attacks a planet
    public const BOUNTY_FEDBOUNTY = 52;        // Sent when the federation places a bounty on a player
    public const PLANET_BOMBED = 53;           // Sent after bombing a planet
    public const ADMIN_ILLEGVALUE = 54;        // Sent to admin on planet destruction instead of capture
    public const ATTACK_DEBUG = 56;            // Log attack debug information
    public const ATTACK_WIN = 57;              // Missing log constant - who knew?!
}
