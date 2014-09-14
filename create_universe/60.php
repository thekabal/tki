<?php
// Copyright © 2014 The Kabal Invasion development team, Ron Harwood, and the BNT development team.
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
//
// File: create_universe/60.php

if (strpos($_SERVER['PHP_SELF'], '/60.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

// Determine current step, next step, and number of steps
$create_universe_info = Tki\BigBang::findStep(__FILE__);

// Set variables
$variables['templateset']            = $tkireg->default_template;
$variables['body_class']             = 'create_universe';
$variables['steps']                  = $create_universe_info['steps'];
$variables['current_step']           = $create_universe_info['current_step'];
$variables['next_step']              = $create_universe_info['next_step'];
$variables['sector_max']             = (int) filter_input(INPUT_POST, 'sektors', FILTER_SANITIZE_NUMBER_INT); // Sanitize the input and typecast it to an int
$variables['spp']                    = filter_input(INPUT_POST, 'spp', FILTER_SANITIZE_NUMBER_INT);
$variables['oep']                    = filter_input(INPUT_POST, 'oep', FILTER_SANITIZE_NUMBER_INT);
$variables['ogp']                    = filter_input(INPUT_POST, 'ogp', FILTER_SANITIZE_NUMBER_INT);
$variables['gop']                    = filter_input(INPUT_POST, 'gop', FILTER_SANITIZE_NUMBER_INT);
$variables['enp']                    = filter_input(INPUT_POST, 'enp', FILTER_SANITIZE_NUMBER_INT);
$variables['nump']                   = filter_input(INPUT_POST, 'nump', FILTER_SANITIZE_NUMBER_INT);
$variables['empty']                  = $variables['sector_max'] - $variables['spp'] - $variables['oep'] - $variables['ogp'] - $variables['gop'] - $variables['enp'];
$variables['initscommod']            = filter_input(INPUT_POST, 'initscommod', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$variables['initbcommod']            = filter_input(INPUT_POST, 'initbcommod', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$variables['fedsecs']                = filter_input(INPUT_POST, 'fedsecs', FILTER_SANITIZE_NUMBER_INT);
$variables['loops']                  = filter_input(INPUT_POST, 'loops', FILTER_SANITIZE_NUMBER_INT);
$variables['swordfish']              = filter_input(INPUT_POST, 'swordfish', FILTER_SANITIZE_URL);
$variables['autorun']                = filter_input(INPUT_POST, 'autorun', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'regional', 'footer', 'global_includes', 'create_universe', 'news'));

$z = 0;
$initsore = $tkireg->ore_limit * $variables['initscommod'] / 100.0;
$initsorganics = $tkireg->organics_limit * $variables['initscommod'] / 100.0;
$initsgoods = $tkireg->goods_limit * $variables['initscommod'] / 100.0;
$initsenergy = $tkireg->energy_limit * $variables['initscommod'] / 100.0;
$initbore = $tkireg->ore_limit * $variables['initbcommod'] / 100.0;
$initborganics = $tkireg->organics_limit * $variables['initbcommod'] / 100.0;
$initbgoods = $tkireg->goods_limit * $variables['initbcommod'] / 100.0;
$initbenergy = $tkireg->energy_limit * $variables['initbcommod'] / 100.0;
$local_table_timer = new Tki\Timer;
$local_table_timer->start(); // Start benchmarking
$insert = $pdo_db->exec("INSERT INTO {$pdo_db->prefix}universe (sector_id, sector_name, zone_id, port_type, port_organics, port_ore, port_goods, port_energy, beacon, angle1, angle2, distance) VALUES ('1', 'Sol', '1', 'special', '0', '0', '0', '0', 'Sol: Hub of the Universe', '0', '0', '0')");
$variables['create_sol_results']['result'] = Tki\Db::logDbErrors($pdo_db, $insert, __LINE__, __FILE__);
$catch_results[$z] = $variables['create_sol_results']['result'];
$z++;
$local_table_timer->stop();
$variables['create_sol_results']['time'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking
$insert = $pdo_db->exec("INSERT INTO {$pdo_db->prefix}universe (sector_id, sector_name, zone_id, port_type, port_organics, port_ore, port_goods, port_energy, beacon, angle1, angle2, distance) VALUES ('2', 'Alpha Centauri', '1', 'energy',  '0', '0', '0', '0', 'Alpha Centauri: Gateway to the Galaxy', '0', '0', '1')");
$variables['create_ac_results']['result'] = Tki\Db::logDbErrors($pdo_db, $insert, __LINE__, __FILE__);
$catch_results[$z] = $variables['create_ac_results']['result'];
$z++;
$local_table_timer->stop();
$variables['create_ac_results']['time'] = $local_table_timer->elapsed();

// Warning: Do not alter loopsize - This should be balanced 50%/50% PHP/MySQL load :)

$loopsize = 500;
$loops = round($tkireg->sector_max / $loopsize);
if ($loops <= 0)
{
    $loops = 1;
}

$variables['insert_sector_loops'] = $loops;

$finish = $loopsize;
if ($finish > ($tkireg->sector_max))
{
    $finish = ($tkireg->sector_max);
}

$start = 3; // We added sol (1), and alpha centauri (2), so start at 3.

for ($i = 1; $i <= $loops; $i++)
{
    $local_table_timer->start(); // Start benchmarking
    $insert = "INSERT INTO {$pdo_db->prefix}universe " .
              "(sector_id, zone_id, angle1, angle2, distance) VALUES ";
    for ($j = $start; $j <= $finish; $j++)
    {
        $sector_id = $j;
        $distance = intval(Tki\Rand::betterRand(1, $tkireg->universe_size));
        $angle1 = Tki\Rand::betterRand(0, 180);
        $angle2 = Tki\Rand::betterRand(0, 90);
        $insert .= "($sector_id, '1', $angle1, $angle2, $distance)";
        if ($j <= ($finish - 1))
        {
            $insert .= ", ";
        }
        else
        {
            $insert .= ";";
        }
    }

    $result = $pdo_db->exec($insert);
    $variables['insert_sector_results'][$i]['result'] = Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);
    $catch_results[$z] = $variables['insert_sector_results'][$i]['result'];
    $z++;

    $local_table_timer->stop();
    $variables['insert_sector_results'][$i]['elapsed'] = $local_table_timer->elapsed();
    $variables['insert_sector_results'][$i]['loop'] = $i;
    $variables['insert_sector_results'][$i]['loops'] = $loops;
    $variables['insert_sector_results'][$i]['start'] = $start;
    $variables['insert_sector_results'][$i]['finish'] = $finish;

    $start = $finish + 1;
    $finish += $loopsize;
    if ($finish > ($tkireg->sector_max))
    {
        $finish = ($tkireg->sector_max);
    }
}

/// Insert zones - Unchartered, fed, free trade, war & Fed space

$local_table_timer->start(); // Start benchmarking
$replace = $pdo_db->exec("INSERT INTO {$pdo_db->prefix}zones (zone_name, owner, corp_zone, allow_beacon, allow_attack, allow_planetattack, allow_warpedit, allow_planet, allow_trade, allow_defenses, max_hull) VALUES ('Unchartered space', 0, 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', '0' )");
$variables['create_unchartered_results']['result'] = Tki\Db::logDbErrors($pdo_db, $replace, __LINE__, __FILE__);
$catch_results[$z] = $variables['create_unchartered_results']['result'];
$z++;
$local_table_timer->stop();
$variables['create_unchartered_results']['time'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking
$replace = $pdo_db->exec("INSERT INTO {$pdo_db->prefix}zones (zone_name, owner, corp_zone, allow_beacon, allow_attack, allow_planetattack, allow_warpedit, allow_planet, allow_trade, allow_defenses, max_hull) VALUES ('Federation space', 0, 'N', 'N', 'N', 'N', 'N', 'N',  'Y', 'N', '$tkireg->fed_max_hull')");
$variables['create_fedspace_results']['result'] = Tki\Db::logDbErrors($pdo_db, $replace, __LINE__, __FILE__);
$catch_results[$z] = $variables['create_fedspace_results']['result'];
$z++;
$local_table_timer->stop();
$variables['create_fedspace_results']['time'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking
$replace = $pdo_db->exec("INSERT INTO {$pdo_db->prefix}zones (zone_name, owner, corp_zone, allow_beacon, allow_attack, allow_planetattack, allow_warpedit, allow_planet, allow_trade, allow_defenses, max_hull) VALUES ('Free-Trade space', 0, 'N', 'N', 'Y', 'N', 'N', 'N','Y', 'N', '0')");
$variables['create_free_results']['result'] = Tki\Db::logDbErrors($pdo_db, $replace, __LINE__, __FILE__);
$catch_results[$z] = $variables['create_free_results']['result'];
$z++;
$local_table_timer->stop();
$variables['create_free_results']['time'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking
$replace = $pdo_db->exec("INSERT INTO {$pdo_db->prefix}zones (zone_name, owner, corp_zone, allow_beacon, allow_attack, allow_planetattack, allow_warpedit, allow_planet, allow_trade, allow_defenses, max_hull) VALUES ('War Zone', 0, 'N', 'Y', 'Y', 'Y', 'Y', 'Y','N', 'Y', '0')");
$variables['create_warzone_results']['result'] = Tki\Db::logDbErrors($pdo_db, $replace, __LINE__, __FILE__);
$catch_results[$z] = $variables['create_warzone_results']['result'];
$z++;
$local_table_timer->stop();
$variables['create_warzone_results']['time'] = $local_table_timer->elapsed();

$local_table_timer->start(); // Start benchmarking
$update = $pdo_db->exec("UPDATE {$pdo_db->prefix}universe SET zone_id='2' WHERE sector_id<=" . $variables['fedsecs']);
$variables['create_fed_sectors_results']['result'] = Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);
$catch_results[$z] = $variables['create_fed_sectors_results']['result'];
$z++;
$local_table_timer->stop();
$variables['create_fed_sectors_results']['time'] = $local_table_timer->elapsed();

// Finding random sectors where port=none and getting their sector ids in one sql query

/// Insert special ports
// Warning: Do not alter loopsize - this should be balanced 50%/50% PHP/MySQL load :)

$loopsize = 500;
$loops = round($variables['spp'] / $loopsize);
if ($loops <= 0)
{
    $loops = 1;
}

$variables['insert_special_loops'] = $loops;

$finish = $loopsize;
if ($finish > $variables['spp'])
{
    $finish = ($variables['spp']);
}

// Since we hard coded a special port already, we start from 1.
$start = 1;

$local_table_timer->start(); // Start benchmarking

$sql = "SELECT sector_id FROM {$pdo_db->prefix}universe WHERE port_type='none' ORDER BY RAND() DESC LIMIT :limit";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':limit', $variables['spp']);
$stmt->execute();
$sql_query = $stmt->fetchAll();

// TODO: This select should have an error check that is reflected in the template
$catch_results[$z] = Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
$z++;

for ($i = 1; $i <= $loops; $i++)
{
    $update = "UPDATE {$pdo_db->prefix}universe SET zone_id='3',port_type='special' WHERE ";
    for ($j = $start; $j < $finish; $j++)
    {
        $result = $sql_query[$j];
        $update .= "(port_type='none' and sector_id=$result[sector_id])";
        if ($j < ($finish - 1))
        {
            $update .= " or ";
        }
        else
        {
            $update .= ";";
        }
    }
    $resx = $pdo_db->exec($update);
    $variables['insert_special_ports'][$i]['result'] = Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
    $catch_results[$z] = $variables['insert_special_ports'][$i]['result'];
    $z++;
    $local_table_timer->stop();
    $variables['insert_special_ports'][$i]['elapsed'] = $local_table_timer->elapsed();
    $variables['insert_special_ports'][$i]['loop'] = $i;
    $variables['insert_special_ports'][$i]['loops'] = $loops;
    $variables['insert_special_ports'][$i]['start'] = ($start + 1);
    $variables['insert_special_ports'][$i]['finish'] = $finish;

    $start = $finish;
    $finish += $loopsize;
    if ($finish > $variables['spp'])
    {
        $finish = ($variables['spp']);
    }
}

// Finding random sectors where port=none and getting their sector ids in one sql query
// For Ore Ports

/// Insert ore ports
// Warning: Do not alter loopsize - This should be balanced 50%/50% PHP/MySQL load :)

$loopsize = 500;
$loops = round($variables['oep'] / $loopsize);
if ($loops <= 0)
{
    $loops = 1;
}

$variables['insert_ore_loops'] = $loops;

$finish = $loopsize;
if ($finish > $variables['oep'])
{
    $finish = ($variables['oep']);
}

$start = 0;

$local_table_timer->start(); // Start benchmarking

$sql = "SELECT sector_id FROM {$pdo_db->prefix}universe WHERE port_type='none' ORDER BY RAND() DESC LIMIT :limit";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':limit', $variables['oep']);
$stmt->execute();
$sql_query = $stmt->fetchAll();

// TODO: This select should have an error check that is reflected in the template
$catch_results[$z] = Tki\Db::logDbErrors($pdo_db, $sql_query, __LINE__, __FILE__);
$z++;
$update = "UPDATE {$pdo_db->prefix}universe SET port_type='ore',port_ore=$initsore,port_organics=$initborganics,port_goods=$initbgoods,port_energy=$initbenergy WHERE ";

for ($i = 1; $i <= $loops; $i++)
{
    $update = "UPDATE {$pdo_db->prefix}universe SET port_type='ore',port_ore=$initsore,port_organics=$initborganics,port_goods=$initbgoods,port_energy=$initbenergy WHERE ";
    for ($j = $start; $j < $finish; $j++)
    {
        $result = $sql_query[$j];
        $update .= "(port_type='none' and sector_id=$result[sector_id])";
        if ($j < ($finish - 1))
        {
            $update .= " or ";
        }
        else
        {
            $update .= ";";
        }
    }
    $resx = $pdo_db->exec($update);
    $variables['insert_ore_ports'][$i]['result'] = Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
    $catch_results[$z] = $variables['insert_ore_ports'][$i]['result'];
    $z++;
    $local_table_timer->stop();
    $variables['insert_ore_ports'][$i]['elapsed'] = $local_table_timer->elapsed();
    $variables['insert_ore_ports'][$i]['loop'] = $i;
    $variables['insert_ore_ports'][$i]['loops'] = $loops;
    $variables['insert_ore_ports'][$i]['start'] = ($start + 1);
    $variables['insert_ore_ports'][$i]['finish'] = $finish;

    $start = $finish;
    $finish += $loopsize;
    if ($finish > $variables['oep'])
    {
        $finish = ($variables['oep']);
    }
}

// Finding random sectors where port=none and getting their sector ids in one sql query
// For Organic Ports

/// Insert organics ports
// Warning: Do not alter loopsize - This should be balanced 50%/50% PHP/MySQL load :)

$loopsize = 500;
$loops = round($variables['ogp'] / $loopsize);
if ($loops <= 0)
{
    $loops = 1;
}

$variables['insert_organics_loops'] = $loops;

$finish = $loopsize;
if ($finish > $variables['ogp'])
{
    $finish = ($variables['ogp']);
}

$start = 0;

$local_table_timer->start(); // Start benchmarking

$sql = "SELECT sector_id FROM {$pdo_db->prefix}universe WHERE port_type='none' ORDER BY RAND() DESC LIMIT :limit";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':limit', $variables['ogp']);
$stmt->execute();
$sql_query = $stmt->fetchAll();

// TODO: This select should have an error check that is reflected in the template
$catch_results[$z] = Tki\Db::logDbErrors($pdo_db, $sql_query, __LINE__, __FILE__);
$z++;
$update = "UPDATE {$pdo_db->prefix}universe SET port_type='organics',port_ore=$initsore,port_organics=$initborganics,port_goods=$initbgoods,port_energy=$initbenergy WHERE ";

for ($i = 1; $i <= $loops; $i++)
{
    $update = "UPDATE {$pdo_db->prefix}universe SET port_type='organics',port_ore=$initbore,port_organics=$initsorganics,port_goods=$initbgoods,port_energy=$initbenergy WHERE ";
    for ($j = $start; $j < $finish; $j++)
    {
        $result = $sql_query[$j];
        $update .= "(port_type='none' and sector_id=$result[sector_id])";
        if ($j < ($finish - 1))
        {
            $update .= " or ";
        }
        else
        {
            $update .= ";";
        }
    }
    $resx = $pdo_db->exec($update);
    $variables['insert_organics_ports'][$i]['result'] = Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
    $catch_results[$z] = $variables['insert_organics_ports'][$i]['result'];
    $z++;
    $local_table_timer->stop();
    $variables['insert_organics_ports'][$i]['elapsed'] = $local_table_timer->elapsed();
    $variables['insert_organics_ports'][$i]['loop'] = $i;
    $variables['insert_organics_ports'][$i]['loops'] = $loops;
    $variables['insert_organics_ports'][$i]['start'] = ($start + 1);
    $variables['insert_organics_ports'][$i]['finish'] = $finish;

    $start = $finish;
    $finish += $loopsize;
    if ($finish > $variables['ogp'])
    {
        $finish = ($variables['ogp']);
    }
}

// Finding random sectors where port=none and getting their sector ids in one sql query
// For Goods Ports

/// Insert goods ports
// Warning: Do not alter loop size - This should be balanced 50%/50% PHP/MySQL load :)

$loopsize = 500;
$loops = round($variables['gop'] / $loopsize);
if ($loops <= 0)
{
    $loops = 1;
}

$variables['insert_goods_loops'] = $loops;

$finish = $loopsize;
if ($finish > $variables['gop'])
{
    $finish = ($variables['gop']);
}

$start = 0;

$local_table_timer->start(); // Start benchmarking

$sql = "SELECT sector_id FROM {$pdo_db->prefix}universe WHERE port_type='none' ORDER BY RAND() DESC LIMIT :limit";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':limit', $variables['gop']);
$stmt->execute();
$sql_query = $stmt->fetchAll();

// TODO: This select should have an error check that is reflected in the template
$catch_results[$z] = Tki\Db::logDbErrors($pdo_db, $sql_query, __LINE__, __FILE__);
$z++;
$update = "UPDATE {$pdo_db->prefix}universe SET port_type='goods',port_ore=$initbore,port_organics=$initborganics,port_goods=$initsgoods,port_energy=$initbenergy WHERE ";

for ($i = 1; $i <= $loops; $i++)
{
    $update = "UPDATE {$pdo_db->prefix}universe SET port_type='goods',port_ore=$initbore,port_organics=$initborganics,port_goods=$initsgoods,port_energy=$initbenergy WHERE ";
    for ($j = $start; $j < $finish; $j++)
    {
        $result = $sql_query[$j];
        $update .= "(port_type='none' and sector_id=$result[sector_id])";
        if ($j < ($finish - 1))
        {
            $update .= " or ";
        }
        else
        {
            $update .= ";";
        }
    }
    $resx = $pdo_db->exec($update);
    $variables['insert_goods_ports'][$i]['result'] = Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
    $catch_results[$z] = $variables['insert_goods_ports'][$i]['result'];
    $z++;
    $local_table_timer->stop();
    $variables['insert_goods_ports'][$i]['elapsed'] = $local_table_timer->elapsed();
    $variables['insert_goods_ports'][$i]['loop'] = $i;
    $variables['insert_goods_ports'][$i]['loops'] = $loops;
    $variables['insert_goods_ports'][$i]['start'] = ($start + 1);
    $variables['insert_goods_ports'][$i]['finish'] = $finish;

    $start = $finish;
    $finish += $loopsize;
    if ($finish > $variables['gop'])
    {
        $finish = ($variables['gop']);
    }
}

// Finding random sectors where port=none and getting their sector ids in one sql query
// For Energy Ports

/// Insert energy ports
// Warning: Do not alter loop size - This should be balanced 50%/50% PHP/MySQL load :)

$loopsize = 500;
$loops = round($variables['enp'] / $loopsize);
if ($loops <= 0)
{
    $loops = 1;
}

$variables['insert_energy_loops'] = $loops;

$finish = $loopsize;
if ($finish > $variables['enp'])
{
    $finish = ($variables['enp']);
}

// Well since we hard coded an energy port already, we start from 1.
$start = 1;

$local_table_timer->start(); // Start benchmarking

$sql = "SELECT sector_id FROM {$pdo_db->prefix}universe WHERE port_type='none' ORDER BY RAND() DESC LIMIT :limit";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':limit', $variables['enp']);
$stmt->execute();
$sql_query = $stmt->fetchAll();

// TODO: This select should have an error check that is reflected in the template
$catch_results[$z] = Tki\Db::logDbErrors($pdo_db, $sql_query, __LINE__, __FILE__);
$z++;
$update = "UPDATE {$pdo_db->prefix}universe SET port_type='energy',port_ore=$initbore,port_organics=$initborganics,port_goods=$initsgoods,port_energy=$initbenergy WHERE ";

for ($i = 1; $i <= $loops; $i++)
{
    $update = "UPDATE {$pdo_db->prefix}universe SET port_type='energy',port_ore=$initbore,port_organics=$initborganics,port_goods=$initsgoods,port_energy=$initbenergy WHERE ";
    for ($j = $start; $j < $finish; $j++)
    {
        $result = $sql_query[$j];
        $update .= "(port_type='none' and sector_id=$result[sector_id])";
        if ($j < ($finish - 1))
        {
            $update .= " or ";
        }
        else
        {
            $update .= ";";
        }
    }

    $resx = $pdo_db->exec($update);
    $variables['insert_energy_ports'][$i]['result'] = Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
    $catch_results[$z] = $variables['insert_energy_ports'][$i]['result'];
    $z++;
    $local_table_timer->stop();
    $variables['insert_energy_ports'][$i]['elapsed'] = $local_table_timer->elapsed();
    $variables['insert_energy_ports'][$i]['loop'] = $i;
    $variables['insert_energy_ports'][$i]['loops'] = $loops;
    $variables['insert_energy_ports'][$i]['start'] = ($start + 1);
    $variables['insert_energy_ports'][$i]['finish'] = $finish;

    $start = $finish;
    $finish += $loopsize;
    if ($finish > $variables['enp'])
    {
        $finish = ($variables['enp']);
    }
}

for ($t = 0; $t < $z; $t++)
{
    if ($catch_results[$t] !== true)
    {
        $variables['autorun'] = false; // We disable autorun if any errors occur in processing
    }
}

$template->addVariables('langvars', $langvars);

// Pull in footer variables from footer_t.php
include './footer_t.php';
$template->addVariables('variables', $variables);
$template->display('templates/classic/create_universe/60.tpl');
