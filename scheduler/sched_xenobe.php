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
// File: sched_xenobe.php
//
// FUTURE: SQL bind varibles

// Xenobe turn updates
echo "<br><strong>Xenobe TURNS</strong><br><br>";

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('sched_xenobe', 'common', 'global_includes', 'combat', 'footer', 'news'));

// Make Xenobe selection
$furcount = 0;
$furcount0 = 0;
$furcount0a = 0;
$furcount1 = 0;
$furcount1a = 0;
$furcount2 = 0;
$furcount2a = 0;
$furcount3 = 0;
$furcount3a = 0;
$furcount3h = 0;

// Lock the tables
$resa = $db->Execute("LOCK TABLES {$db->prefix}xenobe WRITE, {$db->prefix}ships WRITE");
Tki\Db::LogDbErrors($pdo_db, $resa, __LINE__, __FILE__);

/*
//Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
$res = $db->Execute("SELECT * FROM {$db->prefix}ships JOIN {$db->prefix}xenobe WHERE email=xenobe_id and active='Y' and ship_destroyed='N' ORDER BY ship_id");
while (($res instanceof ADORecordSet) && ($res != false))
//while (!$res->EOF)
{
    $xenobeisdead = 0;
    $playerinfo = $res->fields;
    // Regenerate / Buy stats
    Tki\Xenobe::xenobeRegen($pdo_db, $playerinfo, $xen_unemployment, $tkireg);

    // Run through orders
    $furcount++;
    if (random_int(1, 5) > 1)                                 // 20% Chance of not moving at all
    {
        // Orders = 0 Sentinel
        if ($playerinfo['orders'] == 0)
        {
            $furcount0++;
            // Find a target in my sector, not myself, not on a planet

            $reso0 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE sector = ? AND email! = ? AND email NOT LIKE '%@xenobe' AND planet_id = 0 AND ship_id > 1", array($playerinfo['sector'], $playerinfo['email']));
            Tki\Db::LogDbErrors($pdo_db, $res0, __LINE__, __FILE__);
            if (!$reso0->EOF)
            {
                $rowo0 = $reso0->fields;
                if ($playerinfo['aggression'] == 0)            // O = 0 & Aggression = 0 Peaceful
                {
                    // This Guy Does Nothing But Sit As A Target Himself
                }
                elseif ($playerinfo['aggression'] == 1)        // O = 0 & Aggression = 1 Attack sometimes O = 0
                {
                    // Xenobe's only compare number of fighters when determining if they have an attack advantage
                    if ($playerinfo['ship_fighters'] > $rowo0['ship_fighters'])
                    {
                        $furcount0a++;
                        Tki\PlayerLog::WriteLog($pdo_db, $playerinfo['ship_id'], LOG_XENOBE_ATTACK, "$rowo0[character_name]");
                        Tki\Xenobe::xenobeToShip($pdo_db, $db, $rowo0['ship_id'], $tkireg, $playerinfo, $langvars);
                        if ($xenobeisdead > 0)
                        {
                            $res->MoveNext();
                            continue;
                        }
                    }
                }
                elseif ($playerinfo['aggression'] == 2)        // O = 0 & Aggression = 2 attack always
                {
                    $furcount0a++;
                    Tki\PlayerLog::WriteLog($pdo_db, $playerinfo['ship_id'], LOG_XENOBE_ATTACK, "$rowo0[character_name]");
                    Tki\Xenobe::xenobeToShip($pdo_db, $db, $rowo0['ship_id'], $tkireg, $playerinfo, $langvars);
                    if ($xenobeisdead > 0)
                    {
                        $res->MoveNext();
                        continue;
                    }
                }
            }
        }
        elseif ($playerinfo['orders'] == 1) // Orders = 1 roam
        {
            $furcount1++;
            // Roam to a new sector before doing anything else
            $targetlink = $playerinfo['sector'];
            Tki\Xenobe::xenobeMove($pdo_db, $db, $playerinfo, $targetlink, $langvars, $tkireg);
            if ($xenobeisdead > 0)
            {
                $res->MoveNext();
                continue;
            }
            // Find a target in my sector, not myself
            $reso1 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE sector = ? and email! = ? and ship_id > 1", array($targetlink, $playerinfo['email']));
            Tki\Db::LogDbErrors($pdo_db, $reso1, __LINE__, __FILE__);
            if (!$reso1->EOF)
            {
                $rowo1 = $reso1->fields;
                if ($playerinfo['aggression'] == 0)            // O = 1 & Aggression = 0 Peaceful O = 1
                {
                    // This Guy Does Nothing But Roam Around As A Target Himself
                }
                elseif ($playerinfo['aggression'] == 1)        // O = 1 & AGRESSION = 1 ATTACK SOMETIMES
                {
                    // Xenobe's only compare number of fighters when determining if they have an attack advantage
                    if ($playerinfo['ship_fighters'] > $rowo1['ship_fighters'] && $rowo1['planet_id'] == 0)
                    {
                        $furcount1a++;
                        Tki\PlayerLog::WriteLog($pdo_db, $playerinfo['ship_id'], LOG_XENOBE_ATTACK, "$rowo1[character_name]");
                        Tki\Xenobe::xenobeToShip($pdo_db, $db, $rowo1['ship_id'], $tkireg, $playerinfo, $langvars);
                        if ($xenobeisdead > 0)
                        {
                            $res->MoveNext();
                            continue;
                        }
                    }
                }
                elseif ($playerinfo['aggression'] == 2)        //  O = 1 & AGRESSION = 2 ATTACK ALLWAYS
                {
                    $furcount1a++;
                    Tki\PlayerLog::WriteLog($pdo_db, $playerinfo['ship_id'], LOG_XENOBE_ATTACK, "$rowo1[character_name]");
                    if (!$rowo1['planet_id'] == 0)
                    {              // Is on planet
                        Tki\Xenobe::xenobeToPlanet($pdo_db, $db, $rowo1['planet_id'], $tkireg, $playerinfo, $langvars);
                    }
                    else
                    {
                        Tki\Xenobe::xenobeToShip($pdo_db, $db, $rowo1['ship_id'], $tkireg, $playerinfo, $langvars);
                    }

                    if ($xenobeisdead > 0)
                    {
                        $res->MoveNext();
                        continue;
                    }
                }
            }
        }
        // Orders = 2 roam and trade
        elseif ($playerinfo['orders'] == 2)
        {
            $furcount2++;
            // ROAM TO A NEW SECTOR BEFORE DOING ANYTHING ELSE
            $targetlink = $playerinfo['sector'];
            Tki\Xenobe::xenobeMove($pdo_db, $db, $playerinfo, $targetlink, $langvars, $tkireg);
            if ($xenobeisdead > 0)
            {
                $res->MoveNext();
                continue;
            }

            // NOW TRADE BEFORE WE DO ANY AGGRESSION CHECKS
            Tki\Xenobe::xenobeTrade($pdo_db, $playerinfo, $tkireg);
            // FIND A TARGET
            // IN MY SECTOR, NOT MYSELF
            $reso2 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE sector = ? and email! = ? and ship_id > 1", array($targetlink, $playerinfo['email']));
            Tki\Db::LogDbErrors($pdo_db, $reso2, __LINE__, __FILE__);
            if (!$reso2->EOF)
            {
                $rowo2 = $reso2->fields;
                if ($playerinfo['aggression'] == 0)            // O = 2 & AGRESSION = 0 PEACEFUL
                {
                    // This Guy Does Nothing But Roam And Trade
                }
                elseif ($playerinfo['aggression'] == 1)        // O = 2 & AGRESSION = 1 ATTACK SOMETIMES
                {
                    // Xenobe's only compare number of fighters when determining if they have an attack advantage
                    if ($playerinfo['ship_fighters'] > $rowo2['ship_fighters'] && $rowo2['planet_id'] == 0)
                    {
                        $furcount2a++;
                        Tki\PlayerLog::WriteLog($pdo_db, $playerinfo['ship_id'], LOG_XENOBE_ATTACK, "$rowo2[character_name]");
                        Tki\Xenobe::xenobeToShip($pdo_db, $db, $rowo2['ship_id'], $tkireg, $playerinfo, $langvars);
                        if ($xenobeisdead > 0)
                        {
                            $res->MoveNext();
                            continue;
                        }
                    }
                }
                elseif ($playerinfo['aggression'] == 2)        // O = 2 & AGRESSION = 2 ATTACK ALLWAYS
                {
                    $furcount2a++;
                    Tki\PlayerLog::WriteLog($pdo_db, $playerinfo['ship_id'], LOG_XENOBE_ATTACK, "$rowo2[character_name]");
                    if (!$rowo2['planet_id'] == 0)
                    {              // IS ON PLANET
                        Tki\Xenobe::xenobeToPlanet($pdo_db, $db, $rowo2['planet_id'], $tkireg, $playerinfo, $langvars);
                    }
                    else
                    {
                        Tki\Xenobe::xenobeToShip($pdo_db, $db, $rowo2['ship_id'], $tkireg, $playerinfo, $langvars);
                    }

                    if ($xenobeisdead > 0)
                    {
                        $res->MoveNext();
                        continue;
                    }
                }
            }
        }
        // ORDERS = 3 ROAM AND HUNT
        elseif ($playerinfo['orders'] == 3)
        {
            $furcount3++;
            // LET SEE IF WE GO HUNTING THIS ROUND BEFORE WE DO ANYTHING ELSE
            $hunt = random_int(0, 3);                               // 25% CHANCE OF HUNTING
            // Uncomment below for Debugging
            // $hunt = 0;
            if ($hunt == 0)
            {
                $furcount3h++;
                Tki\Xenobe::xenobeHunter($pdo_db, $db, $playerinfo, $xenobeisdead, $langvars, $tkireg);
                if ($xenobeisdead > 0)
                {
                    $res->MoveNext();
                    continue;
                }
            }
            else
            {
                // ROAM TO A NEW SECTOR BEFORE DOING ANYTHING ELSE
                Tki\Xenobe::xenobeMove($pdo_db, $db, $playerinfo, $targetlink, $langvars, $tkireg);
                if ($xenobeisdead > 0)
                {
                    $res->MoveNext();
                    continue;
                }

                // FIND A TARGET
                // IN MY SECTOR, NOT MYSELF
                $reso3 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE sector = ? and email! = ? and ship_id > 1", array($playerinfo['sector'], $playerinfo['email']));
                Tki\Db::LogDbErrors($pdo_db, $reso3, __LINE__, __FILE__);
                if (!$reso3->EOF)
                {
                    $rowo3 = $reso3->fields;
                    if ($playerinfo['aggression'] == 0)            // O = 3 & AGRESSION = 0 PEACEFUL
                    {
                        // This Guy Does Nothing But Roam Around As A Target Himself
                    }
                    elseif ($playerinfo['aggression'] == 1)        // O = 3 & AGRESSION = 1 ATTACK SOMETIMES
                    {
                        // Xenobe's only compare number of fighters when determining if they have an attack advantage
                        if ($playerinfo['ship_fighters'] > $rowo3['ship_fighters'] && $rowo3['planet_id'] == 0)
                        {
                            $furcount3a++;
                            Tki\PlayerLog::WriteLog($pdo_db, $playerinfo['ship_id'], LOG_XENOBE_ATTACK, "$rowo3[character_name]");
                            Tki\Xenobe::xenobeToShip($pdo_db, $db, $rowo3['ship_id'], $tkireg, $playerinfo, $langvars);
                            if ($xenobeisdead > 0)
                            {
                                $res->MoveNext();
                                continue;
                            }
                        }
                    }
                    elseif ($playerinfo['aggression'] == 2)        // O = 3 & AGRESSION = 2 ATTACK ALLWAYS
                    {
                        $furcount3a++;
                        Tki\PlayerLog::WriteLog($pdo_db, $playerinfo['ship_id'], LOG_XENOBE_ATTACK, "$rowo3[character_name]");
                        if (!$rowo3['planet_id'] == 0)
                        {              // IS ON PLANET
                            Tki\Xenobe::xenobeToPlanet($pdo_db, $db, $rowo3['planet_id'], $tkireg, $playerinfo, $langvars);
                        }
                        else
                        {
                            Tki\Xenobe::xenobeToShip($pdo_db, $db, $rowo3['ship_id'], $tkireg, $playerinfo, $langvars);
                        }

                        if ($xenobeisdead > 0)
                        {
                            $res->MoveNext();
                            continue;
                        }
                    }
                }
            }
        }
    }
    $res->MoveNext();
}
$res->_close();
*/
$furnonmove = $furcount - ($furcount0 + $furcount1 + $furcount2 + $furcount3);
echo "Counted $furcount Xenobe players that are ACTIVE with working ships.<br>";
echo "$furnonmove Xenobe players did not do anything this round. <br>";
echo "$furcount0 Xenobe players had SENTINEL orders of which $furcount0a launched attacks. <br>";
echo "$furcount1 Xenobe players had ROAM orders of which $furcount1a launched attacks. <br>";
echo "$furcount2 Xenobe players had ROAM AND TRADE orders of which $furcount2a launched attacks. <br>";
echo "$furcount3 Xenobe players had ROAM AND HUNT orders of which $furcount3a launched attacks and $furcount3h went hunting. <br>";
echo "Xenobe TURNS COMPLETE. <br>";
echo "<br>";
// END OF Xenobe TURNS

// Unlock the tables.
$result = $db->Execute("UNLOCK TABLES");
Tki\Db::LogDbErrors($pdo_db, $result, __LINE__, __FILE__);
