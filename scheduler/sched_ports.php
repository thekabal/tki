<?php declare(strict_types = 1);
/**
 * scheduler/sched_ports.php from The Kabal Invasion.
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

// FUTURE: PDO, Debugging, Output
// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('scheduler'));

// Update Ore in ports
echo "<strong>" . $langvars['l_sched_ports_title'] . "</strong><br><br>";
echo $langvars['l_sched_ports_addore'];

$resa = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_ore = port_ore + ($tkireg->ore_rate * $multiplier * $tkireg->port_regenrate ) WHERE port_type='ore' AND port_ore < $tkireg->ore_limit");
$debug = (string) Tki\Db::logDbErrors($pdo_db, $resa, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_addore_ore'];
$resb = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_ore = port_ore + ($tkireg->ore_rate * $multiplier * $tkireg->port_regenrate) WHERE port_type!='special' AND port_type!='none' AND port_ore < $tkireg->ore_limit");
$debug = (string) Tki\Db::logDbErrors($pdo_db, $resb, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_min_ore'];
$resc = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_ore = 0 WHERE port_ore < 0");
$debug = (string) Tki\Db::logDbErrors($pdo_db, $resc, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);
echo "<br>";

// Update Organics in Ports
echo $langvars['l_sched_ports_addorg'];
$resd = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_organics = port_organics + (? * ? * ?) WHERE port_type='organics' AND port_organics < ?", array($tkireg->organics_rate, $multiplier, $tkireg->port_regenrate, $tkireg->organics_limit));
$debug = (string) Tki\Db::logDbErrors($pdo_db, $resd, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_addorg_org'];
$rese = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_organics = port_organics + (? * ? * ?) WHERE port_type!='special' AND port_type!='none' AND port_organics < ?", array($tkireg->organics_rate, $multiplier, $tkireg->port_regenrate, $tkireg->organics_limit));
$debug = (string) Tki\Db::logDbErrors($pdo_db, $rese, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_min_org'];
$resf = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_organics = 0 WHERE port_organics < 0");
$debug = (string) Tki\Db::logDbErrors($pdo_db, $resf, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);
echo "<br>";

// Update Goods in Ports
echo $langvars['l_sched_ports_addgoods'];
$resg = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_goods = port_goods + (? * ? * ?) WHERE port_type='goods' AND port_goods < ?", array($tkireg->goods_rate, $multiplier, $tkireg->port_regenrate, $tkireg->goods_limit));
$debug = (string) Tki\Db::logDbErrors($pdo_db, $resg, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_addgoods_goods'];
$resh = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_goods = port_goods + (? * ? * ?) WHERE port_type!='special' AND port_type!='none' AND port_goods < ?", array($tkireg->goods_rate, $multiplier, $tkireg->port_regenrate, $tkireg->goods_limit));
$debug = (string) Tki\Db::logDbErrors($pdo_db, $resh, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_min_goods'];
$resi = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_goods = 0 WHERE port_goods < 0");
$debug = (string) Tki\Db::logDbErrors($pdo_db, $resi, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);
echo "<br>";

// Update Energy in Ports
echo $langvars['l_sched_ports_addenergy'];
$resj = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_energy = port_energy + (? * ? * ?) WHERE port_type='energy' AND port_energy < ?", array($tkireg->energy_rate, $multiplier, $tkireg->port_regenrate, $tkireg->energy_limit));
$debug = (string) Tki\Db::logDbErrors($pdo_db, $resj, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_addenergy_energy'];
$resk = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_energy = port_energy + (? * ? * ?) WHERE port_type!='special' AND port_type!='none' AND port_energy < ?", array($tkireg->energy_rate, $multiplier, $tkireg->port_regenrate, $tkireg->energy_limit));
$debug = (string) Tki\Db::logDbErrors($pdo_db, $resk, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_min_energy'];
$resl = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_energy = 0 WHERE port_energy < 0");
$debug = (string) Tki\Db::logDbErrors($pdo_db, $resl, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);
echo "<br>";

// Now check to see if any ports are over max, if so correct them.
echo $langvars['l_sched_ports_energy_cap'];
$resm = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_energy = ? WHERE port_energy > ?", array($tkireg->energy_limit, $tkireg->energy_limit));
$debug = (string) Tki\Db::logDbErrors($pdo_db, $resm, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_goods_cap'];
$resn = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_goods = ? WHERE port_goods > ?", array($tkireg->goods_limit, $tkireg->goods_limit));
$debug = (string) Tki\Db::logDbErrors($pdo_db, $resn, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_organics_cap'];
$reso = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_organics = ? WHERE port_organics > ?", array($tkireg->organics_limit, $tkireg->organics_limit));
$debug = (string) Tki\Db::logDbErrors($pdo_db, $reso, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);

echo $langvars['l_sched_ports_ore_cap'];
$resp = $old_db->Execute("UPDATE {$old_db->prefix}universe SET port_ore = ? WHERE port_ore > ?", array($tkireg->ore_limit, $tkireg->ore_limit));
$debug = (string) Tki\Db::logDbErrors($pdo_db, $resp, __LINE__, __FILE__);
Tki\Scheduler::isQueryOk($pdo_db, $debug);
$multiplier = 0;

echo "<br>";
