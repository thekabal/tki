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

$info['GAMENAME'] = $tkireg->game_name;
$info['GAMEID'] = md5($tkireg->game_name . $tkireg->tki_ls_key);

$xsql = "SELECT UNIX_TIMESTAMP(time) as x FROM {$db->prefix}movement_log WHERE event_id = 1";
$res = $db->Execute($xsql);
$row = $res->fields;
$info['START-DATE'] = $row['x'];
$info['G-DURATION'] = -1;

$xsql = "SELECT count(*) as x FROM {$db->prefix}ships";
$res = $db->Execute($xsql);
$row = $res->fields;
$info['P-ALL'] = $row['x'];

$xsql = "SELECT count(*) as x FROM {$db->prefix}ships WHERE ship_destroyed = 'N' ";
$res = $db->Execute($xsql);
$row = $res->fields;
$info['P-ACTIVE'] = $row['x'];

$xsql = "SELECT count(*) as x FROM {$db->prefix}ships WHERE ship_destroyed = 'N' AND email NOT LIKE '%@kabal'";
$res = $db->Execute($xsql);
$row = $res->fields;
$info['P-HUMAN'] = $row['x'];

$xsql = "SELECT COUNT(*) as x FROM {$db->prefix}ships WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_login)) / 60 <= 5 and email NOT LIKE '%@kabal'";
$res = $db->Execute($xsql);
$row = $res->fields;
$info['P-ONLINE'] = $row['x'];

$res = $db->Execute("SELECT AVG(hull) AS a1 , AVG(engines) AS a2 , AVG(power) AS a3 , AVG(computer) AS a4 , AVG(sensors) AS a5 , AVG(beams) AS a6 , AVG(torp_launchers) AS a7 , AVG(shields) AS a8 , AVG(armor) AS a9 , AVG(cloak) AS a10 FROM {$db->prefix}ships WHERE ship_destroyed='N' and email LIKE '%@kabal'");
$row = $res->fields;
$dyn_kabal_lvl = $row['a1'] + $row['a2'] + $row['a3'] + $row['a4'] + $row['a5'] + $row['a6'] + $row['a7'] + $row['a8'] + $row['a9'] + $row['a10'];
$dyn_kabal_lvl = $dyn_kabal_lvl / 10;
$info['P-AI-LVL'] = $dyn_kabal_lvl;

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

$info['G-TURNS-START'] = 1200;
$info['G-TURNS-MAX'] = $tkireg->max_turns;

$info['G-SCHED-TICKS'] = $tkireg->sched_ticks;
$info['G-SCHED-TYPE'] = $tkireg->sched_type;

$info['G-SPEED-TURNS'] = $tkireg->sched_turns;
$info['G-SPEED-PORTS'] = $tkireg->sched_ports;
$info['G-SPEED-PLANETS'] = $tkireg->sched_planets;
$info['G-SPEED-IBANK'] = $tkireg->sched_ibank;

$info['G-SIZE-SECTOR'] = $tkireg->max_sectors;
$info['G-SIZE-UNIVERSE'] = $tkireg->universe_size;
$info['G-SIZE-PLANETS'] = $tkireg->max_planets_sector;
$info['G-SIZE-PLANETS-TO-OWN'] = $tkireg->min_bases_to_own;

$info['G-COLONIST-LIMIT'] = $tkireg->colonist_limit;
$info['G-DOOMSDAY-VALUE'] = $tkireg->doomsday_value;

$info['G-MONEY-IBANK'] = $tkireg->ibank_interest;
$info['G-MONEY-PLANET'] = round($tkireg->interest_rate - 1, 4);

$info['G-PORT-LIMIT-ORE'] = $tkireg->ore_limit;
$info['G-PORT-RATE-ORE'] = $tkireg->ore_delta;
$info['G-PORT-DELTA-ORE'] = $tkireg->ore_delta;

$info['G-PORT-LIMIT-ORGANICS'] = $tkireg->organics_limit;
$info['G-PORT-RATE-ORGANICS'] = $tkireg->organics_rate;
$info['G-PORT-DELTA-ORGANICS'] = $tkireg->organics_delta;

$info['G-PORT-LIMIT-GOODS'] = $tkireg->goods_limit;
$info['G-PORT-RATE-GOODS'] = $tkireg->goods_rate;
$info['G-PORT-DELTA-GOODS'] = $tkireg->goods_delta;

$info['G-PORT-LIMIT-ENERGY'] = $tkireg->energy_limit;
$info['G-PORT-RATE-ENERGY'] = $tkireg->energy_rate;
$info['G-PORT-DELTA-ENERGY'] = $tkireg->energy_delta;

$info['G-SOFA'] = ($tkireg->allow_sofa === true ? "1" : "0");
$info['G-KSM'] = ($tkireg->allow_ksm ? "1" : "0");

$info['S-CLOSED'] = ($tkireg->game_closed ? "1" : "0");
$info['S-CLOSED-ACCOUNTS'] = ($tkireg->account_creation_closed ? "1" : "0");

$info['ALLOW_FULLSCAN'] = ($tkireg->allow_fullscan ? "1" : "0");
$info['ALLOW_NAVCOMP'] = ($tkireg->allow_navcomp ? "1" : "0");
$info['ALLOW_IBANK'] = ($tkireg->allow_ibank ? "1" : "0");
$info['ALLOW_GENESIS_DESTROY'] = ($tkireg->allow_genesis_destroy ? "1" : "0");

$info['INVENTORY_FACTOR'] = $tkireg->inventory_factor;
$info['UPGRADE_COST'] = $tkireg->upgrade_cost;
$info['UPGRADE_FACTOR'] = $tkireg->upgrade_factor;
$info['LEVEL_FACTOR'] = $tkireg->level_factor;

$info['DEV_GENESIS_PRICE'] = $tkireg->dev_genesis_price;
$info['DEV_BEACON_PRICE'] = $tkireg->dev_beacon_price;
$info['DEV_EMERWARP_PRICE'] = $tkireg->dev_emerwarp_price;
$info['DEV_WARPEDIT_PRICE'] = $tkireg->dev_warpedit_price;
$info['DEV_MINEDEFLECTOR_PRICE'] = $tkireg->dev_minedeflector_price;
$info['DEV_ESCAPEPOD_PRICE'] = $tkireg->dev_escapepod_price;
$info['DEV_FUELSCOOP_PRICE'] = $tkireg->dev_fuelscoop_price;
$info['DEV_LSSD_PRICE'] = $tkireg->dev_lssd_price;

$info['FIGHTER_PRICE'] = $tkireg->fighter_price;
$info['TORPEDO_PRICE'] = $tkireg->torpedo_price;
$info['ARMOR_PRICE'] = $tkireg->armor_price;
$info['COLONIST_PRICE'] = $tkireg->colonist_price;

$info['BASE_DEFENSE'] = $tkireg->base_defense;

$info['COLONIST_PRODUCTION_RATE'] = $tkireg->colonist_production_rate;
$info['COLONIST_REPRODUCTION_RATE'] = $tkireg->colonist_reproduction_rate;
$info['ORGANICS_CONSUMPTION'] = $tkireg->organics_consumption;
$info['STARVATION_DEATH_RATE'] = $tkireg->starvation_death_rate;

$info['TEAM_PLANET_TRANSFERS'] = ($tkireg->team_planet_transfers ? "1" : "0");
$info['MAX_TEAM_MEMBERS'] = $tkireg->max_team_members;

$info['ADMIN_MAIL'] = $tkireg->admin_mail;
$info['LINK_FORUMS'] = $tkireg->link_forums;

foreach ($info as $key => $value)
{
    echo $key . ":" . $value . "<br>";
}
