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
// File: check_mines.php

if (strpos($_SERVER['PHP_SELF'], 'check_mines.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('check_mines', 'common', 'global_includes', 'combat', 'footer', 'news'));

// Put the sector information into the array "sectorinfo"
$result2 = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=?", array($sector));
Tki\Db::logDbErrors($pdo_db, $db, $result2, __LINE__, __FILE__);
$sectorinfo = $result2->fields;

// Put the defence information into the array "defenceinfo"
$result3 = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id=? and defence_type ='M'", array($sector));
Tki\Db::logDbErrors($pdo_db, $db, $result3, __LINE__, __FILE__);

// Correct the targetship bug to reflect the player info
$targetship = $playerinfo;

$num_defences = 0;
$total_sector_mines = 0;
$owner = true;
while (!$result3->EOF)
{
    $row = $result3->fields;
    $defences[$num_defences] = $row;
    $total_sector_mines += $defences[$num_defences]['quantity'];
    if ($defences[$num_defences]['ship_id'] != $playerinfo['ship_id'])
    {
        $owner = false;
    }
    $num_defences++;
    $result3->MoveNext();
}

// Compute the ship average...if its too low then the ship will not hit mines...
$shipavg = Tki\CalcLevels::avgTech($targetship, "ship");

// The mines will attack if 4 conditions are met
//    1) There is at least 1 group of mines in the sector
//    2) There is at least 1 mine in the sector
//    3) You are not the owner or on the team of the owner - team 0 dosent count
//    4) You ship is at least $mine_hullsize (setable in config.php) big

if ($num_defences > 0 && $total_sector_mines > 0 && !$owner && $shipavg > $mine_hullsize)
{
    // Find out if the mine owner and player are on the same team
    $fm_owner = $defences[0]['ship_id'];
    $result2 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($fm_owner));
    Tki\Db::logDbErrors($pdo_db, $db, $result2, __LINE__, __FILE__);

    $mine_owner = $result2->fields;
    if ($mine_owner['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
    {
        // You hit mines
        echo "<h1>" . $title . "</h1>\n";
        $ok=0;
        $totalmines = $total_sector_mines;
        // Before we had a issue where if there where a lot of mines in the sector the result will go -
        // I changed the behaivor so that rand will chose a % of mines to attack will
        // (it will always be at least 5% of the mines or at the very least 1 mine);
        // and if you are very unlucky they all will hit you
        $pren = (random_int(5, 100)/100);
        $roll = round($pren * $total_sector_mines - 1) + 1;
        $totalmines = $totalmines - $roll;

        // You are hit. Tell the player and put it in the log
        $langvars['l_chm_youhitsomemines'] = str_replace("[chm_roll]", $roll, $langvars['l_chm_youhitsomemines']);
        echo $langvars['l_chm_youhitsomemines'] . "<br>";
        Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_HIT_MINES, "$roll|$sector");

        // Tell the owner that his mines where hit
        $langvars['l_chm_hehitminesinsector'] = str_replace("[chm_playerinfo_character_name]", $playerinfo['character_name'], $langvars['l_chm_hehitminesinsector']);
        $langvars['l_chm_hehitminesinsector'] = str_replace("[chm_roll]", "$roll", $langvars['l_chm_hehitminesinsector']);
        $langvars['l_chm_hehitminesinsector'] = str_replace("[chm_sector]", $sector, $langvars['l_chm_hehitminesinsector']);
        Tki\SectorDefense::messageDefenseOwner($db, $sector, $langvars['l_chm_hehitminesinsector']);

        // If the player has enough mine deflectors then subtract the ammount and continue
        if ($playerinfo['dev_minedeflector'] >= $roll)
        {
            $langvars['l_chm_youlostminedeflectors'] = str_replace("[chm_roll]", $roll, $langvars['l_chm_youlostminedeflectors']);
            echo $langvars['l_chm_youlostminedeflectors'] . "<br>";
            $result2 = $db->Execute("UPDATE {$db->prefix}ships SET dev_minedeflector = dev_minedeflector - ? WHERE ship_id = ?", array($roll, $playerinfo['ship_id']));
            Tki\Db::logDbErrors($pdo_db, $db, $result2, __LINE__, __FILE__);
        }
        else
        {
            if ($playerinfo['dev_minedeflector'] > 0)
            {
                echo $langvars['l_chm_youlostallminedeflectors'] . "<br>";
            }
            else
            {
                echo $langvars['l_chm_youhadnominedeflectors'] . "<br>";
            }

            // Shields up
            $mines_left = $roll - $playerinfo['dev_minedeflector'];
            $playershields = Tki\CalcLevels::shields($playerinfo['shields'], $level_factor);
            if ($playershields > $playerinfo['ship_energy'])
            {
                $playershields = $playerinfo['ship_energy'];
            }
            if ($playershields >= $mines_left)
            {
                $langvars['l_chm_yourshieldshitforminesdmg'] = str_replace("[chm_mines_left]", $mines_left, $langvars['l_chm_yourshieldshitforminesdmg']);
                echo $langvars['l_chm_yourshieldshitforminesdmg'] . "<br>";

                $result2 = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy = ship_energy - ?, dev_minedeflector = 0 WHERE ship_id = ?", array($mines_left, $playerinfo['ship_id']));
                Tki\Db::logDbErrors($pdo_db, $db, $result2, __LINE__, __FILE__);
                if ($playershields == $mines_left)
                {
                    echo $langvars['l_chm_yourshieldsaredown'] . "<br>";
                }
            }
            else
            {
                // Direct hit
                echo $langvars['l_chm_youlostallyourshields'] . "<br>";
                $mines_left = $mines_left - $playershields;
                if ($playerinfo['armor_pts'] >= $mines_left)
                {
                    $langvars['l_chm_yourarmorhitforminesdmg'] = str_replace("[chm_mines_left]", $mines_left, $langvars['l_chm_yourarmorhitforminesdmg']);
                    echo $langvars['l_chm_yourarmorhitforminesdmg'] . "<br>";
                    $result2 = $db->Execute("UPDATE {$db->prefix}ships SET armor_pts = armor_pts - ?, ship_energy = 0, dev_minedeflector = 0 WHERE ship_id = ?", array($mines_left, $playerinfo['ship_id']));
                    Tki\Db::logDbErrors($pdo_db, $db, $result2, __LINE__, __FILE__);
                    if ($playerinfo['armor_pts'] == $mines_left)
                    {
                        echo $langvars['l_chm_yourhullisbreached'] . "<br>";
                    }
                }
                else
                {
                    // BOOM
                    $pod = $playerinfo['dev_escapepod'];
                    Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_SHIP_DESTROYED_MINES, "$sector|$pod");
                    $langvars['l_chm_hewasdestroyedbyyourmines'] = str_replace("[chm_playerinfo_character_name]", $playerinfo['character_name'], $langvars['l_chm_hewasdestroyedbyyourmines']);
                    $langvars['l_chm_hewasdestroyedbyyourmines'] = str_replace("[chm_sector]", $sector, $langvars['l_chm_hewasdestroyedbyyourmines']);
                    Tki\SectorDefense::messageDefenseOwner($db, $sector, $langvars['l_chm_hewasdestroyedbyyourmines']);
                    echo $langvars['l_chm_yourshiphasbeendestroyed'] . "<br><br>";

                    // Survival
                    if ($playerinfo['dev_escapepod'] == "Y")
                    {
                        $rating = round($playerinfo['rating'] / 2);
                        echo $langvars['l_chm_luckescapepod'] . "<br><br>";
                        $resx = $db->Execute("UPDATE {$db->prefix}ships SET hull=0, engines=0, power=0, sensors=0, computer=0, beams=0, torp_launchers=0, torps=0, armor=0, armor_pts=100, cloak=0, shields=0, sector=0, ship_organics=0, ship_ore=0, ship_goods=0, ship_energy=?, ship_colonists=0, ship_fighters=100, dev_warpedit=0, dev_genesis=0, dev_beacon=0, dev_emerwarp=0, dev_escapepod='N', dev_fuelscoop='N', dev_minedeflector=0, on_planet='N', rating=?, cleared_defences=' ', dev_lssd='N' WHERE ship_id=?", array($tkireg->start_energy, $rating, $playerinfo['ship_id']));
                        Tki\Db::logDbErrors($pdo_db, $db, $resx, __LINE__, __FILE__);
                        Tki\Bounty::cancel($pdo_db, $db, $playerinfo['ship_id']);
                    }
                    else
                    {
                        // Or they lose!
                        Tki\Bounty::cancel($pdo_db, $db, $playerinfo['ship_id']);
                        Tki\Character::kill($pdo_db, $db, $playerinfo['ship_id'], $langvars, $tkireg, false);
                    }
                }
            }
        }
        Tki\Mines::explode($db, $sector, $roll);
    }
}
