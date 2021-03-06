<?php declare(strict_types = 1);
/**
 * mines.php from The Kabal Invasion.
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

require_once './common.php';

$login = new Tki\Login();
$login->checkLogin($pdo_db, $lang, $tkireg, $tkitimer, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('combat', 'common',
                                'footer', 'insignias', 'mines', 'news',
                                'regional', 'universal'));
$title = $langvars['l_mines_title'];

$header = new Tki\Header();
$hader->display($pdo_db, $lang, $template, $title);

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
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

// Get sectorinfo from database
$sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db);
$sectorinfo = $sectors_gateway->selectSectorInfo($playerinfo['sector']);

$links = array();
$i = 0;

// Pull sector info from database
$defenses_gateway = new \Tki\Defenses\DefensesGateway($pdo_db);
$defenses_present = $defenses_gateway->selectDefenses($playerinfo['sector']);

if (!empty($defenses_present))
{
    foreach ($link_present as $tmp_link)
    {
        $links[$i] = $tmp_link['link_dest'];
        $i++;
    }
}

$num_links = $i;

// Put the defense information into the array "defenseinfo"
$i = 0;
$total_sector_fighters = 0;
$total_sector_mines = 0;
$owns_all = true;
$fighter_id = 0;
$mine_id = 0;
$set_attack = 'CHECKED';
$set_toll = null;
$defenses = array();

// Do we have a valid recordset?
if (!empty($defenses_present))
{
    foreach ($defenses_present as $tmp_defense)
    {
        $defenses[$i] = $tmp_defense;
        if ($defenses[$i]['defense_type'] == 'F')
        {
            $total_sector_fighters += $defenses[$i]['quantity'];
        }
        else
        {
            $total_sector_mines += $defenses[$i]['quantity'];
        }

        if ($defenses[$i]['ship_id'] != $playerinfo['ship_id'])
        {
            $owns_all = false;
        }
        else
        {
            if ($defenses[$i]['defense_type'] == 'F')
            {
                $fighter_id = $defenses[$i]['defense_id'];
                if ($defenses[$i]['fm_setting'] == 'attack')
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
                $mine_id = $defenses[$i]['defense_id'];
            }
        }

        $i++;
    }
}

$num_defenses = $i;
echo "<h1>" . $title . "</h1>\n";
if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_mines_noturn'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
    die();
}

$res = $old_db->Execute("SELECT allow_defenses, {$old_db->prefix}universe.zone_id, owner FROM {$old_db->prefix}zones, {$old_db->prefix}universe WHERE sector_id = ? AND {$old_db->prefix}zones.zone_id = {$old_db->prefix}universe.zone_id", array($playerinfo['sector']));
Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
$zoneinfo = $res->fields;

if ($zoneinfo['allow_defenses'] == 'N')
{
    echo $langvars['l_mines_nopermit'] . "<br><br>";
}
else
{
    if ($num_defenses > 0)
    {
        if (!$owns_all)
        {
            $defense_owner = $defenses[0]['ship_id'];

            $players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
            $fighters_owner = $players_gateway->selectPlayerInfoById($defense_owner);

            if ($fighters_owner['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
            {
                echo $langvars['l_mines_nodeploy'] . "<br>";
                Tki\Text::gotoMain($pdo_db, $lang);
                die();
            }
        }
    }

    if ($zoneinfo['allow_defenses'] == 'L')
    {
        $zone_owner = $zoneinfo['owner'];

        $players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
        $zoneowner_info = $players_gateway->selectPlayerInfoById($zone_owner);

        if ($zone_owner != $playerinfo['ship_id'])
        {
            if ($zoneowner_info['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
            {
                echo $langvars['l_mines_nopermit'] . "<br><br>";
                Tki\Text::gotoMain($pdo_db, $lang);
                die();
            }
        }
    }

    if (!isset($nummines))
    {
        $availmines = number_format($playerinfo['torps'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
        $availfighters = number_format($playerinfo['ship_fighters'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
        echo "<form accept-charset='utf-8' action=mines.php method=post>";
        $langvars['l_mines_info1'] = str_replace("[sector]", (string) $playerinfo['sector'], $langvars['l_mines_info1']);
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
        $nummines = (string) preg_replace('/[^0-9]/', '', (string) $nummines);
        $numfighters = (string) preg_replace('/[^0-9]/', '', (string) $numfighters);
        $nummines = (int) $nummines;
        $numfighters = (int) $numfighters;

        if (empty($nummines))
        {
            $nummines = 0;
        }

        if (empty($numfighters))
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
            $langvars['l_mines_dmines'] = str_replace("[mines]", (string) $nummines, $langvars['l_mines_dmines']);
            echo $langvars['l_mines_dmines'] . "<br>";
        }

        if ($numfighters > $playerinfo['ship_fighters'])
        {
            echo $langvars['l_mines_nofighters'] . ".<br>";
            $numfighters = 0;
        }
        else
        {
            $langvars['l_mines_dfighter'] = str_replace("[fighters]", (string) $numfighters, $langvars['l_mines_dfighter']);
            $langvars['l_mines_dfighter'] = str_replace("[mode]", $mode, $langvars['l_mines_dfighter']);
            echo $langvars['l_mines_dfighter'] . "<br>";
        }

        $cur_time_stamp = date("Y-m-d H:i:s");
        if ($numfighters > 0)
        {
            if ($fighter_id != 0)
            {
                $sql = "UPDATE ::prefix::sector_defense SET quantity = quantity + :numfits, fm_setting = :mode WHERE defense_id = :fighter_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':numfits', $numfighters, \PDO::PARAM_INT);
                $stmt->bindParam(':mode', $mode, \PDO::PARAM_INT);
                $stmt->bindParam(':fighter_id', $fighter_id, \PDO::PARAM_INT);
                $result = $stmt->execute();
                Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            }
            else
            {
                $update = $old_db->Execute("INSERT INTO {$old_db->prefix}sector_defense (ship_id, sector_id, defense_type, quantity, fm_setting) values (?, ?, ?, ?, ?);", array($playerinfo['ship_id'], $playerinfo['sector'], 'F', $numfighters, $mode));
                Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);
                echo $old_db->ErrorMsg();
            }
        }

        if ($nummines > 0)
        {
            if ($mine_id != 0)
            {
                $sql = "UPDATE ::prefix::sector_defense SET quantity = quantity + :nummines, fm_setting = :mode WHERE defense_id = :defense_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':nummines', $nummines, \PDO::PARAM_INT);
                $stmt->bindParam(':mode', $mode, \PDO::PARAM_INT);
                $stmt->bindParam(':defense_id', $mine_id, \PDO::PARAM_INT);
                $result = $stmt->execute();
                Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            }
            else
            {
                $update = $old_db->Execute("INSERT INTO {$old_db->prefix}sector_defense (ship_id, sector_id, defense_type, quantity, fm_setting) values (?, ?, ?, ?, ?);", array($playerinfo['ship_id'], $playerinfo['sector'], 'M', $nummines, $mode));
                Tki\Db::logDbErrors($pdo_db, $update, __LINE__, __FILE__);
            }
        }

        $sql = "UPDATE ::prefix::ships SET last_login = :stamp, turns = turns - 1, " .
               "turns_used = turns_used + 1, ship_fighters = ship_fighters - :numfighters, torps = torps - :nummines WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':stamp', $cur_time_stamp, \PDO::PARAM_STR);
        $stmt->bindParam(':numfighters', $numfighters, \PDO::PARAM_INT);
        $stmt->bindParam(':nummines', $nummines, \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $result = $stmt->execute();
        Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
    }
}

Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
