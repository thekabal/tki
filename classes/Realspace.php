<?php declare(strict_types = 1);
/**
 * classes/Realspace.php from The Kabal Invasion.
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

class Realspace
{
    public function realSpaceMove(\PDO $pdo_db, array $langvars, int $destination, Reg $tkireg): string
    {
        $energyscooped = 0;
        $players_gateway = new Players\PlayersGateway($pdo_db);
        $playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

        $sql = "SELECT angle1, angle2, distance FROM ::prefix::universe WHERE sector_id = :playersector";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':playersector', $playerinfo['sector'], \PDO::PARAM_INT);
        $stmt->execute();
        $start = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (!is_array($start))
        {
            $start = array();
        }

        $sql = "SELECT angle1, angle2, distance FROM ::prefix::universe WHERE sector_id = :destination";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':destination', $destination, \PDO::PARAM_INT);
        $stmt->execute();
        $finish = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (!is_array($finish))
        {
            $finish = array();
        }

        $deg = pi() / 180;
        $sa1 = $start['angle1'] * $deg;
        $sa2 = $start['angle2'] * $deg;
        $fa1 = $finish['angle1'] * $deg;
        $fa2 = $finish['angle2'] * $deg;
        $pos_x = ($start['distance'] * sin($sa1) * cos($sa2)) - ($finish['distance'] * sin($fa1) * cos($fa2));
        $pos_y = ($start['distance'] * sin($sa1) * sin($sa2)) - ($finish['distance'] * sin($fa1) * sin($fa2));
        $pos_z = ($start['distance'] * cos($sa1)) - ($finish['distance'] * cos($fa1));
        $distance = round(sqrt(pow($pos_x, 2) + pow($pos_y, 2) + pow($pos_z, 2)));
        $shipspeed = pow($tkireg->level_factor, $playerinfo['engines']);
        $triptime = round($distance / $shipspeed);

        if ($triptime == 0 && $destination != $playerinfo['sector'])
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

        if ($playerinfo['dev_fuelscoop'] == "Y" && $energyscooped == 0 && $triptime == 1)
        {
            $energyscooped = 100;
        }

        $free_power = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg) - $playerinfo['ship_energy'];

        // Amount of energy that can be stored is less than the amount scooped.
        // Amount scooped is set to what can be stored.
        if ($free_power < $energyscooped)
        {
            $energyscooped = $free_power;
        }

        // Make sure energyscooped is not negative, or decimal
        if ($energyscooped < 1)
        {
            $energyscooped = 0;
        }

        // Check to see if player is already in that sector
        if ($destination == $playerinfo['sector'])
        {
            $triptime = 0;
            $energyscooped = 0;
        }

        if ($triptime > $playerinfo['turns'])
        {
            $langvars['l_rs_movetime'] = str_replace("[triptime]", number_format($triptime, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_rs_movetime']);
            echo $langvars['l_rs_movetime'] . "<br><br>";
            echo $langvars['l_rs_noturns'];

            $sql = "UPDATE ::prefix::ships SET cleared_defenses = ' ' WHERE ship_id = :ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->execute();

            $retval = "BREAK-TURNS";
        }
        else
        {
            // Modified from traderoute.php - sector defense check
            $hostile = 0;

            $sql = "SELECT * FROM ::prefix::sector_defense WHERE sector_id = :sector_id AND ship_id <> :ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $destination, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->execute();
            $defenses_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($defenses_present !== false)
            {
                $sql = "SELECT * FROM ::prefix::ships WHERE ship_id = :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':ship_id', $defenses_present['ship_id'], \PDO::PARAM_INT);
                $stmt->execute();
                $nsfighters = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                if (!is_array($nsfighters))
                {
                    $nsfighters = array();
                }

                if ($nsfighters['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
                {
                    $hostile = 1;
                }
            }

            if (($hostile > 0) && ($playerinfo['hull'] > $tkireg->mine_hullsize))
            {
                $retval = "HOSTILE";
                echo str_replace("[destination]", (string) $destination, $langvars['l_pr_cannot_move_defenses']) . "<br>";
            }
            else
            {
                $cur_time_stamp = date("Y-m-d H:i:s");

                $sql = "UPDATE ::prefix::ships SET last_login = :last_login, sector = :destination, " .
                       "ship_energy = ship_energy + :ship_energy, turns = turns - :turns, " .
                       "turns_used = turns_used + :turns_used WHERE ship_id = :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':last_login', $cur_time_stamp, \PDO::PARAM_STR);
                $stmt->bindParam(':sector', $destination, \PDO::PARAM_INT);
                $stmt->bindParam(':ship_energy', $energyscooped, \PDO::PARAM_INT);
                $stmt->bindParam(':turns', $triptime, \PDO::PARAM_INT);
                $stmt->bindParam(':turns_used', $triptime, \PDO::PARAM_INT);
                $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                $stmt->execute();

                $langvars['l_rs_ready_result'] = null;
                $langvars['l_rs_ready_result'] = str_replace("[sector]", (string) $destination, $langvars['l_rs_ready']);
                $langvars['l_rs_ready_result'] = str_replace("[triptime]", number_format($triptime, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_rs_ready_result']);
                $langvars['l_rs_ready_result'] = str_replace("[energy]", number_format($energyscooped, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_rs_ready_result']);
                echo $langvars['l_rs_ready_result'] . "<br>";
                $retval = "GO";
            }
        }

        return $retval;
    }
}
