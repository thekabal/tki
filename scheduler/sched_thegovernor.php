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
// File: sched_thegovernor.php

if (strpos($_SERVER['PHP_SELF'], 'sched_thegovernor.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

echo "<strong>The Governor</strong><br><br>";

echo "Validating Ship Fighters, Torpedoes, Armor points and Credits...<br>\n";
$tdres = $db->Execute("SELECT * FROM {$db->prefix}ships");
Tki\Db::logDbErrors($db, $tdres, __LINE__, __FILE__);

$detected = (boolean) false;

while (!$tdres->EOF)
{
    $playerinfo = $tdres->fields;
    $ship_fighters_max = Tki\CalcLevels::fighters($playerinfo['computer'], $tkireg->level_factor);
    $torps_max = Tki\CalcLevels::torpedoes($playerinfo['torp_launchers'], $tkireg->level_factor);
    $armor_pts_max = Tki\CalcLevels::armor($playerinfo['armor'], $tkireg->level_factor);

    // Checking Fighters
    if ($playerinfo['ship_fighters'] > $ship_fighters_max)
    {
        echo "'-> <span style='color:#f00;'>Detected Fighters Overload on Ship: {$playerinfo['ship_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $resx = $db->Execute("UPDATE {$db->prefix}ships SET ship_fighters = ? WHERE ship_id = ? LIMIT 1;", array($ship_fighters_max, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($db, $resx, __LINE__, __FILE__);

        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        Tki\AdminLog::writeLog($db, 960, "1|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|{$ship_fighters_max}");
    }
    elseif ($playerinfo['ship_fighters'] < 0)
    {
        echo "'-> <span style='color:#f00;'>Detected Fighters Flip on Ship: {$playerinfo['ship_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $resy = $db->Execute("UPDATE {$db->prefix}ships SET ship_fighters = ? WHERE ship_id = ? LIMIT 1;", array(0, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($db, $resy, __LINE__, __FILE__);

        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        Tki\AdminLog::writeLog($db, 960, "2|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|0");
    }

    // Checking Torpedoes
    if ($playerinfo['torps'] > $torps_max)
    {
        echo "'-> <span style='color:#f00;'>Detected Torpedoes Overload on Ship: {$playerinfo['ship_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $resz = $db->Execute("UPDATE {$db->prefix}ships SET torps = ? WHERE ship_id = ? LIMIT 1;", array($torps_max, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($db, $resz, __LINE__, __FILE__);

        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        Tki\AdminLog::writeLog($db, 960, "3|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|{$ship_fighters_max}");
    }
    elseif ($playerinfo['torps'] < 0)
    {
        echo "'-> <span style='color:#f00;'>Detected Torpedoes Flip on Ship: {$playerinfo['ship_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $resa = $db->Execute("UPDATE {$db->prefix}ships SET torps = ? WHERE ship_id = ? LIMIT 1;", array(0, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($db, $resa, __LINE__, __FILE__);
        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        Tki\AdminLog::writeLog($db, 960, "4|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|0");
    }

    // Checking Armor Points
    if ($playerinfo['armor_pts'] > $armor_pts_max)
    {
        echo "'-> <span style='color:#f00;'>Detected Armor points Overload on Ship: {$playerinfo['ship_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $resb = $db->Execute("UPDATE {$db->prefix}ships SET armor_pts = ? WHERE ship_id = ? LIMIT 1;", array($armor_pts_max, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($db, $resb, __LINE__, __FILE__);

        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        Tki\AdminLog::writeLog($db, 960, "5|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|{$ship_fighters_max}");
    }
    elseif ($playerinfo['armor_pts'] < 0)
    {
        echo "'-> <span style='color:#f00;'>Detected Armor points Flip on Ship: {$playerinfo['ship_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $resc = $db->Execute("UPDATE {$db->prefix}ships SET armor_pts = ? WHERE ship_id = ? LIMIT 1;", array(0, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($db, $resc, __LINE__, __FILE__);

        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        Tki\AdminLog::writeLog($db, 960, "6|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|0");
    }

    // Checking Credits
    if ($playerinfo['credits'] < 0)
    {
        echo "'-> <span style='color:#f00;'>Detected Credits Flip on Ship: {$playerinfo['ship_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $resd = $db->Execute("UPDATE {$db->prefix}ships SET credits = ? WHERE ship_id = ? LIMIT 1;", array(0, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($db, $resd, __LINE__, __FILE__);

        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        Tki\AdminLog::writeLog($db, 960, "7|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|0");
    }

    if ($playerinfo['credits'] > 100000000000000000000)
    {
        echo "'-> <span style='color:#f00;'>Detected Credits Overflow on Ship: {$playerinfo['ship_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $rese = $db->Execute("UPDATE {$db->prefix}ships SET credits = ? WHERE ship_id = ? LIMIT 1;", array(100000000000000000000, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($db, $rese, __LINE__, __FILE__);

        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        Tki\AdminLog::writeLog($db, 960, "7|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|0");
    }

    $tdres->MoveNext();
}

echo "Validating Planets Fighters, Torpedoes, Credits...<br>\n";
$tdres = $db->Execute("SELECT planet_id, credits, fighters, torps, owner FROM {$db->prefix}planets");
Tki\Db::logDbErrors($db, $tdres, __LINE__, __FILE__);

while (!$tdres->EOF)
{
    $planetinfo = $tdres->fields;

    // Checking Credits
    if ($planetinfo['credits'] < 0)
    {
        echo "'-> <span style='color:#f00;'>Detected Credits Flip on Planet: {$planetinfo['planet_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $rese = $db->Execute("UPDATE {$db->prefix}planets SET credits = ? WHERE planet_id = ? LIMIT 1;", array(0, $planetinfo['planet_id']));
        Tki\Db::logDbErrors($db, $rese, __LINE__, __FILE__);

        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        Tki\AdminLog::writeLog($db, 960, "10|{$planetinfo['planet_id']}|{$planetinfo['credits']}|{$planetinfo['owner']}");
    }

    if ($planetinfo['credits'] > 100000000000000000000)
    {
        echo "'-> <span style='color:#f00;'>Detected Credits Overflow on Planet: {$planetinfo['planet_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $resf = $db->Execute("UPDATE {$db->prefix}planets SET credits = ? WHERE planet_id = ? LIMIT 1;", array(100000000000000000000, $planetinfo['planet_id']));
        Tki\Db::logDbErrors($db, $resf, __LINE__, __FILE__);

        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        Tki\AdminLog::writeLog($db, 960, "10|{$planetinfo['planet_id']}|{$planetinfo['credits']}|{$planetinfo['owner']}");
    }

    // Checking Fighters
    if ($planetinfo['fighters'] < 0)
    {
        echo "'-> <span style='color:#f00;'>Detected Fighters Flip on Planet: {$planetinfo['planet_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $resg = $db->Execute("UPDATE {$db->prefix}planets SET fighters = ? WHERE planet_id = ? LIMIT 1;", array(0, $planetinfo['planet_id']));
        Tki\Db::logDbErrors($db, $resg, __LINE__, __FILE__);

        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        Tki\AdminLog::writeLog($db, 960, "11|{$planetinfo['planet_id']}|{$planetinfo['fighters']}|{$planetinfo['owner']}");
    }

    // Checking Torpedoes
    if ($planetinfo['torps'] < 0)
    {
        echo "'-> <span style='color:#f00;'>Detected Torpedoes Flip on Planet: {$planetinfo['planet_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $resh = $db->Execute("UPDATE {$db->prefix}planets SET torps = ? WHERE planet_id = ? LIMIT 1;", array(0, $planetinfo['planet_id']));
        Tki\Db::logDbErrors($db, $resh, __LINE__, __FILE__);

        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        Tki\AdminLog::writeLog($db, 960, "12|{$planetinfo['planet_id']}|{$planetinfo['torps']}|{$planetinfo['owner']}");
    }
    $tdres->MoveNext();
}

echo "Validating IBANK Balance and Loan Credits...<br>\n";
$tdres = $db->Execute("SELECT ship_id, balance, loan FROM {$db->prefix}ibank_accounts");
Tki\Db::logDbErrors($db, $tdres, __LINE__, __FILE__);

while (!$tdres->EOF)
{
    $bankinfo = $tdres->fields;

    // Checking IBANK Balance Credits
    if ($bankinfo['balance'] < 0)
    {
        echo "'-> <span style='color:#f00;'>Detected Balance Credits Flip on IBANK Account: {$bankinfo['ship_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $resi = $db->Execute("UPDATE {$db->prefix}ibank_accounts SET balance = ? WHERE ship_id = ? LIMIT 1;", array(0, $bankinfo['ship_id']));
        Tki\Db::logDbErrors($db, $resi, __LINE__, __FILE__);

        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        Tki\AdminLog::writeLog($db, 960, "20|{$bankinfo['ship_id']}|{$bankinfo['balance']}");
    }

    if ($bankinfo['balance'] > 100000000000000000000)
    {
        echo "'-> <span style='color:#f00;'>Detected Balance Credits Overflow on IBANK Account: {$bankinfo['ship_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $resj = $db->Execute("UPDATE {$db->prefix}ibank_accounts SET balance = ? WHERE ship_id = ? LIMIT 1;", array(100000000000000000000, $bankinfo['ship_id']));
        Tki\Db::logDbErrors($db, $resj, __LINE__, __FILE__);

        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        // Tki\AdminLog::writeLog ($db, 960, "20|{$bankinfo['ship_id']}|{$bankinfo['balance']}");
    }

    // Checking IBANK Loan Credits
    if ($bankinfo['loan'] < 0)
    {
        echo "'-> <span style='color:#f00;'>Detected Loan Credits Flip on IBANK Account: {$bankinfo['ship_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $resk = $db->Execute("UPDATE {$db->prefix}ibank_accounts SET loan = ? WHERE ship_id = ? LIMIT 1;", array(0, $bankinfo['ship_id']));
        Tki\Db::logDbErrors($db, $resk, __LINE__, __FILE__);

        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        Tki\AdminLog::writeLog($db, 960, "21|{$bankinfo['ship_id']}|{$bankinfo['balance']}");
    }

    $tdres->MoveNext();
}

echo "Validating IBANK Transfer Amount Credits...<br>\n";
$tdres = $db->Execute("SELECT transfer_id, source_id, dest_id, amount FROM {$db->prefix}ibank_transfers");
Tki\Db::logDbErrors($db, $tdres, __LINE__, __FILE__);

/*
while (!$tdres->EOF)
{
    $transferinfo = $tdres->fields;

    // Checking IBANK Transfer Amount Credits
    if ($transferinfo['amount'] < 0)
    {
        echo "'-> <span style='color:#f00;'>Detected Transfer Amount Credits Flip on IBANK Transfer: {$transferinfo['ship_id']}.</span> <span style='color:#0f0;'>*** FIXED ***</span><br>\n";
        $db->Execute ("UPDATE {$db->prefix}ibank_transfers SET amount = ? WHERE transfer_id = ? LIMIT 1;", array(0, $transferinfo['transfer_id']));
        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        $detected = (boolean) true;
        Tki\AdminLog::writeLog ($db, 960, "22|{$transferinfo['transfer_id']}|{$transferinfo['amount']}|{$transferinfo['source_id']}|{$transferinfo['dest_id']}");
    }
    $tdres->MoveNext();
}
*/

if ($detected === false)
{
    echo "<hr style='width:300px; height:1px; padding:0px; margin:0px; text-align:left;' />\n";
    echo "<span style='color:#0f0;'>No Flips or Overloads detected.</span><br>\n";
    echo "<hr style='width:300px; height:1px; padding:0px; margin:0px; text-align:left;' />\n";
}

echo "<br>\n";
echo "Checking for Old Session Data...<br>\n";

$old_sessions = 0;

$resl = $db->Execute("SELECT COUNT(*) as old FROM {$db->prefix}sessions WHERE expiry < NOW();");
Tki\Db::logDbErrors($db, $resl, __LINE__, __FILE__);
if ($resl instanceof ADORecordSet)
{
    $old_sessions = (int) $resl->fields['old'];
    if ($old_sessions >0)
    {
        echo "Found {$old_sessions} Old Sessions that needs to be removed.<br>\n";

        $resm = $db->Execute("DELETE FROM {$db->prefix}sessions WHERE expiry < NOW();");
        Tki\Db::logDbErrors($db, $resm, __LINE__, __FILE__);
        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        echo "<br>\n";

        echo "Optimizing Session Table.<br>\n";

        if ($db_type == 'postgres9')
        {
            // Postgresql and SQLite (but SQLite its more like rebuild the whole database!)
            $resn = $db->Execute("VACUUM {$db->prefix}sessions;");
        }
        else
        {
            // Oracle, and mysql
            $resn = $db->Execute("OPTIMIZE TABLE {$db->prefix}sessions;");
        }

        Tki\Db::logDbErrors($db, $resn, __LINE__, __FILE__);
        if ($db->ErrorNo() >0)
        {
            echo "error: ". $db->ErrorMsg() . "<br>\n";
        }
        echo "<br>\n";
    }
    else
    {
        echo "Not found any old Session data - Skipping...<br>\n";
        echo "<br>\n";
    }
}

echo "The Governor has completed.<br>\n";

$multiplier = 0;
