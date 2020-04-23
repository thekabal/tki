<?php declare(strict_types = 1);
/**
 * classes/CalcLevels.php from The Kabal Invasion.
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

class CalcLevels
{
    // This method can be used for armor, holds, shields, torps, beams, and fighters
    public static function abstractLevels(int $level, Reg $tkireg): int
    {
        $result = round(pow($tkireg->level_factor, $level) * 100);
        return (int) $result;
    }

    public static function energy(int $level_power, Reg $tkireg): float
    {
        $result = round(pow($tkireg->level_factor, $level_power) * 500);
        return $result;
    }

    public static function planetBeams(\PDO $pdo_db, array $ownerinfo, Reg $tkireg, array $planetinfo): int
    {
        $base_factor = ($planetinfo['base'] == 'Y') ? $tkireg->base_defense : 0;
        $planetbeams = self::abstractLevels($ownerinfo['beams'] + $base_factor, $tkireg->level_factor);
        $energy_available = $planetinfo['energy'];

        $sql = "SELECT beams FROM ::prefix::ships WHERE planet_id=:planet_id AND on_planet = 'Y'";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':planet_id', $planetinfo['planet_id'], \PDO::PARAM_INT);
        $stmt->execute();
        $beam_defender_here = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($beam_defender_here !== false)
        {
            foreach ($beam_defender_here as $tmp_beams)
            {
                $planetbeams = $planetbeams + self::abstractLevels($tmp_beams['beams'], $tkireg->level_factor);
            }
        }

        if ($planetbeams > $energy_available)
        {
            $planetbeams = $energy_available;
        }

        $planetinfo['energy'] -= $planetbeams;

        return (int) $planetbeams;
    }

    public static function planetShields(\PDO $pdo_db, array $ownerinfo, Reg $tkireg, array $planetinfo): int
    {
        $base_factor = ($planetinfo['base'] == 'Y') ? $tkireg->base_defense : 0;
        $planetshields = self::abstractLevels($ownerinfo['shields'] + $base_factor, $tkireg->level_factor);
        $energy_available = $planetinfo['energy'];

        $sql = "SELECT shields FROM ::prefix::ships WHERE planet_id=:planet_id AND on_planet = 'Y'";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':planet_id', $planetinfo['planet_id'], \PDO::PARAM_INT);
        $stmt->execute();
        $shield_defender_here = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($shield_defender_here !== false)
        {
            foreach ($shield_defender_here as $tmp_shields)
            {
                $planetshields = $planetshields + self::abstractLevels($tmp_shields['shields'], $tkireg->level_factor);
            }
        }

        if ($planetshields > $energy_available)
        {
            $planetshields = $energy_available;
        }

        $planetinfo['energy'] -= $planetshields;

        return (int) $planetshields;
    }

    public static function planetTorps(\PDO $pdo_db, array $ownerinfo, array $planetinfo, Reg $tkireg): int
    {
        $base_factor = ($planetinfo['base'] == 'Y') ? $tkireg->base_defense : 0;
        $torp_launchers = round(pow($tkireg->level_factor, ($ownerinfo['torp_launchers']) + $base_factor)) * 10;
        $torps = $planetinfo['torps'];

        $sql = "SELECT torp_launchers FROM ::prefix::ships WHERE planet_id=:planet_id AND on_planet = 'Y'";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':planet_id', $planetinfo['planet_id'], \PDO::PARAM_INT);
        $stmt->execute();
        $torp_defender_here = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($torp_defender_here !== false)
        {
            foreach ($torp_defender_here as $tmp_torp)
            {
                $ship_torps = round(pow($tkireg->level_factor, $tmp_torp['torp_launchers'])) * 10;
                $torp_launchers = $torp_launchers + $ship_torps;
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

    public static function avgTech(array $ship_info, string $type = 'ship'): float
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
