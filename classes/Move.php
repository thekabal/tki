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
// File: classes/Move.php

namespace Tki;

class Move
{
    public static function calcFuelScooped(Array $playerinfo, int $distance, int $triptime, Reg $tkireg) : int
    {
        // Check if we have a fuel scoop
        if ($playerinfo['dev_fuelscoop'] == 'Y')
        {
            // We have a fuel scoop, now calculate the amount of energy scooped.
            $energyscooped = $distance * 100;
        }
        else
        {
            // Nope, the FuelScoop won't be installed until next Tuesday (Star Trek quote) :P
            $energyscooped = 0;
        }

        // Seems this will never happen ?
        if ($playerinfo['dev_fuelscoop'] == 'Y' && $energyscooped == 0 && $triptime == 1)
        {
            $energyscooped = 100;
        }

        // Calculate the free power for the ship.
        $free_power = CalcLevels::energy($playerinfo['power'], $tkireg) - $playerinfo['ship_energy'];
        if ($free_power < $energyscooped)
        {
            // Limit the energy scooped to the maximum free power available.
            $energyscooped = $free_power;
        }

        // Not too sure what this line is doing, may need to add debugging code.
        // Could be checking for a negitive scoop value.
        if ($energyscooped < 1)
        {
            $energyscooped = 0;
        }

        return (int) $energyscooped;
    }
}
