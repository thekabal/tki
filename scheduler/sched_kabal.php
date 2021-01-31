<?php declare(strict_types = 1);
/**
 * scheduler/sched_kabal.php from The Kabal Invasion.
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

// FUTURE: SQL bind varibles, PDO, debugging, functional(!)
// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('scheduler'));

// Kabal turn updates
echo "<br><strong>" . $langvars['l_sched_kabal_title'] . "</strong><br><br>";

// Make Kabal selection
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

/*
//Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
$res = $old_db->Execute("SELECT * FROM {$old_db->prefix}ships JOIN {$old_db->prefix}kabal WHERE email=kabal_id and active='Y' and ship_destroyed='N' ORDER BY ship_id");
while (($res instanceof ADORecordSet) && ($res != false))
//while (!$res->EOF)
{
    $kabalisdead = 0;
    $playerinfo = $res->fields;
    // Regenerate / Buy stats
    Tki\KabalRegen::regen($pdo_db, $playerinfo, $kabal_unemployment, $tkireg);

    // Run through orders
    $furcount++;
    if (random_int(1, 5) > 1)                                 // 20% Chance of not moving at all
    {
        // Orders = 0 Sentinel
        if ($playerinfo['orders'] == 0)
        {
            $furcount0++;
            // Find a target in my sector, not myself, not on a planet

            $reso0 = $old_db->Execute("SELECT * FROM {$old_db->prefix}ships WHERE sector = ? AND email! = ? AND email NOT LIKE '%@kabal' AND planet_id = 0 AND ship_id > 1", array($playerinfo['sector'], $playerinfo['email']));
            Tki\Db::logDbErrors($pdo_db, $res0, __LINE__, __FILE__);
            if (!$reso0->EOF)
            {
                $rowo0 = $reso0->fields;
                if ($playerinfo['aggression'] == 0)            // O = 0 & Aggression = 0 Peaceful
                {
                    // This Guy Does Nothing But Sit As A Target Himself
                }
                elseif ($playerinfo['aggression'] == 1)        // O = 0 & Aggression = 1 Attack sometimes O = 0
                {
                    // Kabal's only compare number of fighters when determining if they have an attack advantage
                    if ($playerinfo['ship_fighters'] > $rowo0['ship_fighters'])
                    {
                        $furcount0a++;
                        Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::KABAL_ATTACK, "$rowo0[character_name]");
                        Tki\KabalToShip::ship($pdo_db, $lang, $rowo0['ship_id'], $tkireg, $playerinfo, $langvars);
                        if ($kabalisdead > 0)
                        {
                            $res->MoveNext();
                            continue;
                        }
                    }
                }
                elseif ($playerinfo['aggression'] == 2)        // O = 0 & Aggression = 2 attack always
                {
                    $furcount0a++;
                    Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::KABAL_ATTACK, "$rowo0[character_name]");
                    Tki\KabalToShip::ship($pdo_db, $lang, $rowo0['ship_id'], $tkireg, $playerinfo, $langvars);
                    if ($kabalisdead > 0)
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
            Tki\KabalMove::move($pdo_db, $lang, $old_db, $playerinfo, $targetlink, $langvars, $tkireg);
            if ($kabalisdead > 0)
            {
                $res->MoveNext();
                continue;
            }
            // Find a target in my sector, not myself
            $reso1 = $old_db->Execute("SELECT * FROM {$old_db->prefix}ships WHERE sector = ? and email! = ? and ship_id > 1", array($targetlink, $playerinfo['email']));
            Tki\Db::logDbErrors($pdo_db, $reso1, __LINE__, __FILE__);
            if (!$reso1->EOF)
            {
                $rowo1 = $reso1->fields;
                if ($playerinfo['aggression'] == 0)            // O = 1 & Aggression = 0 Peaceful O = 1
                {
                    // This Guy Does Nothing But Roam Around As A Target Himself
                }
                elseif ($playerinfo['aggression'] == 1)        // O = 1 & AGRESSION = 1 ATTACK SOMETIMES
                {
                    // Kabal's only compare number of fighters when determining if they have an attack advantage
                    if ($playerinfo['ship_fighters'] > $rowo1['ship_fighters'] && $rowo1['planet_id'] == 0)
                    {
                        $furcount1a++;
                        Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::KABAL_ATTACK, "$rowo1[character_name]");
                        Tki\KabalToShip::ship($pdo_db, $lang, $rowo1['ship_id'], $tkireg, $playerinfo, $langvars);
                        if ($kabalisdead > 0)
                        {
                            $res->MoveNext();
                            continue;
                        }
                    }
                }
                elseif ($playerinfo['aggression'] == 2)        //  O = 1 & AGRESSION = 2 ATTACK ALLWAYS
                {
                    $furcount1a++;
                    Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::KABAL_ATTACK, "$rowo1[character_name]");
                    if (!$rowo1['planet_id'] == 0)
                    {              // Is on planet
                        Tki\KabalToShip::planet($pdo_db, $rowo1['planet_id'], $tkireg, $playerinfo, $langvars);
                    }
                    else
                    {
                        Tki\KabalToShip::ship($pdo_db, $lang, $rowo1['ship_id'], $tkireg, $playerinfo, $langvars);
                    }

                    if ($kabalisdead > 0)
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
            Tki\KabalMove::move($pdo_db, $lang, $old_db, $playerinfo, $targetlink, $langvars, $tkireg);
            if ($kabalisdead > 0)
            {
                $res->MoveNext();
                continue;
            }

            // NOW TRADE BEFORE WE DO ANY AGGRESSION CHECKS
            Tki\KabalTrade::trade($pdo_db, $playerinfo, $tkireg);
            // FIND A TARGET
            // IN MY SECTOR, NOT MYSELF
            $reso2 = $old_db->Execute("SELECT * FROM {$old_db->prefix}ships WHERE sector = ? and email! = ? and ship_id > 1", array($targetlink, $playerinfo['email']));
            Tki\Db::logDbErrors($pdo_db, $reso2, __LINE__, __FILE__);
            if (!$reso2->EOF)
            {
                $rowo2 = $reso2->fields;
                if ($playerinfo['aggression'] == 0)            // O = 2 & AGRESSION = 0 PEACEFUL
                {
                    // This Guy Does Nothing But Roam And Trade
                }
                elseif ($playerinfo['aggression'] == 1)        // O = 2 & AGRESSION = 1 ATTACK SOMETIMES
                {
                    // Kabal's only compare number of fighters when determining if they have an attack advantage
                    if ($playerinfo['ship_fighters'] > $rowo2['ship_fighters'] && $rowo2['planet_id'] == 0)
                    {
                        $furcount2a++;
                        Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::KABAL_ATTACK, "$rowo2[character_name]");
                        Tki\KabalToShip::ship($pdo_db, $lang, $rowo2['ship_id'], $tkireg, $playerinfo, $langvars);
                        if ($kabalisdead > 0)
                        {
                            $res->MoveNext();
                            continue;
                        }
                    }
                }
                elseif ($playerinfo['aggression'] == 2)        // O = 2 & AGRESSION = 2 ATTACK ALLWAYS
                {
                    $furcount2a++;
                    Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::KABAL_ATTACK, "$rowo2[character_name]");
                    if (!$rowo2['planet_id'] == 0)
                    {              // IS ON PLANET
                        Tki\KabalToPlanet::planet($pdo_db, $lang, $old_db, $rowo2['planet_id'], $tkireg, $playerinfo, $langvars);
                    }
                    else
                    {
                        Tki\KabalToShip::ship($pdo_db, $lang, $rowo2['ship_id'], $tkireg, $playerinfo, $langvars);
                    }

                    if ($kabalisdead > 0)
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
                Tki\KabalHunt::Hunt($pdo_db, $old_db, $playerinfo, $kabalisdead, $langvars, $tkireg);
                if ($kabalisdead > 0)
                {
                    $res->MoveNext();
                    continue;
                }
            }
            else
            {
                // ROAM TO A NEW SECTOR BEFORE DOING ANYTHING ELSE
                Tki\KabalMove::move($pdo_db, $lang, $old_db, $playerinfo, $targetlink, $langvars, $tkireg);
                if ($kabalisdead > 0)
                {
                    $res->MoveNext();
                    continue;
                }

                // FIND A TARGET
                // IN MY SECTOR, NOT MYSELF
                $reso3 = $old_db->Execute("SELECT * FROM {$old_db->prefix}ships WHERE sector = ? and email! = ? and ship_id > 1", array($playerinfo['sector'], $playerinfo['email']));
                Tki\Db::logDbErrors($pdo_db, $reso3, __LINE__, __FILE__);
                if (!$reso3->EOF)
                {
                    $rowo3 = $reso3->fields;
                    if ($playerinfo['aggression'] == 0)            // O = 3 & AGRESSION = 0 PEACEFUL
                    {
                        // This Guy Does Nothing But Roam Around As A Target Himself
                    }
                    elseif ($playerinfo['aggression'] == 1)        // O = 3 & AGRESSION = 1 ATTACK SOMETIMES
                    {
                        // Kabal's only compare number of fighters when determining if they have an attack advantage
                        if ($playerinfo['ship_fighters'] > $rowo3['ship_fighters'] && $rowo3['planet_id'] == 0)
                        {
                            $furcount3a++;
                            Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::KABAL_ATTACK, "$rowo3[character_name]");
                            Tki\KabalToShip::ship($pdo_db, $lang, $rowo3['ship_id'], $tkireg, $playerinfo, $langvars);
                            if ($kabalisdead > 0)
                            {
                                $res->MoveNext();
                                continue;
                            }
                        }
                    }
                    elseif ($playerinfo['aggression'] == 2)        // O = 3 & AGRESSION = 2 ATTACK ALLWAYS
                    {
                        $furcount3a++;
                        Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::KABAL_ATTACK, "$rowo3[character_name]");
                        if (!$rowo3['planet_id'] == 0)
                        {              // IS ON PLANET
                            Tki\KabalToPlanet::planet($pdo_db, $lang, $old_db, $rowo3['planet_id'], $tkireg, $playerinfo, $langvars);
                        }
                        else
                        {
                            Tki\KabalToShip::ship($pdo_db, $lang, $rowo3['ship_id'], $tkireg, $playerinfo, $langvars);
                        }

                        if ($kabalisdead > 0)
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

$langvars['l_sched_kabal_count'] = str_replace("[count]", (string) $furcount, $langvars['l_sched_kabal_count']);
echo $langvars['l_sched_kabal_count'] . ".<br>";

$langvars['l_sched_kabal_inactive'] = str_replace("[count]", (string) $furnonmove, $langvars['l_sched_kabal_inactive']);
echo $langvars['l_sched_kabal_inactive'] . ".<br>";

$langvars['l_sched_kabal_sentinel'] = str_replace("[count]", (string) $furcount0, $langvars['l_sched_kabal_sentinel']);
$langvars['l_sched_kabal_sentinel'] = str_replace("[count2]", (string) $furcount0a, $langvars['l_sched_kabal_sentinel']);
echo $langvars['l_sched_kabal_sentinel'] . ".<br>";

$langvars['l_sched_kabal_roam'] = str_replace("[count]", (string) $furcount1, $langvars['l_sched_kabal_roam']);
$langvars['l_sched_kabal_roam'] = str_replace("[count2]", (string) $furcount1a, $langvars['l_sched_kabal_roam']);
echo $langvars['l_sched_kabal_roam'] . ".<br>";

$langvars['l_sched_kabal_roam_trade'] = str_replace("[count]", (string) $furcount2, $langvars['l_sched_kabal_roam_trade']);
$langvars['l_sched_kabal_roam_trade'] = str_replace("[count2]", (string) $furcount2a, $langvars['l_sched_kabal_roam_trade']);
echo $langvars['l_sched_kabal_roam_trade'] . ".<br>";

$langvars['l_sched_kabal_roam_hunt'] = str_replace("[count]", (string) $furcount3, $langvars['l_sched_kabal_roam_hunt']);
$langvars['l_sched_kabal_roam_hunt'] = str_replace("[count2]", (string) $furcount3a, $langvars['l_sched_kabal_roam_hunt']);
$langvars['l_sched_kabal_roam_hunt'] = str_replace("[count3]", (string) $furcount3h, $langvars['l_sched_kabal_roam_hunt']);
echo $langvars['l_sched_kabal_roam_hunt'] . ".<br>";

echo $langvars['l_sched_kabal_done'] . ".<br>";

echo "<br>";
