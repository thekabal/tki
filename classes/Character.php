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
// File: classes/Character.php

namespace Tki;

class Character
{
    public static function kill($db, $ship_id, $langvars, Reg $tkireg, $remove_planets = false)
    {
        $update_ships_res = $db->Execute("UPDATE {$db->prefix}ships SET ship_destroyed='Y', on_planet='N', sector=0, cleared_defences=' ' WHERE ship_id=?", array($ship_id));
        Db::logDbErrors($db, $update_ships_res, __LINE__, __FILE__);

        $delete_bounty_res = $db->Execute("DELETE FROM {$db->prefix}bounty WHERE placed_by = ?", array($ship_id));
        Db::logDbErrors($db, $delete_bounty_res, __LINE__, __FILE__);

        $sec_pl_res = $db->Execute("SELECT DISTINCT sector_id FROM {$db->prefix}planets WHERE owner=? AND base='Y'", array($ship_id));
        Db::logDbErrors($db, $sec_pl_res, __LINE__, __FILE__);
        $i = 0;

        $sectors = null;

        if ($sec_pl_res instanceof \adodb\ADORecordSet)
        {
            while (!$sec_pl_res->EOF && $sec_pl_res)
            {
                $sectors[$i] = $sec_pl_res->fields['sector_id'];
                $i++;
                $sec_pl_res->MoveNext();
            }
        }

        if ($remove_planets === true && $ship_id > 0)
        {
            $rm_pl_res = $db->Execute("DELETE FROM {$db->prefix}planets WHERE owner = ?", array($ship_id));
            Db::logDbErrors($db, $rm_pl_res, __LINE__, __FILE__);
        }
        else
        {
            $up_pl_res = $db->Execute("UPDATE {$db->prefix}planets SET owner=0, team=0, fighters=0, base='N' WHERE owner=?", array($ship_id));
            Db::logDbErrors($db, $up_pl_res, __LINE__, __FILE__);
        }

        if (!empty($sectors))
        {
            foreach ($sectors as $sector)
            {
                Ownership::calc($db, $sector, $tkireg->min_bases_to_own, $langvars);
            }
        }

        $rm_def_res = $db->Execute("DELETE FROM {$db->prefix}sector_defence WHERE ship_id=?", array($ship_id));
        Db::logDbErrors($db, $rm_def_res, __LINE__, __FILE__);

        $zone_res = $db->Execute("SELECT zone_id FROM {$db->prefix}zones WHERE team_zone='N' AND owner=?", array($ship_id));
        Db::logDbErrors($db, $zone_res, __LINE__, __FILE__);
        $zone = $zone_res->fields;

        $up_zone_res = $db->Execute("UPDATE {$db->prefix}universe SET zone_id=1 WHERE zone_id=?", array($zone['zone_id']));
        Db::logDbErrors($db, $up_zone_res, __LINE__, __FILE__);

        $char_res = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE ship_id=?", array($ship_id));
        Db::logDbErrors($db, $char_res, __LINE__, __FILE__);
        $name = $char_res->fields;

        $headline = $name['character_name'] .' '. $langvars['l_killheadline'];

        $newstext = str_replace('[name]', $name['character_name'], $langvars['l_news_killed']);

        $news_ins_res = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?,?,?,NOW(), 'killed')", array($headline, $newstext, $ship_id));
        Db::logDbErrors($db, $news_ins_res, __LINE__, __FILE__);
    }

    // Choosing to use a method instead of a property.
    // If we went with a method, and it needed to be changed, we would have to change lots of property->method calls.
    public static function getInsignia(\PDO $pdo_db, $a_username, $langvars)
    {
        unset($player_insignia);

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
            $value = pow(2, $i*2);
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

        return $player_insignia;
    }
}

