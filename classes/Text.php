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
// File: classes/Text.php

namespace Tki;

class Text
{
    public static function gotoMain(\PDO $pdo_db, $lang)
    {
        $langvars = Translate::load($pdo_db, $lang, array('global_funcs', 'common'));
        echo str_replace('[here]', "<a href='main.php'>" . $langvars['l_here'] . '</a>', $langvars['l_global_mmenu']);
    }
}
