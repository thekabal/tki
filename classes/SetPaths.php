<?php declare(strict_types = 1);
/**
 * classes/SetPaths.php from The Kabal Invasion.
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

// Auto detect and set the game path (uses the logic from setup_info)
// If it does not work, please comment this out and set it in config/SecureConfig.php instead.
// But PLEASE also report that it did not work for you at the github project page ()

namespace Tki;

use Symfony\Component\HttpFoundation\Request;

class SetPaths
{
    public static function setGamepath(): string
    {
        $request = Request::createFromGlobals();
        $gamepath = dirname($request->server->get('SCRIPT_NAME'));
        if (strlen($gamepath) > 0)
        {
            if ($gamepath === "\\")
            {
                $gamepath = '/';
            }

            if ($gamepath[0] != '.')
            {
                if ($gamepath[0] != '/')
                {
                    $gamepath = "/$gamepath";
                }

                if ($gamepath[strlen($gamepath) - 1] != '/')
                {
                    $gamepath = "$gamepath/";
                }
            }
            else
            {
                $gamepath = '/';
            }

            $gamepath = str_replace("\\", '/', stripcslashes($gamepath));
        }

        return $gamepath;
    }

    public static function setGamedomain(): string
    {
        $request = Request::createFromGlobals();
        $gamedomain = $request->server->get('HTTP_HOST');

        if ($gamedomain !== null && strlen($gamedomain) > 0)
        {
            $pos = (int) strpos($gamedomain, 'https://');
            $gamedomain = substr($gamedomain, $pos);

            $pos = (int) strpos($gamedomain, 'www.') + 4;
            $gamedomain = substr($gamedomain, $pos);

            $pos = (int) strpos($gamedomain, ':');
            $gamedomain = substr($gamedomain, 0, $pos);

            if ($gamedomain[0] != '.')
            {
                $gamedomain = ".$gamedomain";
            }
        }

        return $gamedomain;
    }
}
