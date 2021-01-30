<?php declare(strict_types = 1);
/**
 * classes/Login.php from The Kabal Invasion.
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

class Login
{
    public function checkLogin(\PDO $pdo_db, string $lang, Reg $tkireg, Timer $tkitimer, Smarty $template): bool
    {
        // Database driven language entries
        $langvars = Translate::load($pdo_db, $lang, array('common', 'footer',
                                    'login', 'self_destruct', 'universal'));

        $game_closed = new Game();
        $playerinfo = Player::auth($pdo_db, $lang, $tkireg, $tkitimer, $template);

        if (empty($playerinfo))
        {
            return false;
        }

        // Establish timestamp for interval in checking bans
        $cur_time_stamp = date('Y-m-d H:i:s');
        $timestamp = array();
        $timestamp['now']  = (int) strtotime($cur_time_stamp);
        $timestamp['last'] = (int) strtotime($playerinfo['last_login']);

        if ($game_closed->isGameClosed($pdo_db, $tkireg, $tkitimer, $lang, $template))
        {
            return false;
        }

        if (Player::ban($pdo_db, $lang, $timestamp, $template, $playerinfo, $tkireg, $tkitimer))
        {
            return false;
        }

        $is_ship_destroyed = !\Tki\Ship::isDestroyed($pdo_db, $lang, $tkireg, $tkitimer, $template, $playerinfo);
        return $is_ship_destroyed;
    }
}
