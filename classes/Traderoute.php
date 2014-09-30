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
// File: classes/Traderoute.php
//
// TODO: These are horribly bad. But in the interest of saying goodbye to the includes directory, and raw functions, this
// will at least allow us to auto-load and use classes instead. Plenty to do in the future, though!

namespace Bad;

class Traderoute
{
    public static function traderouteEngage($db, $pdo_db, $lang, $j, $langvars)
    {
        global $playerinfo, $color_line1, $color_line2, $color_header;
        global $engage, $dist;
        global $color_line2;
        global $color_line1;
        global $traderoutes;
        global $fighter_price;
        global $torpedo_price;
        global $colonist_price;
        global $colonist_limit;
        global $inventory_factor;
        global $ore_price;
        global $ore_delta;
        global $ore_limit;
        global $organics_price;
        global $organics_delta;
        global $organics_limit;
        global $goods_price;
        global $goods_delta;
        global $goods_limit;
        global $energy_price;
        global $energy_delta;
        global $energy_limit;
        global $mine_hullsize;
        global $portfull;
        global $level_factor;

        foreach ($traderoutes as $testroute)
        {
            if ($testroute['traderoute_id'] == $engage)
            {
                $traderoute = $testroute;
            }
        }

        if (!isset($traderoute))
        {
            Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_engagenonexist'], $template);
        }

        if ($traderoute['owner'] != $playerinfo['ship_id'])
        {
            Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_notowntdr'], $template);
        }

        // Source Check
        if ($traderoute['source_type'] == 'P')
        {
            // Retrieve port info here, we'll need it later anyway
            $result = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=?", array($traderoute['source_id']));
            \Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);

            if (!$result || $result->EOF)
            {
                BadTraderteou::traderouteDie($db, $lang, $langvars, $tkireg, $langvars['l_tdr_invalidspoint'], $template);
            }

            $source = $result->fields;

            if ($traderoute['source_id'] != $playerinfo['sector'])
            {
                $langvars['l_tdr_inittdr'] = str_replace("[tdr_source_id]", $traderoute['source_id'], $langvars['l_tdr_inittdr']);
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_inittdr'], $template);
            }
        }
        elseif ($traderoute['source_type'] == 'L' || $traderoute['source_type'] == 'C')  // Get data from planet table
        {
            $result = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=? AND (owner = ? OR (corp <> 0 AND corp = ?));", array($traderoute['source_id'], $playerinfo['ship_id'], $playerinfo['team']));
            \Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);
            if (!$result || $result->EOF)
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_invalidsrc'], $template);
            }

            $source = $result->fields;

            if ($source['sector_id'] != $playerinfo['sector'])
            {
                // Check for valid Owned Source Planet
                // $langvars['l_tdr_inittdrsector'] = str_replace("[tdr_source_sector_id]", $source['sector_id'], $langvars['l_tdr_inittdrsector']);
                // Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_inittdrsector'], $template);
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, 'You must be in starting sector before you initiate a trade route!', $template);
            }

            if ($traderoute['source_type'] == 'L')
            {
                if ($source['owner'] != $playerinfo['ship_id'])
                {
                    // $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_name]", $source[name], $langvars['l_tdr_notyourplanet']);
                    // $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_sector_id]", $source[sector_id], $langvars['l_tdr_notyourplanet']);
                    // Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_notyourplanet'], $template);
                    Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_invalidsrc'], $template);
                }
            }
            elseif ($traderoute['source_type'] == 'C')   // Check to make sure player and planet are in the same corp.
            {
                if ($source['corp'] != $playerinfo['team'])
                {
                    // $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_name]", $source[name], $langvars['l_tdr_notyourplanet']);
                    // $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_sector_id]", $source[sector_id], $langvars['l_tdr_notyourplanet']);
                    // $not_corp_planet = "$source[name] in $source[sector_id] not a Copporate Planet";
                    // Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $not_corp_planet, $template);
                    Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_invalidsrc'], $template);
                }
            }

            // Store starting port info, we'll need it later
            $result = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=?", array($source['sector_id']));
            \Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);

            if (!$result || $result->EOF)
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_invalidssector'], $template);
            }

            $sourceport = $result->fields;
        }

        // Destination Check
        if ($traderoute['dest_type'] == 'P')
        {
            $result = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=?", array($traderoute['dest_id']));
            \Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);

            if (!$result || $result->EOF)
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_invaliddport'], $template);
            }

            $dest = $result->fields;
        }
        elseif (($traderoute['dest_type'] == 'L') || ($traderoute['dest_type'] == 'C'))  // Get data from planet table
        {
            // Check for valid Owned Source Planet
            // This now only returns Planets that the player owns or planets that belong to the team and set as corp planets..
            $result = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=? AND (owner = ? OR (corp <> 0 AND corp = ?));", array($traderoute['dest_id'], $playerinfo['ship_id'], $playerinfo['team']));
            \Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);

            if (!$result || $result->EOF)
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_invaliddplanet'], $template);
            }

            $dest = $result->fields;

            if ($traderoute['dest_type'] == 'L')
            {
                if ($dest['owner'] != $playerinfo['ship_id'])
                {
                    $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_name]", $dest['name'], $langvars['l_tdr_notyourplanet']);
                    $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_sector_id]", $dest['sector_id'], $langvars['l_tdr_notyourplanet']);
                    Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_notyourplanet'], $template);
                }
            }
            elseif ($traderoute['dest_type'] == 'C')   // Check to make sure player and planet are in the same corp.
            {
                if ($dest['corp'] != $playerinfo['team'])
                {
                    $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_name]", $dest['name'], $langvars['l_tdr_notyourplanet']);
                    $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_sector_id]", $dest['sector_id'], $langvars['l_tdr_notyourplanet']);
                    Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_notyourplanet'], $template);
                }
            }

            $result = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=?", array($dest['sector_id']));
            \Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);
            if (!$result || $result->EOF)
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_invaliddsector'], $template);
            }

            $destport = $result->fields;
        }

        if (!isset($sourceport))
        {
            $sourceport= $source;
        }

        if (!isset($destport))
        {
            $destport= $dest;
        }

        // Warp or RealSpace and generate distance
        if ($traderoute['move_type'] == 'W')
        {
            $query = $db->Execute("SELECT link_id FROM {$db->prefix}links WHERE link_start=? AND link_dest=?", array($source['sector_id'], $dest['sector_id']));
            \Tki\Db::logDbErrors($db, $query, __LINE__, __FILE__);
            if ($query->EOF)
            {
                $langvars['l_tdr_nowlink1'] = str_replace("[tdr_src_sector_id]", $source['sector_id'], $langvars['l_tdr_nowlink1']);
                $langvars['l_tdr_nowlink1'] = str_replace("[tdr_dest_sector_id]", $dest['sector_id'], $langvars['l_tdr_nowlink1']);
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_nowlink1'], $template);
            }

            if ($traderoute['circuit'] == '2')
            {
                $query = $db->Execute("SELECT link_id FROM {$db->prefix}links WHERE link_start=? AND link_dest=?", array($dest['sector_id'], $source['sector_id']));
                \Tki\Db::logDbErrors($db, $query, __LINE__, __FILE__);
                if ($query->EOF)
                {
                    $langvars['l_tdr_nowlink2'] = str_replace("[tdr_src_sector_id]", $source['sector_id'], $langvars['l_tdr_nowlink2']);
                    $langvars['l_tdr_nowlink2'] = str_replace("[tdr_dest_sector_id]", $dest['sector_id'], $langvars['l_tdr_nowlink2']);
                    Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_nowlink2'], $template);
                }
                $dist['triptime'] = 4;
            }
            else
            {
                $dist['triptime'] = 2;
            }

            $dist['scooped'] = 0;
            $dist['scooped1'] = 0;
            $dist['scooped2'] = 0;
        }
        else
        {
            $dist = Traderoute::traderouteDistance($db, 'P', 'P', $sourceport, $destport, $playerinfo, $traderoute['circuit']);
        }

        // Check if player has enough turns
        if ($playerinfo['turns'] < $dist['triptime'])
        {
            $langvars['l_tdr_moreturnsneeded'] = str_replace("[tdr_dist_triptime]", $dist['triptime'], $langvars['l_tdr_moreturnsneeded']);
            $langvars['l_tdr_moreturnsneeded'] = str_replace("[tdr_playerinfo_turns]", $playerinfo['turns'], $langvars['l_tdr_moreturnsneeded']);
            Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_moreturnsneeded'], $template);
        }

        // Sector Defense Check
        $hostile = 0;

        $result99 = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id = ? AND ship_id <> ?", array($source['sector_id'], $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($db, $result99, __LINE__, __FILE__);
        if (!$result99->EOF)
        {
            $fighters_owner = $result99->fields;
            $nsresult = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id=?", array($fighters_owner['ship_id']));
            \Tki\Db::logDbErrors($db, $nsresult, __LINE__, __FILE__);
            $nsfighters = $nsresult->fields;

            if ($nsfighters['team'] != $playerinfo['team'] || $playerinfo['team']==0)
            {
                $hostile = 1;
            }
        }

        $result98 = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id = ? AND ship_id <> ?", array($dest['sector_id'], $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($db, $result98, __LINE__, __FILE__);
        if (!$result98->EOF)
        {
            $fighters_owner = $result98->fields;
            $nsresult = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id=?", array($fighters_owner['ship_id']));
            \Tki\Db::logDbErrors($db, $nsresult, __LINE__, __FILE__);
            $nsfighters = $nsresult->fields;

            if ($nsfighters['team'] != $playerinfo['team'] || $playerinfo['team']==0)
            {
                $hostile = 1;
            }
        }

        if ($hostile > 0 && $playerinfo['hull'] > $mine_hullsize)
        {
            Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_hostdef'], $template);
        }

        // Special Port Nothing to do
        if ($traderoute['source_type'] == 'P' && $source['port_type'] == 'special' && $playerinfo['trade_colonists'] == 'N' && $playerinfo['trade_fighters'] == 'N' && $playerinfo['trade_torps'] == 'N')
        {
            Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_globalsetbuynothing'], $template);
        }

        // Check if zone allows trading  SRC
        if ($traderoute['source_type'] == 'P')
        {
            $res = $db->Execute("SELECT * FROM {$db->prefix}zones,{$db->prefix}universe WHERE {$db->prefix}universe.sector_id=? AND {$db->prefix}zones.zone_id={$db->prefix}universe.zone_id;", array($traderoute['source_id']));
            \Tki\Db::logDbErrors($db, $res, __LINE__, __FILE__);
            $zoneinfo = $res->fields;
            if ($zoneinfo['allow_trade'] == 'N')
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_nosrcporttrade'], $template);
            }
            elseif ($zoneinfo['allow_trade'] == 'L')
            {
                if ($zoneinfo['corp_zone'] == 'N')
                {
                    $res = $db->Execute("SELECT team FROM {$db->prefix}ships WHERE ship_id=?", array($zoneinfo['owner']));
                    \Tki\Db::logDbErrors($db, $res, __LINE__, __FILE__);
                    $ownerinfo = $res->fields;

                    if ($playerinfo['ship_id'] != $zoneinfo['owner'] && $playerinfo['team'] == 0 || $playerinfo['team'] != $ownerinfo['team'])
                    {
                        Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_tradesrcportoutsider'], $template);
                    }
                }
                else
                {
                    if ($playerinfo['team'] != $zoneinfo['owner'])
                    {
                        Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_tradesrcportoutsider'], $template);
                    }
                }
            }
        }

        // Check if zone allows trading  DEST
        if ($traderoute['dest_type'] == 'P')
        {
            $res = $db->Execute("SELECT * FROM {$db->prefix}zones,{$db->prefix}universe WHERE {$db->prefix}universe.sector_id=? AND {$db->prefix}zones.zone_id={$db->prefix}universe.zone_id;", array($traderoute['dest_id']));
            \Tki\Db::logDbErrors($db, $res, __LINE__, __FILE__);
            $zoneinfo = $res->fields;
            if ($zoneinfo['allow_trade'] == 'N')
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_nodestporttrade'], $template);
            }
            elseif ($zoneinfo['allow_trade'] == 'L')
            {
                if ($zoneinfo['corp_zone'] == 'N')
                {
                    $res = $db->Execute("SELECT team FROM {$db->prefix}ships WHERE ship_id=?", array($zoneinfo['owner']));
                    \Tki\Db::logDbErrors($db, $res, __LINE__, __FILE__);
                    $ownerinfo = $res->fields;

                    if ($playerinfo['ship_id'] != $zoneinfo['owner'] && $playerinfo['team'] == 0 || $playerinfo['team'] != $ownerinfo['team'])
                    {
                        Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_tradedestportoutsider'], $template);
                    }
                }
                else
                {
                    if ($playerinfo['team'] != $zoneinfo['owner'])
                    {
                        Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_tradedestportoutsider'], $template);
                    }
                }
            }
        }

        Traderoute::traderouteResultsTableTop($db, $pdo_db, $lang, $langvars);
        // Determine if Source is Planet or Port
        if ($traderoute['source_type'] == 'P')
        {
            echo $langvars['l_tdr_portin'] . " " . $source['sector_id'];
        }
        elseif (($traderoute['source_type'] == 'L') || ($traderoute['source_type'] == 'C'))
        {
            echo $langvars['l_tdr_planet'] . " " . $source['name'] . " in " . $sourceport['sector_id'];
        }
        Traderoute::traderouteResultsSource();

        // Determine if Destination is Planet or Port
        if ($traderoute['dest_type'] == 'P')
        {
            echo $langvars['l_tdr_portin'] . " " . $dest['sector_id'];
        }
        elseif (($traderoute['dest_type'] == 'L') || ($traderoute['dest_type'] == 'C'))
        {
            echo $langvars['l_tdr_planet'] . " " . $dest['name'] . " in " . $destport['sector_id'];
        }
        Traderoute::traderouteResultsDestination();

        $sourcecost=0;

        // Source is Port
        if ($traderoute['source_type'] == 'P')
        {
            // Special Port Section (begin)
            if ($source['port_type'] == 'special')
            {
                $ore_buy = 0;
                $goods_buy = 0;
                $organics_buy = 0;
                $energy_buy = 0;

                $total_credits = $playerinfo['credits'];

                if ($playerinfo['trade_colonists'] == 'Y')
                {
                    $free_holds = \Tki\CalcLevels::holds($playerinfo['hull'], $level_factor) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
                    $colonists_buy = $free_holds;

                    if ($playerinfo['credits'] < $colonist_price * $colonists_buy)
                    {
                        $colonists_buy = $playerinfo['credits'] / $colonist_price;
                    }

                    if ($colonists_buy != 0)
                    {
                        echo $langvars['l_tdr_bought'] . " " . number_format($colonists_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_colonists'] . "<br>";
                    }

                    $sourcecost-= $colonists_buy * $colonist_price;
                    $total_credits-= $colonists_buy * $colonist_price;
                }
                else
                {
                    $colonists_buy = 0;
                }

                if ($playerinfo['trade_fighters'] == 'Y')
                {
                    $free_fighters = \Tki\CalcLevels::fighters($playerinfo['computer'], $level_factor) - $playerinfo['ship_fighters'];
                    $fighters_buy = $free_fighters;

                    if ($total_credits < $fighters_buy * $fighter_price)
                    {
                        $fighters_buy = $total_credits / $fighter_price;
                    }

                    if ($fighters_buy != 0)
                    {
                        echo $langvars['l_tdr_bought'] . " " . number_format($fighters_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_fighters'] . "<br>";
                    }

                    $sourcecost-= $fighters_buy * $fighter_price;
                    $total_credits-= $fighters_buy * $fighter_price;
                }
                else
                {
                    $fighters_buy = 0;
                }

                if ($playerinfo['trade_torps'] == 'Y')
                {
                    $free_torps = \Tki\CalcLevels::fighters($playerinfo['torp_launchers'], $level_factor) - $playerinfo['torps'];
                    $torps_buy = $free_torps;

                    if ($total_credits < $torps_buy * $torpedo_price)
                    {
                        $torps_buy = $total_credits / $torpedo_price;
                    }

                    if ($torps_buy != 0)
                    {
                        echo $langvars['l_tdr_bought'] . " " . number_format($torps_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_torps'] . "<br>";
                    }

                    $sourcecost-= $torps_buy * $torpedo_price;
                }
                else
                {
                    $torps_buy = 0;
                }

                if ($torps_buy == 0 && $colonists_buy == 0 && $fighters_buy == 0)
                {
                    echo $langvars['l_tdr_nothingtotrade'] . "<br>";
                }

                if ($traderoute['circuit'] == '1')
                {
                    $resb = $db->Execute("UPDATE {$db->prefix}ships SET ship_colonists=ship_colonists+?, ship_fighters=ship_fighters+?,torps=torps+?, ship_energy=ship_energy+? WHERE ship_id=?", array($colonists_buy, $fighters_buy, $torps_buy, $dist['scooped1'], $playerinfo['ship_id']));
                    \Tki\Db::logDbErrors($db, $resb, __LINE__, __FILE__);
                }
            }
            // Normal Port Section
            else
            {
                // Sells commodities
                // Added below initializations, for traderoute bug
                $ore_buy = 0;
                $goods_buy = 0;
                $organics_buy = 0;
                $energy_buy = 0;

                if ($source['port_type'] != 'ore')
                {
                    $ore_price1 = $ore_price + $ore_delta * $source['port_ore'] / $ore_limit * $inventory_factor;
                    if ($source['port_ore'] - $playerinfo['ship_ore'] < 0)
                    {
                        $ore_buy = $source['port_ore'];
                        $portfull = 1;
                    }
                    else
                    {
                        $ore_buy = $playerinfo['ship_ore'];
                    }

                    $sourcecost += $ore_buy * $ore_price1;
                    if ($ore_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . "<br>";
                        }
                    }
                    $playerinfo['ship_ore'] -= $ore_buy;
                }

                $portfull = 0;
                if ($source['port_type'] != 'goods')
                {
                    $goods_price1 = $goods_price + $goods_delta * $source['port_goods'] / $goods_limit * $inventory_factor;
                    if ($source['port_goods'] - $playerinfo['ship_goods'] < 0)
                    {
                        $goods_buy = $source['port_goods'];
                        $portfull = 1;
                    }
                    else
                    {
                        $goods_buy = $playerinfo['ship_goods'];
                    }

                    $sourcecost += $goods_buy * $goods_price1;
                    if ($goods_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . "<br>";
                        }
                    }
                    $playerinfo['ship_goods'] -= $goods_buy;
                }

                $portfull = 0;
                if ($source['port_type'] != 'organics')
                {
                    $organics_price1 = $organics_price + $organics_delta * $source['port_organics'] / $organics_limit * $inventory_factor;
                    if ($source['port_organics'] - $playerinfo['ship_organics'] < 0)
                    {
                        $organics_buy = $source['port_organics'];
                        $portfull = 1;
                    }
                    else
                    {
                        $organics_buy = $playerinfo['ship_organics'];
                    }

                    $sourcecost += $organics_buy * $organics_price1;
                    if ($organics_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . "<br>";
                        }
                    }
                    $playerinfo['ship_organics'] -= $organics_buy;
                }

                $portfull = 0;
                if ($source['port_type'] != 'energy' && $playerinfo['trade_energy'] == 'Y')
                {
                    $energy_price1 = $energy_price + $energy_delta * $source['port_energy'] / $energy_limit * $inventory_factor;
                    if ($source['port_energy'] - $playerinfo['ship_energy'] < 0)
                    {
                        $energy_buy = $source['port_energy'];
                        $portfull = 1;
                    }
                    else
                    {
                        $energy_buy = $playerinfo['ship_energy'];
                    }
                    $sourcecost += $energy_buy * $energy_price1;
                    if ($energy_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . "<br>";
                        }
                    }
                    $playerinfo['ship_energy'] -= $energy_buy;
                }

                $free_holds = \Tki\CalcLevels::holds($playerinfo['hull'], $level_factor) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];

                // Time to buy
                if ($source['port_type'] == 'ore')
                {
                    $ore_price1 = $ore_price - $ore_delta * $source['port_ore'] / $ore_limit * $inventory_factor;
                    $ore_buy = $free_holds;
                    if ($playerinfo['credits'] + $sourcecost < $ore_buy * $ore_price1)
                    {
                        $ore_buy = ($playerinfo['credits'] + $sourcecost) / $ore_price1;
                    }

                    if ($source['port_ore'] < $ore_buy)
                    {
                        $ore_buy = $source['port_ore'];
                        if ($source['port_ore'] == 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . "(" . $langvars['l_tdr_portisempty'] . ")<br>";
                        }
                    }

                    if ($ore_buy != 0)
                    {
                        echo $langvars['l_tdr_bought'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . "<br>";
                    }
                    $playerinfo['ship_ore'] += $ore_buy;
                    $sourcecost -= $ore_buy * $ore_price1;
                    $resc = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=port_ore-?, port_energy=port_energy-?, port_goods=port_goods-?, port_organics=port_organics-? WHERE sector_id=?", array($ore_buy, $energy_buy, $goods_buy, $organics_buy, $source['sector_id']));
                    \Tki\Db::logDbErrors($db, $resc, __LINE__, __FILE__);
                }

                if ($source['port_type'] == 'goods')
                {
                    $goods_price1 = $goods_price - $goods_delta * $source['port_goods'] / $goods_limit * $inventory_factor;
                    $goods_buy = $free_holds;
                    if ($playerinfo['credits'] + $sourcecost < $goods_buy * $goods_price1)
                    {
                        $goods_buy = ($playerinfo['credits'] + $sourcecost) / $goods_price1;
                    }

                    if ($source['port_goods'] < $goods_buy)
                    {
                        $goods_buy = $source['port_goods'];
                        if ($source['port_goods'] == 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . " (" . $langvars['l_tdr_portisempty'] . ")<br>";
                        }
                    }

                    if ($goods_buy != 0)
                    {
                        echo $langvars['l_tdr_bought'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . "<br>";
                    }

                    $playerinfo['ship_goods'] += $goods_buy;
                    $sourcecost -= $goods_buy * $goods_price1;

                    $resd = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=port_ore-?, port_energy=port_energy-?, port_goods=port_goods-?, port_organics=port_organics-? WHERE sector_id=?", array($ore_buy, $energy_buy, $goods_buy, $organics_buy, $source['sector_id']));
                    \Tki\Db::logDbErrors($db, $resd, __LINE__, __FILE__);
                }

                if ($source['port_type'] == 'organics')
                {
                    $organics_price1 = $organics_price - $organics_delta * $source['port_organics'] / $organics_limit * $inventory_factor;
                    $organics_buy = $free_holds;

                    if ($playerinfo['credits'] + $sourcecost < $organics_buy * $organics_price1)
                    {
                        $organics_buy = ($playerinfo['credits'] + $sourcecost) / $organics_price1;
                    }

                    if ($source['port_organics'] < $organics_buy)
                    {
                        $organics_buy = $source['port_organics'];
                        if ($source['port_organics'] == 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . " (" . $langvars['l_tdr_portisempty'] . ")<br>";
                        }
                    }

                    if ($organics_buy != 0)
                    {
                        echo $langvars['l_tdr_bought'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . "<br>";
                    }

                    $playerinfo['ship_organics'] += $organics_buy;
                    $sourcecost -= $organics_buy * $organics_price1;
                    $rese = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=port_ore-?, port_energy=port_energy-?, port_goods=port_goods-?, port_organics=port_organics-? WHERE sector_id=?", array($ore_buy, $energy_buy, $goods_buy, $organics_buy, $source['sector_id']));
                    \Tki\Db::logDbErrors($db, $rese, __LINE__, __FILE__);
                }

                if ($source['port_type'] == 'energy')
                {
                    $energy_price1 = $energy_price - $energy_delta * $source['port_energy'] / $energy_limit * $inventory_factor;
                    $energy_buy = \Tki\CalcLevels::energy($playerinfo['power'], $level_factor) - $playerinfo['ship_energy'] - $dist['scooped1'];

                    if ($playerinfo['credits'] + $sourcecost < $energy_buy * $energy_price1)
                    {
                        $energy_buy = ($playerinfo['credits'] + $sourcecost) / $energy_price1;
                    }

                    if ($source['port_energy'] < $energy_buy)
                    {
                        $energy_buy = $source['port_energy'];
                        if ($source['port_energy'] == 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . " (" . $langvars['l_tdr_portisempty'] . ")<br>";
                        }
                    }

                    if ($energy_buy != 0)
                    {
                        echo $langvars['l_tdr_bought'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . "<br>";
                    }
                    $playerinfo['ship_energy'] += $energy_buy;
                    $sourcecost -= $energy_buy * $energy_price1;
                    $resf = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=port_ore-?, port_energy=port_energy-?, port_goods=port_goods-?, port_organics=port_organics-? WHERE sector_id=?", array($ore_buy, $energy_buy, $goods_buy, $organics_buy, $source['sector_id']));
                    \Tki\Db::logDbErrors($db, $resf, __LINE__, __FILE__);
                }

                if ($dist['scooped1'] > 0)
                {
                    $playerinfo['ship_energy']+= $dist['scooped1'];
                    if ($playerinfo['ship_energy'] > \Tki\CalcLevels::energy($playerinfo['power'], $level_factor))
                    {
                        $playerinfo['ship_energy'] = \Tki\CalcLevels::energy($playerinfo['power'], $level_factor);
                    }
                }

                if ($ore_buy == 0 && $goods_buy == 0 && $energy_buy == 0 && $organics_buy == 0)
                {
                    echo $langvars['l_tdr_nothingtotrade'] . "<br>";
                }

                if ($traderoute['circuit'] == '1')
                {
                    $resf = $db->Execute("UPDATE {$db->prefix}ships SET ship_ore=?, ship_goods=?, ship_organics=?, ship_energy=? WHERE ship_id=?", array($playerinfo['ship_ore'], $playerinfo['ship_goods'], $playerinfo['ship_organics'], $playerinfo['ship_energy'], $playerinfo['ship_id']));
                    \Tki\Db::logDbErrors($db, $resf, __LINE__, __FILE__);
                }
            }
        }
        // Source is planet
        elseif (($traderoute['source_type'] == 'L') || ($traderoute['source_type'] == 'C'))
        {
            $free_holds = \Tki\CalcLevels::holds($playerinfo['hull'], $level_factor) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
            if ($traderoute['dest_type'] == 'P')
            {
                // Pick stuff up to sell at port
                if (($playerinfo['ship_id'] == $source['owner']) || ($playerinfo['team'] == $source['corp']))
                {
                    if ($source['goods'] > 0 && $free_holds > 0 && $dest['port_type'] != 'goods')
                    {
                        if ($source['goods'] > $free_holds)
                        {
                            $goods_buy = $free_holds;
                        }
                        else
                        {
                            $goods_buy = $source['goods'];
                        }

                        $free_holds -= $goods_buy;
                        $playerinfo['ship_goods'] += $goods_buy;
                        echo $langvars['l_tdr_loaded'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . "<br>";
                    }
                    else
                    {
                        $goods_buy = 0;
                    }

                    if ($source['ore'] > 0 && $free_holds > 0 && $dest['port_type'] != 'ore')
                    {
                        if ($source['ore'] > $free_holds)
                        {
                            $ore_buy = $free_holds;
                        }
                        else
                        {
                            $ore_buy = $source['ore'];
                        }

                        $free_holds -= $ore_buy;
                        $playerinfo['ship_ore'] += $ore_buy;
                        echo $langvars['l_tdr_loaded'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . "<br>";
                    }
                    else
                    {
                        $ore_buy = 0;
                    }

                    if ($source['organics'] > 0 && $free_holds > 0 && $dest['port_type'] != 'organics')
                    {
                        if ($source['organics'] > $free_holds)
                        {
                            $organics_buy = $free_holds;
                        }
                        else
                        {
                            $organics_buy = $source['organics'];
                        }

                        $free_holds -= $organics_buy;
                        $playerinfo['ship_organics'] += $organics_buy;
                        echo $langvars['l_tdr_loaded'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . "<br>";
                    }
                    else
                    {
                        $organics_buy = 0;
                    }

                    if ($ore_buy == 0 && $goods_buy == 0 && $organics_buy == 0)
                    {
                        echo $langvars['l_tdr_nothingtoload'] . "<br>";
                    }

                    if ($traderoute['circuit'] == '1')
                    {
                        $resg = $db->Execute("UPDATE {$db->prefix}ships SET ship_ore=?, ship_goods=?, ship_organics=? WHERE ship_id=?", array($playerinfo['ship_ore'], $playerinfo['ship_goods'], $playerinfo['ship_organics'], $playerinfo['ship_id']));
                        \Tki\Db::logDbErrors($db, $resg, __LINE__, __FILE__);
                    }
                }
                else  // Buy from planet - not implemented yet
                {
                }

                $resh = $db->Execute("UPDATE {$db->prefix}planets SET ore=ore-?, goods=goods-?, organics=organics-? WHERE planet_id=?", array($ore_buy, $goods_buy, $organics_buy, $source['planet_id']));
                \Tki\Db::logDbErrors($db, $resh, __LINE__, __FILE__);
            }
            // Destination is a planet, so load cols and weapons
            elseif (($traderoute['dest_type'] == 'L') || ($traderoute['dest_type'] == 'C'))
            {
                if ($source['colonists'] > 0 && $free_holds > 0 && $playerinfo['trade_colonists'] == 'Y')
                {
                    if ($source['colonists'] > $free_holds)
                    {
                        $colonists_buy = $free_holds;
                    }
                    else
                    {
                        $colonists_buy = $source['colonists'];
                    }

                    $free_holds -= $colonists_buy;
                    $playerinfo['ship_colonists'] += $colonists_buy;
                    echo $langvars['l_tdr_loaded'] . " " . number_format($colonists_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_colonists'] . "<br>";
                }
                else
                {
                    $colonists_buy = 0;
                }

                $free_torps = \Tki\CalcLevels::torpedoes($playerinfo['torp_launchers'], $level_factor) - $playerinfo['torps'];
                if ($source['torps'] > 0 && $free_torps > 0 && $playerinfo['trade_torps'] == 'Y')
                {
                    if ($source['torps'] > $free_torps)
                    {
                        $torps_buy = $free_torps;
                    }
                    else
                    {
                        $torps_buy = $source['torps'];
                    }

                    $free_torps -= $torps_buy;
                    $playerinfo['torps'] += $torps_buy;
                    echo $langvars['l_tdr_loaded'] . " " . number_format($torps_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_torps'] . "<br>";
                }
                else
                {
                    $torps_buy = 0;
                }

                $free_fighters = \Tki\CalcLevels::fighters($playerinfo['computer'], $level_factor) - $playerinfo['ship_fighters'];
                if ($source['fighters'] > 0 && $free_fighters > 0 && $playerinfo['trade_fighters'] == 'Y')
                {
                    if ($source['fighters'] > $free_fighters)
                    {
                        $fighters_buy = $free_fighters;
                    }
                    else
                    {
                        $fighters_buy = $source['fighters'];
                    }

                    $free_fighters -= $fighters_buy;
                    $playerinfo['ship_fighters'] += $fighters_buy;
                    echo $langvars['l_tdr_loaded'] . " " . number_format($fighters_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_fighters'] . "<br>";
                }
                else
                {
                    $fighters_buy = 0;
                }

                if ($fighters_buy == 0 && $torps_buy == 0 && $colonists_buy == 0)
                {
                    echo $langvars['l_tdr_nothingtoload'] . "<br>";
                }

                if ($traderoute['circuit'] == '1')
                {
                    $resi = $db->Execute("UPDATE {$db->prefix}ships SET torps=?, ship_fighters=?, ship_colonists=? WHERE ship_id=?", array($playerinfo['torps'], $playerinfo['ship_fighters'], $playerinfo['ship_colonists'], $playerinfo['ship_id']));
                    \Tki\Db::logDbErrors($db, $resi, __LINE__, __FILE__);
                }

                $resj = $db->Execute("UPDATE {$db->prefix}planets SET colonists=colonists-?, torps=torps-?, fighters=fighters-? WHERE planet_id=?", array($colonists_buy, $torps_buy, $fighters_buy, $source['planet_id']));
                \Tki\Db::logDbErrors($db, $resj, __LINE__, __FILE__);
            }
        }

        if ($dist['scooped1'] != 0)
        {
            echo $langvars['l_tdr_scooped'] . " " . number_format($dist['scooped1'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . "<br>";
        }

        Traderoute::traderouteResultsCloseCell();

        if ($traderoute['circuit'] == '2')
        {
            $playerinfo['credits'] += $sourcecost;
            $destcost = 0;
            if ($traderoute['dest_type'] == 'P')
            {
                // Added the below for traderoute bug
                $ore_buy = 0;
                $goods_buy = 0;
                $organics_buy = 0;
                $energy_buy = 0;

                // Sells commodities
                $portfull = 0;
                if ($dest['port_type'] != 'ore')
                {
                    $ore_price1 = $ore_price + $ore_delta * $dest['port_ore'] / $ore_limit * $inventory_factor;
                    if ($dest['port_ore'] - $playerinfo['ship_ore'] < 0)
                    {
                        $ore_buy = $dest['port_ore'];
                        $portfull = 1;
                    }
                    else
                    {
                        $ore_buy = $playerinfo['ship_ore'];
                    }

                    $destcost += $ore_buy * $ore_price1;

                    if ($ore_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . "<br>";
                        }
                    }
                    $playerinfo['ship_ore'] -= $ore_buy;
                }

                $portfull = 0;
                if ($dest['port_type'] != 'goods')
                {
                    $goods_price1 = $goods_price + $goods_delta * $dest['port_goods'] / $goods_limit * $inventory_factor;
                    if ($dest['port_goods'] - $playerinfo['ship_goods'] < 0)
                    {
                        $goods_buy = $dest['port_goods'];
                        $portfull = 1;
                    }
                    else
                    {
                        $goods_buy = $playerinfo['ship_goods'];
                    }

                    $destcost += $goods_buy * $goods_price1;
                    if ($goods_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . "<br>";
                        }
                    }
                    $playerinfo['ship_goods'] -= $goods_buy;
                }

                $portfull = 0;
                if ($dest['port_type'] != 'organics')
                {
                    $organics_price1 = $organics_price + $organics_delta * $dest['port_organics'] / $organics_limit * $inventory_factor;
                    if ($dest['port_organics'] - $playerinfo['ship_organics'] < 0)
                    {
                        $organics_buy = $dest['port_organics'];
                        $portfull = 1;
                    }
                    else
                    {
                        $organics_buy = $playerinfo['ship_organics'];
                    }

                    $destcost += $organics_buy * $organics_price1;
                    if ($organics_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . "<br>";
                        }
                    }
                    $playerinfo['ship_organics'] -= $organics_buy;
                }

                $portfull = 0;
                if ($dest['port_type'] != 'energy' && $playerinfo['trade_energy'] == 'Y')
                {
                    $energy_price1 = $energy_price + $energy_delta * $dest['port_energy'] / $energy_limit * $inventory_factor;
                    if ($dest['port_energy'] - $playerinfo['ship_energy'] < 0)
                    {
                        $energy_buy = $dest['port_energy'];
                        $portfull = 1;
                    }
                    else
                    {
                        $energy_buy = $playerinfo['ship_energy'];
                    }

                    $destcost += $energy_buy * $energy_price1;
                    if ($energy_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . "<br>";
                        }
                    }
                    $playerinfo['ship_energy'] -= $energy_buy;
                }
                else
                {
                    $energy_buy = 0;
                }

                $free_holds = \Tki\CalcLevels::holds($playerinfo['hull'], $level_factor) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];

                // Time to buy
                if ($dest['port_type'] == 'ore')
                {
                    $ore_price1 = $ore_price - $ore_delta * $dest['port_ore'] / $ore_limit * $inventory_factor;
                    if ($traderoute['source_type'] == 'L')
                    {
                        $ore_buy = 0;
                    }
                    else
                    {
                        $ore_buy = $free_holds;
                        if ($playerinfo['credits'] + $destcost < $ore_buy * $ore_price1)
                        {
                            $ore_buy = ($playerinfo['credits'] + $destcost) / $ore_price1;
                        }

                        if ($dest['port_ore'] < $ore_buy)
                        {
                            $ore_buy = $dest['port_ore'];
                            if ($dest['port_ore'] == 0)
                            {
                                echo $langvars['l_tdr_bought'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . " (" . $langvars['l_tdr_portisempty'] . ")<br>";
                            }
                        }

                        if ($ore_buy != 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . "<br>";
                        }

                        $playerinfo['ship_ore'] += $ore_buy;
                        $destcost -= $ore_buy * $ore_price1;
                    }
                    $resk = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=port_ore-?, port_energy=port_energy-?, port_goods=port_goods-?, port_organics=port_organics-? WHERE sector_id=?", array($ore_buy, $energy_buy, $goods_buy, $organics_buy, $dest['sector_id']));
                    \Tki\Db::logDbErrors($db, $resk, __LINE__, __FILE__);
                }

                if ($dest['port_type'] == 'goods')
                {
                    $goods_price1 = $goods_price - $goods_delta * $dest['port_goods'] / $goods_limit * $inventory_factor;
                    if ($traderoute['source_type'] == 'L')
                    {
                        $goods_buy = 0;
                    }
                    else
                    {
                        $goods_buy = $free_holds;
                        if ($playerinfo['credits'] + $destcost < $goods_buy * $goods_price1)
                        {
                            $goods_buy = ($playerinfo['credits'] + $destcost) / $goods_price1;
                        }

                        if ($dest['port_goods'] < $goods_buy)
                        {
                            $goods_buy = $dest['port_goods'];
                            if ($dest['port_goods'] == 0)
                            {
                                echo $langvars['l_tdr_bought'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . " (" . $langvars['l_tdr_portisempty'] . ")<br>";
                            }
                        }

                        if ($goods_buy != 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . "<br>";
                        }

                        $playerinfo['ship_goods'] += $goods_buy;
                        $destcost -= $goods_buy * $goods_price1;
                    }
                    $resl = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=port_ore-?, port_energy=port_energy-?, port_goods=port_goods-?, port_organics=port_organics-? WHERE sector_id=?", array($ore_buy, $energy_buy, $goods_buy, $organics_buy, $dest['sector_id']));
                    \Tki\Db::logDbErrors($db, $resl, __LINE__, __FILE__);
                }

                if ($dest['port_type'] == 'organics')
                {
                    $organics_price1 = $organics_price - $organics_delta * $dest['port_organics'] / $organics_limit * $inventory_factor;
                    if ($traderoute['source_type'] == 'L')
                    {
                        $organics_buy = 0;
                    }
                    else
                    {
                        $organics_buy = $free_holds;
                        if ($playerinfo['credits'] + $destcost < $organics_buy * $organics_price1)
                        {
                            $organics_buy = ($playerinfo['credits'] + $destcost) / $organics_price1;
                        }
                        if ($dest['port_organics'] < $organics_buy)
                        {
                            $organics_buy = $dest['port_organics'];

                            if ($dest['port_organics'] == 0)
                            {
                                echo $langvars['l_tdr_bought'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . " (" . $langvars['l_tdr_portisempty'] . ")<br>";
                            }
                        }

                        if ($organics_buy != 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . "<br>";
                        }

                        $playerinfo['ship_organics'] += $organics_buy;
                        $destcost -= $organics_buy * $organics_price1;
                    }
                    $resm = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=port_ore-?, port_energy=port_energy-?, port_goods=port_goods-?, port_organics=port_organics-? WHERE sector_id=?", array($ore_buy, $energy_buy, $goods_buy, $organics_buy, $dest['sector_id']));
                    \Tki\Db::logDbErrors($db, $resm, __LINE__, __FILE__);
                }

                if ($dest['port_type'] == 'energy')
                {
                    $energy_price1 = $energy_price - $energy_delta * $dest['port_energy'] / $energy_limit * $inventory_factor;
                    if ($traderoute['source_type'] == 'L')
                    {
                        $energy_buy = 0;
                    }
                    else
                    {
                        $energy_buy = \Tki\CalcLevels::energy($playerinfo['power'], $level_factor) - $playerinfo['ship_energy'] - $dist['scooped1'];
                        if ($playerinfo['credits'] + $destcost < $energy_buy * $energy_price1)
                        {
                            $energy_buy = ($playerinfo['credits'] + $destcost) / $energy_price1;
                        }

                        if ($dest['port_energy'] < $energy_buy)
                        {
                            $energy_buy = $dest['port_energy'];
                            if ($dest['port_energy'] == 0)
                            {
                                echo $langvars['l_tdr_bought'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . " (" . $langvars['l_tdr_portisempty'] . ")<br>";
                            }
                        }

                        if ($energy_buy != 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . "<br>";
                        }

                        $playerinfo['ship_energy'] += $energy_buy;
                        $destcost -= $energy_buy * $energy_price1;
                    }

                    if ($ore_buy == 0 && $goods_buy == 0 && $energy_buy == 0 && $organics_buy == 0)
                    {
                        echo $langvars['l_tdr_nothingtotrade'] . "<br>";
                    }

                    $resn = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=port_ore-?, port_energy=port_energy-?, port_goods=port_goods-?, port_organics=port_organics-? WHERE sector_id=?", array($ore_buy, $energy_buy, $goods_buy, $organics_buy, $dest['sector_id']));
                    \Tki\Db::logDbErrors($db, $resn, __LINE__, __FILE__);
                }

                if ($dist['scooped2'] > 0)
                {
                    $playerinfo['ship_energy']+= $dist['scooped2'];

                    if ($playerinfo['ship_energy'] > \Tki\CalcLevels::energy($playerinfo['power'], $level_factor))
                    {
                        $playerinfo['ship_energy'] = \Tki\CalcLevels::energy($playerinfo['power'], $level_factor);
                    }
                }
                $reso = $db->Execute("UPDATE {$db->prefix}ships SET ship_ore=?, ship_goods=?, ship_organics=?, ship_energy=? WHERE ship_id=?", array($playerinfo['ship_ore'], $playerinfo['ship_goods'], $playerinfo['ship_organics'], $playerinfo['ship_energy'], $playerinfo['ship_id']));
                \Tki\Db::logDbErrors($db, $reso, __LINE__, __FILE__);
            }
            else // Dest is planet
            {
                if ($traderoute['source_type'] == 'L' || $traderoute['source_type'] == 'C')
                {
                    $colonists_buy = 0;
                    $fighters_buy = 0;
                    $torps_buy = 0;
                }

                $setcol = 0;

                if ($playerinfo['trade_colonists'] == 'Y')
                {
                    $colonists_buy += $playerinfo['ship_colonists'];
                    $col_dump = $playerinfo['ship_colonists'];
                    if ($dest['colonists'] + $colonists_buy >= $colonist_limit)
                    {
                        $exceeding = $dest['colonists'] + $colonists_buy - $colonist_limit;
                        $col_dump = $exceeding;
                        $setcol = 1;
                        $colonists_buy-= $exceeding;
                        if ($colonists_buy < 0)
                        {
                            $colonists_buy = 0;
                        }
                    }
                }
                else
                {
                    $col_dump = 0;
                }

                if ($colonists_buy != 0)
                {
                    if ($setcol == 1)
                    {
                        echo $langvars['l_tdr_dumped'] . " " . number_format($colonists_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_colonists'] . " (" . $langvars['l_tdr_planetisovercrowded'] . ")<br>";
                    }
                    else
                    {
                        echo $langvars['l_tdr_dumped'] . " " . number_format($colonists_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_colonists'] . "<br>";
                    }
                }

                if ($playerinfo['trade_fighters'] == 'Y')
                {
                    $fighters_buy += $playerinfo['ship_fighters'];
                    $fight_dump = $playerinfo['ship_fighters'];
                }
                else
                {
                    $fight_dump = 0;
                }

                if ($fighters_buy != 0)
                {
                    echo $langvars['l_tdr_dumped'] . " " . number_format($fighters_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_fighters'] . "<br>";
                }

                if ($playerinfo['trade_torps'] == 'Y')
                {
                    $torps_buy += $playerinfo['torps'];
                    $torps_dump = $playerinfo['torps'];
                }
                else
                {
                    $torps_dump = 0;
                }

                if ($torps_buy != 0)
                {
                    echo $langvars['l_tdr_dumped'] . " " . number_format($torps_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_torps'] . "<br>";
                }

                if ($torps_buy == 0 && $fighters_buy == 0 && $colonists_buy == 0)
                {
                    echo $langvars['l_tdr_nothingtodump'] . "<br>";
                }

                if ($traderoute['source_type'] == 'L' || $traderoute['source_type'] == 'C')
                {
                    if ($playerinfo['trade_colonists'] == 'Y')
                    {
                        if ($setcol != 1)
                        {
                            $col_dump = 0;
                        }
                    }
                    else
                    {
                        $col_dump = $playerinfo['ship_colonists'];
                    }

                    if ($playerinfo['trade_fighters'] == 'Y')
                    {
                        $fight_dump = 0;
                    }
                    else
                    {
                        $fight_dump = $playerinfo['ship_fighters'];
                    }

                    if ($playerinfo['trade_torps'] == 'Y')
                    {
                        $torps_dump = 0;
                    }
                    else
                    {
                        $torps_dump = $playerinfo['torps'];
                    }
                }

                $resp = $db->Execute("UPDATE {$db->prefix}planets SET colonists=colonists+?, fighters=fighters+?, torps=torps+? WHERE planet_id=?", array($colonists_buy, $fighters_buy, $torps_buy, $traderoute['dest_id']));
                \Tki\Db::logDbErrors($db, $resp, __LINE__, __FILE__);

                if ($traderoute['source_type'] == 'L' || $traderoute['source_type'] == 'C')
                {
                    $resq = $db->Execute("UPDATE {$db->prefix}ships SET ship_colonists=?, ship_fighters=?, torps=?, ship_energy=ship_energy+? WHERE ship_id=?", array($col_dump, $fight_dump, $torps_dump, $dist['scooped'], $playerinfo['ship_id']));
                    \Tki\Db::logDbErrors($db, $resq, __LINE__, __FILE__);
                }
                else
                {
                    if ($setcol == 1)
                    {
                        $resr = $db->Execute("UPDATE {$db->prefix}ships SET ship_colonists=?, ship_fighters=ship_fighters-?, torps=torps-?, ship_energy=ship_energy+? WHERE ship_id=?", array($col_dump, $fight_dump, $torps_dump, $dist['scooped'], $playerinfo['ship_id']));
                        \Tki\Db::logDbErrors($db, $resr, __LINE__, __FILE__);
                    }
                    else
                    {
                        $ress = $db->Execute("UPDATE {$db->prefix}ships SET ship_colonists=ship_colonists-?, ship_fighters=ship_fighters-?, torps=torps-?, ship_energy=ship_energy+? WHERE ship_id=?", array($col_dump, $fight_dump, $torps_dump, $dist['scooped'], $playerinfo['ship_id']));
                        \Tki\Db::logDbErrors($db, $ress, __LINE__, __FILE__);
                    }
                }
            }
            if ($dist['scooped2'] != 0)
            {
                echo $langvars['l_tdr_scooped'] . " " . number_format($dist['scooped1'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . "<br>";
            }
        }
        else
        {
            echo $langvars['l_tdr_onlyonewaytdr'];
            $destcost = 0;
        }
        Traderoute::traderouteResultsShowCost();

        if ($sourcecost > 0)
        {
            echo $langvars['l_tdr_profit'] . " : " . number_format(abs($sourcecost), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
        }
        else
        {
            echo $langvars['l_tdr_cost'] . " : " . number_format(abs($sourcecost), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
        }
        Traderoute::traderouteResultsCloseCost();

        if ($destcost > 0)
        {
            echo $langvars['l_tdr_profit'] . " : " . number_format(abs($destcost), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
        }
        else
        {
            echo $langvars['l_tdr_cost'] . " : " . number_format(abs($destcost), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
        }

        Traderoute::traderouteResultsCloseTable();

        $total_profit = $sourcecost + $destcost;
        Traderoute::traderouteResultsDisplayTotals($db, $pdo_db, $lang, $langvars, $total_profit);

        if ($traderoute['circuit'] == '1')
        {
            $newsec = $destport['sector_id'];
        }
        else
        {
            $newsec = $sourceport['sector_id'];
        }
        $rest = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-?, credits=credits+?, turns_used=turns_used+?, sector=? WHERE ship_id=?", array($dist['triptime'], $total_profit, $dist['triptime'], $newsec, $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($db, $rest, __LINE__, __FILE__);
        $playerinfo['credits']+= $total_profit - $sourcecost;
        $playerinfo['turns']-= $dist['triptime'];

        $tdr_display_creds = number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
        Traderoute::traderouteResultsDisplaySummary($db, $pdo_db, $lang, $langvars, $tdr_display_creds, $dist, $playerinfo);
        // echo $j." -- ";
        if ($traderoute['circuit'] == 2)
        {
            $langvars['l_tdr_engageagain'] = str_replace("[here]", "<a href=\"traderoute.php?engage=[tdr_engage]\">" . $langvars['l_here'] . "</a>", $langvars['l_tdr_engageagain']);
            $langvars['l_tdr_engageagain'] = str_replace("[five]", "<a href=\"traderoute.php?engage=[tdr_engage]&amp;tr_repeat=5\">" . $langvars['l_tdr_five'] . "</a>", $langvars['l_tdr_engageagain']);
            $langvars['l_tdr_engageagain'] = str_replace("[ten]", "<a href=\"traderoute.php?engage=[tdr_engage]&amp;tr_repeat=10\">" . $langvars['l_tdr_ten'] . "</a>", $langvars['l_tdr_engageagain']);
            $langvars['l_tdr_engageagain'] = str_replace("[fifty]", "<a href=\"traderoute.php?engage=[tdr_engage]&amp;tr_repeat=50\">" . $langvars['l_tdr_fifty'] . "</a>", $langvars['l_tdr_engageagain']);
            $langvars['l_tdr_engageagain'] = str_replace("[tdr_engage]", $engage, $langvars['l_tdr_engageagain']);
            if ($j == 1)
            {
                echo $langvars['l_tdr_engageagain'] . "\n";
                Traderoute::traderouteResultsShowRepeat($engage);
            }
        }
        if ($j == 1)
        {
            Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, null, $template);
        }
    }

    public static function traderouteNew($db, $pdo_db, $lang, $langvars, $tkireg, $traderoute_id, $template, $num_traderoutes, $playerinfo, $color_line1, $color_line2, $color_header)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer'));
        $editroute = null;

        if (!empty ($traderoute_id))
        {
            $result = $db->Execute("SELECT * FROM {$db->prefix}traderoutes WHERE traderoute_id=?", array($traderoute_id));
            \Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);

            if (!$result || $result->EOF)
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_editerr'], $template);
            }

            $editroute = $result->fields;

            if ($editroute['owner'] != $playerinfo['ship_id'])
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_notowner'], $template);
            }
        }

        if ($num_traderoutes >= $tkireg->max_traderoutes_player && is_null($editroute))
        {
            Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, '<p>' . $langvars['l_tdr_maxtdr'] . '<p>', $template);
        }

        echo "<p><font size=3 color=blue><strong>";

        if (is_null($editroute))
        {
            echo $langvars['l_tdr_createnew'];
        }
        else
        {
            echo $langvars['l_tdr_editinga'] . " ";
        }

        echo $langvars['l_tdr_traderoute'] . "</strong></font><p>";

        // Get Planet info Corp and Personal

        $result = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE owner=? ORDER BY sector_id", array($playerinfo['ship_id']));
        \Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);

        $num_planets = $result->RecordCount();
        $i = 0;
        while (!$result->EOF)
        {
            $planets[$i] = $result->fields;

            if ($planets[$i]['name'] === null)
            {
                $planets[$i]['name'] = $langvars['l_tdr_unnamed'];
            }

            $i++;
            $result->MoveNext();
        }

        $result = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE corp=? AND corp!=0 AND owner<>? ORDER BY sector_id", array($playerinfo['team'], $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);

        $num_corp_planets = $result->RecordCount();
        $i = 0;
        while (!$result->EOF)
        {
            $planets_corp[$i] = $result->fields;

            if ($planets_corp[$i]['name'] === null)
            {
                $planets_corp[$i]['name'] = $langvars['l_tdr_unnamed'];
            }

            $i++;
            $result->MoveNext();
        }

        // Display Current Sector
        echo $langvars['l_tdr_cursector'] . " " . $playerinfo['sector'] . "<br>";

        // Start of form for starting location
        echo "
            <form accept-charset='utf-8' action=traderoute.php?command=create method=post>
            <table border=0><tr>
            <td align=right><font size=2><strong>" . $langvars['l_tdr_selspoint'] . " <br>&nbsp;</strong></font></td>
            <tr>
            <td align=right><font size=2>" . $langvars['l_tdr_port'] . " : </font></td>
            <td><input type=radio name=\"ptype1\" value=\"port\"
            ";

        if (is_null($editroute) || (!is_null($editroute) && $editroute['source_type'] == 'P'))
        {
            echo " checked";
        }

        echo "
            ></td>
            <td>&nbsp;&nbsp;<input type=text name=port_id1 size=20 align='center'
            ";

        if (!is_null($editroute) && $editroute['source_type'] == 'P')
        {
            echo " value=\"$editroute[source_id]\"";
        }

        echo "
            ></td>
            </tr><tr>
            ";

        // Personal Planet
        echo "
            <td align=right><font size=2>Personal " . $langvars['l_tdr_planet'] . " : </font></td>
            <td><input type=radio name=\"ptype1\" value=\"planet\"
            ";

        if (!is_null($editroute) && $editroute['source_type'] == 'L')
        {
            echo " checked";
        }

        echo '
            ></td>
            <td>&nbsp;&nbsp;<select name=planet_id1>
            ';

        if ($num_planets == 0)
        {
            echo "<option value=none>" . $langvars['l_tdr_none'] . "</option>";
        }
        else
        {
            $i = 0;
            while ($i < $num_planets)
            {
                echo "<option ";

                if ($planets[$i]['planet_id'] == $editroute['source_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets[$i]['planet_id'] . ">" . $planets[$i]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets[$i]['sector_id'] . "</option>";
                $i++;
            }
        }

        // Corp Planet
        echo "
            </tr><tr>
            <td align=right><font size=2>Corporate " . $langvars['l_tdr_planet'] . " : </font></td>
            <td><input type=radio name=\"ptype1\" value=\"corp_planet\"
            ";

        if (!is_null($editroute) && $editroute['source_type'] == 'C')
        {
            echo " checked";
        }

        echo '
            ></td>
            <td>&nbsp;&nbsp;<select name=corp_planet_id1>
            ';

        if ($num_corp_planets == 0)
        {
            echo "<option value=none>" . $langvars['l_tdr_none'] . "</option>";
        }
        else
        {
            $i = 0;
            while ($i < $num_corp_planets)
            {
                echo "<option ";

                if ($planets_corp[$i]['planet_id'] == $editroute['source_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets_corp[$i]['planet_id'] . ">" . $planets_corp[$i]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets_corp[$i]['sector_id'] . "</option>";
                $i++;
            }
        }

        echo "
            </select>
            </tr>";

        // Begin Ending point selection
        echo "
            <tr><td>&nbsp;
            </tr><tr>
            <td align=right><font size=2><strong>" . $langvars['l_tdr_selendpoint'] . " : <br>&nbsp;</strong></font></td>
            <tr>
            <td align=right><font size=2>" . $langvars['l_tdr_port'] . " : </font></td>
            <td><input type=radio name=\"ptype2\" value=\"port\"
            ";

        if (is_null($editroute) || (!is_null($editroute) && $editroute['dest_type'] == 'P'))
        {
            echo " checked";
        }

        echo '
            ></td>
            <td>&nbsp;&nbsp;<input type=text name=port_id2 size=20 align="center"
            ';

        if (!is_null($editroute) && $editroute['dest_type'] == 'P')
        {
            echo " value=\"$editroute[dest_id]\"";
        }

        echo "
            ></td>
            </tr>";

        // Personal Planet
        echo "
            <tr>
            <td align=right><font size=2>Personal " . $langvars['l_tdr_planet'] . " : </font></td>
            <td><input type=radio name=\"ptype2\" value=\"planet\"
            ";

        if (!is_null($editroute) && $editroute['dest_type'] == 'L')
        {
            echo " checked";
        }

        echo '
            ></td>
            <td>&nbsp;&nbsp;<select name=planet_id2>
            ';

        if ($num_planets == 0)
        {
            echo "<option value=none>" . $langvars['l_tdr_none'] . "</option>";
        }
        else
        {
            $i = 0;
            while ($i < $num_planets)
            {
                echo "<option ";

                if ($planets[$i]['planet_id'] == $editroute['dest_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets[$i]['planet_id'] . ">" . $planets[$i]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets[$i]['sector_id'] . "</option>";
                $i++;
            }
        }

        // Corp Planet
        echo "
            </tr><tr>
            <td align=right><font size=2>Corporate " . $langvars['l_tdr_planet'] . " : </font></td>
            <td><input type=radio name=\"ptype2\" value=\"corp_planet\"
            ";

        if (!is_null($editroute) && $editroute['dest_type'] == 'C')
        {
            echo " checked";
        }

        echo '
            ></td>
            <td>&nbsp;&nbsp;<select name=corp_planet_id2>
            ';

        if ($num_corp_planets == 0)
        {
            echo "<option value=none>" . $langvars['l_tdr_none'] . "</option>";
        }
        else
        {
            $i = 0;
            while ($i < $num_corp_planets)
            {
                echo "<option ";

                if ($planets_corp[$i]['planet_id'] == $editroute['dest_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets_corp[$i]['planet_id'] . ">" . $planets_corp[$i]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets_corp[$i]['sector_id'] . "</option>";
                $i++;
            }
        }
        echo "
            </select>
            </tr>";

        echo "
            </select>
            </tr><tr>
            <td>&nbsp;
            </tr><tr>
            <td align=right><font size=2><strong>" . $langvars['l_tdr_selmovetype'] . " : </strong></font></td>
            <td colspan=2 valign=top><font size=2><input type=radio name=\"move_type\" value=\"realspace\"
            ";

        if (is_null($editroute) || (!is_null($editroute) && $editroute['move_type'] == 'R'))
        {
            echo " checked";
        }

        echo "
            >&nbsp;" . $langvars['l_tdr_realspace'] . "&nbsp;&nbsp<font size=2><input type=radio name=\"move_type\" value=\"warp\"
            ";

        if (!is_null($editroute) && $editroute['move_type'] == 'W')
        {
            echo " checked";
        }

        echo "
            >&nbsp;" . $langvars['l_tdr_warp'] . "</font></td>
            </tr><tr>
            <td align=right><font size=2><strong>" . $langvars['l_tdr_selcircuit'] . " : </strong></font></td>
            <td colspan=2 valign=top><font size=2><input type=radio name=\"circuit_type\" value=\"1\"
            ";

        if (is_null($editroute) || (!empty ($editroute) && $editroute['circuit'] == '1'))
        {
            echo " checked";
        }

        echo "
            >&nbsp;" . $langvars['l_tdr_oneway'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=\"circuit_type\" value=\"2\"
            ";

        if (!is_null($editroute) && $editroute['circuit'] == '2')
        {
            echo " checked";
        }

        echo "
            >&nbsp;" . $langvars['l_tdr_bothways'] . "</font></td>
            </tr><tr>
            <td>&nbsp;
            </tr><tr>
            <td><td><td align='center'>
            ";

        if (is_null($editroute))
        {
            echo "<input type=submit value=\"" . $langvars['l_tdr_create'] . "\">";
        }
        else
        {
            echo "<input type=hidden name=editing value=$editroute[traderoute_id]>";
            echo "<input type=submit value=\"" . $langvars['l_tdr_modify'] . "\">";
        }

        $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);

        echo "
            </table>
            " . $langvars['l_tdr_returnmenu'] . "<br>
            </form>
            ";

        echo "<div style='text-align:left;'>\n";
        \Tki\Text::gotoMain($db, $lang, $langvars);
        echo "</div>\n";

        \Tki\Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
        die ();
    }

    public static function traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $error_msg, $template)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        echo "<p>" . $error_msg . "<p>";
        echo "<div style='text-align:left;'>\n";
        \Tki\Text::gotoMain($db, $lang, $langvars);
        echo "</div>\n";
        \Tki\Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
        die ();
    }

    public static function traderouteCheckCompatible($db, $pdo_db, $lang, $langvars, $type1, $type2, $move, $circuit, $src, $dest, $playerinfo)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        // Check circuit compatibility (we only use types 1 and 2 so block anything else)
        if ($circuit != "1" && $circuit != "2")
        {
            \Tki\AdminLog::writeLog($db, LOG_RAW, "{$playerinfo['ship_id']}|Tried to use an invalid circuit_type of '{$circuit}', This is normally a result from using an external page and should be banned.");
            Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, "Invalid Circuit type!<br>*** Possible Exploit has been reported to the admin. ***", $template);
        }

        // Check warp links compatibility
        if ($move == 'warp')
        {
            $query = $db->Execute("SELECT link_id FROM {$db->prefix}links WHERE link_start=? AND link_dest=?;", array($src['sector_id'], $dest['sector_id']));
            \Tki\Db::logDbErrors($db, $query, __LINE__, __FILE__);
            if ($query->EOF)
            {
                $langvars['l_tdr_nowlink1'] = str_replace("[tdr_src_sector_id]", $src['sector_id'], $langvars['l_tdr_nowlink1']);
                $langvars['l_tdr_nowlink1'] = str_replace("[tdr_dest_sector_id]", $dest['sector_id'], $langvars['l_tdr_nowlink1']);
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_nowlink1'], $template);
            }

            if ($circuit == '2')
            {
                $query = $db->Execute("SELECT link_id FROM {$db->prefix}links WHERE link_start=? AND link_dest=?;", array($dest['sector_id'], $src['sector_id']));
                \Tki\Db::logDbErrors($db, $query, __LINE__, __FILE__);
                if ($query->EOF)
                {
                    $langvars['l_tdr_nowlink2'] = str_replace("[tdr_src_sector_id]", $src['sector_id'], $langvars['l_tdr_nowlink2']);
                    $langvars['l_tdr_nowlink2'] = str_replace("[tdr_dest_sector_id]", $dest['sector_id'], $langvars['l_tdr_nowlink2']);
                    Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_nowlink2'], $template);
                }
            }
        }

      // Check ports compatibility
        if ($type1 == 'port')
        {
            if ($src['port_type'] == 'special')
            {
                if (($type2 != 'planet') && ($type2 != 'corp_planet'))
                {
                    Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_sportissrc'], $template);
                }

                if ($dest['owner'] != $playerinfo['ship_id'] && ($dest['corp'] == 0 || ($dest['corp'] != $playerinfo['team'])))
                {
                    Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_notownplanet'], $template);
                }
            }
            else
            {
                if ($type2 == 'planet')
                {
                    Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_planetisdest'], $template);
                }

                if ($src['port_type'] == $dest['port_type'])
                {
                    Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_samecom'], $template);
                }
            }
        }
        else
        {
            if (array_key_exists('port_type', $dest) == true && $dest['port_type'] == 'special')
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_sportcom'], $template);
            }
        }
    }

    public static function traderouteDistance($db, $langvars, $type1, $type2, $start, $dest, $circuit, $playerinfo, $sells = 'N')
    {
        $retvalue['triptime'] = 0;
        $retvalue['scooped1'] = 0;
        $retvalue['scooped2'] = 0;
        $retvalue['scooped'] = 0;

        if ($type1 == 'L')
        {
            $query = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=?;", array($start));
            \Tki\Db::logDbErrors($db, $query, __LINE__, __FILE__);
            $start = $query->fields;
        }

        if ($type2 == 'L')
        {
            $query = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=?;", array($dest));
            \Tki\Db::logDbErrors($db, $query, __LINE__, __FILE__);
            $dest = $query->fields;
        }

        if ($start['sector_id'] == $dest['sector_id'])
        {
            if ($circuit == '1')
            {
                $retvalue['triptime'] = '1';
            }
            else
            {
                $retvalue['triptime'] = '2';
            }

            return $retvalue;
        }

        $deg = pi() / 180;

        $sa1 = $start['angle1'] * $deg;
        $sa2 = $start['angle2'] * $deg;
        $fa1 = $dest['angle1'] * $deg;
        $fa2 = $dest['angle2'] * $deg;
        $x = $start['distance'] * sin($sa1) * cos($sa2) - $dest['distance'] * sin($fa1) * cos($fa2);
        $y = $start['distance'] * sin($sa1) * sin($sa2) - $dest['distance'] * sin($fa1) * sin($fa2);
        $z = $start['distance'] * cos($sa1) - $dest['distance'] * cos($fa1);
        $distance = round(sqrt(pow($x, 2) + pow($y, 2) + pow($z, 2)));
        $shipspeed = pow($tkireg->level_factor, $playerinfo['engines']);
        $triptime = round($distance / $shipspeed);

        if (!$triptime && $dest['sector_id'] != $playerinfo['sector'])
        {
            $triptime = 1;
        }

        if ($playerinfo['dev_fuelscoop'] == "Y")
        {
            $energyscooped = $distance * 100;
        }
        else
        {
            $energyscooped = 0;
        }

        if ($playerinfo['dev_fuelscoop'] == "Y" && !$energyscooped && $triptime == 1)
        {
            $energyscooped = 100;
        }

        $free_power = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg->level_factor) - $playerinfo['ship_energy'];

        if ($free_power < $energyscooped)
        {
            $energyscooped = $free_power;
        }

        if ($energyscooped < 1)
        {
            $energyscooped = 0;
        }

        $retvalue['scooped1'] = $energyscooped;

        if ($circuit == '2')
        {
            if ($sells == 'Y' && $playerinfo['dev_fuelscoop'] == 'Y' && $type2 == 'P' && $dest['port_type'] != 'energy')
            {
                $energyscooped = $distance * 100;
                $free_power = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg->level_factor);

                if ($free_power < $energyscooped)
                {
                    $energyscooped = $free_power;
                }

                $retvalue['scooped2'] = $energyscooped;
            }
            elseif ($playerinfo['dev_fuelscoop'] == 'Y')
            {
                $energyscooped = $distance * 100;
                $free_power = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg->level_factor) - $retvalue['scooped1'] - $playerinfo['ship_energy'];

                if ($free_power < $energyscooped)
                {
                    $energyscooped = $free_power;
                }

                $retvalue['scooped2'] = $energyscooped;
            }
        }

        if ($circuit == '2')
        {
            $triptime*= 2;
            $triptime+= 2;
        }
        else
        {
            $triptime+= 1;
        }

        $retvalue['triptime'] = $triptime;
        $retvalue['scooped'] = $retvalue['scooped1'] + $retvalue['scooped2'];

        return $retvalue;
    }

    public static function traderouteCreate($db, $pdo_db, $lang, $langvars, $tkireg, $template)
    {
        global $playerinfo;
        global $num_traderoutes;
        global $ptype1;
        global $ptype2;
        global $port_id1;
        global $port_id2;
        global $planet_id1;
        global $planet_id2;
        global $corp_planet_id1;
        global $corp_planet_id2;
        global $move_type;
        global $circuit_type;
        global $editing;
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        if ($num_traderoutes >= $tkireg->max_traderoutes_player && empty ($editing))
        { // Dont let them exceed max traderoutes
            Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_maxtdr'], $template);
        }

        // Database sanity check for source
        if ($ptype1 == 'port')
        {
            // Check for valid Source Port
            if ($port_id1 >= $tkireg->max_sectors)
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_invalidspoint'], $template);
            }

            $query = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=?;", array($port_id1));
            \Tki\Db::logDbErrors($db, $query, __LINE__, __FILE__);
            if (!$query || $query->EOF)
            {
                $langvars['l_tdr_errnotvalidport'] = str_replace("[tdr_port_id]", $port_id1, $langvars['l_tdr_errnotvalidport']);
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_errnotvalidport'], $template);
            }

            // OK we definitely have a port here
            $source= $query->fields;
            if ($source['port_type'] == 'none')
            {
                $langvars['l_tdr_errnoport'] = str_replace("[tdr_port_id]", $port_id1, $langvars['l_tdr_errnoport']);
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_errnoport'], $template);
            }
        }
        else
        {
            $query = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=?;", array($planet_id1));
            \Tki\Db::logDbErrors($db, $query, __LINE__, __FILE__);
            $source = $query->fields;
            if (!$query || $query->EOF)
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_errnosrc'], $template);
            }

            // Check for valid Source Planet
            if ($source['sector_id'] >= $tkireg->max_sectors)
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_invalidsrc'], $template);
            }

            if ($source['owner'] != $playerinfo['ship_id'])
            {
                if (($playerinfo['team'] == 0 || $playerinfo['team'] != $source['corp']) && $source['sells'] == 'N')
                {
                    // $langvars['l_tdr_errnotownnotsell'] = str_replace("[tdr_source_name]", $source[name], $langvars['l_tdr_errnotownnotsell']);
                    // $langvars['l_tdr_errnotownnotsell'] = str_replace("[tdr_source_sector_id]", $source[sector_id], $langvars['l_tdr_errnotownnotsell']);
                    // Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_errnotownnotsell'], $template);

                    // Check for valid Owned Source Planet
                    \Tki\AdminLog::writeLog($db, 902, "{$playerinfo['ship_id']}|Tried to find someones planet: {$planet_id1} as source.");
                    Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_invalidsrc'], $template);
                }
            }
        }
        // OK we have $source, *probably* now lets see if we have ever been there
        // Attempting to fix the map the universe via traderoute bug

        $pl1query = $db->Execute("SELECT * FROM {$db->prefix}movement_log WHERE sector_id=? AND ship_id = ?;", array($source['sector_id'], $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($db, $pl1query, __LINE__, __FILE__);
        $num_res1 = $pl1query->numRows();
        if ($num_res1 == 0)
        {
            Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, "You cannot create a traderoute from a sector you have not visited!", $template);
        }
        // Note: shouldnt we, more realistically, require a ship to be *IN* the source sector to create the traderoute?

        // Database sanity check for dest
        if ($ptype2 == 'port')
        {
            // Check for valid Dest Port
            if ($port_id2 >= $tkireg->max_sectors)
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_invaliddport'], $template);
            }

            $query = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=?;", array($port_id2));
            \Tki\Db::logDbErrors($db, $query, __LINE__, __FILE__);
            if (!$query || $query->EOF)
            {
                $langvars['l_tdr_errnotvaliddestport'] = str_replace("[tdr_port_id]", $port_id2, $langvars['l_tdr_errnotvaliddestport']);
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_errnotvaliddestport'], $template);
            }

            $destination = $query->fields;

            if ($destination['port_type'] == 'none')
            {
                $langvars['l_tdr_errnoport2'] = str_replace("[tdr_port_id]", $port_id2, $langvars['l_tdr_errnoport2']);
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_errnoport2'], $template);
            }
        }
        else
        {
            $query = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=?;", array($planet_id2));
            \Tki\Db::logDbErrors($db, $query, __LINE__, __FILE__);
            $destination = $query->fields;
            if (!$query || $query->EOF)
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_errnodestplanet'], $template);
            }

            // Check for valid Dest Planet
            if ($destination['sector_id'] >= $tkireg->max_sectors)
            {
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_invaliddplanet'], $template);
            }

            if ($destination['owner'] != $playerinfo['ship_id'] && $destination['sells'] == 'N')
            {
                // $langvars['l_tdr_errnotownnotsell2'] = str_replace("[tdr_dest_name]", $destination['name'], $langvars['l_tdr_errnotownnotsell2']);
                // $langvars['l_tdr_errnotownnotsell2'] = str_replace("[tdr_dest_sector_id]", $destination['sector_id'], $langvars['l_tdr_errnotownnotsell2']);
                // Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_errnotownnotsell2'], $template);

                // Check for valid Owned Source Planet
                \Tki\AdminLog::writeLog($db, 902, "{$playerinfo['ship_id']}|Tried to find someones planet: {$planet_id2} as dest.");
                Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_invaliddplanet'], $template);
            }
        }

        // OK now we have $destination lets see if we've been there.
        $pl2query = $db->Execute("SELECT * FROM {$db->prefix}movement_log WHERE sector_id=? AND ship_id = ?;", array($destination['sector_id'], $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($db, $pl2query, __LINE__, __FILE__);
        $num_res2 = $pl2query->numRows();
        if ($num_res2 == 0)
        {
            Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, "You cannot create a traderoute into a sector you have not visited!", $template);
        }

        // Check destination - we cannot trade INTO a special port
        if (array_key_exists('port_type', $destination) == true && $destination['port_type'] == 'special')
        {
            Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, "You cannot create a traderoute into a special port!", $template);
        }
        // Check traderoute for src => dest
        Traderoute::traderouteCheckCompatible($db, $pdo_db, $lang, $langvars, $ptype1, $ptype2, $move_type, $circuit_type, $source, $destination, $playerinfo);

        if ($ptype1 == 'port')
        {
            $src_id = $port_id1;
        }
        elseif ($ptype1 == 'planet')
        {
            $src_id = $planet_id1;
        }
        elseif ($ptype1 == 'corp_planet')
        {
            $src_id = $corp_planet_id1;
        }

        if ($ptype2 == 'port')
        {
            $dest_id = $port_id2;
        }
        elseif ($ptype2 == 'planet')
        {
            $dest_id = $planet_id2;
        }
        elseif ($ptype2 == 'corp_planet')
        {
            $dest_id = $corp_planet_id2;
        }

        if ($ptype1 == 'port')
        {
            $src_type = 'P';
        }
        elseif ($ptype1 == 'planet')
        {
            $src_type = 'L';
        }
        elseif ($ptype1 == 'corp_planet')
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
        elseif ($ptype2 == 'corp_planet')
        {
            $dest_type = 'C';
        }

        if ($move_type == 'realspace')
        {
            $mtype = 'R';
        }
        else
        {
            $mtype = 'W';
        }

        if (empty ($editing))
        {
            $query = $db->Execute("INSERT INTO {$db->prefix}traderoutes VALUES(NULL, ?, ?, ?, ?, ?, ?, ?);", array($src_id, $dest_id, $src_type, $dest_type, $mtype, $playerinfo['ship_id'], $circuit_type));
            \Tki\Db::logDbErrors($db, $query, __LINE__, __FILE__);
            echo "<p>" . $langvars['l_tdr_newtdrcreated'];
        }
        else
        {
            $query = $db->Execute("UPDATE {$db->prefix}traderoutes SET source_id=?, dest_id=?, source_type=?, dest_type=?, move_type=?, owner=?, circuit=? WHERE traderoute_id=?;", array($src_id, $dest_id, $src_type, $dest_type, $mtype, $playerinfo['ship_id'], $circuit_type, $editing));
            \Tki\Db::logDbErrors($db, $query, __LINE__, __FILE__);
            echo "<p>" . $langvars['l_tdr_modified'];
        }

        $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);
        echo " " . $langvars['l_tdr_returnmenu'];
        Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, null, $template);
    }

    public static function traderouteDelete($db, $lang, $langvars, $tkireg, $template, $playerinfo, $confirm, $num_traderoutes, $traderoute_id, $traderoutes)
    {
        $query = $db->Execute("SELECT * FROM {$db->prefix}traderoutes WHERE traderoute_id=?;", array($traderoute_id));
        \Tki\Db::logDbErrors($db, $query, __LINE__, __FILE__);

        if (!$query || $query->EOF)
        {
            Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_doesntexist'], $template);
        }

        $delroute = $query->fields;

        if ($delroute['owner'] != $playerinfo['ship_id'])
        {
            Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, $langvars['l_tdr_notowntdr'], $template);
        }

        if (empty ($confirm))
        {
            $num_traderoutes = 1;
            $traderoutes[0] = $delroute;
            // Here it continues to the main file area to print the route
        }
        else
        {
            $query = $db->Execute("DELETE FROM {$db->prefix}traderoutes WHERE traderoute_id=?;", array($traderoute_id));
            \Tki\Db::logDbErrors($db, $query, __LINE__, __FILE__);
            $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);
            echo $langvars['l_tdr_deleted'] . " " . $langvars['l_tdr_returnmenu'];
            Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, null, $template);
        }
    }

    public static function traderouteSettings($db, $pdo_db, $lang, $langvars, $tkireg, $template, $playerinfo)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        echo "<p><font size=3 color=blue><strong>" . $langvars['l_tdr_globalset'] . "</strong></font><p>";
        echo "<font color=white size=2><strong>" . $langvars['l_tdr_sportsrc'] . " :</strong></font><p>".
             "<form accept-charset='utf-8' action=traderoute.php?command=setsettings method=post>".
             "<table border=0><tr>".
             "<td><font size=2 color=white> - " . $langvars['l_tdr_colonists'] . " :</font></td>".
             "<td><input type=checkbox name=colonists";

        if ($playerinfo['trade_colonists'] == 'Y')
        {
            echo " checked";
        }

        echo "></tr><tr>".
            "<td><font size=2 color=white> - " . $langvars['l_tdr_fighters'] . " :</font></td>".
            "<td><input type=checkbox name=fighters";

        if ($playerinfo['trade_fighters'] == 'Y')
        {
            echo " checked";
        }

        echo "></tr><tr>".
            "<td><font size=2 color=white> - " . $langvars['l_tdr_torps'] . " :</font></td>".
            "<td><input type=checkbox name=torps";

        if ($playerinfo['trade_torps'] == 'Y')
        {
            echo " checked";
        }

        echo "></tr>".
            "</table>".
            "<p>".
            "<font color=white size=2><strong>" . $langvars['l_tdr_tdrescooped'] . " :</strong></font><p>".
            "<table border=0><tr>".
            "<td><font size=2 color=white>&nbsp;&nbsp;&nbsp;" . $langvars['l_tdr_trade'] . "</font></td>".
            "<td><input type=radio name=energy value=\"Y\"";

        if ($playerinfo['trade_energy'] == 'Y')
        {
            echo " checked";
        }

        echo "></td></tr><tr>".
            "<td><font size=2 color=white>&nbsp;&nbsp;&nbsp;" . $langvars['l_tdr_keep'] . "</font></td>".
            "<td><input type=radio name=energy value=\"N\"";

        if ($playerinfo['trade_energy'] == 'N')
        {
            echo " checked";
        }

        echo "></td></tr><tr><td>&nbsp;</td></tr><tr><td>".
            "<td><input type=submit value=\"" . $langvars['l_tdr_save'] . "\"></td>".
            "</tr></table>".
            "</form>";

        $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);
        echo $langvars['l_tdr_returnmenu'];
        Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, null, $template);
    }

    public static function traderouteSetsettings($db, $pdo_db, $lang, $langvars, $tkireg, $template, $playerinfo, $colonists, $fighters, $torps, $energy)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        empty ($colonists) ? $colonists = 'N' : $colonists = 'Y';
        empty ($fighters) ? $fighters = 'N' : $fighters = 'Y';
        empty ($torps) ? $torps = 'N' : $torps = 'Y';

        $resa = $db->Execute("UPDATE {$db->prefix}ships SET trade_colonists=?, trade_fighters=?, trade_torps=?, trade_energy=? WHERE ship_id=?;", array($colonists, $fighters, $torps, $energy, $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($db, $resa, __LINE__, __FILE__);

        $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);
        echo $langvars['l_tdr_globalsetsaved'] . " " . $langvars['l_tdr_returnmenu'];
        Traderoute::traderouteDie($db, $pdo_db, $lang, $langvars, $tkireg, null, $template);
    }

    public static function traderouteResultsTableTop($db, $pdo_db, $lang, $langvars)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        echo "<table border='1' cellspacing='1' cellpadding='2' width='65%' align='center'>\n";
        echo "  <tr bgcolor='".$tkireg->color_line2."'>\n";
        echo "    <td align='center' colspan='7'><strong><font color='white'>" . $langvars['l_tdr_res'] . "</font></strong></td>\n";
        echo "  </tr>\n";
        echo "  <tr align='center' bgcolor='".$tkireg->color_line2."'>\n";
        echo "    <td width='50%'><font size='2' color='white'><strong>";
    }

    public static function traderouteResultsSource()
    {
        echo "</strong></font></td>\n";
        echo "    <td width='50%'><font size='2' color='white'><strong>";
    }

    public static function traderouteResultsDestination()
    {
        echo "</strong></font></td>\n";
        echo "  </tr>\n";
        echo "  <tr bgcolor='".$tkireg->color_line1."'>\n";
        echo "    <td align='center'><font size='2' color='white'>";
    }

    public static function traderouteResultsCloseCell()
    {
        echo "</font></td>\n";
        echo "    <td align='center'><font size='2' color='white'>";
    }

    public static function traderouteResultsShowCost()
    {
        echo "</font></td>\n";
        echo "  </tr>\n";
        echo "  <tr bgcolor='".$tkireg->color_line2."'>\n";
        echo "    <td align='center'><font size='2' color='white'>";
    }

    public static function traderouteResultsCloseCost()
    {
        echo "</font></td>\n";
        echo "    <td align='center'><font size='2' color='white'>";
    }

    public static function traderouteResultsCloseTable()
    {
        echo "</font></td>\n";
        echo "  </tr>\n";
        echo "</table>\n";
        // echo "<p><center><font size=3 color=white><strong>\n";
    }

    public static function traderouteResultsDisplayTotals($db, $pdo_db, $lang, $langvars, $total_profit)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        if ($total_profit > 0)
        {
            echo "<p><center><font size=3 color=white><strong>" . $langvars['l_tdr_totalprofit'] . " : <font style='color:#0f0;'><strong>" . number_format(abs($total_profit), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</strong></font><br>\n";
        }
        else
        {
            echo "<p><center><font size=3 color=white><strong>" . $langvars['l_tdr_totalcost'] . " : <font style='color:#f00;'><strong>" . number_format(abs($total_profit), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . "</strong></font><br>\n";
        }
    }

    public static function traderouteResultsDisplaySummary($db, $pdo_db, $lang, $langvars, $tdr_display_creds, $dist, $playerinfo)
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

        echo "\n<font size='3' color='white'><strong>" . $langvars['l_tdr_turnsused'] . " : <font style='color:#f00;'>$dist[triptime]</font></strong></font><br>";
        echo "\n<font size='3' color='white'><strong>" . $langvars['l_tdr_turnsleft'] . " : <font style='color:#0f0;'>$playerinfo[turns]</font></strong></font><br>";

        echo "\n<font size='3' color='white'><strong>" . $langvars['l_tdr_credits'] . " : <font style='color:#0f0;'> $tdr_display_creds\n</font></strong></font><br> </strong></font></center>\n";
        //echo "<font size='2'>\n";
    }

    public static function traderouteResultsShowRepeat($engage)
    {
        echo "<form accept-charset='utf-8' action='traderoute.php?engage=".$engage."' method='post'>\n";
        echo "<br>Enter times to repeat <input type='TEXT' name='tr_repeat' value='1' size='5'> <input type='SUBMIT' value='SUBMIT'>\n";
        echo "</form>\n";
        // echo "<p>";
    }
}

