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
// File: classes/PlanetCombat.php

namespace Tki;

class PlanetCombat
{
    public static function prime(\PDO $pdo_db, $db, string $lang, array $langvars, Reg $tkireg, Smarty $template, array $playerinfo, array $ownerinfo, array $planetinfo): void
    {
        if ($playerinfo['turns'] < 1)
        {
            echo $langvars['l_cmb_atleastoneturn'] . "<br><br>";
            \Tki\Text::gotoMain($pdo_db, $lang);

            $footer = new \Tki\Footer;
            $footer->display($pdo_db, $lang, $tkireg, $template);
            die();
        }

        // Planetary defense system calculation
        $planetbeams        = \Tki\CalcLevels::planetBeams($pdo_db, $ownerinfo, $tkireg->base_defense, $planetinfo);
        $planetfighters     = $planetinfo['fighters'];
        $planetshields      = \Tki\CalcLevels::planetShields($pdo_db, $ownerinfo, $tkireg->base_defense, $planetinfo);
        $planettorps        = \Tki\CalcLevels::planetTorps($pdo_db, $ownerinfo, $planetinfo, $tkireg);

        // Attacking ship calculations

        $attackerbeams      = \Tki\CalcLevels::beams($playerinfo['beams'], $tkireg);
        $attackerfighters   = $playerinfo['ship_fighters'];
        $attackershields    = \Tki\CalcLevels::shields($playerinfo['shields'], $tkireg);
        $attackertorps      = round(pow($tkireg->level_factor, $playerinfo['torp_launchers'])) * 2;
        $attackerarmor      = $playerinfo['armor_pts'];

        // Beams
        if ($attackerbeams > $playerinfo['ship_energy'])
        {
            $attackerbeams = $playerinfo['ship_energy'];
        }

        $playerinfo['ship_energy'] = $playerinfo['ship_energy'] - $attackerbeams;

        // Shields
        if ($attackershields > $playerinfo['ship_energy'])
        {
            $attackershields = $playerinfo['ship_energy'];
        }

        $playerinfo['ship_energy'] = $playerinfo['ship_energy'] - $attackershields;

        // Torpedos
        if ($attackertorps > $playerinfo['torps'])
        {
            $attackertorps = $playerinfo['torps'];
        }

        $playerinfo['torps'] = $playerinfo['torps'] - $attackertorps;

        // Setup torp damage rate for both Planet and Ship
        $planettorpdamage   = $tkireg->torp_dmg_rate * $planettorps;
        $attackertorpdamage = $tkireg->torp_dmg_rate * $attackertorps;

        echo "
        <center>
        <hr>
        <table width='75%' border='0'>
        <tr align='center'>
        <td width='9%' height='27'></td>
        <td width='12%' height='27'><font color='white'>" . $langvars['l_cmb_beams'] . "</font></td>
        <td width='17%' height='27'><font color='white'>" . $langvars['l_cmb_fighters'] . "</font></td>
        <td width='18%' height='27'><font color='white'>" . $langvars['l_cmb_shields'] . "</font></td>
        <td width='11%' height='27'><font color='white'>" . $langvars['l_cmb_torps'] . "</font></td>
        <td width='22%' height='27'><font color='white'>" . $langvars['l_cmb_torpdamage'] . "</font></td>
        <td width='11%' height='27'><font color='white'>" . $langvars['l_cmb_armor'] . "</font></td>
        </tr>
        <tr align='center'>
        <td width='9%'> <font color='red'>" . $langvars['l_cmb_you'] . "</td>
        <td width='12%'><font color='red'><strong>" . $attackerbeams . "</strong></font></td>
        <td width='17%'><font color='red'><strong>" . $attackerfighters . "</strong></font></td>
        <td width='18%'><font color='red'><strong>" . $attackershields . "</strong></font></td>
        <td width='11%'><font color='red'><strong>" . $attackertorps . "</strong></font></td>
        <td width='22%'><font color='red'><strong>" . $attackertorpdamage . "</strong></font></td>
        <td width='11%'><font color='red'><strong>" . $attackerarmor . "</strong></font></td>
        </tr>
        <tr align='center'>
        <td width='9%'> <font color='#6098F8'>" . $langvars['l_cmb_planet'] . "</font></td>
        <td width='12%'><font color='#6098F8'><strong>" . $planetbeams . "</strong></font></td>
        <td width='17%'><font color='#6098F8'><strong>" . $planetfighters . "</strong></font></td>
        <td width='18%'><font color='#6098F8'><strong>" . $planetshields . "</strong></font></td>
        <td width='11%'><font color='#6098F8'><strong>" . $planettorps . "</strong></font></td>
        <td width='22%'><font color='#6098F8'><strong>" . $planettorpdamage . "</strong></font></td>
        <td width='11%'><font color='#6098F8'><strong>N/A</strong></font></td>
        </tr>
        </table>
        <hr>
        </center>
        ";

        // Begin actual combat calculations

        echo "<br><center><strong><font size='+2'>" . $langvars['l_cmb_combatflow'] . "</font></strong><br><br>\n";
        echo "<table width='75%' border='0'><tr align='center'><td><font color='red'>" . $langvars['l_cmb_you'] . "</font></td><td><font color='#6098F8'>" . $langvars['l_cmb_defender'] . "</font></td>\n";
        echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_attackingplanet'] . " " . $playerinfo['sector'] . "</strong></font></td><td></td>";
        echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_youfireyourbeams'] . "</strong></font></td><td></td>\n";
        if ($planetfighters > 0 && $attackerbeams > 0)
        {
            if ($attackerbeams > $planetfighters)
            {
                $langvars['l_cmb_defenselost'] = str_replace("[cmb_planetfighters]", $planetfighters, $langvars['l_cmb_defenselost']);
                echo "<tr align='center'><td></td><td><font color='#6098F8'><strong>" . $langvars['l_cmb_defenselost'] . "</strong></font>";
                $attackerbeams = $attackerbeams - $planetfighters;
                $planetfighters = 0;
            }
            else
            {
                $langvars['l_cmb_defenselost2'] = str_replace("[cmb_attackerbeams]", $attackerbeams, $langvars['l_cmb_defenselost2']);
                $planetfighters = $planetfighters - $attackerbeams;
                echo "<tr align='center'><td></td><td><font color='#6098F8'><strong>" . $langvars['l_cmb_defenselost2'] . "</strong></font>";
                $attackerbeams = 0;
            }
        }

        if ($attackerfighters > 0 && $planetbeams > 0)
        {
            // If there are more beams on the planet than attacker has fighters
            if ($planetbeams > round($attackerfighters / 2))
            {
                // Half the attacker fighters
                $temp = round($attackerfighters / 2);
                // Attacker loses half his fighters
                $lost = $attackerfighters - $temp;
                // Set attacker fighters to 1/2 it's original value
                $attackerfighters = $temp;
                // Subtract half the attacker fighters from available planetary beams
                $planetbeams = $planetbeams - $lost;
                $langvars['l_cmb_planetarybeams'] = str_replace("[cmb_temp]", $temp, $langvars['l_cmb_planetarybeams']);
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_planetarybeams']  . "</strong></font><td></td>";
            }
            else
            {
                $langvars['l_cmb_planetarybeams2'] = str_replace("[cmb_planetbeams]", $planetbeams, $langvars['l_cmb_planetarybeams2']);
                $attackerfighters = $attackerfighters - $planetbeams;
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_planetarybeams2'] . "</strong></font><td></td>";
                $planetbeams = 0;
            }
        }

        if ($attackerbeams > 0)
        {
            if ($attackerbeams > $planetshields)
            {
                $attackerbeams = $attackerbeams - $planetshields;
                $planetshields = 0;
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_youdestroyedplanetshields'] . "</font></strong><td></td>";
            }
            else
            {
                $langvars['l_cmb_beamsexhausted'] = str_replace("[cmb_attackerbeams]", $attackerbeams, $langvars['l_cmb_beamsexhausted']);
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_beamsexhausted'] . "</font></strong><td></td>";
                $planetshields = $planetshields - $attackerbeams;
                $attackerbeams = 0;
            }
        }

        if ($planetbeams > 0)
        {
            if ($planetbeams > $attackershields)
            {
                $planetbeams = $planetbeams - $attackershields;
                $attackershields = 0;
                echo "<tr align='center'><td></td><td><font color='#6098F8'><strong>" . $langvars['l_cmb_breachedyourshields'] . "</font></strong></td>";
            }
            else
            {
                $attackershields = $attackershields - $planetbeams;
                $langvars['l_cmb_destroyedyourshields'] = str_replace("[cmb_planetbeams]", $planetbeams, $langvars['l_cmb_destroyedyourshields']);
                echo "<tr align='center'><td></td><font color='#6098F8'><strong>" . $langvars['l_cmb_destroyedyourshields'] . "</font></strong></td>";
                $planetbeams = 0;
            }
        }

        if ($planetbeams > 0)
        {
            if ($planetbeams > $attackerarmor)
            {
                $attackerarmor = 0;
                echo "<tr align='center'><td></td><td><font color='#6098F8'><strong>" . $langvars['l_cmb_breachedyourarmor'] . "</strong></font></td>";
            }
            else
            {
                $attackerarmor = $attackerarmor - $planetbeams;
                $langvars['l_cmb_destroyedyourarmor'] = str_replace("[cmb_planetbeams]", $planetbeams, $langvars['l_cmb_destroyedyourarmor']);
                echo "<tr align='center'><td></td><td><font color='#6098F8'><strong>" . $langvars['l_cmb_destroyedyourarmor'] . "</font></strong></td>";
            }
        }

        echo "<tr align='center'><td><font color='YELLOW'><strong>" . $langvars['l_cmb_torpedoexchangephase'] . "</strong></font></td><td><strong><font color='YELLOW'>" . $langvars['l_cmb_torpedoexchangephase'] . "</strong></font></td><br>";
        if ($planetfighters > 0 && $attackertorpdamage > 0)
        {
            if ($attackertorpdamage > $planetfighters)
            {
                $langvars['l_cmb_nofightersleft'] = str_replace("[cmb_planetfighters]", $planetfighters, $langvars['l_cmb_nofightersleft']);
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_nofightersleft'] . "</font></strong></td><td></td>";
                $attackertorpdamage = $attackertorpdamage - $planetfighters;
                $planetfighters = 0;
            }
            else
            {
                $planetfighters = $planetfighters - $attackertorpdamage;
                $langvars['l_cmb_youdestroyfighters'] = str_replace("[cmb_attackertorpdamage]", $attackertorpdamage, $langvars['l_cmb_youdestroyfighters']);
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_youdestroyfighters'] . "</font></strong></td><td></td>";
                $attackertorpdamage = 0;
            }
        }

        if ($attackerfighters > 0 && $planettorpdamage > 0)
        {
            if ($planettorpdamage > round($attackerfighters / 2))
            {
                $temp = round($attackerfighters / 2);
                $lost = $attackerfighters - $temp;
                $attackerfighters = $temp;
                $planettorpdamage = $planettorpdamage - $lost;
                $langvars['l_cmb_planettorpsdestroy'] = str_replace("[cmb_temp]", $temp, $langvars['l_cmb_planettorpsdestroy']);
                echo "<tr align='center'><td></td><td><font color='red'><strong>" . $langvars['l_cmb_planettorpsdestroy'] . "</strong></font></td>";
            }
            else
            {
                $attackerfighters = $attackerfighters - $planettorpdamage;
                $langvars['l_cmb_planettorpsdestroy2'] = str_replace("[cmb_planettorpdamage]", $planettorpdamage, $langvars['l_cmb_planettorpsdestroy2']);
                echo "<tr align='center'><td></td><td><font color='red'><strong>" . $langvars['l_cmb_planettorpsdestroy2'] . "</strong></font></td>";
                $planettorpdamage = 0;
            }
        }

        if ($planettorpdamage > 0)
        {
            if ($planettorpdamage > $attackerarmor)
            {
                $attackerarmor = 0;
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_torpsbreachedyourarmor'] . "</strong></font></td><td></td>";
            }
            else
            {
                $attackerarmor = $attackerarmor - $planettorpdamage;
                $langvars['l_cmb_planettorpsdestroy3'] = str_replace("[cmb_planettorpdamage]", $planettorpdamage, $langvars['l_cmb_planettorpsdestroy3']);
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_planettorpsdestroy3'] . "</strong></font></td><td></td>";
            }
        }

        if ($attackertorpdamage > 0 && $planetfighters > 0)
        {
            $planetfighters = $planetfighters - $attackertorpdamage;
            if ($planetfighters < 0)
            {
                $planetfighters = 0;
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_youdestroyedallfighters'] . "</strong></font></td><td></td>";
            }
            else
            {
                $langvars['l_cmb_youdestroyplanetfighters'] = str_replace("[cmb_attackertorpdamage]", $attackertorpdamage, $langvars['l_cmb_youdestroyplanetfighters']);
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_youdestroyplanetfighters'] . "</strong></font></td><td></td>";
            }
        }

        echo "<tr align='center'><td><font color='YELLOW'><strong>" . $langvars['l_cmb_fightercombatphase'] . "</strong></font></td><td><strong><font color='YELLOW'>" . $langvars['l_cmb_fightercombatphase'] . "</strong></font></td><br>";
        if ($attackerfighters > 0 && $planetfighters > 0)
        {
            if ($attackerfighters > $planetfighters)
            {
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_youdestroyedallfighters2'] . "</strong></font></td><td></td>";
                $tempplanetfighters = 0;
            }
            else
            {
                $langvars['l_cmb_youdestroyplanetfighters2'] = str_replace("[cmb_attackerfighters]", $attackerfighters, $langvars['l_cmb_youdestroyplanetfighters2']);
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_youdestroyplanetfighters2'] . "</strong></font></td><td></td>";
                $tempplanetfighters = $planetfighters - $attackerfighters;
            }

            if ($planetfighters > $attackerfighters)
            {
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_allyourfightersdestroyed'] . "</strong></font></td><td></td>";
                $tempplayfighters = 0;
            }
            else
            {
                $tempplayfighters = $attackerfighters - $planetfighters;
                $langvars['l_cmb_fightertofighterlost'] = str_replace("[cmb_planetfighters]", $planetfighters, $langvars['l_cmb_fightertofighterlost']);
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_fightertofighterlost'] . "</strong></font></td><td></td>";
            }

            $attackerfighters = $tempplayfighters;
            $planetfighters = $tempplanetfighters;
        }

        if ($attackerfighters > 0 && $planetshields > 0)
        {
            if ($attackerfighters > $planetshields)
            {
                $attackerfighters = $attackerfighters - round($planetshields / 2);
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_youbreachedplanetshields'] . "</strong></font></td><td></td>";
                $planetshields = 0;
            }
            else
            {
                $langvars['l_cmb_shieldsremainup'] = str_replace("[cmb_attackerfighters]", $attackerfighters, $langvars['l_cmb_shieldsremainup']);
                echo "<tr align='center'><td></td><font color='#6098F8'><strong>" . $langvars['l_cmb_shieldsremainup'] . "</strong></font></td>";
                $planetshields = $planetshields - $attackerfighters;
            }
        }

        if ($planetfighters > 0)
        {
            if ($planetfighters > $attackerarmor)
            {
                $attackerarmor = 0;
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_fighterswarm'] . "</strong></font></td><td></td>";
            }
            else
            {
                $attackerarmor = $attackerarmor - $planetfighters;
                echo "<tr align='center'><td><font color='red'><strong>" . $langvars['l_cmb_swarmandrepel'] . "</strong></font></td><td></td>";
            }
        }

        echo "</table></center>\n";
        // Send each docked ship in sequence to attack agressor
        $result4 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE planet_id = ? AND on_planet = 'Y'", array($planetinfo['planet_id']));
        \Tki\Db::logDbErrors($pdo_db, $result4, __LINE__, __FILE__);
        $shipsonplanet = $result4->RecordCount();

        if ($shipsonplanet > 0)
        {
            $langvars['l_cmb_shipdock'] = str_replace("[cmb_shipsonplanet]", $shipsonplanet, $langvars['l_cmb_shipdock']);
            echo "<br><br><center>" . $langvars['l_cmb_shipdock'] . "<br>" . $langvars['l_cmb_engshiptoshipcombat'] . "</center><br><br>\n";
            while (!$result4->EOF)
            {
                $onplanet = $result4->fields;

                if ($attackerfighters < 0)
                {
                    $attackerfighters = 0;
                }

                if ($attackertorps < 0)
                {
                    $attackertorps = 0;
                }

                if ($attackershields < 0)
                {
                    $attackershields = 0;
                }

                if ($attackerbeams < 0)
                {
                    $attackerbeams = 0;
                }

                if ($attackerarmor < 1)
                {
                    break;
                }

                echo "<br>-" . $onplanet['ship_name'] . " " . $langvars['l_cmb_approachattackvector'] . "-<br>";
                \Tki\Combat::shipToShip($pdo_db, $langvars, $onplanet['ship_id'], $tkireg, $playerinfo, $attackerbeams, $attackerfighters, $attackershields, $attackertorps, $attackerarmor, $attackertorpdamage);
                $result4->MoveNext();
            }
        }
        else
        {
            echo "<br><br><center>" . $langvars['l_cmb_noshipsdocked'] . "</center><br><br>\n";
        }

        if ($attackerarmor < 1)
        {
            $free_ore = round($playerinfo['ship_ore'] / 2);
            $free_organics = round($playerinfo['ship_organics'] / 2);
            $free_goods = round($playerinfo['ship_goods'] / 2);
            $ship_value = $tkireg->upgrade_cost * (round(pow($tkireg->upgrade_factor, $playerinfo['hull'])) + round(pow($tkireg->upgrade_factor, $playerinfo['engines'])) + round(pow($tkireg->upgrade_factor, $playerinfo['power'])) + round(pow($tkireg->upgrade_factor, $playerinfo['computer'])) + round(pow($tkireg->upgrade_factor, $playerinfo['sensors'])) + round(pow($tkireg->upgrade_factor, $playerinfo['beams'])) + round(pow($tkireg->upgrade_factor, $playerinfo['torp_launchers'])) + round(pow($tkireg->upgrade_factor, $playerinfo['shields'])) + round(pow($tkireg->upgrade_factor, $playerinfo['armor'])) + round(pow($tkireg->upgrade_factor, $playerinfo['cloak'])));
            $ship_salvage_rate = random_int(0, 10);
            $ship_salvage = $ship_value * $ship_salvage_rate / 100;
            echo "<br><center><font size='+2' COLOR='red'><strong>" . $langvars['l_cmb_yourshipdestroyed'] . "</font></strong></center><br>";
            if ($playerinfo['dev_escapepod'] == "Y")
            {
                echo "<center><font color='white'>" . $langvars['l_cmb_escapepod'] . "</font></center><br><br>";
                $sql = "UPDATE ::prefix::ships SET hull=0,engines=0,power=0,sensors=0,computer=0,beams=0," .
                       "torp_launchers=0,torps=0,armor=0,armor_pts=100,cloak=0,shields=0,sector=1,ship_organics=0," .
                       "ship_ore=0,ship_goods=0,ship_energy=100,ship_colonists=0,ship_fighters=100,dev_warpedit=0," .
                       "dev_genesis=0,dev_beacon=0,dev_emerwarp=0,dev_escapepod='N',dev_fuelscoop='N'," .
                       "dev_minedeflector=0,on_planet='N',dev_lssd='N' WHERE ship_id=:ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                $result = $stmt->execute();
                Tki\Db::LogDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                \Tki\Bounty::collect($pdo_db, $langvars, $planetinfo['owner'], $playerinfo['ship_id']);
            }
            else
            {
                $character_object = new Character;
                $character_object->kill($pdo_db, $playerinfo['ship_id'], $langvars, $tkireg, false);
                \Tki\Bounty::collect($pdo_db, $langvars, $planetinfo['owner'], $playerinfo['ship_id']);
            }
        }
        else
        {
            $free_ore = 0;
            $free_goods = 0;
            $free_organics = 0;
            $ship_salvage_rate = 0;
            $ship_salvage = 0;
            $planetrating = $ownerinfo['hull'] + $ownerinfo['engines'] + $ownerinfo['computer'] + $ownerinfo['beams'] + $ownerinfo['torp_launchers'] + $ownerinfo['shields'] + $ownerinfo['armor'];
            if ($ownerinfo['rating'] != 0)
            {
                $rating_change = ($ownerinfo['rating'] / abs($ownerinfo['rating'])) * $planetrating * 10;
            }
            else
            {
                $rating_change = -100;
            }

            echo "<center><br><strong><font size='+2'>" . $langvars['l_cmb_finalcombatstats'] . "</font></strong><br><br>";
            $fighters_lost = $playerinfo['ship_fighters'] - $attackerfighters;
            $langvars['l_cmb_youlostfighters'] = str_replace("[cmb_fighters_lost]", $fighters_lost, $langvars['l_cmb_youlostfighters']);
            $langvars['l_cmb_youlostfighters'] = str_replace("[cmb_playerinfo_ship_fighters]", $playerinfo['ship_fighters'], $langvars['l_cmb_youlostfighters']);
            echo $langvars['l_cmb_youlostfighters'] . "<br>";
            $armor_lost = $playerinfo['armor_pts'] - $attackerarmor;
            $langvars['l_cmb_youlostarmorpoints'] = str_replace("[cmb_armor_lost]", $armor_lost, $langvars['l_cmb_youlostarmorpoints']);
            $langvars['l_cmb_youlostarmorpoints'] = str_replace("[cmb_playerinfo_armor_pts]", $playerinfo['armor_pts'], $langvars['l_cmb_youlostarmorpoints']);
            $langvars['l_cmb_youlostarmorpoints'] = str_replace("[cmb_attackerarmor]", $attackerarmor, $langvars['l_cmb_youlostarmorpoints']);
            echo $langvars['l_cmb_youlostarmorpoints'] . "<br>";
            $energy = $playerinfo['ship_energy'];
            $energy_lost = 100 - $playerinfo['ship_energy'];
            $langvars['l_cmb_energyused'] = str_replace("[cmb_energy_lost]", $energy_lost, $langvars['l_cmb_energyused']);
            $langvars['l_cmb_energyused'] = str_replace("[cmb_playerinfo_ship_energy]", 100, $langvars['l_cmb_energyused']);
            echo $langvars['l_cmb_energyused'] . "<br></center>";

            $sql = "UPDATE ::prefix::ships SET ship_energy=:ship_energy, ship_fighters=ship_fighters-:lost_fighters, " .
                   "torps=torps-:attacker_torps, armor_pts=armor_pts-:armor_lost, rating=rating-:rating_change WHERE ship_id=:ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':ship_energy', $energy, \PDO::PARAM_INT);
            $stmt->bindParam(':lost_fighters', $fighters_lost, \PDO::PARAM_INT);
            $stmt->bindParam(':attacker_torps', $attackertorps, \PDO::PARAM_INT);
            $stmt->bindParam(':armor_lost', $armor_lost, \PDO::PARAM_INT);
            $stmt->bindParam(':rating_change', $rating_change, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $result = $stmt->execute();
            Tki\Db::LogDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        }

        $result4 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE planet_id = ? AND on_planet = 'Y';", array($planetinfo['planet_id']));
        \Tki\Db::logDbErrors($pdo_db, $result4, __LINE__, __FILE__);
        $shipsonplanet = $result4->RecordCount();

        if ($planetshields < 1 && $planetfighters < 1 && $attackerarmor > 0 && $shipsonplanet == 0)
        {
            echo "<br><br><center><font color='GREEN'><strong>" . $langvars['l_cmb_planetdefeated'] . "</strong></font></center><br><br>";

            // Patch to stop players dumping credits for other players.
            $self_tech = \Tki\CalcLevels::avgTech($playerinfo);
            $target_tech = round(\Tki\CalcLevels::avgTech($ownerinfo));

            $roll = random_int(0, (int) $target_tech);
            if ($roll > $self_tech)
            {
                // Reset Planet Assets.
                $sql  = "UPDATE {$db->prefix}planets ";
                $sql .= "SET organics = '0', ore = '0', goods = '0', energy = '0', colonists = '2', credits = '0', fighters = '0', torps = '0', team = '0', base = 'N', sells = 'N', prod_organics = '20', prod_ore = '20', prod_goods = '20', prod_energy = '20', prod_fighters = '10', prod_torp = '10' ";
                $sql .= "WHERE planet_id = ? LIMIT 1;";
                $resx = $db->Execute($sql, array($planetinfo['planet_id']));
                \Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                echo "<div style='text-align:center; font-size:18px; color:#f00;'>The planet become unstable due to not being looked after, and all life and assets have been destroyed.</div>\n";
            }

            if ($tkireg->min_value_capture != 0)
            {
                $playerscore = \Tki\Score::updateScore($pdo_db, $playerinfo['ship_id'], $tkireg, $playerinfo);
                $playerscore *= $playerscore;

                $planetscore = $planetinfo['organics'] * $tkireg->organics_price + $planetinfo['ore'] * $tkireg->ore_price + $planetinfo['goods'] * $tkireg->goods_price + $planetinfo['energy'] * $tkireg->energy_price + $planetinfo['fighters'] * $tkireg->fighter_price + $planetinfo['torps'] * $tkireg->torpedo_price + $planetinfo['colonists'] * $tkireg->colonist_price + $planetinfo['credits'];
                $planetscore = $planetscore * $tkireg->min_value_capture / 100;

                if ($playerscore < $planetscore)
                {
                    echo "<center>" . $langvars['l_cmb_citizenswanttodie'] . "</center><br><br>";
                    $resx = $db->Execute("DELETE FROM {$db->prefix}planets WHERE planet_id = ?;", array($planetinfo['planet_id']));
                    \Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                    \Tki\PlayerLog::writeLog($pdo_db, $ownerinfo['ship_id'], LogEnums::PLANET_DEFEATED_D, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");
                    $admin_log = new AdminLog;
                    $admin_log->writeLog($pdo_db, LogEnums::ADMIN_PLANETDEL, "$playerinfo[character_name]|$ownerinfo[character_name]|$playerinfo[sector]");
                    \Tki\Score::updateScore($pdo_db, $ownerinfo['ship_id'], $tkireg, $playerinfo);
                }
                else
                {
                    $langvars['l_cmb_youmaycapture'] = str_replace("[capture]", "<a href='planet.php?planet_id=". $planetinfo['planet_id'] . "&amp;command=capture'>" . $langvars['l_planet_capture1'] . "</a>", $langvars['l_cmb_youmaycapture']);
                    echo "<center><font color=red>" . $langvars['l_cmb_youmaycapture'] . "</font></center><br><br>";
                    \Tki\PlayerLog::writeLog($pdo_db, $ownerinfo['ship_id'], LogEnums::PLANET_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");
                    \Tki\Score::updateScore($pdo_db, $ownerinfo['ship_id'], $tkireg, $playerinfo);
                    $update7a = $db->Execute("UPDATE {$db->prefix}planets SET owner=0, fighters=0, torps=torps-?, base='N', defeated='Y' WHERE planet_id = ?;", array($planettorps, $planetinfo['planet_id']));
                    \Tki\Db::logDbErrors($pdo_db, $update7a, __LINE__, __FILE__);
                }
            }
            else
            {
                $langvars['l_cmb_youmaycapture'] = str_replace("[capture]", "<a href='planet.php?planet_id=". $planetinfo['planet_id'] . "&amp;command=capture'>" . $langvars['l_planet_capture1'] . "</a>", $langvars['l_cmb_youmaycapture']);
                echo "<center>" . $langvars['l_cmb_youmaycapture'] . "</center><br><br>";
                \Tki\PlayerLog::writeLog($pdo_db, $ownerinfo['ship_id'], LogEnums::PLANET_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");
                \Tki\Score::updateScore($pdo_db, $ownerinfo['ship_id'], $tkireg, $playerinfo);
                $update7a = $db->Execute("UPDATE {$db->prefix}planets SET owner=0,fighters=0, torps=torps-?, base='N', defeated='Y' WHERE planet_id = ?;", array($planettorps, $planetinfo['planet_id']));
                \Tki\Db::logDbErrors($pdo_db, $update7a, __LINE__, __FILE__);
            }

            \Tki\Ownership::calc($pdo_db, $planetinfo['sector_id'], $tkireg->min_bases_to_own, $langvars);
        }
        else
        {
            echo "<br><br><center><font color='#6098F8'><strong>" . $langvars['l_cmb_planetnotdefeated'] . "</strong></font></center><br><br>";
            $fighters_lost = $planetinfo['fighters'] - $planetfighters;
            $langvars['l_cmb_fighterloststat'] = str_replace("[cmb_fighters_lost]", $fighters_lost, $langvars['l_cmb_fighterloststat']);
            $langvars['l_cmb_fighterloststat'] = str_replace("[cmb_planetinfo_fighters]", $planetinfo['fighters'], $langvars['l_cmb_fighterloststat']);
            $langvars['l_cmb_fighterloststat'] = str_replace("[cmb_planetfighters]", $planetfighters, $langvars['l_cmb_fighterloststat']);
            $energy = $planetinfo['energy'];
            \Tki\PlayerLog::writeLog($pdo_db, $ownerinfo['ship_id'], LogEnums::PLANET_NOT_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]|$free_ore|$free_organics|$free_goods|$ship_salvage_rate|$ship_salvage");
            \Tki\Score::updateScore($pdo_db, $ownerinfo['ship_id'], $tkireg, $playerinfo);
            $update7b = $db->Execute(
                "UPDATE {$db->prefix}planets SET energy = ?, fighters = fighters - ?, " .
                "torps = torps - ?, ore = ore + ?, goods = goods + ?, organics = organics + ?, " .
                "credits = credits + ? WHERE planet_id = ?;",
            array($energy, $fighters_lost, $planettorps, $free_ore, $free_goods, $free_organics, $ship_salvage, $planetinfo['planet_id'])
            );
            \Tki\Db::logDbErrors($pdo_db, $update7b, __LINE__, __FILE__);
        }

        $sql = "UPDATE ::prefix::ships SET turns=turns-1, turns_used=turns_used+1 WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $result = $stmt->execute();
        \Tki\Db::LogDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    }
}
