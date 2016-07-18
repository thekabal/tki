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
// File: classes/SetPaths.php
//
// Auto detect and set the game path (uses the logic from setup_info)
// If it does not work, please comment this out and set it in config/SecureConfig.php instead.
// But PLEASE also report that it did not work for you at the github project page ()

namespace Tki;

class SetPaths
{
    public static function setGamepath() : string
    {
        $gamepath = dirname($_SERVER['SCRIPT_NAME']);
        if ($gamepath !== null && mb_strlen($gamepath) > 0)
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

                if ($gamepath[mb_strlen($gamepath) - 1] != '/')
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

    public static function setGamedomain() : string
    {
        $remove_port = true;
        $gamedomain = $_SERVER['HTTP_HOST'];

        if ($gamedomain !== null && mb_strlen($gamedomain) > 0)
        {
            $pos = mb_strpos($gamedomain, 'https://');
            if (is_int($pos))
            {
                $gamedomain = mb_substr($gamedomain, $pos + 7);
            }

            $pos = mb_strpos($gamedomain, 'www.');
            if (is_int($pos))
            {
                $gamedomain = mb_substr($gamedomain, $pos + 4);
            }

            if ($remove_port)
            {
                $pos = mb_strpos($gamedomain, ':');
            }

            if (is_int($pos))
            {
                $gamedomain = mb_substr($gamedomain, 0, $pos);
            }

            if ($gamedomain[0] != '.')
            {
                $gamedomain = ".$gamedomain";
            }
        }

        return $gamedomain;
    }
}
