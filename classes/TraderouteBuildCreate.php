<?php declare(strict_types = 1);
/**
 * classes/TraderouteBuildCreate.php from The Kabal Invasion.
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

namespace Tki;

class TraderouteBuildCreate
{
    public static function create(\PDO $pdo_db, $old_db, string $lang, Registry $tkireg, Timer $tkitimer, Smarty $template, array $playerinfo, int $num_traderoutes, string $ptype1, string $ptype2, int $port_id1, int $port_id2, int $team_planet_id1, int $team_planet_id2, string $move_type, int $circuit_type, int $editing, ?int $planet_id1 = null, ?int $planet_id2 = null): void
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('common',
                                         'footer', 'insignias', 'regional',
                                         'traderoutes', 'universal'));
        $admin_log = new AdminLog();
        $src_id = null;
        $dest_id = null;
        $src_type = null;
        $dest_type = null;

        if ($num_traderoutes >= $tkireg->max_traderoutes_player && empty($editing))
        { // Dont let them exceed max traderoutes
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_maxtdr']);
        }

        // Database sanity check for source
        if ($ptype1 == 'port')
        {
            // Check for valid Source Port
            if ($port_id1 >= $tkireg->max_sectors)
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_invalidspoint']);
            }

            $query = $old_db->Execute("SELECT * FROM {$old_db->prefix}universe WHERE sector_id = ?;", array($port_id1));
            \Tki\Db::logDbErrors($pdo_db, $query, __LINE__, __FILE__);
            if (!$query || $query->EOF)
            {
                $langvars['l_tdr_errnotvalidport'] = str_replace("[tdr_port_id]", (string) $port_id1, $langvars['l_tdr_errnotvalidport']);
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_errnotvalidport']);
            }

            // OK we definitely have a port here
            $source = $query->fields;
            if ($source['port_type'] == 'none')
            {
                $langvars['l_tdr_errnoport'] = str_replace("[tdr_port_id]", (string) $port_id1, $langvars['l_tdr_errnoport']);
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_errnoport']);
            }
        }
        else
        {
            $query = $old_db->Execute("SELECT * FROM {$old_db->prefix}planets WHERE planet_id = ?;", array($planet_id1));
            \Tki\Db::logDbErrors($pdo_db, $query, __LINE__, __FILE__);
            $source = $query->fields;
            if (!$query || $query->EOF)
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_errnosrc']);
            }

            // Check for valid Source Planet
            if ($source['sector_id'] >= $tkireg->max_sectors)
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_invalidsrc']);
            }

            if ($source['owner'] != $playerinfo['ship_id'])
            {
                if (($playerinfo['team'] == 0 || $playerinfo['team'] != $source['team']) && $source['sells'] == 'N')
                {
                    // $langvars['l_tdr_errnotownnotsell'] = str_replace("[tdr_source_name]", $source[name], $langvars['l_tdr_errnotownnotsell']);
                    // $langvars['l_tdr_errnotownnotsell'] = str_replace("[tdr_source_sector_id]", $source[sector_id], $langvars['l_tdr_errnotownnotsell']);
                    // \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_errnotownnotsell']);

                    // Check for valid Owned Source Planet
                    $admin_log->writeLog($pdo_db, 902, "{$playerinfo['ship_id']}|Tried to find someones planet: {$planet_id1} as source.");
                    \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_invalidsrc']);
                }
            }
        }

        // OK we have $source, *probably* now lets see if we have ever been there
        // Attempting to fix the map the universe via traderoute bug

        $pl1query = $old_db->Execute("SELECT * FROM {$old_db->prefix}movement_log WHERE sector_id = ? AND ship_id = ?;", array($source['sector_id'], $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($pdo_db, $pl1query, __LINE__, __FILE__);
        $num_res1 = $pl1query->numRows();
        if ($num_res1 == 0)
        {
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, "You cannot create a traderoute from a sector you have not visited!");
        }

        // Note: shouldnt we, more realistically, require a ship to be *IN* the source sector to create the traderoute?
        // Database sanity check for dest
        if ($ptype2 == 'port')
        {
            // Check for valid Dest Port
            if ($port_id2 >= $tkireg->max_sectors)
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_invaliddport']);
            }

            $query = $old_db->Execute("SELECT * FROM {$old_db->prefix}universe WHERE sector_id = ?;", array($port_id2));
            \Tki\Db::logDbErrors($pdo_db, $query, __LINE__, __FILE__);
            if (!$query || $query->EOF)
            {
                $langvars['l_tdr_errnotvaliddestport'] = str_replace("[tdr_port_id]", (string) $port_id2, $langvars['l_tdr_errnotvaliddestport']);
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_errnotvaliddestport']);
            }

            $destination = $query->fields;

            if ($destination['port_type'] == 'none')
            {
                $langvars['l_tdr_errnoport2'] = str_replace("[tdr_port_id]", (string) $port_id2, $langvars['l_tdr_errnoport2']);
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_errnoport2']);
            }
        }
        else
        {
            $query = $old_db->Execute("SELECT * FROM {$old_db->prefix}planets WHERE planet_id = ?;", array($planet_id2));
            \Tki\Db::logDbErrors($pdo_db, $query, __LINE__, __FILE__);
            $destination = $query->fields;
            if (!$query || $query->EOF)
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_errnodestplanet']);
            }

            // Check for valid Dest Planet
            if ($destination['sector_id'] >= $tkireg->max_sectors)
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_invaliddplanet']);
            }

            if ($destination['owner'] != $playerinfo['ship_id'] && $destination['sells'] == 'N')
            {
                // $langvars['l_tdr_errnotownnotsell2'] = str_replace("[tdr_dest_name]", $destination['name'], $langvars['l_tdr_errnotownnotsell2']);
                // $langvars['l_tdr_errnotownnotsell2'] = str_replace("[tdr_dest_sector_id]", $destination['sector_id'], $langvars['l_tdr_errnotownnotsell2']);
                // \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_errnotownnotsell2']);

                // Check for valid Owned Source Planet
                $admin_log->writeLog($pdo_db, 902, "{$playerinfo['ship_id']}|Tried to find someones planet: {$planet_id2} as dest.");
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_invaliddplanet']);
            }
        }

        // OK now we have $destination lets see if we've been there.
        $pl2query = $old_db->Execute("SELECT * FROM {$old_db->prefix}movement_log WHERE sector_id = ? AND ship_id = ?;", array($destination['sector_id'], $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($pdo_db, $pl2query, __LINE__, __FILE__);
        $num_res2 = $pl2query->numRows();
        if ($num_res2 == 0)
        {
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, "You cannot create a traderoute into a sector you have not visited!");
        }

        // Check destination - we cannot trade INTO a special port
        if (array_key_exists('port_type', $destination) === true && $destination['port_type'] == 'special')
        {
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, "You cannot create a traderoute into a special port!");
        }

        // Check traderoute for src => dest
        \Tki\TraderouteCheck::isCompatible($pdo_db, $lang, $ptype1, $ptype2, $move_type, $circuit_type, $source, $destination, $playerinfo, $tkireg, $tkitimer, $template);

        if ($ptype1 == 'port')
        {
            $src_id = $port_id1;
        }
        elseif ($ptype1 == 'planet')
        {
            $src_id = $planet_id1;
        }
        elseif ($ptype1 == 'team_planet')
        {
            $src_id = $team_planet_id1;
        }

        if ($ptype2 == 'port')
        {
            $dest_id = $port_id2;
        }
        elseif ($ptype2 == 'planet')
        {
            $dest_id = $planet_id2;
        }
        elseif ($ptype2 == 'team_planet')
        {
            $dest_id = $team_planet_id2;
        }

        if ($ptype1 == 'port')
        {
            $src_type = 'P';
        }
        elseif ($ptype1 == 'planet')
        {
            $src_type = 'L';
        }
        elseif ($ptype1 == 'team_planet')
        {
            $src_type = 'C';
        }

        if ($ptype2 == 'port')
        {
            $dest_type = 'P';
        }
        elseif ($ptype2 == 'planet')
        {
            $dest_type = 'L';
        }
        elseif ($ptype2 == 'team_planet')
        {
            $dest_type = 'C';
        }

        $mtype = 'W';
        if ($move_type == 'realspace')
        {
            $mtype = 'R';
        }

        if (empty($editing))
        {
            $query = $old_db->Execute("INSERT INTO {$old_db->prefix}traderoutes VALUES(null, ?, ?, ?, ?, ?, ?, ?);", array($src_id, $dest_id, $src_type, $dest_type, $mtype, $playerinfo['ship_id'], $circuit_type));
            \Tki\Db::logDbErrors($pdo_db, $query, __LINE__, __FILE__);
            echo "<p>" . $langvars['l_tdr_newtdrcreated'];
        }
        else
        {
            $query = $old_db->Execute("UPDATE {$old_db->prefix}traderoutes SET source_id = ?, dest_id = ?, source_type = ?, dest_type = ?, move_type = ?, owner = ?, circuit = ? WHERE traderoute_id = ?;", array($src_id, $dest_id, $src_type, $dest_type, $mtype, $playerinfo['ship_id'], $circuit_type, $editing));
            \Tki\Db::logDbErrors($pdo_db, $query, __LINE__, __FILE__);
            echo "<p>" . $langvars['l_tdr_modified'];
        }

        $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);
        echo " " . $langvars['l_tdr_returnmenu'];
        \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, null);
    }
}
