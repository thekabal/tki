<?php declare(strict_types = 1);
/**
 * navcomp.php from The Kabal Invasion.
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
$login->checkLogin($pdo_db, $lang, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'footer',
                                'insignias', 'navcomp', 'universal'));
$title = $langvars['l_nav_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

echo "<h1>" . $title . "</h1>\n";

if (!$tkireg->allow_navcomp)
{
    echo $langvars['l_nav_nocomp'] . '<br><br>';
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
    die();
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$state = null;
$state = (int) filter_input(INPUT_POST, 'state', FILTER_SANITIZE_NUMBER_INT);
if ($state === 0)
{
    $state = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$stop_sector = null;
$stop_sector = (int) filter_input(INPUT_POST, 'stop_sector', FILTER_SANITIZE_NUMBER_INT);
if ($stop_sector === 0)
{
    $stop_sector = false;
}

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

$current_sector = $playerinfo['sector'];
$computer_tech  = $playerinfo['computer'];
$found = null;
$search_result = null;
$search_depth  = 0;

// Get sectorinfo from database
$sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db);
$sectorinfo = $sectors_gateway->selectSectorInfo($current_sector);

if ($state == 0)
{
    echo "<form accept-charset='utf-8' action=\"navcomp.php\" method=post>";
    echo $langvars['l_nav_query'] . " <input name=\"stop_sector\">&nbsp;<input type=submit value=" . $langvars['l_submit'] . "><br>\n";
    echo "<input name=\"state\" value=1 type=hidden>";
    echo "</form>\n";
}
elseif ($state == 1)
{
    if ($computer_tech < 5)
    {
        $max_search_depth = 2;
    }
    elseif ($computer_tech < 10)
    {
        $max_search_depth = 3;
    }
    elseif ($computer_tech < 15)
    {
        $max_search_depth = 4;
    }
    elseif ($computer_tech < 20)
    {
        $max_search_depth = 5;
    }
    else
    {
        $max_search_depth = 6;
    }

    for ($search_depth = 1; $search_depth <= $max_search_depth; $search_depth++)
    {
        $search_query = "SELECT distinct a1.link_start, a1.link_dest ";
        for ($i = 2; $i <= $search_depth; $i++)
        {
            $search_query = $search_query . " ,a" . $i . ".link_dest ";
        }

        $search_query = $search_query . "FROM     {$old_db->prefix}links AS a1 ";

        for ($i = 2; $i <= $search_depth; $i++)
        {
            $search_query = $search_query . "    ,{$old_db->prefix}links AS a" . $i . " ";
        }

        $search_query = $search_query . "WHERE         a1.link_start = $current_sector ";

        for ($i = 2; $i <= $search_depth; $i++)
        {
            $temp1 = $i - 1;
            $search_query = $search_query . "    AND a" . $temp1 . ".link_dest = a" . $i . ".link_start ";
        }

        $search_query = $search_query . "    AND a" . $search_depth . ".link_dest = $stop_sector ";
        $search_query = $search_query . "    AND a1.link_dest != a1.link_start ";

        for ($i = 2; $i <= $search_depth; $i++)
        {
            $search_query = $search_query . "    AND a" . $i . ".link_dest not in (a1.link_dest, a1.link_start ";

            for ($temp2 = 2; $temp2 < $i; $temp2++)
            {
                $search_query = $search_query . ",a" . $temp2 . ".link_dest ";
            }

            $search_query = $search_query . ")";
        }

        $search_query = $search_query . "ORDER BY a1.link_start, a1.link_dest ";
        for ($i = 2; $i <= $search_depth; $i++)
        {
            $search_query = $search_query . ", a" . $i . ".link_dest";
        }

        $search_query = $search_query . " LIMIT 1";
        //echo "$search_query\n\n";

        $old_db->SetFetchMode(ADODB_FETCH_NUM);

        $search_result = $old_db->Execute($search_query);
        if ($search_result === false)
        {
            die('Invalid query');
        }
        else
        {
            Tki\Db::logDbErrors($pdo_db, $search_result, __LINE__, __FILE__);
            $found = $search_result->RecordCount();
            if ($found > 0)
            {
                break;
            }
        }
    }

    if ($found > 0)
    {
        echo "<h3>" . $langvars['l_nav_pathfnd'] . "</h3>\n";
        $links = $search_result->fields;
        echo $links[0];
        for ($i = 1; $i < $search_depth + 1; $i++)
        {
            echo " >> " . $links[$i];
        }

        $old_db->SetFetchMode(ADODB_FETCH_ASSOC);

        echo "<br><br>";
        echo $langvars['l_nav_answ1'] . " " . $search_depth . " " . $langvars['l_nav_answ2'] . "<br><br>";
    }
    else
    {
        echo $langvars['l_nav_proper'] . "<br><br>";
    }
}

$old_db->SetFetchMode(ADODB_FETCH_ASSOC);

Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
