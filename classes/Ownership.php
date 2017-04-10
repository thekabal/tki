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
// File: classes/Ownership.php

namespace Tki;

class Ownership
{
    public static function calc(\PDO $pdo_db, int $sector, Reg $tkireg, array $langvars) : string
    {
        $sql = "SELECT owner, team FROM ::prefix::planets WHERE sector_id=:sector_id AND base='Y'";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $sector, \PDO::PARAM_INT);
        $stmt->execute();
        $bases_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $i = 0;
        $bases = array();
        if ($bases_present !== null)
        {
            foreach ($bases_present as $tmp_base)
            {
                $bases[$i] = $tmp_base;
                $i++;
            }
        }
        else
        {
            return "Sector ownership didn't change";
        }

        $owner_num = 0;
        $owners = array();

        foreach ($bases as $curbase)
        {
            $curteam = -1;
            $curship = -1;
            $loop = 0;
            while ($loop < $owner_num)
            {
                if ($curbase['team'] != 0)
                {
                    if ($owners[$loop]['type'] == 'C')
                    {
                        if ($owners[$loop]['id'] == $curbase['team'])
                        {
                            $curteam = $loop;
                            $owners[$loop]['num']++;
                        }
                    }
                }

                if ($owners[$loop]['type'] == 'S')
                {
                    if ($owners[$loop]['id'] == $curbase['owner'])
                    {
                        $curship = $loop;
                        $owners[$loop]['num']++;
                    }
                }

                $loop++;
            }

            if ($curteam == -1)
            {
                if ($curbase['team'] != 0)
                {
                    $curteam = $owner_num;
                    $owner_num++;
                    $owners[$curteam]['type'] = 'C';
                    $owners[$curteam]['num'] = 1;
                    $owners[$curteam]['id'] = $curbase['team'];
                }
            }

            if ($curship == -1)
            {
                if ($curbase['owner'] != 0)
                {
                    $curship = $owner_num;
                    $owner_num++;
                    $owners[$curship]['type'] = 'S';
                    $owners[$curship]['num'] = 1;
                    $owners[$curship]['id'] = $curbase['owner'];
                }
            }
        }

        // We've got all the contenders with their bases.
        // Time to test for conflict
        $loop = 0;
        $nbteams = 0;
        $nbships = 0;
        $ships = array();
        $steams = array();
        while ($loop < $owner_num)
        {
            if ($owners[$loop]['type'] == 'C')
            {
                $nbteams++;
            }
            else
            {
                $sql = "SELECT team FROM ::prefix::ships WHERE ship_id=:ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':ship_id', $owners[$loop]['id'], \PDO::PARAM_INT);
                $stmt->execute();
                $team_owner = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                if ($team_owner !== null)
                {
                    $ships[$nbships] = $owners[$loop]['id'];
                    $steams[$nbships] = $team_owner['team'];
                    $nbships++;
                }
            }

            $loop++;
        }

        // More than one team, war
        if ($nbteams > 1)
        {
            $sql = "UPDATE ::prefix::universe SET zone_id=4 WHERE sector_id=:sector_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $sector, \PDO::PARAM_INT);
            $stmt->execute();

            return (string) $langvars['l_global_warzone'];
        }

        // More than one unallied ship, war
        $numunallied = 0;
        foreach ($steams as $team)
        {
            if ($team == 0)
            {
                $numunallied++;
            }
        }

        if ($numunallied > 1)
        {
            $sql = "UPDATE ::prefix::universe SET zone_id=4 WHERE sector_id=:sector_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $sector, \PDO::PARAM_INT);
            $stmt->execute();

            return (string) $langvars['l_global_warzone'];
        }

        // Unallied ship, another team present, war
        if ($numunallied > 0 && $nbteams > 0)
        {
            $sql = "UPDATE ::prefix::universe SET zone_id=4 WHERE sector_id=:sector_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $sector, \PDO::PARAM_INT);
            $stmt->execute();

            return (string) $langvars['l_global_warzone'];
        }

        // Unallied ship, another ship in a team, war
        if ($numunallied > 0)
        {
            $questionMarks = join(",", array_pad(array(), count($ships), "?"));
            $sql = "SELECT team FROM ::prefix::ships WHERE ship_id in ($questionMarks) AND team <> 0";
            $stmt = $pdo_db->prepare($sql);
            $stmt->execute($ships);
            $select_team = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if ($select_team !== null)
            {
                $sql = "UPDATE ::prefix::universe SET zone_id=4 WHERE sector_id=:sector_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':sector_id', $sector, \PDO::PARAM_INT);
                $stmt->execute();
                return (string) $langvars['l_global_warzone'];
            }
        }

        // Ok, all bases are allied at this point. Let's make a winner.
        $winner = 0;
        $i = 1;
        while ($i < $owner_num)
        {
            if ($owners[$i]['num'] > $owners[$winner]['num'])
            {
                $winner = $i;
            }
            elseif ($owners[$i]['num'] == $owners[$winner]['num'])
            {
                if ($owners[$i]['type'] == 'C')
                {
                    $winner = $i;
                }
            }

            $i++;
        }

        if ($owners[$winner]['num'] < $tkireg->min_bases_to_own)
        {
            $sql = "UPDATE ::prefix::universe SET zone_id=1 WHERE sector_id=:sector_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $sector, \PDO::PARAM_INT);
            $stmt->execute();

            return (string) $langvars['l_global_nzone'];
        }

        if ($owners[$winner]['type'] == 'C')
        {
            $sql = "SELECT zone_id FROM ::prefix::zones WHERE team_zone='Y' AND owner=:owner";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner', $owners[$winner]['id'], \PDO::PARAM_INT);
            $stmt->execute();
            $zone = $stmt->fetch(\PDO::FETCH_ASSOC);

            $sql = "SELECT team_name FROM ::prefix::teams WHERE id=:id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':id', $owners[$winner]['id'], \PDO::PARAM_INT);
            $stmt->execute();
            $team = $stmt->fetch(\PDO::FETCH_ASSOC);

            $sql = "UPDATE ::prefix::universe SET zone_id=:zone_id WHERE sector_id=:sector_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':zone_id', $zone['zone_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':sector_id', $sector, \PDO::PARAM_INT);
            $stmt->execute();

            return (string) $langvars['l_global_team'] . ' ' . $team['team_name'] . '!';
        }
        else
        {
            $onpar = 0;
            foreach ($owners as $curowner)
            {
                if ($curowner['type'] == 'S' && $curowner['id'] != $owners[$winner]['id'] && $curowner['num'] == $owners[$winner]['num'])
                {
                    $onpar = 1;
                    break;
                }
            }

            // Two allies have the same number of bases
            if ($onpar == 1)
            {
                $sql = "UPDATE ::prefix::universe SET zone_id=1 WHERE sector_id=:sector_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':sector_id', $sector, \PDO::PARAM_INT);
                $stmt->execute();

                return (string) $langvars['l_global_nzone'];
            }
            else
            {
                $sql = "SELECT zone_id FROM ::prefix::zones WHERE team_zone='N' AND owner=:owner";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':owner', $owners[$winner]['id'], \PDO::PARAM_INT);
                $stmt->execute();
                $zone = $stmt->fetch(\PDO::FETCH_ASSOC);

                $sql = "SELECT character_name FROM ::prefix::ships WHERE ship_id=:ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':ship_id', $owners[$winner]['id'], \PDO::PARAM_INT);
                $stmt->execute();
                $ship = $stmt->fetch(\PDO::FETCH_ASSOC);

                $sql = "UPDATE ::prefix::universe SET zone_id=:zone_id WHERE sector_id=:sector_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':zone_id', $zone['zone_id'], \PDO::PARAM_INT);
                $stmt->bindParam(':sector_id', $sector, \PDO::PARAM_INT);
                $stmt->execute();

                return (string) $langvars['l_global_player'] . ' ' . $ship['character_name'] . '!';
            }
        }
    }
}
