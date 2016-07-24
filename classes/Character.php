<?php
declare(strict_types = 1);
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
// File: classes/Character.php

namespace Tki;

class Character
{
    public static function kill(\PDO $pdo_db, $db, int $ship_id, Array $langvars, Reg $tkireg, $remove_planets = false)
    {
        $sql = "UPDATE {$pdo_db->prefix}ships SET ship_destroyed='Y', on_planet='N', sector=0, cleared_defenses=' ' WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $ship_id);
        $stmt->execute();

        $sql = "DELETE FROM {$pdo_db->prefix}bounty WHERE placed_by = :placed_by";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':placed_by', $ship_id);
        $stmt->execute();

        if ($remove_planets === true && $ship_id > 0)
        {
            $sql = "DELETE FROM {$pdo_db->prefix}planets WHERE owner=:owner";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner', $ship_id);
            $stmt->execute();
        }
        else
        {
            $sql = "UPDATE {$pdo_db->prefix}planets SET owner=0, team=0, fighters=0, base='N' WHERE owner=:owner";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':owner', $ship_id);
            $stmt->execute();
        }

        $sql = "SELECT DISTINCT sector_id FROM {$pdo_db->prefix}planets WHERE owner=:owner AND base='Y'";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':owner', $ship_id);
        $stmt->execute();
        $sectors_owned = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($sectors_owned !== null)
        {
            foreach ($sectors_owned as $tmp_sector)
            {
                Ownership::calc($pdo_db, $db, $tmp_sector, $tkireg->min_bases_to_own, $langvars);
            }
        }

        $sql = "DELETE FROM {$pdo_db->prefix}sector_defense WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':owner', $ship_id);
        $stmt->execute();

        $sql = "SELECT zone_id FROM {$pdo_db->prefix}zones WHERE team_zone='N' AND owner=:owner";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':owner', $ship_id);
        $stmt->execute();
        $zone = $stmt->fetch(\PDO::FETCH_ASSOC);

        $sql = "UPDATE {$pdo_db->prefix}universe SET zone_id=1 WHERE zone_id=:zone_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':zone_id', $zone['zone_id']);
        $stmt->execute();

        $sql = "SELECT character_name FROM {$pdo_db->prefix}ships WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':owner', $ship_id);
        $stmt->execute();
        $name = $stmt->fetch(\PDO::FETCH_ASSOC);

        $headline = $name['character_name'] .' '. $langvars['l_killheadline'];
        $newstext = str_replace('[name]', $name['character_name'], $langvars['l_news_killed']);

        $sql = "INSERT INTO {$pdo_db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (:headline,:newstext,:user_id,NOW(), 'killed')";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':headline', $headline);
        $stmt->bindParam(':newstext', $newstext);
        $stmt->bindParam(':user_id', $ship_id);
        $stmt->execute();
    }

    // Choosing to use a method instead of a property.
    // If we went with a method, and it needed to be changed, we would have to change lots of property->method calls.
    public static function getInsignia(\PDO $pdo_db, $a_username, Array $langvars) : string
    {
        // Lookup players score.
        $sql = "SELECT score FROM {$pdo_db->prefix}ships WHERE email =:email";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':email', $a_username);
        $res = $stmt->execute();
        Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
        $res = $stmt->fetch();
        $playerinfo = array();
        $playerinfo['score'] = $res['score'];

        for ($i = 0; $i < 20; $i++)
        {
            $value = pow(2, $i * 2);
            if (!$value)
            {
                // Pow returned false so we need to return an error.
                $player_insignia = "<span style='color:#f00;'>ERR</span> [<span style='color:#09f; font-size:12px; cursor:help;' title='Error looking up insignia, please report this error.'>?</span>]";
                break;
            }

            $value *= (500 * 2);
            if ($playerinfo['score'] <= $value)
            {
                // Ok we have found our Insignia, now set and break out of the for loop.
                $temp_insignia = 'l_insignia_' . $i;
                $player_insignia = $langvars[$temp_insignia];
                break;
            }
        }

        if (!isset($player_insignia))
        {
            // Hmm, player has out ranked out highest rank, so just return that.
            $player_insignia = $langvars['l_insignia_19'];
        }

        return (string) $player_insignia;
    }
}
