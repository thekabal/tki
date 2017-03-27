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
// File: sched_ports.php
//
// FUTURE: PDO, Debugging, Output

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('scheduler'));

// Update Ore in ports
echo "<strong>" . $langvars['l_sched_ports_title'] . "</strong><br><br>";
echo $langvars['l_sched_ports_addore'];

$resa = $db->Execute("UPDATE {$db->prefix}universe SET port_ore = port_ore + ($tkireg->ore_rate * $multiplier * $tkireg->port_regenrate ) WHERE port_type='ore' AND port_ore < $tkireg->ore_limit");
$debug = Tki\Db::LogDbErrors($pdo_db, $resa, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_addore_ore'];
$resb = $db->Execute("UPDATE {$db->prefix}universe SET port_ore = port_ore + ($tkireg->ore_rate * $multiplier * $tkireg->port_regenrate) WHERE port_type!='special' AND port_type!='none' AND port_ore < $tkireg->ore_limit");
$debug = Tki\Db::LogDbErrors($pdo_db, $resb, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_min_ore'];
$resc = $db->Execute("UPDATE {$db->prefix}universe SET port_ore = 0 WHERE port_ore < 0");
$debug = Tki\Db::LogDbErrors($pdo_db, $resc, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);
echo "<br>";

// Update Organics in Ports
echo $langvars['l_sched_ports_addorg'];
$resd = $db->Execute("UPDATE {$db->prefix}universe SET port_organics = port_organics + (? * ? * ?) WHERE port_type='organics' AND port_organics < ?", array($tkireg->organics_rate, $multiplier, $tkireg->port_regenrate, $tkireg->organics_limit));
$debug = Tki\Db::LogDbErrors($pdo_db, $resd, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_addorg_org'];
$rese = $db->Execute("UPDATE {$db->prefix}universe SET port_organics = port_organics + (? * ? * ?) WHERE port_type!='special' AND port_type!='none' AND port_organics < ?", array($tkireg->organics_rate, $multiplier, $tkireg->port_regenrate, $tkireg->organics_limit));
$debug = Tki\Db::LogDbErrors($pdo_db, $rese, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_min_org'];
$resf = $db->Execute("UPDATE {$db->prefix}universe SET port_organics = 0 WHERE port_organics < 0");
$debug = Tki\Db::LogDbErrors($pdo_db, $resf, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);
echo "<br>";

// Update Goods in Ports
echo $langvars['l_sched_ports_addgoods'];
$resg = $db->Execute("UPDATE {$db->prefix}universe SET port_goods = port_goods + (? * ? * ?) WHERE port_type='goods' AND port_goods < ?", array($tkireg->goods_rate, $multiplier, $tkireg->port_regenrate, $tkireg->goods_limit));
$debug = Tki\Db::LogDbErrors($pdo_db, $resg, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_addgoods_goods'];
$resh = $db->Execute("UPDATE {$db->prefix}universe SET port_goods = port_goods + (? * ? * ?) WHERE port_type!='special' AND port_type!='none' AND port_goods < ?", array($tkireg->goods_rate, $multiplier, $tkireg->port_regenrate, $tkireg->goods_limit));
$debug = Tki\Db::LogDbErrors($pdo_db, $resh, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_min_goods'];
$resi = $db->Execute("UPDATE {$db->prefix}universe SET port_goods = 0 WHERE port_goods < 0");
$debug = Tki\Db::LogDbErrors($pdo_db, $resi, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);
echo "<br>";

// Update Energy in Ports
echo $langvars['l_sched_ports_addenergy'];
$resj = $db->Execute("UPDATE {$db->prefix}universe SET port_energy = port_energy + (? * ? * ?) WHERE port_type='energy' AND port_energy < ?", array($tkireg->energy_rate, $multiplier, $tkireg->port_regenrate, $tkireg->energy_limit));
$debug = Tki\Db::LogDbErrors($pdo_db, $resj, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_addenergy_energy'];
$resk = $db->Execute("UPDATE {$db->prefix}universe SET port_energy = port_energy + (? * ? * ?) WHERE port_type!='special' AND port_type!='none' AND port_energy < ?", array($tkireg->energy_rate, $multiplier, $tkireg->port_regenrate, $tkireg->energy_limit));
$debug = Tki\Db::LogDbErrors($pdo_db, $resk, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_min_energy'];
$resl = $db->Execute("UPDATE {$db->prefix}universe SET port_energy = 0 WHERE port_energy < 0");
$debug = Tki\Db::LogDbErrors($pdo_db, $resl, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);
echo "<br>";

// Now check to see if any ports are over max, if so correct them.
echo $langvars['l_sched_ports_energy_cap'];
$resm = $db->Execute("UPDATE {$db->prefix}universe SET port_energy = ? WHERE port_energy > ?", array($tkireg->energy_limit, $tkireg->energy_limit));
$debug = Tki\Db::LogDbErrors($pdo_db, $resm, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_goods_cap'];
$resn = $db->Execute("UPDATE {$db->prefix}universe SET port_goods = ? WHERE port_goods > ?", array($tkireg->goods_limit, $tkireg->goods_limit));
$debug = Tki\Db::LogDbErrors($pdo_db, $resn, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_organics_cap'];
$reso = $db->Execute("UPDATE {$db->prefix}universe SET port_organics = ? WHERE port_organics > ?", array($tkireg->organics_limit, $tkireg->organics_limit));
$debug = Tki\Db::LogDbErrors($pdo_db, $reso, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_ore_cap'];
$resp = $db->Execute("UPDATE {$db->prefix}universe SET port_ore = ? WHERE port_ore > ?", array($tkireg->ore_limit, $tkireg->ore_limit));
$debug = Tki\Db::LogDbErrors($pdo_db, $resp, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);
$multiplier = 0;

echo "<br>";
