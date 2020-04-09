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
// File: classes/Combat.php

namespace Tki;

class Combat
{
    public static function shipToShip(\PDO $pdo_db, array $langvars, int $ship_id, Reg $tkireg, array $playerinfo, int $attackerbeams, int $attackerfighters, int $attackershields, int $attackertorps, int $attackerarmor, int $attackertorpdamage): void
    {
        $sql = "SELECT * FROM ::prefix::ships WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
        $stmt->execute();
        $targetinfo = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        // Future: Handle a bad return for targetinfo

        echo "<br><br>-=-=-=-=-=-=-=--<br>
        " . $langvars['l_cmb_startingstats'] . ":<br>
        <br>
        " . $langvars['l_cmb_statattackerbeams'] . ": $attackerbeams<br>
        " . $langvars['l_cmb_statattackerfighters'] . ": $attackerfighters<br>
        " . $langvars['l_cmb_statattackershields'] . ": $attackershields<br>
        " . $langvars['l_cmb_statattackertorps'] . ": $attackertorps<br>
        " . $langvars['l_cmb_statattackerarmor'] . ": $attackerarmor<br>
        " . $langvars['l_cmb_statattackertorpdamage'] . ": $attackertorpdamage<br>";

        $targetbeams = \Tki\CalcLevels::abstractLevels($targetinfo['beams'], $tkireg);
        if ($targetbeams > $targetinfo['ship_energy'])
        {
            $targetbeams = $targetinfo['ship_energy'];
        }

        $targetinfo['ship_energy'] = $targetinfo['ship_energy'] - $targetbeams;
        $targetshields = \Tki\CalcLevels::abstractLevels($targetinfo['shields'], $tkireg);
        if ($targetshields > $targetinfo['ship_energy'])
        {
            $targetshields = $targetinfo['ship_energy'];
        }

        $targetinfo['ship_energy'] = $targetinfo['ship_energy'] - $targetshields;
        $targettorpnum = round(pow($tkireg->level_factor, $targetinfo['torp_launchers'])) * 2;
        if ($targettorpnum > $targetinfo['torps'])
        {
            $targettorpnum = $targetinfo['torps'];
        }

        $targettorpdmg = $tkireg->torp_dmg_rate * $targettorpnum;
        $targetarmor = $targetinfo['armor_pts'];
        $targetfighters = $targetinfo['ship_fighters'];
        echo "-->$targetinfo[ship_name] " . $langvars['l_cmb_isattackingyou'] . "<br><br>";
        echo $langvars['l_cmb_beamexchange'] . "<br>";
        if ($targetfighters > 0 && $attackerbeams > 0)
        {
            if ($attackerbeams > round($targetfighters / 2))
            {
                $temp = round($targetfighters / 2);
                $lost = $targetfighters - $temp;
                $targetfighters = $temp;
                $attackerbeams = $attackerbeams - $lost;
                $langvars['l_cmb_beamsdestroy'] = str_replace("[cmb_lost]", (string) $lost, $langvars['l_cmb_beamsdestroy']);
                echo "<-- " . $langvars['l_cmb_beamsdestroy'] . "<br>";
            }
            else
            {
                $targetfighters = $targetfighters - $attackerbeams;
                $langvars['l_cmb_beamsdestroy2']  = str_replace("[cmb_attackerbeams]", (string) $attackerbeams, $langvars['l_cmb_beamsdestroy2']);
                echo "--> " . $langvars['l_cmb_beamsdestroy2'] . "<br>";
                $attackerbeams = 0;
            }
        }
        elseif ($targetfighters > 0 && $attackerbeams < 1)
        {
            echo $langvars['l_cmb_nobeamsareleft'] . "<br>";
        }
        else
        {
            echo $langvars['l_cmb_beamshavenotarget'] . "<br>";
        }

        if ($attackerfighters > 0 && $targetbeams > 0)
        {
            if ($targetbeams > round($attackerfighters / 2))
            {
                $temp = round($attackerfighters / 2);
                $lost = $attackerfighters - $temp;
                $attackerfighters = $temp;
                $targetbeams = $targetbeams - $lost;
                $langvars['l_cmb_fighterdestroyedbybeams'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_fighterdestroyedbybeams']);
                $langvars['l_cmb_fighterdestroyedbybeams'] = str_replace("[cmb_lost]", (string) $lost, $langvars['l_cmb_fighterdestroyedbybeams']);
                echo "--> " . $langvars['l_cmb_fighterdestroyedbybeams'] . "<br>";
            }
            else
            {
                $attackerfighters = $attackerfighters - $targetbeams;
                $langvars['l_cmb_beamsdestroystillhave'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_beamsdestroystillhave']);
                $langvars['l_cmb_beamsdestroystillhave'] = str_replace("[cmb_targetbeams]", (string) $targetbeams, $langvars['l_cmb_beamsdestroystillhave']);
                $langvars['l_cmb_beamsdestroystillhave'] = str_replace("[cmb_attackerfighters]", (string) $attackerfighters, $langvars['l_cmb_beamsdestroystillhave']);
                echo "<-- " . $langvars['l_cmb_beamsdestroystillhave'] . "<br>";
                $targetbeams = 0;
            }
        }
        elseif ($attackerfighters > 0 && $targetbeams < 1)
        {
            echo $langvars['l_cmb_fighterunhindered'] . "<br>";
        }
        else
        {
            echo $langvars['l_cmb_youhavenofightersleft'] . "<br>";
        }

        if ($attackerbeams > 0)
        {
            if ($attackerbeams > $targetshields)
            {
                $attackerbeams = $attackerbeams - $targetshields;
                $langvars['l_cmb_breachedsomeshields'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_breachedsomeshields']);
                echo "<-- " . $langvars['l_cmb_breachedsomeshields'] . "<br>";
            }
            else
            {
                $langvars['l_cmb_shieldsarehitbybeams'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_shieldsarehitbybeams']);
                $langvars['l_cmb_shieldsarehitbybeams'] = str_replace("[cmb_attackerbeams]", (string) $attackerbeams, $langvars['l_cmb_shieldsarehitbybeams']);
                echo $langvars['l_cmb_shieldsarehitbybeams'] . "<br>";
                $attackerbeams = 0;
            }
        }
        else
        {
            $langvars['l_cmb_nobeamslefttoattack'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_nobeamslefttoattack']);
            echo $langvars['l_cmb_nobeamslefttoattack'] . "<br>";
        }

        if ($targetbeams > 0)
        {
            if ($targetbeams > $attackershields)
            {
                $targetbeams = $targetbeams - $attackershields;
                $attackershields = 0;
                $langvars['l_cmb_yourshieldsbreachedby'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_yourshieldsbreachedby']);
                echo "--> " . $langvars['l_cmb_yourshieldsbreachedby'] . "<br>";
            }
            else
            {
                $langvars['l_cmb_yourshieldsarehit'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_yourshieldsarehit']);
                $langvars['l_cmb_yourshieldsarehit'] = str_replace("[cmb_targetbeams]", $targetbeams, $langvars['l_cmb_yourshieldsarehit']);
                echo "<-- " . $langvars['l_cmb_yourshieldsarehit'] . "<br>";
                $attackershields = $attackershields - $targetbeams;
                $targetbeams = 0;
            }
        }
        else
        {
            $langvars['l_cmb_hehasnobeamslefttoattack'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_hehasnobeamslefttoattack']);
            echo $langvars['l_cmb_hehasnobeamslefttoattack'] . "<br>";
        }

        if ($attackerbeams > 0)
        {
            if ($attackerbeams > $targetarmor)
            {
                $targetarmor = 0;
                $langvars['l_cmb_yourbeamsbreachedhim'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_yourbeamsbreachedhim']);
                echo "--> " . $langvars['l_cmb_yourbeamsbreachedhim'] . "<br>";
            }
            else
            {
                $targetarmor = $targetarmor - $attackerbeams;
                $langvars['l_cmb_yourbeamshavedonedamage'] = str_replace("[cmb_attackerbeams]", (string) $attackerbeams, $langvars['l_cmb_yourbeamshavedonedamage']);
                $langvars['l_cmb_yourbeamshavedonedamage'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_yourbeamshavedonedamage']);
                echo $langvars['l_cmb_yourbeamshavedonedamage'] . "<br>";
            }
        }
        else
        {
            $langvars['l_cmb_nobeamstoattackarmor'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_nobeamstoattackarmor']);
            echo $langvars['l_cmb_nobeamstoattackarmor'] . "<br>";
        }

        if ($targetbeams > 0)
        {
            if ($targetbeams > $attackerarmor)
            {
                $attackerarmor = 0;
                $langvars['l_cmb_yourarmorbreachedbybeams'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_yourarmorbreachedbybeams']);
                echo "--> " . $langvars['l_cmb_yourarmorbreachedbybeams'] . "<br>";
            }
            else
            {
                $attackerarmor = $attackerarmor - $targetbeams;
                $langvars['l_cmb_yourarmorhitdamaged'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_yourarmorhitdamaged']);
                $langvars['l_cmb_yourarmorhitdamaged'] = str_replace("[cmb_targetbeams]", $targetbeams, $langvars['l_cmb_yourarmorhitdamaged']);
                echo "<-- " . $langvars['l_cmb_yourarmorhitdamaged'] . "<br>";
            }
        }
        else
        {
            $langvars['l_cmb_hehasnobeamslefttoattackyou'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_hehasnobeamslefttoattackyou']);
            echo $langvars['l_cmb_hehasnobeamslefttoattackyou'] . "<br>";
        }

        echo "<br>" . $langvars['l_cmb_torpedoexchange'] . "<br>";
        if ($targetfighters > 0 && $attackertorpdamage > 0)
        {
            if ($attackertorpdamage > round($targetfighters / 2))
            {
                $temp = round($targetfighters / 2);
                $lost = $targetfighters - $temp;
                $targetfighters = $temp;
                $attackertorpdamage = $attackertorpdamage - $lost;
                $langvars['l_cmb_yourtorpsdestroy'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_yourtorpsdestroy']);
                $langvars['l_cmb_yourtorpsdestroy'] = str_replace("[cmb_lost]", (string) $lost, $langvars['l_cmb_yourtorpsdestroy']);
                echo "--> " . $langvars['l_cmb_yourtorpsdestroy'] . "<br>";
            }
            else
            {
                $targetfighters = $targetfighters - $attackertorpdamage;
                $langvars['l_cmb_yourtorpsdestroy2'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_yourtorpsdestroy2']);
                $langvars['l_cmb_yourtorpsdestroy2'] = str_replace("[cmb_attackertorpdamage]", (string) $attackertorpdamage, $langvars['l_cmb_yourtorpsdestroy2']);
                echo "<-- " . $langvars['l_cmb_yourtorpsdestroy2'] . "<br>";
                $attackertorpdamage = 0;
            }
        }
        elseif ($targetfighters > 0 && $attackertorpdamage < 1)
        {
            $langvars['l_cmb_youhavenotorpsleft'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_youhavenotorpsleft']);
            echo $langvars['l_cmb_youhavenotorpsleft'] . "<br>";
        }
        else
        {
            $langvars['l_cmb_hehasnofighterleft'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_hehasnofighterleft']);
            echo $langvars['l_cmb_hehasnofighterleft'] . "<br>";
        }

        if ($attackerfighters > 0 && $targettorpdmg > 0)
        {
            if ($targettorpdmg > round($attackerfighters / 2))
            {
                $temp = round($attackerfighters / 2);
                $lost = $attackerfighters - $temp;
                $attackerfighters = $temp;
                $targettorpdmg = $targettorpdmg - $lost;
                $langvars['l_cmb_torpsdestroyyou'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_torpsdestroyyou']);
                $langvars['l_cmb_torpsdestroyyou'] = str_replace("[cmb_lost]", (string) $lost, $langvars['l_cmb_torpsdestroyyou']);
                echo "--> " . $langvars['l_cmb_torpsdestroyyou'] . "<br>";
            }
            else
            {
                $attackerfighters = $attackerfighters - $targettorpdmg;
                $langvars['l_cmb_someonedestroyedfighters'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_someonedestroyedfighters']);
                $langvars['l_cmb_someonedestroyedfighters'] = str_replace("[cmb_targettorpdmg]", (string) $targettorpdmg, $langvars['l_cmb_someonedestroyedfighters']);
                echo "<-- " . $langvars['l_cmb_someonedestroyedfighters'] . "<br>";
                $targettorpdmg = 0;
            }
        }
        elseif ($attackerfighters > 0 && $targettorpdmg < 1)
        {
            $langvars['l_cmb_hehasnotorpsleftforyou'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_hehasnotorpsleftforyou']);
            echo $langvars['l_cmb_hehasnotorpsleftforyou'] . "<br>";
        }
        else
        {
            $langvars['l_cmb_youhavenofightersanymore'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_youhavenofightersanymore']);
            echo $langvars['l_cmb_youhavenofightersanymore'] . "<br>";
        }

        if ($attackertorpdamage > 0)
        {
            if ($attackertorpdamage > $targetarmor)
            {
                $targetarmor = 0;
                $langvars['l_cmb_youbreachedwithtorps'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_youbreachedwithtorps']);
                echo "--> " . $langvars['l_cmb_youbreachedwithtorps'] . "<br>";
            }
            else
            {
                $targetarmor = $targetarmor - $attackertorpdamage;
                $langvars['l_cmb_hisarmorishitbytorps'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_hisarmorishitbytorps']);
                $langvars['l_cmb_hisarmorishitbytorps'] = str_replace("[cmb_attackertorpdamage]", (string) $attackertorpdamage, $langvars['l_cmb_hisarmorishitbytorps']);
                echo "<-- " . $langvars['l_cmb_hisarmorishitbytorps'] . "<br>";
            }
        }
        else
        {
            $langvars['l_cmb_notorpslefttoattackarmor'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_notorpslefttoattackarmor']);
            echo $langvars['l_cmb_notorpslefttoattackarmor'] . "<br>";
        }

        if ($targettorpdmg > 0)
        {
            if ($targettorpdmg > $attackerarmor)
            {
                $attackerarmor = 0;
                $langvars['l_cmb_yourarmorbreachedbytorps'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_yourarmorbreachedbytorps']);
                echo "<-- " . $langvars['l_cmb_yourarmorbreachedbytorps'] . "<br>";
            }
            else
            {
                $attackerarmor = $attackerarmor - $targettorpdmg;
                $langvars['l_cmb_yourarmorhitdmgtorps'] = str_replace("[cmb_targettorpdmg]", (string) $targettorpdmg, $langvars['l_cmb_yourarmorhitdmgtorps']);
                $langvars['l_cmb_yourarmorhitdmgtorps'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_yourarmorhitdmgtorps']);
                echo "<-- " . $langvars['l_cmb_yourarmorhitdmgtorps'] . "<br>";
            }
        }
        else
        {
            $langvars['l_cmb_hehasnotorpsforyourarmor'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_hehasnotorpsforyourarmor']);
            echo $langvars['l_cmb_hehasnotorpsforyourarmor'] . "<br>";
        }

        echo "<br>" . $langvars['l_cmb_fightersattackexchange'] . "<br>";
        if ($attackerfighters > 0 && $targetfighters > 0)
        {
            if ($attackerfighters > $targetfighters)
            {
                $langvars['l_cmb_enemylostallfighters'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_enemylostallfighters']);
                echo "--> " . $langvars['l_cmb_enemylostallfighters'] . "<br>";
                $temptargfighters = 0;
            }
            else
            {
                $langvars['l_cmb_helostsomefighters'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_helostsomefighters']);
                $langvars['l_cmb_helostsomefighters'] = str_replace("[cmb_attackerfighters]", (string) $attackerfighters, $langvars['l_cmb_helostsomefighters']);
                echo $langvars['l_cmb_helostsomefighters'] . "<br>";
                $temptargfighters = $targetfighters - $attackerfighters;
            }

            if ($targetfighters > $attackerfighters)
            {
                echo "<-- " . $langvars['l_cmb_youlostallfighters'] . "<br>";
                $tempplayfighters = 0;
            }
            else
            {
                $langvars['l_cmb_youalsolostsomefighters'] = str_replace("[cmb_targetfighters]", (string) $targetfighters, $langvars['l_cmb_youalsolostsomefighters']);
                echo "<-- " . $langvars['l_cmb_youalsolostsomefighters'] . "<br>";
                $tempplayfighters = $attackerfighters - $targetfighters;
            }

            $attackerfighters = $tempplayfighters;
            $targetfighters = $temptargfighters;
        }
        elseif ($attackerfighters > 0 && $targetfighters < 1)
        {
            $langvars['l_cmb_hehasnofightersleftattack'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_hehasnofightersleftattack']);
            echo $langvars['l_cmb_hehasnofightersleftattack'] . "<br>";
        }
        else
        {
            $langvars['l_cmb_younofightersattackleft'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_younofightersattackleft']);
            echo $langvars['l_cmb_younofightersattackleft'] . "<br>";
        }

        if ($attackerfighters > 0)
        {
            if ($attackerfighters > $targetarmor)
            {
                $targetarmor = 0;
                $langvars['l_cmb_youbreachedarmorwithfighters'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_youbreachedarmorwithfighters']);
                echo "--> " . $langvars['l_cmb_youbreachedarmorwithfighters'] . "<br>";
            }
            else
            {
                $targetarmor = $targetarmor - $attackerfighters;
                $langvars['l_cmb_youhitarmordmgfighters'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_youhitarmordmgfighters']);
                $langvars['l_cmb_youhitarmordmgfighters'] = str_replace("[cmb_attackerfighters]", (string) $attackerfighters, $langvars['l_cmb_youhitarmordmgfighters']);
                echo "<-- " . $langvars['l_cmb_youhitarmordmgfighters'] . "<br>";
            }
        }
        else
        {
            $langvars['l_cmb_youhavenofighterstoarmor'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_youhavenofighterstoarmor']);
            echo $langvars['l_cmb_youhavenofighterstoarmor'] . "<br>";
        }

        if ($targetfighters > 0)
        {
            if ($targetfighters > $attackerarmor)
            {
                $attackerarmor = 0;
                $langvars['l_cmb_hasbreachedarmorfighters'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_hasbreachedarmorfighters']);
                echo "<-- " . $langvars['l_cmb_hasbreachedarmorfighters'] . "<br>";
            }
            else
            {
                $attackerarmor = $attackerarmor - $targetfighters;
                $langvars['l_cmb_yourarmorishitfordmgby'] = str_replace("[cmb_targetfighters]", $targetfighters, $langvars['l_cmb_yourarmorishitfordmgby']);
                $langvars['l_cmb_yourarmorishitfordmgby'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_yourarmorishitfordmgby']);
                echo "--> " . $langvars['l_cmb_yourarmorishitfordmgby'] . "<br>";
            }
        }
        else
        {
            $langvars['l_cmb_nofightersleftheforyourarmor'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_nofightersleftheforyourarmor']);
            echo $langvars['l_cmb_nofightersleftheforyourarmor'] . "<br>";
        }

        if ($targetarmor < 1)
        {
            $langvars['l_cmb_hehasbeendestroyed'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_hehasbeendestroyed']);
            echo "<br>" . $langvars['l_cmb_hehasbeendestroyed'] . "<br>";
            if ($attackerarmor > 0)
            {
                $rating_change = round($targetinfo['rating'] * $tkireg->rating_combat_factor);
                $free_ore = round($targetinfo['ship_ore'] / 2);
                $free_organics = round($targetinfo['ship_organics'] / 2);
                $free_goods = round($targetinfo['ship_goods'] / 2);
                $free_holds = \Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
                $salv_goods = 0;
                if ($free_holds > $free_goods)
                {
                    $salv_goods = $free_goods;
                    $free_holds = $free_holds - $free_goods;
                }
                elseif ($free_holds > 0)
                {
                    $salv_goods = $free_holds;
                    $free_holds = 0;
                }

                $salv_ore = 0;
                if ($free_holds > $free_ore)
                {
                    $salv_ore = $free_ore;
                    $free_holds = $free_holds - $free_ore;
                }
                elseif ($free_holds > 0)
                {
                    $salv_ore = $free_holds;
                    $free_holds = 0;
                }

                $salv_organics = 0;
                if ($free_holds > $free_organics)
                {
                    $salv_organics = $free_organics;
                }
                elseif ($free_holds > 0)
                {
                    $salv_organics = $free_holds;
                }

                $ship_value = $tkireg->upgrade_cost * (round(pow($tkireg->upgrade_factor, $targetinfo['hull'])) + round(pow($tkireg->upgrade_factor, $targetinfo['engines'])) + round(pow($tkireg->upgrade_factor, $targetinfo['power'])) + round(pow($tkireg->upgrade_factor, $targetinfo['computer'])) + round(pow($tkireg->upgrade_factor, $targetinfo['sensors'])) + round(pow($tkireg->upgrade_factor, $targetinfo['beams'])) + round(pow($tkireg->upgrade_factor, $targetinfo['torp_launchers'])) + round(pow($tkireg->upgrade_factor, $targetinfo['shields'])) + round(pow($tkireg->upgrade_factor, $targetinfo['armor'])) + round(pow($tkireg->upgrade_factor, $targetinfo['cloak'])));
                $ship_salvage_rate = random_int(10, 20);
                $ship_salvage = $ship_value * $ship_salvage_rate / 100;
                $langvars['l_cmb_yousalvaged'] = str_replace("[cmb_salv_ore]", (string) $salv_ore, $langvars['l_cmb_yousalvaged']);
                $langvars['l_cmb_yousalvaged'] = str_replace("[cmb_salv_organics]", (string) $salv_organics, $langvars['l_cmb_yousalvaged']);
                $langvars['l_cmb_yousalvaged'] = str_replace("[cmb_salv_goods]", (string) $salv_goods, $langvars['l_cmb_yousalvaged']);
                $langvars['l_cmb_yousalvaged'] = str_replace("[cmb_salvage_rate]", (string) $ship_salvage_rate, $langvars['l_cmb_yousalvaged']);
                $langvars['l_cmb_yousalvaged'] = str_replace("[cmb_salvage]", (string) $ship_salvage, $langvars['l_cmb_yousalvaged']);
                $langvars['l_cmb_yousalvaged2'] = str_replace("[cmb_number_rating_change]", number_format(abs($rating_change), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_cmb_yousalvaged2']);
                echo $langvars['l_cmb_yousalvaged'] . "<br>" . $langvars['l_cmb_yousalvaged2'];

                $sql = "UPDATE ::prefix::ships SET ship_ore=ship_ore+:salv_ore, ship_organics=ship_organics+:salv_organics, ship_goods=ship_goods+:salv_goods WHERE ship_id = :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':salv_ore', $salv_ore, \PDO::PARAM_INT);
                $stmt->bindParam(':salv_organics', $salv_organics, \PDO::PARAM_INT);
                $stmt->bindParam(':salv_goods', $salv_goods, \PDO::PARAM_INT);
                $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                $stmt->execute();
            }

            if ($targetinfo['dev_escapepod'] == "Y")
            {
                $rating = round($targetinfo['rating'] / 2);
                echo $langvars['l_cmb_escapepodlaunched'] . "<br><br>";
                echo "<br><br>ship_id = $targetinfo[ship_id]<br><br>";

                $sql = "UPDATE ::prefix::ships SET hull = 0, engines = 0, power = 0, " .
                       "sensors = 0, computer = 0, beams = 0, torp_launchers = 0, " .
                       "torps = 0, armor = 0, armor_pts = 100, cloak = 0, shields = 0, " .
                       "sector = 1, ship_organics = 0, ship_ore = 0, " .
                       "ship_goods = 0, ship_energy = 100, ship_colonists = 0, " .
                       "ship_fighters = 100, dev_warpedit = 0, dev_genesis = 0, " .
                       "dev_beacon = 0, dev_emerwarp = 0, dev_escapepod = 'N', " .
                       "dev_fuelscoop = 'N', dev_minedeflector = 0, on_planet = 'N', " .
                       "rating = :rating, dev_lssd='N' WHERE ship_id = :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':rating', $rating, \PDO::PARAM_INT);
                $stmt->bindParam(':ship_id', $targetinfo['ship_id'], \PDO::PARAM_INT);
                $update = $stmt->execute();
                \Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);
                \Tki\PlayerLog::writeLog($pdo_db, $targetinfo['ship_id'], LogEnums::ATTACK_LOSE, "$playerinfo[character_name]|Y");
                \Tki\Bounty::collect($pdo_db, $langvars, $playerinfo['ship_id'], $targetinfo['ship_id']);
            }
            else
            {
                \Tki\PlayerLog::writeLog($pdo_db, $targetinfo['ship_id'], LogEnums::ATTACK_LOSE, "$playerinfo[character_name]|N");
                $character_object = new Character();
                $character_object->kill($pdo_db, $targetinfo['ship_id'], $langvars, $tkireg, false);
                \Tki\Bounty::collect($pdo_db, $langvars, $playerinfo['ship_id'], $targetinfo['ship_id']);
            }
        }
        else
        {
            $langvars['l_cmb_youdidntdestroyhim'] = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $langvars['l_cmb_youdidntdestroyhim']);
            echo $langvars['l_cmb_youdidntdestroyhim'] . "<br>";
            $target_armor_lost = $targetinfo['armor_pts'] - $targetarmor;
            $target_fighters_lost = $targetinfo['ship_fighters'] - $targetfighters;
            $target_energy = $targetinfo['ship_energy'];
            \Tki\PlayerLog::writeLog($pdo_db, $targetinfo['ship_id'], LogEnums::ATTACKED_WIN, "$playerinfo[character_name]|$target_armor_lost|$target_fighters_lost");

            $sql = "UPDATE ::prefix::ships SET ship_energy = :target_energy, " .
                   "ship_fighters = ship_fighters - :target_fighters_lost, " .
                   "armor_pts = armor_pts - :target_armor_lost, " .
                   "torps = torps - :target_torp_num WHERE ship_id = :ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':target_energy', $target_energy, \PDO::PARAM_INT);
            $stmt->bindParam(':target_fighters_lost', $target_fighters_lost, \PDO::PARAM_INT);
            $stmt->bindParam(':target_armor_lost', $target_armor_lost, \PDO::PARAM_INT);
            $stmt->bindParam(':target_torp_num', $targettorpnum, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $targetinfo['ship_id'], \PDO::PARAM_INT);
            $update = $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);
        }

        echo "<br>_+_+_+_+_+_+_<br>";
        echo $langvars['l_cmb_shiptoshipcombatstats'] . "<br>";
        echo $langvars['l_cmb_statattackerbeams'] . ": $attackerbeams<br>";
        echo $langvars['l_cmb_statattackerfighters'] . ": $attackerfighters<br>";
        echo $langvars['l_cmb_statattackershields'] . ": $attackershields<br>";
        echo $langvars['l_cmb_statattackertorps'] . ": $attackertorps<br>";
        echo $langvars['l_cmb_statattackerarmor'] . ": $attackerarmor<br>";
        echo $langvars['l_cmb_statattackertorpdamage'] . ": $attackertorpdamage<br>";
        echo "_+_+_+_+_+_+<br>";
    }
}
