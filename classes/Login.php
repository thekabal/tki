<?php
declare(strict_types=1);
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
// File: classes/Login.php

namespace Tki;

class Login
{
    public static function checkLogin(\PDO $pdo_db, $lang, Reg $tkireg, $template) : bool
    {
        // Database driven language entries
        $langvars = Translate::load($pdo_db, $lang, array('login', 'global_funcs', 'common', 'footer', 'self_destruct'));

        // Check if game is closed - Ignore the false return if it is open
        Game::isGameClosed($pdo_db, $tkireg, $lang, $template, $langvars);

        // Handle authentication check - Will die if fails, or return correct playerinfo
        $playerinfo = Player::handleAuth($pdo_db, $lang, $langvars, $tkireg, $template);

        // Establish timestamp for interval in checking bans
        $stamp = date('Y-m-d H:i:s');
        $timestamp = array();
        $timestamp['now']  = (int) strtotime($stamp);
        $timestamp['last'] = (int) strtotime($playerinfo['last_login']);

        // Check for ban - Ignore the false return if not
        Player::handleBan($pdo_db, $lang, $timestamp, $template, $playerinfo, $langvars, $tkireg);

        // Check for destroyed ship - Ignore the false return if not
        Ship::isDestroyed($pdo_db, $lang, $tkireg, $langvars, $template, $playerinfo);

        return true;
    }
}
