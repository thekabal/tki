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
// File: classes/Ports.php

namespace Tki;

class Ports
{
    public static function getType(string $ptype, array $langvars) : string
    {
        switch ($ptype)
        {
            case 'ore':
                return $langvars['l_ore'];
            case 'none':
                return $langvars['l_none'];
            case 'energy':
                return $langvars['l_energy'];
            case 'organics':
                return $langvars['l_organics'];
            case 'goods':
                return $langvars['l_goods'];
            case 'special':
                return $langvars['l_special'];
            default:
                return 'unknown';
        }
    }

    public static function dropdown(string $element_name, int $current_value, string $onchange, int $temp_devices) : string
    {
        $counter = $current_value;
        $dropdownvar = "<select size='1' name='$element_name'";
        $dropdownvar = "$dropdownvar $onchange>\n";
        while ($counter <= $temp_devices)
        {
            if ($current_value == $counter)
            {
                $dropdownvar = "$dropdownvar        <option value='$counter' selected>$counter</option>\n";
            }
            else
            {
                $dropdownvar = "$dropdownvar        <option value='$counter'>$counter</option>\n";
            }

            $counter++;
        }

        $dropdownvar = "$dropdownvar       </select>\n";

        return $dropdownvar;
    }

    public static function buildOneCol(string $text = "&nbsp;", string $align = "left"): void
    {
        echo "
        <tr>
          <td colspan=99 align=" . $align . ">" . $text . ".</td>
        </tr>
        ";
    }

    public static function buildTwoCol(
        string $text_col1 = "&nbsp;",
        string $text_col2 = "&nbsp;",
        string $align_col1 = "left",
        string $align_col2 = "left"
    ): void
    {
        echo "
        <tr>
          <td align=" . $align_col1 . ">" . $text_col1 . "</td>
          <td align=" . $align_col2 . ">" . $text_col2 . "</td>
        </tr>";
    }

    public static function phpTrueDelta(int $futurevalue, int $shipvalue): int
    {
        $tempval = $futurevalue - $shipvalue;

        return $tempval;
    }

    public static function phpChangeDelta(int $desired_value, int $current_value, int $upgrade_cost): int
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

    // Here is the trade function to strip out some "spaghetti code".
    // The function saves about 60 lines of code, I hope it will be
    // easier to modify/add something in this part.

    public static function trade(
        int $price,
        int $delta,
        int $max,
        int $limit,
        float $factor,
        string $port_type,
        int $origin,
        array $price_array,
        array $sectorinfo
    ): int
    {
        if ($sectorinfo['port_type'] == $port_type)
        {
            $price_array[$port_type] = $price - $delta * $max / $limit * $factor;
        }
        else
        {
            $price_array[$port_type] = $price + $delta * $max / $limit * $factor;
            $origin = -$origin;
        }

        // Debug info
        // echo "$origin * $price_array[$port_type]=" . $origin * $price_array[$port_type] . "<br>";
        return $origin;
    }
}
