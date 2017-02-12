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
// File: classes/Traderoute2.php

namespace Tki;

class Traderoute2
{
    public static function traderouteDie(\PDO $pdo_db, string $lang, Reg $tkireg, Smarty $template, string $error_msg = null): void
    {
        echo "<p>" . $error_msg . "<p>";
        echo "<div style='text-align:left;'>\n";
        \Tki\Text::gotoMain($pdo_db, $lang);
        echo "</div>\n";
        \Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
        die();
    }

    public static function traderouteCheckCompatible(\PDO $pdo_db, $db, string $lang, $type1, $type2, $move, $circuit, $src, $dest, array $playerinfo, Reg $tkireg, Smarty $template): void
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        // Check circuit compatibility (we only use types 1 and 2 so block anything else)
        if ($circuit != "1" && $circuit != "2")
        {
            \Tki\AdminLog::writeLog($pdo_db, LOG_RAW, "{$playerinfo['ship_id']}|Tried to use an invalid circuit_type of '{$circuit}', This is normally a result from using an external page and should be banned.");
            self::traderouteDie($pdo_db, $lang, $tkireg, $template, "Invalid Circuit type!<br>*** Possible Exploit has been reported to the admin. ***");
        }

        // Check warp links compatibility
        if ($move == 'warp')
        {
            $query = $db->Execute("SELECT link_id FROM {$db->prefix}links WHERE link_start = ? AND link_dest = ?;", array($src['sector_id'], $dest['sector_id']));
            \Tki\Db::logDbErrors($pdo_db, $query, __LINE__, __FILE__);
            if ($query->EOF)
            {
                $langvars['l_tdr_nowlink1'] = str_replace("[tdr_src_sector_id]", $src['sector_id'], $langvars['l_tdr_nowlink1']);
                $langvars['l_tdr_nowlink1'] = str_replace("[tdr_dest_sector_id]", $dest['sector_id'], $langvars['l_tdr_nowlink1']);
                self::traderouteDie($pdo_db, $lang, $tkireg, $template, $langvars['l_tdr_nowlink1']);
            }

            if ($circuit == '2')
            {
                $query = $db->Execute("SELECT link_id FROM {$db->prefix}links WHERE link_start = ? AND link_dest = ?;", array($dest['sector_id'], $src['sector_id']));
                \Tki\Db::logDbErrors($pdo_db, $query, __LINE__, __FILE__);
                if ($query->EOF)
                {
                    $langvars['l_tdr_nowlink2'] = str_replace("[tdr_src_sector_id]", $src['sector_id'], $langvars['l_tdr_nowlink2']);
                    $langvars['l_tdr_nowlink2'] = str_replace("[tdr_dest_sector_id]", $dest['sector_id'], $langvars['l_tdr_nowlink2']);
                    self::traderouteDie($pdo_db, $lang, $tkireg, $template, $langvars['l_tdr_nowlink2']);
                }
            }
        }

        // Check ports compatibility
        if ($type1 == 'port')
        {
            if ($src['port_type'] == 'special')
            {
                if (($type2 != 'planet') && ($type2 != 'team_planet'))
                {
                    self::traderouteDie($pdo_db, $lang, $tkireg, $template, $langvars['l_tdr_sportissrc']);
                }

                if ($dest['owner'] != $playerinfo['ship_id'] && ($dest['team'] == 0 || ($dest['team'] != $playerinfo['team'])))
                {
                    self::traderouteDie($pdo_db, $lang, $tkireg, $template, $langvars['l_tdr_notownplanet']);
                }
            }
            else
            {
                if ($type2 == 'planet')
                {
                    self::traderouteDie($pdo_db, $lang, $tkireg, $template, $langvars['l_tdr_planetisdest']);
                }

                if ($src['port_type'] == $dest['port_type'])
                {
                    self::traderouteDie($pdo_db, $lang, $tkireg, $template, $langvars['l_tdr_samecom']);
                }
            }
        }
        else
        {
            if (array_key_exists('port_type', $dest) === true && $dest['port_type'] == 'special')
            {
                self::traderouteDie($pdo_db, $lang, $tkireg, $template, $langvars['l_tdr_sportcom']);
            }
        }
    }
}
