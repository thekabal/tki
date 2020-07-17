<?php declare(strict_types = 1);
/**
 * classes/Timer.php from The Kabal Invasion.
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

class Timer
{
    private float $t_start = 0.0;
    private float $t_stop = 0.0;
    private float $t_elapsed = 0.0;

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
        $rounded_elapsed_time = round($this->t_elapsed, 4); // Round it down to four significant digits - setting max_sector in CU is faster than 3
        return $rounded_elapsed_time;
    }
}
