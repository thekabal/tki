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
// File: classes/CalcLevels.php

namespace Tki;

class CalcLevels
{
    public static function armor($level_armor, Reg $tkireg)
    {
        $result = round(pow($tkireg->level_factor, $level_armor) * 100);
        return $result;
    }

    public static function holds($level_hull, Reg $tkireg)
    {
        $result = round(pow($tkireg->level_factor, $level_hull) * 100);
        return $result;
    }

    public static function shields($level_shields, Reg $tkireg)
    {
        $result = round(pow($tkireg->level_factor, $level_shields) * 100);
        return $result;
    }

    public static function torpedoes($level_torp_launchers, Reg $tkireg)
    {
        $result = round(pow($tkireg->level_factor, $level_torp_launchers) * 100);
        return $result;
    }

    public static function beams($level_beams, Reg $tkireg)
    {
        $result = round(pow($tkireg->level_factor, $level_beams) * 100);
        return $result;
    }

    public static function fighters($level_computer, Reg $tkireg)
    {
        $result = round(pow($tkireg->level_factor, $level_computer) * 100);
        return $result;
    }

    public static function energy($level_power, Reg $tkireg)
    {
        $result = round(pow($tkireg->level_factor, $level_power) * 500);
        return $result;
    }

    public static function planetBeams(\PDO $pdo_db, $db, $ownerinfo, Reg $tkireg, $planetinfo)
    {
        $base_factor = ($planetinfo['base'] == 'Y') ? $tkireg->base_defense : 0;

        $planetbeams = self::beams($ownerinfo['beams'] + $base_factor, $tkireg->level_factor);
        $energy_available = $planetinfo['energy'];

        $res = $db->Execute("SELECT beams FROM {$db->prefix}ships WHERE planet_id = ? AND on_planet = 'Y';", array($planetinfo['planet_id']));
        Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
        if ($res instanceof \adodb\ADORecordSet)
        {
            while (!$res->EOF)
            {
                $planetbeams = $planetbeams + self::beams($res->fields['beams'], $tkireg->level_factor);
                $res->MoveNext();
            }
        }

        if ($planetbeams > $energy_available)
        {
            $planetbeams = $energy_available;
        }
        $planetinfo['energy'] -= $planetbeams;

        return $planetbeams;
    }

    public static function planetShields(\PDO $pdo_db, $db, $ownerinfo, Reg $tkireg, $planetinfo)
    {
        $base_factor = ($planetinfo['base'] == 'Y') ? $tkireg->base_defense : 0;
        $planetshields = self::shields($ownerinfo['shields'] + $base_factor, $tkireg->level_factor);
        $energy_available = $planetinfo['energy'];

        $res = $db->Execute("SELECT shields FROM {$db->prefix}ships WHERE planet_id = ? AND on_planet = 'Y';", array($planetinfo['planet_id']));
        Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);

        if ($res instanceof \adodb\ADORecordSet)
        {
            while (!$res->EOF)
            {
                $planetshields += self::shields($res->fields['shields'], $tkireg->level_factor);
                $res->MoveNext();
            }
        }

        if ($planetshields > $energy_available)
        {
            $planetshields = $energy_available;
        }
        $planetinfo['energy'] -= $planetshields;

        return $planetshields;
    }

    public static function planetTorps(\PDO $pdo_db, $db, $ownerinfo, $planetinfo, Reg $tkireg)
    {
        $base_factor = ($planetinfo['base'] == 'Y') ? $tkireg->base_defense : 0;
        $torp_launchers = round(pow($tkireg->level_factor, ($ownerinfo['torp_launchers']) + $base_factor)) * 10;
        $torps = $planetinfo['torps'];

        $res = $db->Execute("SELECT torp_launchers FROM {$db->prefix}ships WHERE planet_id = ? AND on_planet = 'Y';", array($planetinfo['planet_id']));
        Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
        if ($res instanceof \adodb\ADORecordSet)
        {
            while (!$res->EOF)
            {
                $ship_torps =  round(pow($tkireg->level_factor, $res->fields['torp_launchers'])) * 10;
                $torp_launchers = $torp_launchers + $ship_torps;
                $res->MoveNext();
            }
        }

        if ($torp_launchers > $torps)
        {
            $planettorps = $torps;
        }
        else
        {
            $planettorps = $torp_launchers;
        }

        $planetinfo['torps'] -= $planettorps;

        return (int) $planettorps;
    }

    public static function avgTech($ship_info = null, $type = 'ship')
    {
        // Used to define what devices are used to calculate the average tech level.
        $calc_ship_tech    = array('hull', 'engines', 'computer', 'armor', 'shields', 'beams', 'torp_launchers');
        $calc_planet_tech  = array('hull', 'engines', 'computer', 'armor', 'shields', 'beams', 'torp_launchers');

        if ($type == 'ship')
        {
            $calc_tech = $calc_ship_tech;
        }
        else
        {
            $calc_tech = $calc_planet_tech;
        }

        $count = count($calc_tech);

        $shipavg = 0;
        for ($i = 0; $i < $count; $i++)
        {
            $shipavg += $ship_info[$calc_tech[$i]];
        }
        $shipavg /= $count;

        return $shipavg;
    }
}
