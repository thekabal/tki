<?php declare(strict_types = 1);
/**
 * scheduler/sched_thegovernor.php from The Kabal Invasion.
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

// FUTURE: Split this into separate files that fit their purpose better, PDO, output/debugging handling, investigate better handling of SecureConfig(?)
require_once './config/SecureConfig.php';

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('scheduler'));

echo "<strong>" . $langvars['l_sched_gov_title'] . "</strong><br><br>";

echo $langvars['l_sched_gov_valid_fits'];
$tdres = $old_db->Execute("SELECT * FROM {$old_db->prefix}ships");
Tki\Db::logDbErrors($pdo_db, $tdres, __LINE__, __FILE__);

$detected = false;

$admin_log = new Tki\AdminLog();
while (!$tdres->EOF)
{
    $playerinfo = $tdres->fields;
    $ship_fighters_max = Tki\CalcLevels::abstractLevels((int) $playerinfo['computer'], $tkireg);
    $torps_max = Tki\CalcLevels::abstractLevels((int) $playerinfo['torp_launchers'], $tkireg);
    $armor_pts_max = Tki\CalcLevels::abstractLevels((int) $playerinfo['armor'], $tkireg);

    // Checking Fighters
    if ($playerinfo['ship_fighters'] > $ship_fighters_max)
    {
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_detect_fits_ships'] . $playerinfo['ship_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $resx = $old_db->Execute("UPDATE {$old_db->prefix}ships SET ship_fighters = ? WHERE ship_id = ? LIMIT 1;", array($ship_fighters_max, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);

        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }

        $detected = true;
        $admin_log->writeLog($pdo_db, 960, "1|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|{$ship_fighters_max}");
    }
    elseif ($playerinfo['ship_fighters'] < 0)
    {
        echo $langvars['l_sched_gov_detect_fits_ships'];
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_fit_flip'] . $playerinfo['ship_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $resy = $old_db->Execute("UPDATE {$old_db->prefix}ships SET ship_fighters = ? WHERE ship_id = ? LIMIT 1;", array(0, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($pdo_db, $resy, __LINE__, __FILE__);

        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }

        $detected = true;
        $admin_log->writeLog($pdo_db, 960, "2|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|0");
    }

    // Checking Torpedoes
    if ($playerinfo['torps'] > $torps_max)
    {
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_torp_over'] . $playerinfo['ship_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $resz = $old_db->Execute("UPDATE {$old_db->prefix}ships SET torps = ? WHERE ship_id = ? LIMIT 1;", array($torps_max, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($pdo_db, $resz, __LINE__, __FILE__);

        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }

        $detected = true;
        $admin_log->writeLog($pdo_db, 960, "3|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|{$ship_fighters_max}");
    }
    elseif ($playerinfo['torps'] < 0)
    {
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_torp_flip'] . $playerinfo['ship_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $resa = $old_db->Execute("UPDATE {$old_db->prefix}ships SET torps = ? WHERE ship_id = ? LIMIT 1;", array(0, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($pdo_db, $resa, __LINE__, __FILE__);
        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }

        $detected = true;
        $admin_log->writeLog($pdo_db, 960, "4|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|0");
    }

    // Checking Armor Points
    if ($playerinfo['armor_pts'] > $armor_pts_max)
    {
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_armor_over'] . $playerinfo['ship_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $resb = $old_db->Execute("UPDATE {$old_db->prefix}ships SET armor_pts = ? WHERE ship_id = ? LIMIT 1;", array($armor_pts_max, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($pdo_db, $resb, __LINE__, __FILE__);

        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }

        $detected = true;
        $admin_log->writeLog($pdo_db, 960, "5|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|{$ship_fighters_max}");
    }
    elseif ($playerinfo['armor_pts'] < 0)
    {
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_armor_flip'] . $playerinfo['ship_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $resc = $old_db->Execute("UPDATE {$old_db->prefix}ships SET armor_pts = ? WHERE ship_id = ? LIMIT 1;", array(0, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($pdo_db, $resc, __LINE__, __FILE__);

        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }

        $detected = true;
        $admin_log->writeLog($pdo_db, 960, "6|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|0");
    }

    // Checking Credits
    if ($playerinfo['credits'] < 0)
    {
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_credits_flip'] . $playerinfo['ship_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $resd = $old_db->Execute("UPDATE {$old_db->prefix}ships SET credits = ? WHERE ship_id = ? LIMIT 1;", array(0, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($pdo_db, $resd, __LINE__, __FILE__);

        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }

        $detected = true;
        $admin_log->writeLog($pdo_db, 960, "7|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|0");
    }

    if ($playerinfo['credits'] > 100000000000000000000)
    {
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_credits_over'] . $playerinfo['ship_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $rese = $old_db->Execute("UPDATE {$old_db->prefix}ships SET credits = ? WHERE ship_id = ? LIMIT 1;", array(100000000000000000000, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($pdo_db, $rese, __LINE__, __FILE__);

        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }

        $detected = true;
        $admin_log->writeLog($pdo_db, 960, "7|{$playerinfo['ship_id']}|{$playerinfo['ship_fighters']}|0");
    }

    $tdres->MoveNext();
}

echo $langvars['l_sched_gov_valid_planets'] . "<br>";
$tdres = $old_db->Execute("SELECT planet_id, credits, fighters, torps, owner FROM {$old_db->prefix}planets");
Tki\Db::logDbErrors($pdo_db, $tdres, __LINE__, __FILE__);

while (!$tdres->EOF)
{
    $planetinfo = $tdres->fields;

    // Checking Credits
    if ($planetinfo['credits'] < 0)
    {
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_credits_flip'] . $planetinfo['planet_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $rese = $old_db->Execute("UPDATE {$old_db->prefix}planets SET credits = ? WHERE planet_id = ? LIMIT 1;", array(0, $planetinfo['planet_id']));
        Tki\Db::logDbErrors($pdo_db, $rese, __LINE__, __FILE__);

        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }

        $detected = true;
        $admin_log->writeLog($pdo_db, 960, "10|{$planetinfo['planet_id']}|{$planetinfo['credits']}|{$planetinfo['owner']}");
    }

    if ($planetinfo['credits'] > 100000000000000000000)
    {
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_credits_over'] . $planetinfo['planet_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $resf = $old_db->Execute("UPDATE {$old_db->prefix}planets SET credits = ? WHERE planet_id = ? LIMIT 1;", array(100000000000000000000, $planetinfo['planet_id']));
        Tki\Db::logDbErrors($pdo_db, $resf, __LINE__, __FILE__);

        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }

        $detected = true;
        $admin_log->writeLog($pdo_db, 960, "10|{$planetinfo['planet_id']}|{$planetinfo['credits']}|{$planetinfo['owner']}");
    }

    // Checking Fighters
    if ($planetinfo['fighters'] < 0)
    {
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_fighters_flip'] . $planetinfo['planet_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $resg = $old_db->Execute("UPDATE {$old_db->prefix}planets SET fighters = ? WHERE planet_id = ? LIMIT 1;", array(0, $planetinfo['planet_id']));
        Tki\Db::logDbErrors($pdo_db, $resg, __LINE__, __FILE__);

        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }

        $detected = true;
        $admin_log->writeLog($pdo_db, 960, "11|{$planetinfo['planet_id']}|{$planetinfo['fighters']}|{$planetinfo['owner']}");
    }

    // Checking Torpedoes
    if ($planetinfo['torps'] < 0)
    {
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_torp_flip'] . $planetinfo['planet_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $resh = $old_db->Execute("UPDATE {$old_db->prefix}planets SET torps = ? WHERE planet_id = ? LIMIT 1;", array(0, $planetinfo['planet_id']));
        Tki\Db::logDbErrors($pdo_db, $resh, __LINE__, __FILE__);

        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }

        $detected = true;
        $admin_log->writeLog($pdo_db, 960, "12|{$planetinfo['planet_id']}|{$planetinfo['torps']}|{$planetinfo['owner']}");
    }

    $tdres->MoveNext();
}

echo $langvars['l_sched_gov_valid_ibank'] . "<br>";
$tdres = $old_db->Execute("SELECT ship_id, balance, loan FROM {$old_db->prefix}ibank_accounts");
Tki\Db::logDbErrors($pdo_db, $tdres, __LINE__, __FILE__);

while (!$tdres->EOF)
{
    $bankinfo = $tdres->fields;

    // Checking IBANK Balance Credits
    if ($bankinfo['balance'] < 0)
    {
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_balance_flip'] . $bankinfo['ship_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $resi = $old_db->Execute("UPDATE {$old_db->prefix}ibank_accounts SET balance = ? WHERE ship_id = ? LIMIT 1;", array(0, $bankinfo['ship_id']));
        Tki\Db::logDbErrors($pdo_db, $resi, __LINE__, __FILE__);

        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }

        $detected = true;
        $admin_log->writeLog($pdo_db, 960, "20|{$bankinfo['ship_id']}|{$bankinfo['balance']}");
    }

    if ($bankinfo['balance'] > 100000000000000000000)
    {
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_balance_overflow'] . $bankinfo['ship_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $resj = $old_db->Execute("UPDATE {$old_db->prefix}ibank_accounts SET balance = ? WHERE ship_id = ? LIMIT 1;", array(100000000000000000000, $bankinfo['ship_id']));
        Tki\Db::logDbErrors($pdo_db, $resj, __LINE__, __FILE__);

        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }

        $detected = true;
        // $admin_log->writeLog ($pdo_db, 960, "20|{$bankinfo['ship_id']}|{$bankinfo['balance']}");
    }

    // Checking IBANK Loan Credits
    if ($bankinfo['loan'] < 0)
    {
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_loan_flip'] . $bankinfo['ship_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $resk = $old_db->Execute("UPDATE {$old_db->prefix}ibank_accounts SET loan = ? WHERE ship_id = ? LIMIT 1;", array(0, $bankinfo['ship_id']));
        Tki\Db::logDbErrors($pdo_db, $resk, __LINE__, __FILE__);

        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }

        $detected = true;
        $admin_log->writeLog($pdo_db, 960, "21|{$bankinfo['ship_id']}|{$bankinfo['balance']}");
    }

    $tdres->MoveNext();
}

echo $langvars['l_sched_gov_valid_ibank_trans'] . "<br>";
$tdres = $old_db->Execute("SELECT transfer_id, source_id, dest_id, amount FROM {$old_db->prefix}ibank_transfers");
Tki\Db::logDbErrors($pdo_db, $tdres, __LINE__, __FILE__);

/*
while (!$tdres->EOF)
{
    $transferinfo = $tdres->fields;

    // Checking IBANK Transfer Amount Credits
    if ($transferinfo['amount'] < 0)
    {
        echo "'-> <span style='color:#f00;'>" . $langvars['l_sched_gov_detected_ibank_flip'] . $transferinfo['ship_id'] . "</span> <span style='color:#0f0;'>*** " . $langvars['l_sched_fixed'] . "***</span><br>";
        $old_db->Execute ("UPDATE {$old_db->prefix}ibank_transfers SET amount = ? WHERE transfer_id = ? LIMIT 1;", array(0, $transferinfo['transfer_id']));
        if ($old_db->ErrorNo() > 0)
        {
            echo $langvars['l_sched_database_error'] . $old_db->ErrorMsg() . "<br>";
        }
        $detected = true;
        $admin_log->writeLog ($pdo_db, 960, "22|{$transferinfo['transfer_id']}|{$transferinfo['amount']}|{$transferinfo['source_id']}|{$transferinfo['dest_id']}");
    }
    $tdres->MoveNext();
}
*/

if ($detected === false)
{
    echo "<hr style='width:300px; height:1px; padding:0px; margin:0px; text-align:left;' />";
    echo "<span style='color:#0f0;'>" . $langvars['l_sched_gov_no_flip_or_over'] . "</span><br>";
    echo "<hr style='width:300px; height:1px; padding:0px; margin:0px; text-align:left;' />";
}

echo "<br>";
echo $langvars['l_sched_gov_clear_sessions'] . "<br>";

$sql = "DELETE FROM ::prefix::sessions WHERE expiry < NOW()";
$stmt = $pdo_db->prepare($sql);
$stmt->execute();

echo $langvars['l_sched_gov_opt_sessions'] . "<br>";

if (\Tki\SecureConfig::DB_TYPE == 'postgres9')
{
    // Postgresql and SQLite (but SQLite its more like rebuild the whole database!)
    $resn = $old_db->Execute("VACUUM {$old_db->prefix}sessions;");
}
else
{
    // Oracle, and mysql
    $resn = $old_db->Execute("OPTIMIZE TABLE {$old_db->prefix}sessions;");
}

echo $langvars['l_sched_gov_done'] . "<br>";

$multiplier = 0;
