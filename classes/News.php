<?php
declare(strict_types = 1);
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
// File: classes/News.php
//
// FUTURE: Add validity checking for the format of $day

namespace Tki;

class News
{
    public static function previousDay($day) : string
    {
        // Convert the formatted date into a timestamp
        $day = strtotime($day);

        // Subtract one day in seconds from the timestamp
        $day = $day - 86400;

        // Return the final version formatted as YYYY/MM/DD
        $date = date('Y/m/d', $day);
        return $date;
    }

    public static function nextDay($day) : string
    {
        // Convert the formatted date into a timestamp
        $day = strtotime($day);

        // Add one day in seconds to the timestamp
        $day = $day + 86400;

        // Return the final version formatted as YYYY/MM/DD
        $date = date('Y/m/d', $day);
        return $date;
    }
}
