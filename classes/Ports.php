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
// File: classes/Ports.php

namespace Tki;

class Ports
{
    public static function getType($ptype, $langvars)
    {
        $ret = null;
        switch ($ptype)
        {
            case 'ore':
                $ret = $langvars['l_ore'];
                break;
            case 'none':
                $ret = $langvars['l_none'];
                break;
            case 'energy':
                $ret = $langvars['l_energy'];
                break;
            case 'organics':
                $ret = $langvars['l_organics'];
                break;
            case 'goods':
                $ret = $langvars['l_goods'];
                break;
            case 'special':
                $ret = $langvars['l_special'];
                break;
        }
        return $ret;
    }

    public static function dropdown($element_name, $current_value, $onchange, $temp_devices)
    {
        $i = $current_value;
        $dropdownvar = "<select size='1' name='$element_name'";
        $dropdownvar = "$dropdownvar $onchange>\n";
        while ($i <= (int) $temp_devices)
        {
            if ($current_value == $i)
            {
                $dropdownvar = "$dropdownvar        <option value='$i' selected>$i</option>\n";
            }
            else
            {
                $dropdownvar = "$dropdownvar        <option value='$i'>$i</option>\n";
            }
            $i++;
        }

        $dropdownvar = "$dropdownvar       </select>\n";

        return $dropdownvar;
    }

    function build_one_col($text = "&nbsp;", $align = "left")
    {
        echo "
        <tr>
          <td colspan=99 align=" . $align . ">" . $text . ".</td>
        </tr>
        ";
    }

    function build_two_col($text_col1 = "&nbsp;", $text_col2 = "&nbsp;", $align_col1 = "left", $align_col2 = "left")
    {
        echo "
        <tr>
          <td align=" . $align_col1 . ">" . $text_col1 . "</td>
          <td align=" . $align_col2 . ">" . $text_col2 . "</td>
        </tr>";
    }

    function php_true_delta($futurevalue, $shipvalue)
    {
        $tempval = $futurevalue - $shipvalue;

        return $tempval;
    }

    function php_change_delta($desired_value, $current_value, $upgrade_cost)
    {
        $delta_cost = 0;
        $delta = $desired_value - $current_value;

        while ($delta > 0)
        {
            $delta_cost = $delta_cost + pow(2, $desired_value - $delta);
            $delta--;
        }

        $delta_cost = $delta_cost * $upgrade_cost;

        return $delta_cost;
    }

    // Here is the trade fonction to strip out some "spaghetti code". The function saves about 60 lines of code, I hope it will be
    // easier to modify/add something in this part.
    function trade($price, $delta, $max, $limit, $factor, $port_type, $origin, $price_array, $sectorinfo)
    {
        if ($sectorinfo['port_type'] ==  $port_type)
        {
            $price_array[$port_type] = $price - $delta * $max / $limit * $factor;
        }
        else
        {
            $price_array[$port_type] = $price + $delta * $max / $limit * $factor;
            $origin = -$origin;
        }

        // Debug info
        // echo "$origin * $price_array[$port_type]=";
        // echo $origin * $price_array[$port_type] . "<br>";
        return $origin;
    }
}
