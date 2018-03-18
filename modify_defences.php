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
// File: modify_defenses.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

$title = $langvars['l_md_title'];
$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('modify_defenses', 'common', 'global_includes', 'global_funcs', 'footer', 'news'));

if (!isset($defense_id))
{
    echo $langvars['l_md_invalid'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $template);
    die();
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$response = null;
$response = filter_input(INPUT_POST, 'response', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($response)) === 0)
{
    $response = false;
}

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

// Get sectorinfo from database
$sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db); // Build a sector gateway object to handle the SQL calls
$sectorinfo = $sectors_gateway->selectSectorInfo($playerinfo['sector']);

if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_md_noturn'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $template);
    die();
}

$sql = "SELECT * FROM ::prefix::sector_defense WHERE defense_id=:defense_id";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':defense_id', $defense_id, PDO::PARAM_INT);
$stmt->execute();
$defenseinfo = $stmt->fetchAll(PDO::FETCH_ASSOC); // Put the defense information into the array "defenseinfo"

if (!$defenseinfo)  // Not too sure, may need more checks on this.
{
    echo $langvars['l_md_nolonger'] . "<br>";
    Tki\Text::gotoMain($pdo_db, $lang);
    die();
}

if ($defenseinfo['sector_id'] != $playerinfo['sector'])
{
    echo $langvars['l_md_nothere'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $template);
    die();
}

if ($defenseinfo['ship_id'] == $playerinfo['ship_id'])
{
    $defense_owner = $langvars['l_md_you'];
}
else
{
    $defense_ship_id = $defenseinfo['ship_id'];
    $resulta = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($defense_ship_id));
    $ownerinfo = $resulta->fields;
    $defense_owner = $ownerinfo['character_name'];
}

$defense_type = $defenseinfo['defense_type'] == 'F' ? $langvars['l_fighters'] : $langvars['l_mines'];
$qty = $defenseinfo['quantity'];
if ($defenseinfo['fm_setting'] == 'attack')
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
    case 'fight':
        echo "<h1>" . $title . "</h1>\n";
        if ($defenseinfo['ship_id'] == $playerinfo['ship_id'])
        {
            echo $langvars['l_md_yours'] . "<br><br>";
            Tki\Text::gotoMain($pdo_db, $lang);

            $footer = new Tki\Footer();
            $footer->display($pdo_db, $lang, $tkireg, $template);
            die();
        }

        $sector = $playerinfo['sector'];
        if ($defenseinfo['defense_type'] == 'F')
        {
            $countres = $db->Execute("SELECT SUM(quantity) AS totalfighters FROM {$db->prefix}sector_defense WHERE sector_id = ? AND defense_type = 'F';", array($sector));
            $ttl = $countres->fields;
            $total_sector_fighters = $ttl['totalfighters'];
            $calledfrom = "modify_defenses.php";
            include_once './sector_fighters.php';
        }
        else
        {
            // Attack mines goes here
            $countres = $db->Execute("SELECT SUM(quantity) AS totalmines FROM {$db->prefix}sector_defense WHERE sector_id = ? AND defense_type = 'M';", array($sector));
            $ttl = $countres->fields;
            $total_sector_mines = $ttl['totalmines'];
            $playerbeams = Tki\CalcLevels::abstractLevels($playerinfo['beams'], $tkireg);
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
            Tki\Mines::explode($pdo_db, $sector, $playerbeams);
            $char_name = $playerinfo['character_name'];
            $langvars['l_md_msgdownerb'] = str_replace("[sector]", $sector, $langvars['l_md_msgdownerb']);
            $langvars['l_md_msgdownerb'] = str_replace("[mines]", $playerbeams, $langvars['l_md_msgdownerb']);
            $langvars['l_md_msgdownerb'] = str_replace("[name]", $char_name, $langvars['l_md_msgdownerb']);
            Tki\SectorDefense::messageDefenseOwner($pdo_db, $sector, $langvars['l_md_msgdownerb']);
            Tki\Text::gotoMain($pdo_db, $lang);
            die();
        }
        break;

    case 'retrieve':
        if ($defenseinfo['ship_id'] != $playerinfo['ship_id'])
        {
             echo $langvars['l_md_notyours'] . "<br><br>";
             Tki\Text::gotoMain($pdo_db, $lang);

             $footer = new Tki\Footer();
             $footer->display($pdo_db, $lang, $tkireg, $template);
             die();
        }

        $quantity = preg_replace('/[^0-9]/', '', $quantity);
        if ($quantity < 0)
        {
            $quantity = 0;
        }

        if ($quantity > $defenseinfo['quantity'])
        {
            $quantity = $defenseinfo['quantity'];
        }

        $torpedo_max = Tki\CalcLevels::abstractLevels($playerinfo['torp_launchers'], $tkireg) - $playerinfo['torps'];
        $fighter_max = Tki\CalcLevels::abstractLevels($playerinfo['computer'], $tkireg) - $playerinfo['ship_fighters'];
        if ($defenseinfo['defense_type'] == 'F')
        {
            if ($quantity > $fighter_max)
            {
                $quantity = $fighter_max;
            }
        }

        if ($defenseinfo['defense_type'] == 'M')
        {
            if ($quantity > $torpedo_max)
            {
                $quantity = $torpedo_max;
            }
        }

        if ($quantity > 0)
        {
            $db->Execute("UPDATE {$db->prefix}sector_defense SET quantity=quantity - ? WHERE defense_id = ?", array($quantity, $defense_id));
            if ($defenseinfo['defense_type'] == 'M')
            {
                $db->Execute("UPDATE {$db->prefix}ships SET torps=torps + ? WHERE ship_id = ?", array($quantity, $playerinfo['ship_id']));
            }
            else
            {
                $db->Execute("UPDATE {$db->prefix}ships SET ship_fighters = ship_fighters + ? WHERE ship_id = ?", array($quantity, $playerinfo['ship_id']));
            }

            $db->Execute("DELETE FROM {$db->prefix}sector_defense WHERE quantity <= 0");
        }

        $stamp = date("Y-m-d H:i:s");

        $db->Execute("UPDATE {$db->prefix}ships SET last_login = ?,turns = turns - 1, turns_used = turns_used + 1, sector = ? WHERE ship_id = ?;", array($stamp, $playerinfo['sector'], $playerinfo['ship_id']));
        echo "<h1>" . $title . "</h1>\n";
        echo $langvars['l_md_retr'] . " " . $quantity . " " . $defense_type . ".<br>";
        Tki\Text::gotoMain($pdo_db, $lang);
        die();

    case 'change':
        echo "<h1>" . $title . "</h1>\n";
        if ($defenseinfo['ship_id'] != $playerinfo['ship_id'])
        {
            echo $langvars['l_md_notyours'] . "<br><br>";
            Tki\Text::gotoMain($pdo_db, $lang);

            $footer = new Tki\Footer();
            $footer->display($pdo_db, $lang, $tkireg, $template);
            die();
        }

        $db->Execute("UPDATE {$db->prefix}sector_defense SET fm_setting = ? WHERE defense_id = ?", array($mode, $defense_id));
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
        Tki\Text::gotoMain($pdo_db, $lang);
        die();

    default:
        echo "<h1>" . $title . "</h1>\n";
        $langvars['l_md_consist'] = str_replace("[qty]", $qty, $langvars['l_md_consist']);
        $langvars['l_md_consist'] = str_replace("[type]", $defense_type, $langvars['l_md_consist']);
        $langvars['l_md_consist'] = str_replace("[owner]", $defense_owner, $langvars['l_md_consist']);
        echo $langvars['l_md_consist'] . "<br>";

        if ($defenseinfo['ship_id'] == $playerinfo['ship_id'])
        {
            echo $langvars['l_md_youcan'] . ":<br>";
            echo "<form accept-charset='utf-8' action=modify_defenses.php method=post>";
            echo $langvars['l_md_retrieve'] . " <input type=test name=quantity size=10 maxlength=10 value=0></input> $defense_type<br>";
            echo "<input type=hidden name=response value=retrieve>";
            echo "<input type=hidden name=defense_id value=$defense_id>";
            echo "<input type=submit value=" . $langvars['l_submit'] . "><br><br>";
            echo "</form>";
            if ($defenseinfo['defense_type'] == 'F')
            {
                echo $langvars['l_md_change'] . ":<br>";
                echo "<form accept-charset='utf-8' action=modify_defenses.php method=post>";
                echo $langvars['l_md_cmode'] . " <input type=radio name=mode $set_attack value=attack>" . $langvars['l_md_attack'] . "</input>";
                echo "<input type=radio name=mode $set_toll value=toll>" . $langvars['l_md_toll'] . "</input><br>";
                echo "<input type=submit value=" . $langvars['l_submit'] . "><br><br>";
                echo "<input type=hidden name=response value=change>";
                echo "<input type=hidden name=defense_id value=$defense_id>";
                echo "</form>";
            }
        }
        else
        {
            $result2 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($defenseinfo['ship_id']));
            $fighters_owner = $result2->fields;

            if ($fighters_owner['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
            {
                echo $langvars['l_md_youcan'] . ":<br>";
                echo "<form accept-charset='utf-8' action=modify_defenses.php method=post>";
                echo $langvars['l_md_attdef'] . "<br><input type=submit value=" . $langvars['l_md_attack'] . "></input><br>";
                echo "<input type=hidden name=response value=fight>";
                echo "<input type=hidden name=defense_id value=$defense_id>";
                echo "</form>";
            }
        }

        Tki\Text::gotoMain($pdo_db, $lang);
        die();
}

Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
