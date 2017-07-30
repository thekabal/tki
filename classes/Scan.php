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
// File: classes/Scan.php

namespace Tki;

class Scan
{
    public static function success(int $level_scan, int $level_cloak): int
    {
        return (5 + $level_scan - $level_cloak) * 5;
    }

    public static function error(int $level_scan, int $level_cloak, int $scan_error_factor): float
    {
        $sc_error = (4 + $level_scan / 2 - $level_cloak / 2) * $scan_error_factor;

        if ($sc_error < 1)
        {
            $sc_error = 1;
        }

        if ($sc_error > 99)
        {
            $sc_error = 99;
        }

        return $sc_error;
    }
}
