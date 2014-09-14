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
// File: classes/eventsystem/event_list.php

// Used to hook into the ranking page, Event is called for every account.
define("EVENT_RANKING_PLAYERINFO", 0x00000001, true);

// Used to hook into when the player joins the game.
define("EVENT_PLAYER_JOIN", 0x00000002, true);

// Triggered on every page load.
define("EVENT_TICK", 0x00000003, true);

// Triggered on every time the Scheduler is run.
define("SCHEDULER_RUN", 0x00000004, true);

// Triggered on every create_universe.php page load.
define("EVENT_CREATE_UNIVERSE", 0x00000005, true);
?>
