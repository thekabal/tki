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
// File: classes/TraderouteDistance.php

namespace Tki;

class TraderouteDistance
{
    public static function calc(\PDO $pdo_db, string $type1, string $type2, $start, $dest, $circuit, array $playerinfo, Reg $tkireg, $sells = 'N'): array
    {
        $retvalue = array();
        $retvalue['triptime'] = 0;
        $retvalue['scooped1'] = 0;
        $retvalue['scooped2'] = 0;
        $retvalue['scooped'] = 0;

        if ($type1 == 'L')
        {
            $sql = "SELECT * FROM ::prefix::universe WHERE sector_id=:sector_id LIMIT 1";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $start, \PDO::PARAM_INT);
            $stmt->execute();
            $start = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if ($type2 == 'L')
        {
            $sql = "SELECT * FROM ::prefix::universe WHERE sector_id=:sector_id LIMIT 1";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':dest', $dest, \PDO::PARAM_INT);
            $stmt->execute();
            $dest = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if ($start['sector_id'] == $dest['sector_id'])
        {
            if ($circuit == '1')
            {
                $retvalue['triptime'] = '1';
            }
            else
            {
                $retvalue['triptime'] = '2';
            }

            return $retvalue;
        }

        $deg = pi() / 180;

        $sa1 = $start['angle1'] * $deg;
        $sa2 = $start['angle2'] * $deg;
        $fa1 = $dest['angle1'] * $deg;
        $fa2 = $dest['angle2'] * $deg;
        $pos_x = $start['distance'] * sin($sa1) * cos($sa2) - $dest['distance'] * sin($fa1) * cos($fa2);
        $pos_y = $start['distance'] * sin($sa1) * sin($sa2) - $dest['distance'] * sin($fa1) * sin($fa2);
        $pos_z = $start['distance'] * cos($sa1) - $dest['distance'] * cos($fa1);
        $distance = round(sqrt(pow($pos_x, 2) + pow($pos_y, 2) + pow($pos_z, 2)));
        $shipspeed = pow($tkireg->level_factor, $playerinfo['engines']);
        $triptime = round($distance / $shipspeed);

        if (!$triptime && $dest['sector_id'] != $playerinfo['sector'])
        {
            $triptime = 1;
        }

        if ($playerinfo['dev_fuelscoop'] == "Y")
        {
            $energyscooped = $distance * 100;
        }
        else
        {
            $energyscooped = 0;
        }

        if ($playerinfo['dev_fuelscoop'] == "Y" && !$energyscooped && $triptime == 1)
        {
            $energyscooped = 100;
        }

        $free_power = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg) - $playerinfo['ship_energy'];

        if ($free_power < $energyscooped)
        {
            $energyscooped = $free_power;
        }

        if ($energyscooped < 1)
        {
            $energyscooped = 0;
        }

        $retvalue['scooped1'] = $energyscooped;

        if ($circuit == '2')
        {
            if ($sells == 'Y' && $playerinfo['dev_fuelscoop'] == 'Y' && $type2 == 'P' && $dest['port_type'] != 'energy')
            {
                $energyscooped = $distance * 100;
                $free_power = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg);

                if ($free_power < $energyscooped)
                {
                    $energyscooped = $free_power;
                }

                $retvalue['scooped2'] = $energyscooped;
            }
            elseif ($playerinfo['dev_fuelscoop'] == 'Y')
            {
                $energyscooped = $distance * 100;
                $free_power = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg) - $retvalue['scooped1'] - $playerinfo['ship_energy'];

                if ($free_power < $energyscooped)
                {
                    $energyscooped = $free_power;
                }

                $retvalue['scooped2'] = $energyscooped;
            }
        }

        if ($circuit == '2')
        {
            $triptime *= 2;
            $triptime += 2;
        }
        else
        {
            $triptime++;
        }

        $retvalue['triptime'] = $triptime;
        $retvalue['scooped'] = $retvalue['scooped1'] + $retvalue['scooped2'];

        return $retvalue;
    }

    public static function warpCalc(\PDO $pdo_db, string $lang, array $langvars, Reg $tkireg, Smarty $template, array $traderoute, array $source, array $dest): array
    {
        $dist = array();
        $sql = "SELECT link_id FROM ::prefix::links WHERE link_start=:link_start AND link_dest=:link_dest";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':link_start', $source['sector_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':link_dest', $dest['sector_id'], \PDO::PARAM_INT);
        $stmt->execute();
        $link_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($link_present !== null)
        {
            $langvars['l_tdr_nowlink1'] = str_replace("[tdr_src_sector_id]", $source['sector_id'], $langvars['l_tdr_nowlink1']);
            $langvars['l_tdr_nowlink1'] = str_replace("[tdr_dest_sector_id]", $dest['sector_id'], $langvars['l_tdr_nowlink1']);
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $template, $langvars['l_tdr_nowlink1']);
        }

        if ($traderoute['circuit'] == '2')
        {
            $sql = "SELECT link_id FROM ::prefix::links WHERE link_start=:link_start AND link_dest=:link_dest";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':link_start', $dest['sector_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':link_dest', $source['sector_id'], \PDO::PARAM_INT);
            $stmt->execute();
            $link_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($link_present !== null)
            {
                $langvars['l_tdr_nowlink2'] = str_replace("[tdr_src_sector_id]", $source['sector_id'], $langvars['l_tdr_nowlink2']);
                $langvars['l_tdr_nowlink2'] = str_replace("[tdr_dest_sector_id]", $dest['sector_id'], $langvars['l_tdr_nowlink2']);
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $template, $langvars['l_tdr_nowlink2']);
            }

            $dist['triptime'] = 4;
        }
        else
        {
            $dist['triptime'] = 2;
        }

        $dist['scooped'] = 0;
        $dist['scooped1'] = 0;
        $dist['scooped2'] = 0;
        return $dist;
    }
}
