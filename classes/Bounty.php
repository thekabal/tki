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
// File: classes/Bounty.php

namespace Tki;

class Bounty
{
    public static function cancel($db, $bounty_on)
    {
        $res = $db->Execute("SELECT * FROM {$db->prefix}bounty,{$db->prefix}ships WHERE bounty_on = ? AND bounty_on = ship_id", array($bounty_on));
        Db::logDbErrors($db, $res, __LINE__, __FILE__);
        if ($res)
        {
            while (!$res->EOF)
            {
                $bountydetails = $res->fields;
                if ($bountydetails['placed_by'] != 0)
                {
                    $update_creds_res = $db->Execute("UPDATE {$db->prefix}ships SET credits = credits + ? WHERE ship_id = ?", array($bountydetails['amount'], $bountydetails['placed_by']));
                    Db::logDbErrors($db, $update_creds_res, __LINE__, __FILE__);
                    PlayerLog::writeLog($db, $bountydetails['placed_by'], LOG_BOUNTY_CANCELLED, "$bountydetails[amount]|$bountydetails[character_name]");
                }

                 $delete_bounty_res = $db->Execute("DELETE FROM {$db->prefix}bounty WHERE bounty_id = ?", array($bountydetails['bounty_id']));
                 Db::logDbErrors($db, $delete_bounty_res, __LINE__, __FILE__);
                 $res->MoveNext();
            }
        }
    }

    public static function collect($db, $langvars, $attacker, $bounty_on)
    {
        $res = $db->Execute("SELECT * FROM {$db->prefix}bounty,{$db->prefix}ships WHERE bounty_on = ? AND bounty_on = ship_id and placed_by <> 0", array($bounty_on));
        Db::logDbErrors($db, $res, __LINE__, __FILE__);

        if ($res)
        {
            while (!$res->EOF)
            {
                $bountydetails = $res->fields;
                if ($res->fields['placed_by'] == 0)
                {
                    $placed = $langvars['l_by_thefeds'];
                }
                else
                {
                    $pl_bounty_res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?", array($bountydetails['placed_by']));
                    Db::logDbErrors($db, $pl_bounty_res, __LINE__, __FILE__);
                    $placed = $pl_bounty_res->fields['character_name'];
                }

                $update_creds_res = $db->Execute("UPDATE {$db->prefix}ships SET credits = credits + ? WHERE ship_id = ?", array($bountydetails['amount'], $attacker));
                Db::logDbErrors($db, $update_creds_res, __LINE__, __FILE__);
                $delete_bounty_res = $db->Execute("DELETE FROM {$db->prefix}bounty WHERE bounty_id = ?", array($bountydetails['bounty_id']));
                Db::logDbErrors($db, $delete_bounty_res, __LINE__, __FILE__);

                PlayerLog::writeLog($db, $attacker, LOG_BOUNTY_CLAIMED, "$bountydetails[amount]|$bountydetails[character_name]|$placed");
                PlayerLog::writeLog($db, $bountydetails['placed_by'], LOG_BOUNTY_PAID, "$bountydetails[amount]|$bountydetails[character_name]");
                $res->MoveNext();
            }
        }
        $delete_bounty_on_res = $db->Execute("DELETE FROM {$db->prefix}bounty WHERE bounty_on = ?", array($bounty_on));
        Db::logDbErrors($db, $delete_bounty_on_res, __LINE__, __FILE__);
    }
}

