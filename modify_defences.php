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
// File: modify_defences.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $langvars, $tkireg, $template);

$title = $langvars['l_md_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('modify_defences', 'common', 'global_includes', 'global_funcs', 'footer', 'news'));

if (!isset($defence_id))
{
    echo $langvars['l_md_invalid'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang, $langvars);
    Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
    die ();
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$response = null;
$response = filter_input(INPUT_POST, 'response', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($response)) === 0)
{
    $response = false;
}

$res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email = ?;", array($_SESSION['username']));
Tki\Db::logDbErrors($pdo_db, $db, $res, __LINE__, __FILE__);
$playerinfo = $res->fields;

$res = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id = ?;", array($playerinfo['sector']));
Tki\Db::logDbErrors($pdo_db, $db, $res, __LINE__, __FILE__);
$sectorinfo = $res->fields;

if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_md_noturn'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang, $langvars);
    Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
    die ();
}

$result3 = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE defence_id = ?;", array($defence_id));
Tki\Db::logDbErrors($pdo_db, $db, $result3, __LINE__, __FILE__);
// Put the defence information into the array "defenceinfo"

if (!$result3 instanceof ADORecordSet) // Not too sure, may need more checks on this.
{
    echo $langvars['l_md_nolonger'] . "<br>";
    Tki\Text::gotoMain($pdo_db, $lang, $langvars);
    die();
}

$defenceinfo = $result3->fields;
if ($defenceinfo['sector_id'] != $playerinfo['sector'])
{
    echo $langvars['l_md_nothere'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang, $langvars);
    Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
    die();
}

if ($defenceinfo['ship_id'] == $playerinfo['ship_id'])
{
    $defence_owner = $langvars['l_md_you'];
}
else
{
    $defence_ship_id = $defenceinfo['ship_id'];
    $resulta = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($defence_ship_id));
    $ownerinfo = $resulta->fields;
    $defence_owner = $ownerinfo['character_name'];
}

$defence_type = $defenceinfo['defence_type'] == 'F' ? $langvars['l_fighters'] : $langvars['l_mines'];
$qty = $defenceinfo['quantity'];
if ($defenceinfo['fm_setting'] == 'attack')
{
    $set_attack = 'CHECKED';
    $set_toll = null;
}
else
{
    $set_attack = null;
    $set_toll = 'CHECKED';
}

switch ($response)
{
    case "fight":
        echo "<h1>" . $title . "</h1>\n";
        if ($defenceinfo['ship_id'] == $playerinfo['ship_id'])
        {
            echo $langvars['l_md_yours'] . "<br><br>";
            Tki\Text::gotoMain($pdo_db, $lang, $langvars);
            Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
            die();
        }

        $sector = $playerinfo['sector'] ;
        if ($defenceinfo['defence_type'] == 'F')
        {
            $countres = $db->Execute("SELECT SUM(quantity) AS totalfighters FROM {$db->prefix}sector_defence WHERE sector_id = ? AND defence_type = 'F';", array($sector));
            $ttl = $countres->fields;
            $total_sector_fighters = $ttl['totalfighters'];
            $calledfrom = "modify_defences.php";
            include_once './sector_fighters.php';
        }
        else
        {
            // Attack mines goes here
            $countres = $db->Execute("SELECT SUM(quantity) AS totalmines FROM {$db->prefix}sector_defence WHERE sector_id = ? AND defence_type = 'M';", array($sector));
            $ttl = $countres->fields;
            $total_sector_mines = $ttl['totalmines'];
            $playerbeams = Tki\CalcLevels::beams($playerinfo['beams'], $level_factor);
            if ($playerbeams > $playerinfo['ship_energy'])
            {
                $playerbeams = $playerinfo['ship_energy'];
            }
            if ($playerbeams > $total_sector_mines)
            {
                $playerbeams = $total_sector_mines;
            }
            echo $langvars['l_md_bmines'] . " " . $playerbeams . " " . $langvars['l_mines'] . "<br>";
            $update4b = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy = ship_energy - ? WHERE ship_id = ?;", array($playerbeams, $playerinfo['ship_id']));
            Tki\Mines::explode($pdo_db, $db, $sector, $playerbeams);
            $char_name = $playerinfo['character_name'];
            $langvars['l_md_msgdownerb'] = str_replace("[sector]", $sector, $langvars['l_md_msgdownerb']);
            $langvars['l_md_msgdownerb'] = str_replace("[mines]", $playerbeams, $langvars['l_md_msgdownerb']);
            $langvars['l_md_msgdownerb'] = str_replace("[name]", $char_name, $langvars['l_md_msgdownerb']);
            Tki\SectorDefense::messageDefenseOwner($db, $sector, $langvars['l_md_msgdownerb']);
            Tki\Text::gotoMain($pdo_db, $lang, $langvars);
            die();
        }
        break;

    case "retrieve":
        if ($defenceinfo['ship_id'] != $playerinfo['ship_id'])
        {
             echo $langvars['l_md_notyours'] . "<br><br>";
             Tki\Text::gotoMain($pdo_db, $lang, $langvars);
             Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
             die();
        }
        $quantity = preg_replace('/[^0-9]/', '', $quantity);
        if ($quantity < 0)
        {
            $quantity = 0;
        }

        if ($quantity > $defenceinfo['quantity'])
        {
            $quantity = $defenceinfo['quantity'];
        }

        $torpedo_max = Tki\CalcLevels::torpedoes($playerinfo['torp_launchers'], $level_factor) - $playerinfo['torps'];
        $fighter_max = Tki\CalcLevels::fighters($playerinfo['computer'], $level_factor) - $playerinfo['ship_fighters'];
        if ($defenceinfo['defence_type'] == 'F')
        {
            if ($quantity > $fighter_max)
            {
                $quantity = $fighter_max;
            }
        }

        if ($defenceinfo['defence_type'] == 'M')
        {
            if ($quantity > $torpedo_max)
            {
                $quantity = $torpedo_max;
            }
        }
        if ($quantity > 0)
        {
            $db->Execute("UPDATE {$db->prefix}sector_defence SET quantity=quantity - ? WHERE defence_id = ?", array($quantity, $defence_id));
            if ($defenceinfo['defence_type'] == 'M')
            {
                $db->Execute("UPDATE {$db->prefix}ships SET torps=torps + ? WHERE ship_id = ?", array($quantity, $playerinfo['ship_id']));
            }
            else
            {
                $db->Execute("UPDATE {$db->prefix}ships SET ship_fighters = ship_fighters + ? WHERE ship_id = ?", array($quantity, $playerinfo['ship_id']));
            }
            $db->Execute("DELETE FROM {$db->prefix}sector_defence WHERE quantity <= 0");
        }
        $stamp = date("Y-m-d H:i:s");

        $db->Execute("UPDATE {$db->prefix}ships SET last_login = ?,turns = turns - 1, turns_used = turns_used + 1, sector = ? WHERE ship_id = ?;", array($stamp, $playerinfo['sector'], $playerinfo['ship_id']));
        echo "<h1>" . $title . "</h1>\n";
        echo $langvars['l_md_retr'] . " " . $quantity . " " . $defence_type . ".<br>";
        Tki\Text::gotoMain($pdo_db, $lang, $langvars);
        die();
        break;

    case "change":
        echo "<h1>" . $title . "</h1>\n";
        if ($defenceinfo['ship_id'] != $playerinfo['ship_id'])
        {
            echo $langvars['l_md_notyours'] . "<br><br>";
            Tki\Text::gotoMain($pdo_db, $lang, $langvars);
            Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
            die();
        }

        $db->Execute("UPDATE {$db->prefix}sector_defence SET fm_setting = ? WHERE defence_id = ?", array($mode, $defence_id));
        $stamp = date("Y-m-d H:i:s");
        $db->Execute("UPDATE {$db->prefix}ships SET last_login = ?, turns = turns - 1, turns_used = turns_used + 1, sector = ? WHERE ship_id = ?;", array($stamp, $playerinfo['sector'], $playerinfo['ship_id']));
        if ($mode == 'attack')
        {
            $mode = $langvars['l_md_attack'];
        }
        else
        {
            $mode = $langvars['l_md_toll'];
        }

        $langvars['l_md_mode'] = str_replace("[mode]", $mode, $langvars['l_md_mode']);
        echo $langvars['l_md_mode'] . "<br>";
        Tki\Text::gotoMain($pdo_db, $lang, $langvars);
        die();
        break;

    default:
        echo "<h1>" . $title . "</h1>\n";
        $langvars['l_md_consist'] = str_replace("[qty]", $qty, $langvars['l_md_consist']);
        $langvars['l_md_consist'] = str_replace("[type]", $defence_type, $langvars['l_md_consist']);
        $langvars['l_md_consist'] = str_replace("[owner]", $defence_owner, $langvars['l_md_consist']);
        echo $langvars['l_md_consist'] . "<br>";

        if ($defenceinfo['ship_id'] == $playerinfo['ship_id'])
        {
            echo $langvars['l_md_youcan'] . ":<br>";
            echo "<form accept-charset='utf-8' action=modify_defences.php method=post>";
            echo $langvars['l_md_retrieve'] . " <input type=test name=quantity size=10 maxlength=10 value=0></input> $defence_type<br>";
            echo "<input type=hidden name=response value=retrieve>";
            echo "<input type=hidden name=defence_id value=$defence_id>";
            echo "<input type=submit value=" . $langvars['l_submit'] . "><br><br>";
            echo "</form>";
            if ($defenceinfo['defence_type'] == 'F')
            {
                echo $langvars['l_md_change'] . ":<br>";
                echo "<form accept-charset='utf-8' action=modify_defences.php method=post>";
                echo $langvars['l_md_cmode'] . " <input type=radio name=mode $set_attack value=attack>" . $langvars['l_md_attack'] . "</input>";
                echo "<input type=radio name=mode $set_toll value=toll>" . $langvars['l_md_toll'] . "</input><br>";
                echo "<input type=submit value=" . $langvars['l_submit'] . "><br><br>";
                echo "<input type=hidden name=response value=change>";
                echo "<input type=hidden name=defence_id value=$defence_id>";
                echo "</form>";
            }
        }
        else
        {
            $result2 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($defenceinfo['ship_id']));
            $fighters_owner = $result2->fields;

            if ($fighters_owner['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
            {
                echo $langvars['l_md_youcan'] . ":<br>";
                echo "<form accept-charset='utf-8' action=modify_defences.php method=post>";
                echo $langvars['l_md_attdef'] . "<br><input type=submit value=" . $langvars['l_md_attack'] . "></input><br>";
                echo "<input type=hidden name=response value=fight>";
                echo "<input type=hidden name=defence_id value=$defence_id>";
                echo "</form>";
            }
        }

        Tki\Text::gotoMain($pdo_db, $lang, $langvars);
        die();
        break;
}

Tki\Text::gotoMain($pdo_db, $lang, $langvars);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
