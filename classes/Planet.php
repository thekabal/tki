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
// File: classes/Planet.php

namespace Tki;

class Planet
{
    public static function getOwner(\PDO $pdo_db, int $planet_id, &$owner_info): bool
    {
        $owner_info = null;
        if (($planet_id !== null) && is_numeric($planet_id) && $planet_id > 0)
        {
            $sql  = "SELECT ship_id, character_name, team FROM ::prefix::planets ";
            $sql .= "LEFT JOIN ::prefix::ships ON ::prefix::ships.ship_id = ::prefix::planets.owner ";
            $sql .= "WHERE ::prefix::planets.planet_id = :planet_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
            $result = $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);

            if ($result !== null)
            {
                $owner_info = (array) $stmt->fetch(\PDO::FETCH_ASSOC);
                return true;
            }
        }

        return false;
    }

    public static function bombing(
        \PDO $pdo_db,
        string $lang,
        array $langvars,
        Reg $tkireg,
        array $playerinfo,
        array $ownerinfo,
        array $planetinfo,
        Smarty $template
    ): void
    {
        if ($playerinfo['turns'] < 1)
        {
            echo $langvars['l_cmb_atleastoneturn'] . "<br><br>";
            \Tki\Text::gotoMain($pdo_db, $lang);

            $footer = new \Tki\Footer;
            $footer->display($pdo_db, $lang, $tkireg, $template);
            throw new \Exception();
        }

        echo $langvars['l_bombsaway'] . "<br><br>\n";
        $planetfighterslost = 0;
        $attackerfitscapacity = \Tki\CalcLevels::fighters($playerinfo['computer'], $tkireg);
        $ownerfightercapacity = \Tki\CalcLevels::fighters($ownerinfo['computer'], $tkireg);

        $planettorps = \Tki\CalcLevels::planetTorps($pdo_db, $ownerinfo, $planetinfo, $tkireg);
        $planetbeams = \Tki\CalcLevels::planetBeams($pdo_db, $ownerinfo, $tkireg->base_defense, $planetinfo);

        $planetfighters = $planetinfo['fighters'];
        $attackerfighters = $playerinfo['ship_fighters'];

        if ($ownerfightercapacity / $attackerfitscapacity < 1)
        {
            echo $langvars['l_bigfigs'] . "<br><br>\n";
        }

        if ($planetbeams <= $attackerfighters)
        {
            $attackerfighterslost = $planetbeams;
            $beamsused = $planetbeams;
        }
        else
        {
            $attackerfighterslost = $attackerfighters;
            $beamsused = $attackerfighters;
        }

        if ($attackerfighters <= $attackerfighterslost)
        {
            echo $langvars['l_bigbeams'] . "<br>\n";
        }
        else
        {
            $attackerfighterslost += $planettorps * $tkireg->torp_dmg_rate;

            if ($attackerfighters <= $attackerfighterslost)
            {
                echo $langvars['l_bigtorps'] . "<br>\n";
            }
            else
            {
                echo $langvars['l_strafesuccess'] . "<br>\n";
                if ($ownerfightercapacity / $attackerfitscapacity > 1)
                {
                    $planetfighterslost = $attackerfighters - $attackerfighterslost;
                }
                else
                {
                    $planetfighterslost = round(
                    ($attackerfighters - $attackerfighterslost) * $ownerfightercapacity / $attackerfitscapacity);
                }

                if ($planetfighterslost > $planetfighters)
                {
                    $planetfighterslost = $planetfighters;
                }
            }
        }

        echo "<br><br>\n";
        \Tki\PlayerLog::writeLog(
            $pdo_db,
            $ownerinfo['ship_id'],
            LogEnums::PLANET_BOMBED,
            "$planetinfo[name]|$playerinfo[sector]" .
            "|$playerinfo[character_name]|$beamsused|" .
            "$planettorps|$planetfighterslost"
        );

        $stmt = $pdo_db->prepare("UPDATE ::prefix::ships SET turns = turns - 1, " .
                                 "turns_used = turns_used + 1, " .
                                 "ship_fighters = ship_fighters - :ship_fighters WHERE " .
                                 "ship_id=:ship_id");
        $stmt->bindParam(':ship_fighters', $attackerfighters, \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $result = $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);

        $stmt = $pdo_db->prepare("UPDATE ::prefix::planets SET energy = energy - " .
                                 ":energy, fighters = fighters - :fighters, " .
                                 "torps = torps - :torps WHERE planet_id=:planet_id");
        $stmt->bindParam(':energy', $beamsused, \PDO::PARAM_INT);
        $stmt->bindParam(':fighters', $planetfighterslost, \PDO::PARAM_INT);
        $stmt->bindParam(':torps', $planettorps, \PDO::PARAM_INT);
        $stmt->bindParam(':planet_id', $planetinfo['planet_id'], \PDO::PARAM_INT);
        $result = $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);
    }
}
