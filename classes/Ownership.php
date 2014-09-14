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
// File: classes/Ownership.php

namespace Tki;

class Ownership
{
    public static function calc($db, $sector, $min_bases_to_own, $langvars)
    {
        $bases_res = $db->Execute("SELECT owner, corp FROM {$db->prefix}planets WHERE sector_id=? AND base='Y'", array($sector));
        Db::logDbErrors($db, $bases_res, __LINE__, __FILE__);
        $num_bases = $bases_res->RecordCount();

        $i = 0;
        if ($num_bases > 0)
        {
            while (!$bases_res->EOF)
            {
                $bases[$i] = $bases_res->fields;
                $i++;
                $bases_res->MoveNext();
            }
        }
        else
        {
            return "Sector ownership didn't change";
        }

        $owner_num = 0;

        foreach ($bases as $curbase)
        {
            $curcorp = -1;
            $curship = -1;
            $loop = 0;
            while ($loop < $owner_num)
            {
                if ($curbase['corp'] != 0)
                {
                    if ($owners[$loop]['type'] == 'C')
                    {
                        if ($owners[$loop]['id'] == $curbase['corp'])
                        {
                            $curcorp = $loop;
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

            if ($curcorp == -1)
            {
                if ($curbase['corp'] != 0)
                {
                    $curcorp = $owner_num;
                    $owner_num++;
                    $owners[$curcorp]['type'] = 'C';
                    $owners[$curcorp]['num'] = 1;
                    $owners[$curcorp]['id'] = $curbase['corp'];
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
        $nbcorps = 0;
        $nbships = 0;
        while ($loop < $owner_num)
        {
            if ($owners[$loop]['type'] == 'C')
            {
                $nbcorps++;
            }
            else
            {
                $team_res = $db->Execute("SELECT team FROM {$db->prefix}ships WHERE ship_id=?", array($owners[$loop]['id']));
                Db::logDbErrors($db, $team_res, __LINE__, __FILE__);
                if ($team_res && $team_res->RecordCount() != 0)
                {
                    $curship = $team_res->fields;
                    $ships[$nbships] = $owners[$loop]['id'];
                    $scorps[$nbships] = $curship['team'];
                    $nbships++;
                }
            }
            $loop++;
        }

        // More than one corp, war
        if ($nbcorps > 1)
        {
            $setzone_res = $db->Execute("UPDATE {$db->prefix}universe SET zone_id=4 WHERE sector_id=?", array($sector));
            Db::logDbErrors($db, $setzone_res, __LINE__, __FILE__);

            return $langvars['l_global_warzone'];
        }

        // More than one unallied ship, war
        $numunallied = 0;
        foreach ($scorps as $corp)
        {
            if ($corp == 0)
            {
                $numunallied++;
            }
        }

        if ($numunallied > 1)
        {
            $setzone_resb = $db->Execute("UPDATE {$db->prefix}universe SET zone_id=4 WHERE sector_id=?", array($sector));
            Db::logDbErrors($db, $setzone_resb, __LINE__, __FILE__);

            return $langvars['l_global_warzone'];
        }

        // Unallied ship, another corp present, war
        if ($numunallied > 0 && $nbcorps > 0)
        {
            $setzone_resc = $db->Execute("UPDATE {$db->prefix}universe SET zone_id=4 WHERE sector_id=?", array($sector));
            Db::logDbErrors($db, $setzone_resc, __LINE__, __FILE__);

            return $langvars['l_global_warzone'];
        }

        // Unallied ship, another ship in a corp, war
        if ($numunallied > 0)
        {
            $query = "SELECT team FROM {$db->prefix}ships WHERE (";
            $i = 0;
            foreach ($ships as $ship)
            {
                $query = $query . 'ship_id=$ship';
                $i++;
                if ($i != $nbships)
                {
                    $query = $query . ' OR ';
                }
                else
                {
                    $query = $query . ')';
                }
            }

            $query = $query . ' AND team!=0';
            $select_team_res = $db->Execute($query);
            Db::logDbErrors($db, $select_team_res, __LINE__, __FILE__);

            if ($select_team_res !== false && ($select_team_res->RecordCount() != 0))
            {
                $setzone_resd = $db->Execute("UPDATE {$db->prefix}universe SET zone_id=4 WHERE sector_id=?", array($sector));
                Db::logDbErrors($db, $setzone_resd, __LINE__, __FILE__);

                return $langvars['l_global_warzone'];
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

        if ($owners[$winner]['num'] < $min_bases_to_own)
        {
            $setzone_rese = $db->Execute("UPDATE {$db->prefix}universe SET zone_id=1 WHERE sector_id=?", array($sector));
            Db::logDbErrors($db, $setzone_rese, __LINE__, __FILE__);

            return $langvars['l_global_nzone'];
        }

        if ($owners[$winner]['type'] == 'C')
        {
            $setzone_resf = $db->Execute("SELECT zone_id FROM {$db->prefix}zones WHERE corp_zone='Y' AND owner=?", array($owners[$winner]['id']));
            Db::logDbErrors($db, $setzone_resf, __LINE__, __FILE__);
            $zone = $setzone_resf->fields;

            $setzone_resg = $db->Execute("SELECT team_name FROM {$db->prefix}teams WHERE id=?", array($owners[$winner]['id']));
            Db::logDbErrors($db, $setzone_resg, __LINE__, __FILE__);
            $corp = $setzone_resg->fields;

            $update_res = $db->Execute("UPDATE {$db->prefix}universe SET zone_id=? WHERE sector_id=?", array($zone['zone_id'], $sector));
            Db::logDbErrors($db, $update_res, __LINE__, __FILE__);

            return $langvars['l_global_team'] . ' ' . $corp['team_name'] . '!';
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
                $setzone_resh = $db->Execute("UPDATE {$db->prefix}universe SET zone_id=1 WHERE sector_id=?", array($sector));
                Db::logDbErrors($db, $setzone_resh, __LINE__, __FILE__);

                return $langvars['l_global_nzone'];
            }
            else
            {

                $setzone_resi = $db->Execute("SELECT zone_id FROM {$db->prefix}zones WHERE corp_zone='N' AND owner=?", array($owners[$winner]['id']));
                Db::logDbErrors($db, $setzone_resi, __LINE__, __FILE__);
                $zone = $setzone_resi->fields;

                $setzone_resj = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE ship_id=?", array($owners[$winner]['id']));
                Db::logDbErrors($db, $setzone_resj, __LINE__, __FILE__);
                $ship = $setzone_resj->fields;

                $update_res2 = $db->Execute("UPDATE {$db->prefix}universe SET zone_id=? WHERE sector_id=?", array($zone['zone_id'], $sector));
                Db::logDbErrors($db, $update_res2, __LINE__, __FILE__);

                return $langvars['l_global_player'] . ' ' . $ship['character_name'] . '!';
            }
        }
    }
}

