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
// File: classes/Timer.php

namespace Tki;

class Timer
{
    public float $t_start = 0.0;

    public float $t_stop = 0.0;

    public float $t_elapsed = 0.0;

    public function start(): void
    {
        $this->t_start = microtime(true);
    }

    public function stop(): void
    {
        $this->t_stop = microtime(true);
    }

    public function elapsed(): float
    {
        $this->t_elapsed = $this->t_stop - $this->t_start;
        $rounded = round($this->t_elapsed, 3); // Round it down to three significant digits
        return $rounded;
    }
}
