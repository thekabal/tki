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
// File: info_publiclist.php

require_once './common.php';

$info = array();

$info['GAMENAME'] = $bntreg->game_name;
$info['GAMEID'] = md5($bntreg->game_name . $bntreg->bnt_ls_key);

$xsql = "SELECT UNIX_TIMESTAMP(time) as x FROM {$db->prefix}movement_log WHERE event_id = 1";
$res = $db->Execute($xsql);
$row = $res->fields;
$info['START-DATE'] = $row[x];
$info['G-DURATION'] = -1;

$xsql = "SELECT count(*) as x FROM {$db->prefix}ships";
$res = $db->Execute($xsql);
$row = $res->fields;
$info['P-ALL'] = $row[x];

$xsql = "SELECT count(*) as x FROM {$db->prefix}ships WHERE ship_destroyed = 'N' ";
$res = $db->Execute($xsql);
$row = $res->fields;
$info['P-ACTIVE'] = $row[x];

$xsql = "SELECT count(*) as x FROM {$db->prefix}ships WHERE ship_destroyed = 'N' AND email NOT LIKE '%@xenobe'";
$res = $db->Execute($xsql);
$row = $res->fields;
$info['P-HUMAN'] = $row[x];

$xsql = "SELECT COUNT(*) as x FROM {$db->prefix}ships WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_login)) / 60 <= 5 and email NOT LIKE '%@xenobe'";
$res = $db->Execute($xsql);
$row = $res->fields;
$info['P-ONLINE'] = $row[x];

$res = $db->Execute("SELECT AVG(hull) AS a1 , AVG(engines) AS a2 , AVG(power) AS a3 , AVG(computer) AS a4 , AVG(sensors) AS a5 , AVG(beams) AS a6 , AVG(torp_launchers) AS a7 , AVG(shields) AS a8 , AVG(armor) AS a9 , AVG(cloak) AS a10 FROM {$db->prefix}ships WHERE ship_destroyed='N' and email LIKE '%@xenobe'");
$row = $res->fields;
$dyn_xenobe_lvl = $row[a1] + $row[a2] + $row[a3] + $row[a4] + $row[a5] + $row[a6] + $row[a7] + $row[a8] + $row[a9] + $row[a10];
$dyn_xenobe_lvl = $dyn_xenobe_lvl / 10;
$info['P-AI-LVL'] = $dyn_xenobe_lvl;

$xsql = "SELECT character_name, score  FROM {$db->prefix}ships WHERE ship_destroyed = 'N' ORDER BY score DESC LIMIT 3 ";
$res = $db->Execute($xsql);
while (!$res->EOF)
{
    $row = $res->fields;
    $tmp = $res->CurrentRow() + 1;
    $info['P-TOP{$tmp}-NAME'] = $row['character_name'];
    $info['P-TOP{$tmp}-SCORE'] = $row['score'];
    $res->MoveNext();
}

$info['G-TURNS-START'] = $bntreg->start_turns;
$info['G-TURNS-MAX'] = $bntreg->max_turns;

$info['G-SCHED-TICKS'] = $bntreg->sched_ticks;
$info['G-SCHED-TYPE'] = $bntreg->sched_type;

$info['G-SPEED-TURNS'] = $bntreg->sched_turns;
$info['G-SPEED-PORTS'] = $bntreg->sched_ports;
$info['G-SPEED-PLANETS'] = $bntreg->sched_planets;
$info['G-SPEED-IGB'] = $bntreg->sched_igb;

$info['G-SIZE-SECTOR'] = $bntreg->sector_max;
$info['G-SIZE-UNIVERSE'] = $bntreg->universe_size;
$info['G-SIZE-PLANETS'] = $bntreg->max_planets_sector;
$info['G-SIZE-PLANETS-TO-OWN'] = $bntreg->min_bases_to_own;

$info['G-COLONIST-LIMIT'] = $bntreg->colonist_limit;
$info['G-DOOMSDAY-VALUE'] = $bntreg->doomsday_value;

$info['G-MONEY-IGB'] = $bntreg->ibank_interest;
$info['G-MONEY-PLANET'] = round($bntreg->interest_rate - 1, 4);

$info['G-PORT-LIMIT-ORE'] = $bntreg->ore_limit;
$info['G-PORT-RATE-ORE'] = $bntreg->ore_delta;
$info['G-PORT-DELTA-ORE'] = $bnteg->ore_delta;

$info['G-PORT-LIMIT-ORGANICS'] = $bntreg->organics_limit;
$info['G-PORT-RATE-ORGANICS'] = $bntreg->organics_rate;
$info['G-PORT-DELTA-ORGANICS'] = $bntreg->organics_delta;

$info['G-PORT-LIMIT-GOODS'] = $bntreg->goods_limit;
$info['G-PORT-RATE-GOODS'] = $bntreg->goods_rate;
$info['G-PORT-DELTA-GOODS'] = $bntreg->goods_delta;

$info['G-PORT-LIMIT-ENERGY'] = $bntreg->energy_limit;
$info['G-PORT-RATE-ENERGY'] = $bntreg->energy_rate;
$info['G-PORT-DELTA-ENERGY'] = $bntreg->energy_delta;

$info['G-SOFA'] = ($bntreg->allow_sofa===true ? "1" : "0");
$info['G-KSM'] = ($bntreg->allow_ksm ? "1" : "0");

$info['S-CLOSED'] = ($bntreg->game_closed ? "1" : "0");
$info['S-CLOSED-ACCOUNTS'] = ($bntreg->account_creation_closed ? "1" : "0");

$info['ALLOW_FULLSCAN'] = ($bntreg->allow_fullscan ? "1" : "0");
$info['ALLOW_NAVCOMP'] = ($bntreg->allow_navcomp ? "1" : "0");
$info['ALLOW_IBANK'] = ($bntreg->allow_ibank ? "1" : "0");
$info['ALLOW_GENESIS_DESTROY'] = ($bntreg->allow_genesis_destroy ? "1" : "0");

$info['INVENTORY_FACTOR'] = $bntreg->inventory_factor;
$info['UPGRADE_COST'] = $bntreg->upgrade_cost;
$info['UPGRADE_FACTOR'] = $bntreg->upgrade_factor;
$info['LEVEL_FACTOR'] = $bntreg->level_factor;

$info['DEV_GENESIS_PRICE'] = $bntreg->dev_genesis_price;
$info['DEV_BEACON_PRICE'] = $bntreg->dev_beacon_price;
$info['DEV_EMERWARP_PRICE'] = $bntreg->dev_emerwarp_price;
$info['DEV_WARPEDIT_PRICE'] = $bntreg->dev_warpedit_price;
$info['DEV_MINEDEFLECTOR_PRICE'] = $bntreg->dev_minedeflector_price;
$info['DEV_ESCAPEPOD_PRICE'] = $bntreg->dev_escapepod_price;
$info['DEV_FUELSCOOP_PRICE'] = $bntreg->dev_fuelscoop_price;
$info['DEV_LSSD_PRICE'] = $bntreg->dev_lssd_price;

$info['FIGHTER_PRICE'] = $bntreg->fighter_price;
$info['TORPEDO_PRICE'] = $bntreg->torpedo_price;
$info['ARMOR_PRICE'] = $bntreg->armor_price;
$info['COLONIST_PRICE'] = $bntreg->colonist_price;

$info['BASE_DEFENSE'] = $bntreg->base_defense;

$info['COLONIST_PRODUCTION_RATE'] = $bntreg->colonist_production_rate;
$info['COLONIST_REPRODUCTION_RATE'] = $bntreg->colonist_reproduction_rate;
$info['ORGANICS_CONSUMPTION'] = $bntreg->organics_consumption;
$info['STARVATION_DEATH_RATE'] = $bntreg->starvation_death_rate;

$info['CORP_PLANET_TRANSFERS'] = ($bntreg->corp_planet_transfers ? "1" : "0");
$info['MAX_TEAM_MEMBERS'] = $bntreg->max_team_members;

$info['ADMIN_MAIL'] = $bntreg->admin_mail;
$info['LINK_FORUMS'] = $bntreg->link_forums;

foreach ($info as $key => $value)
{
    echo $key . ":" . $value . "<br>";
}
