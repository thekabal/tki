<?php declare(strict_types = 1);
/**
 * classes/TraderouteCheck.php from The Kabal Invasion.
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

class TraderouteCheck
{
    public static function isCompatible(\PDO $pdo_db, string $lang, string $type1, string $type2, string $move, int $circuit, array $src, array $dest, array $playerinfo, Registry $tkireg, Timer $tkitimer, Smarty $template): void
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('common',
                                         'footer', 'insignias', 'regional',
                                         'traderoutes', 'universal'));
        $admin_log = new AdminLog();

        // Check circuit compatibility (we only use types 1 and 2 so block anything else)
        if ($circuit != "1" && $circuit != "2")
        {
            $admin_log->writeLog($pdo_db, LogEnums::RAW, "{$playerinfo['ship_id']}|Tried to use an invalid circuit_type of '{$circuit}', This is normally a result from using an external page and should be banned.");
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, "Invalid Circuit type!<br>*** Possible Exploit has been reported to the admin. ***");
        }

        // Check warp links compatibility
        if ($move == 'warp')
        {
            $links_gateway = new \Tki\Links\LinksGateway($pdo_db);
            $link1_results = $links_gateway->selectLinkId($src['sector_id'], $dest['sector_id']);

            if (empty($link1_results))
            {
                $langvars['l_tdr_nowlink1'] = str_replace("[tdr_src_sector_id]", $src['sector_id'], $langvars['l_tdr_nowlink1']);
                $langvars['l_tdr_nowlink1'] = str_replace("[tdr_dest_sector_id]", $dest['sector_id'], $langvars['l_tdr_nowlink1']);
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_nowlink1']);
            }

            if ($circuit == '2')
            {
                $links_gateway = new \Tki\Links\LinksGateway($pdo_db);
                $link2_results = $links_gateway->selectLinkId($dest['sector_id'], $src['sector_id']); // Note that the link start/dest is flipped on purpose

                if (empty($link2_results))
                {
                    $langvars['l_tdr_nowlink2'] = str_replace("[tdr_src_sector_id]", $src['sector_id'], $langvars['l_tdr_nowlink2']);
                    $langvars['l_tdr_nowlink2'] = str_replace("[tdr_dest_sector_id]", $dest['sector_id'], $langvars['l_tdr_nowlink2']);
                    \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_nowlink2']);
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
                    \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_sportissrc']);
                }

                if ($dest['owner'] != $playerinfo['ship_id'] && ($dest['team'] == 0 || ($dest['team'] != $playerinfo['team'])))
                {
                    \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_notownplanet']);
                }
            }
            else
            {
                if ($type2 == 'planet')
                {
                    \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_planetisdest']);
                }

                if ($src['port_type'] == $dest['port_type'])
                {
                    \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_samecom']);
                }
            }
        }
        else
        {
            if (array_key_exists('port_type', $dest) === true && $dest['port_type'] == 'special')
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_sportcom']);
            }
        }
    }
}
