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
// File: classes/Compress.php
//

namespace Bnt;

class Compress
{
    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    public static function compress($output)
    {

        // Check to see if we have data, if not, then return null
        if (is_null($output))
        {
            return null;
        }

        // Handle the supported compressions.
        $supported_enc = array();
        if (array_key_exists('HTTP_ACCEPT_ENCODING', $_SERVER))
        {
            $supported_enc = explode(',', $_SERVER['HTTP_ACCEPT_ENCODING']);
        }

        if (in_array('gzip', $supported_enc) === true)
        {
            header('Vary: Accept-Encoding');
            header('Content-Encoding: gzip');
            header('DEBUG: gzip found');

            return gzencode($output, 9);
        }
        elseif (in_array('deflate', $supported_enc) === true)
        {
            header('Vary: Accept-Encoding');
            header('Content-Encoding: deflate');
            header('DEBUG: deflate found');

            return gzdeflate($output, 9);
        }
        else
        {
            header('DEBUG: None found');

            return $output;
        }
    }
}
?>
