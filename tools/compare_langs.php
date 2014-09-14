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
// File: compare_langs.php

require_once 'class_translate.php';

function slurp($language)
{
    // Slurp in language variables from the ini file directly
    $ini_file = '../languages/' . $language . '.ini.php';
    $ini_keys = parse_ini_file ($ini_file, true);
    foreach ($ini_keys as $config_category => $config_line)
    {
        foreach ($config_line as $config_key => $config_value)
        {
            $langvars[$config_key] = $config_value;
        }
    }

    return $langvars;
}

$first_language = 'english';
$second_language = 'french';

// Suck in english
$first = slurp ($first_language);

// Suck in another language
$second = slurp ($second_language);

// Compare array list
$diff = array_diff_key ($first, $second);

// Print out missing array items with clear specify of which has it
//var_dump ($diff);
//var_dump ($first);

$trans = new translate ($first['local_lang'], $second['local_lang']);

echo "<pre>";
while (list ($var, $val) = each ($diff))
{
//    echo $var . " = '" . $val . "'<br>";
//    echo $var . '(' . strlen($var) . ')' . ' = ';
    echo $var;
    $tablen = '30' - strlen ($var); // longest variable name is 30 characters
    $tablen = $tablen + '32'; // 8 tabs
    for ($i = 0; $i < $tablen; $i++)
    {
        echo "&nbsp;";
    }

    echo "<br>";

    $cursor = 0;
    $val_len = strlen ($val);
    $y = 0;

//    echo "Value: " . $val . "<br>";
    for ($x = 0; $x < $val_len; $x++)
    {
        $bracket_at = strpos ($val, '[', $cursor);
//        echo "Bracket at is " . $bracket_at . "<br>";
        if ($bracket_at === false)
        {
//            echo "X is " . $x . "<br>";
//            echo "cursor is " . $cursor . "<br>";
            if ($x > 0)
            {
//                echo "OH NOE!";
                $y++;
                $partial[$y] = substr ($val, $cursor);
                $x = $val_len;
            }
            else
            {
                $y++;
                $partial[$y] = $val;
                $x = $val_len;
            }
        }
        else
        {
            $bracket_end = strpos ($val, ']', $cursor);
            if ($bracket_at > 0)
            {
                $y++;
//                echo "Bracket end is " . $bracket_end . "<br>";
//                echo "Cursor is " . $cursor . "<br>";
                $partial[$y] = substr ($val, $cursor, ($bracket_at - $cursor));
                $bracket[$y] = substr ($val, $bracket_at, $bracket_end);
                $cursor = $bracket_end +1;
            }
            else
            {
                $partial[$y] = substr ($val, $cursor, $bracket_end);
                $bracket[$y] = '';
///                $partial[$y]['bracket'] = false;
            }

//            $cursor = $bracket_end +1;
        }
//        echo $y . ': "' . $partial[$y];
        echo $y . ': "' . $trans->get($partial[$y]);
        if ($y > 0)
        {
            echo $bracket[$y];
        }
        echo '"<br><br>';
    }

//    echo '= "' . $trans->get ($val) . '";<br>';
}
echo "</pre>";
