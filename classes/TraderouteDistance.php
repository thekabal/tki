<?php declare(strict_types = 1);
/**
 * classes/TraderouteDistance.php from The Kabal Invasion.
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

namespace Tki;

class TraderouteDistance
{
    public static function calc(\PDO $pdo_db, string $type1, string $type2, int $start, int $dest, int $circuit, array $playerinfo, Reg $tkireg, string $sells = 'N'): array
    {
        $retvalue = array();
        $retvalue['triptime'] = 0;
        $retvalue['scooped1'] = 0;
        $retvalue['scooped2'] = 0;
        $retvalue['scooped'] = 0;
        $start_traderoute = array();
        $dest_traderoute = array();

        if ($type1 == 'L')
        {
            // Get sectorinfo from database
            $sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db);
            $start_traderoute = $sectors_gateway->selectSectorInfo($start);
        }

        if ($type2 == 'L')
        {
            // Get sectorinfo from database
            $sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db);
            $dest_traderoute = $sectors_gateway->selectSectorInfo($dest);
        }

        if ($start_traderoute['sector_id'] == $dest_traderoute['sector_id'])
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

        $sa1 = $start_traderoute['angle1'] * $deg;
        $sa2 = $start_traderoute['angle2'] * $deg;
        $fa1 = $dest_traderoute['angle1'] * $deg;
        $fa2 = $dest_traderoute['angle2'] * $deg;
        $pos_x = $start_traderoute['distance'] * sin($sa1) * cos($sa2) - $dest_traderoute['distance'] * sin($fa1) * cos($fa2);
        $pos_y = $start_traderoute['distance'] * sin($sa1) * sin($sa2) - $dest_traderoute['distance'] * sin($fa1) * sin($fa2);
        $pos_z = $start_traderoute['distance'] * cos($sa1) - $dest_traderoute['distance'] * cos($fa1);
        $distance = round(sqrt(pow($pos_x, 2) + pow($pos_y, 2) + pow($pos_z, 2)));
        $shipspeed = pow($tkireg->level_factor, $playerinfo['engines']);
        $triptime = round($distance / $shipspeed);

        if (($triptime > 0) && ($dest_traderoute['sector_id'] != $playerinfo['sector']))
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

        if (($playerinfo['dev_fuelscoop'] == "Y") && ($energyscooped > 0) && ($triptime == 1))
        {
            $energyscooped = 100;
        }

        $free_power = (int) \Tki\CalcLevels::energy((int) $playerinfo['power'], $tkireg) - (int) $playerinfo['ship_energy'];

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
            if ($sells == 'Y' && $playerinfo['dev_fuelscoop'] == 'Y' && $type2 == 'P' && $dest_traderoute['port_type'] != 'energy')
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

    public static function warpCalc(\PDO $pdo_db, string $lang, Reg $tkireg, Timer $tkitimer, Smarty $template, array $traderoute, array $source, array $dest): array
    {
        $langvars = Translate::load($pdo_db, $lang, array('traderoutes'));
        $dist = array();
        $sql = "SELECT link_id FROM ::prefix::links WHERE link_start = :link_start AND link_dest = :link_dest";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':link_start', $source['sector_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':link_dest', $dest['sector_id'], \PDO::PARAM_INT);
        $stmt->execute();
        $link_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (is_array($link_present))
        {
            $langvars['l_tdr_nowlink1'] = str_replace("[tdr_src_sector_id]", $source['sector_id'], $langvars['l_tdr_nowlink1']);
            $langvars['l_tdr_nowlink1'] = str_replace("[tdr_dest_sector_id]", $dest['sector_id'], $langvars['l_tdr_nowlink1']);
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_nowlink1']);
        }

        if ($traderoute['circuit'] == '2')
        {
            $sql = "SELECT link_id FROM ::prefix::links WHERE link_start = :link_start AND link_dest = :link_dest";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':link_start', $dest['sector_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':link_dest', $source['sector_id'], \PDO::PARAM_INT);
            $stmt->execute();
            $link_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (is_array($link_present))
            {
                $langvars['l_tdr_nowlink2'] = str_replace("[tdr_src_sector_id]", $source['sector_id'], $langvars['l_tdr_nowlink2']);
                $langvars['l_tdr_nowlink2'] = str_replace("[tdr_dest_sector_id]", $dest['sector_id'], $langvars['l_tdr_nowlink2']);
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_nowlink2']);
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
