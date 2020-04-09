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
// File: classes/BigBang.php

namespace Tki;

class BigBang
{
    public function findStep(string $current_file) : array
    {
        $num_files = 0;

        // Setup $bigbang_files as an array type.
        // We add a null value as index 0 due to we need to start from index 1.
        $bigbang_files = array(null);
        $bigbang_info = array();

        // Setup $filelist as an array.
        $filelist = array();

        $bigbang_dir = new \DirectoryIterator('create_universe/');
        foreach ($bigbang_dir as $file_info) // Get a list of the files in the bigbang directory
        {
            // If it is a PHP file, add it to the list of accepted make galaxy files
            if ($file_info->isFile() && $file_info->getExtension() == 'php')
            {
                $num_files++; // Increment a counter, so we know how many files there are to choose from
                $filelist[$num_files] = $file_info->getFilename(); // The actual file name
            }
        }

        // Now order the files in the correct order.
        natsort($filelist);

        // Now move files over to the $bigbang_files array creating the correct index key order.
        foreach ($filelist as $ofile)
        {
            array_push($bigbang_files, $ofile);
        }

        // Now remove the unwanted array.
        unset($filelist);

        $bigbang_info['steps'] = $num_files;
        if ($current_file === '')
        {
            // If current file is set to null string, just return the search from 0.
            $bigbang_info['current_step'] = array_search('0.php', $bigbang_files, true);
        }
        else
        {
            // Usual search, from the current step
            $bigbang_info['current_step'] = array_search(basename($current_file), $bigbang_files, true);
        }

        if (is_int($bigbang_info['current_step']))
        {
            if (($bigbang_info['current_step'] + 1) > $num_files)
            {
                $new_current_file = $num_files;
            }
            else
            {
                $new_current_file = $bigbang_info['current_step'] + 1;
            }
        }
        else
        {
            $new_current_file = '';
        }

        $bigbang_info['next_step'] = array_search($bigbang_files[$new_current_file], $bigbang_files, true);
        $bigbang_info['files'] = $bigbang_files;

        return $bigbang_info;
    }
}
