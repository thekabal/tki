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
// File: mines.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('mines', 'common', 'global_includes', 'global_funcs', 'combat', 'footer', 'news', 'regional'));

$title = $langvars['l_mines_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

$op = null;
if (array_key_exists('op', $_GET) === true)
{
    $op = $_GET['op'];
}
elseif (array_key_exists('op', $_POST) === true)
{
    $op = $_POST['op'];
}

// Get playerinfo from database
$sql = "SELECT * FROM {$pdo_db->prefix}ships WHERE email=:email LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['username']);
$stmt->execute();
$playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

$res = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id = ?;", array($playerinfo['sector']));
Tki\Db::logDbErrors($pdo_db, $db, $res, __LINE__, __FILE__);
$sectorinfo = $res->fields;

$result3 = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id = ?;", array($playerinfo['sector']));
Tki\Db::logDbErrors($pdo_db, $db, $result3, __LINE__, __FILE__);

// Put the defence information into the array "defenceinfo"
$i = 0;
$total_sector_fighters = 0;
$total_sector_mines = 0;
$owns_all = true;
$fighter_id = 0;
$mine_id = 0;
$set_attack = 'CHECKED';
$set_toll = null;

// Do we have a valid recordset?
if ($result3 instanceof ADORecordSet)
{
    while (!$result3->EOF)
    {
        $defences[$i] = $result3->fields;
        if ($defences[$i]['defence_type'] == 'F')
        {
            $total_sector_fighters += $defences[$i]['quantity'];
        }
        else
        {
            $total_sector_mines += $defences[$i]['quantity'];
        }

        if ($defences[$i]['ship_id'] != $playerinfo['ship_id'])
        {
            $owns_all = false;
        }
        else
        {
            if ($defences[$i]['defence_type'] == 'F')
            {
                $fighter_id = $defences[$i]['defence_id'];
                if ($defences[$i]['fm_setting'] == 'attack')
                {
                    $set_attack = 'CHECKED';
                    $set_toll = null;
                }
                else
                {
                    $set_attack = null;
                    $set_toll = 'CHECKED';
                }
            }
            else
            {
                $mine_id = $defences[$i]['defence_id'];
            }
        }
        $i++;
        $result3->MoveNext();
    }
}

$num_defences = $i;
echo "<h1>" . $title . "</h1>\n";
if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_mines_noturn'] . "<br><br>";
    Tki\Text::gotomain($pdo_db, $lang);
    Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
    die ();
}

$res = $db->Execute("SELECT allow_defenses, {$db->prefix}universe.zone_id, owner FROM {$db->prefix}zones, {$db->prefix}universe WHERE sector_id = ? AND {$db->prefix}zones.zone_id = {$db->prefix}universe.zone_id", array($playerinfo['sector']));
Tki\Db::logDbErrors($pdo_db, $db, $res, __LINE__, __FILE__);
$zoneinfo = $res->fields;

if ($zoneinfo['allow_defenses'] == 'N')
{
    echo $langvars['l_mines_nopermit'] . "<br><br>";
}
else
{
    if ($num_defences > 0)
    {
        if (!$owns_all)
        {
            $defence_owner = $defences[0]['ship_id'];
            $result2 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($defence_owner));
            Tki\Db::logDbErrors($pdo_db, $db, $result2, __LINE__, __FILE__);
            $fighters_owner = $result2->fields;

            if ($fighters_owner['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
            {
                echo $langvars['l_mines_nodeploy'] . "<br>";
                Tki\Text::gotomain($pdo_db, $lang);
                die();
            }
        }
    }

    if ($zoneinfo['allow_defenses'] == 'L')
    {
        $zone_owner = $zoneinfo['owner'];
        $result2 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($zone_owner));
        Tki\Db::logDbErrors($pdo_db, $db, $result2, __LINE__, __FILE__);
        $zoneowner_info = $result2->fields;

        if ($zone_owner != $playerinfo['ship_id'])
        {
            if ($zoneowner_info['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
            {
                echo $langvars['l_mines_nopermit'] . "<br><br>";
                Tki\Text::gotomain($pdo_db, $lang);
                die();
            }
        }
    }

    if (!isset($nummines) || !isset($numfighters) || !isset($mode))
    {
        $availmines = number_format($playerinfo['torps'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
        $availfighters = number_format($playerinfo['ship_fighters'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
        echo "<form accept-charset='utf-8' action=mines.php method=post>";
        $langvars['l_mines_info1'] = str_replace("[sector]", $playerinfo['sector'], $langvars['l_mines_info1']);
        $langvars['l_mines_info1'] = str_replace("[mines]", number_format($total_sector_mines, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_mines_info1']);
        $langvars['l_mines_info1'] = str_replace("[fighters]", number_format($total_sector_fighters, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_mines_info1']);
        echo $langvars['l_mines_info1'] . "<br><br>";
        $langvars['l_mines_info2'] = str_replace("[mines]", $availmines, $langvars['l_mines_info2']);
        $langvars['l_mines_info2'] = str_replace("[fighters]", $availfighters, $langvars['l_mines_info2']);
        echo "You have $availmines mines and $availfighters fighters available to deploy.<br>\n";
        echo "<br>\n";
        echo $langvars['l_mines_deploy'] . " <input type=text name=nummines size=10 maxlength=10 value=$playerinfo[torps]> " . $langvars['l_mines'] . ".<br>";
        echo $langvars['l_mines_deploy'] . " <input type=text name=numfighters size=10 maxlength=10 value=$playerinfo[ship_fighters]> " . $langvars['l_fighters'] . ".<br>";
        echo "Fighter mode <input type=radio name=mode $set_attack value=attack>" . $langvars['l_mines_att'] . "</input>";
        echo "<input type=radio name=mode $set_toll value=toll>" . $langvars['l_mines_toll'] . "</input><br>";
         echo "<br>\n";
        echo "<input type=submit value=" . $langvars['l_submit'] . "><input type=reset value=" . $langvars['l_reset'] . "><br><br>";
        echo "<input type=hidden name=op value=$op>";
        echo "</form>";
    }
    else
    {
        $nummines = preg_replace('/[^0-9]/', '', $nummines);
        $numfighters = preg_replace('/[^0-9]/', '', $numfighters);
        if (empty ($nummines))
        {
            $nummines = 0;
        }
        if (empty ($numfighters))
        {
            $numfighters = 0;
        }
        if ($nummines < 0)
        {
            $nummines = 0;
        }
        if ($numfighters < 0)
        {
            $numfighters = 0;
        }
        if ($nummines > $playerinfo['torps'])
        {
            echo $langvars['l_mines_notorps'] . "<br>";
            $nummines = 0;
        }
        else
        {
            $langvars['l_mines_dmines'] = str_replace("[mines]", $nummines, $langvars['l_mines_dmines']);
            echo $langvars['l_mines_dmines'] . "<br>";
        }

        if ($numfighters > $playerinfo['ship_fighters'])
        {
            echo $langvars['l_mines_nofighters'] . ".<br>";
            $numfighters = 0;
        }
        else
        {
            $langvars['l_mines_dfighter'] = str_replace("[fighters]", $numfighters, $langvars['l_mines_dfighter']);
            $langvars['l_mines_dfighter'] = str_replace("[mode]", $mode, $langvars['l_mines_dfighter']);
            echo $langvars['l_mines_dfighter'] . "<br>";
        }

        $stamp = date("Y-m-d H:i:s");
        if ($numfighters > 0)
        {
            if ($fighter_id != 0)
            {
                $update = $db->Execute("UPDATE {$db->prefix}sector_defence SET quantity = quantity + ? ,fm_setting = ? WHERE defence_id = ?;", array($numfighters, $mode, $fighter_id));
                Tki\Db::logDbErrors($pdo_db, $db, $update, __LINE__, __FILE__);
            }
            else
            {
                $update = $db->Execute("INSERT INTO {$db->prefix}sector_defence (ship_id, sector_id, defence_type, quantity, fm_setting) values (?, ?, ?, ?, ?);", array($playerinfo['ship_id'], $playerinfo['sector'], 'F', $numfighters, $mode));
                Tki\Db::logDbErrors($pdo_db, $db, $update, __LINE__, __FILE__);
                echo $db->ErrorMsg();
            }
        }

        if ($nummines > 0)
        {
            if ($mine_id != 0)
            {
                $update = $db->Execute("UPDATE {$db->prefix}sector_defence SET quantity = quantity + ?, fm_setting = ? WHERE defence_id = ?;", array($nummines, $mode, $mine_id));
                Tki\Db::logDbErrors($pdo_db, $db, $update, __LINE__, __FILE__);
            }
            else
            {
                $update = $db->Execute("INSERT INTO {$db->prefix}sector_defence (ship_id, sector_id, defence_type, quantity, fm_setting) values (?, ?, ?, ?, ?);", array($playerinfo['ship_id'], $playerinfo['sector'], 'M', $nummines, $mode));
                Tki\Db::logDbErrors($pdo_db, $db, $update, __LINE__, __FILE__);
            }
        }

        $update = $db->Execute("UPDATE {$db->prefix}ships SET last_login = ?, turns = turns - 1, turns_used = turns_used + 1, ship_fighters = ship_fighters - ?, torps = torps - ? WHERE ship_id = ?;", array($stamp, $numfighters, $nummines, $playerinfo['ship_id']));
        Tki\Db::logDbErrors($pdo_db, $db, $update, __LINE__, __FILE__);
    }
}

Tki\Text::gotomain($pdo_db, $lang);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
