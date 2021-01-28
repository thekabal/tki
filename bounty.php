<?php declare(strict_types = 1);
/**
 * bounty.php from The Kabal Invasion.
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
$langvars = Tki\Translate::load($pdo_db, $lang, array('bounty', 'combat',
                                'common', 'footer', 'insignias', 'news',
                                'port', 'universal'));
$title = $langvars['l_by_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

// Detect if this variable exists, and filter it. Returns false if anything wasn't right
$response = null;
$response = filter_input(INPUT_POST, 'response', FILTER_SANITIZE_STRING);
if (strlen(trim($response)) === 0)
{
    $response = false;
}

if (array_key_exists('response', $_GET))
{
    $response = filter_input(INPUT_GET, 'response', FILTER_SANITIZE_STRING);
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right
$bounty_on = null;
$bounty_on = (int) filter_input(INPUT_POST, 'bounty_on', FILTER_SANITIZE_NUMBER_INT);
if (strlen(trim((string) $bounty_on)) === 0)
{
    $bounty_on = false;
}

if (array_key_exists('bounty_on', $_GET))
{
    $bounty_on = filter_input(INPUT_GET, 'bounty_on', FILTER_SANITIZE_NUMBER_INT);
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right
$bid = null;
$bid = (int) filter_input(INPUT_POST, 'bid', FILTER_SANITIZE_NUMBER_INT);
if (strlen(trim((string) $bid)) === 0)
{
    $bid = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right
$amount = 0;
$amount = (int) filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_INT);
if (strlen(trim((string) $amount)) === 0)
{
    $amount = 0;
}

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);
$bounty_details = array();
$bounties = array();

switch ($response) {
    case 'display':
        echo "<h1>" . $title . "</h1>\n";
        $res5 = $old_db->Execute("SELECT * FROM {$old_db->prefix}ships, {$old_db->prefix}bounty WHERE bounty_on = ship_id AND bounty_on = ?;", array($bounty_on));
        Tki\Db::logDbErrors($pdo_db, $res5, __LINE__, __FILE__);
        $bount_details_count = 0;
        if ($res5)
        {
            while (!$res5->EOF)
            {
                $bounty_details[$bount_details_count] = $res5->fields;
                $bount_details_count++;
                $res5->MoveNext();
            }
        }

        $num_details = $bount_details_count;
        if ($num_details < 1)
        {
            echo $langvars['l_by_nobounties'] . "<br>";
        }
        else
        {
            echo $langvars['l_by_bountyon'] . " " . $bounty_details[0]['character_name'];
            echo '<table border=1 cellspacing=1 cellpadding=2 width="50%" align=center>';
            echo "<tr bgcolor=\"$tkireg->color_header\">";
            echo "<td><strong>" . $langvars['l_amount'] . "</td>";
            echo "<td><strong>" . $langvars['l_by_placedby'] . "</td>";
            echo "<td><strong>" . $langvars['l_by_action'] . "</td>";
            echo "</tr>";
            $color = $tkireg->color_line1;
            for ($bount_details_count = 0; $bount_details_count < $num_details; $bount_details_count++)
            {
                $players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
                $details = $players_gateway->selectPlayerInfoById($bounty_details[$bount_details_count]['placed_by']);

                echo "<tr bgcolor=\"$color\">";
                echo "<td>" . $bounty_details[$bount_details_count]['amount'] . "</td>";
                if ($bounty_details[$bount_details_count]['placed_by'] == 0)
                {
                    echo "<td>" . $langvars['l_by_thefeds'] . "</td>";
                }
                else
                {
                    echo "<td>" . $details['character_name'] . "</td>";
                }

                if ($bounty_details[$bount_details_count]['placed_by'] == $playerinfo['ship_id'])
                {
                    echo "<td><a href=bounty.php?bid=" . $bounty_details[$bount_details_count]['bounty_id'] . "&response=cancel>" . $langvars['l_by_cancel'] . "</a></td>";
                }
                else
                {
                    echo "<td>n/a</td>";
                }

                echo "</tr>";

                if ($color == $tkireg->color_line1)
                {
                    $color = $tkireg->color_line2;
                }
                else
                {
                    $color = $tkireg->color_line1;
                }
            }

            echo "</table>";
        }
        break;
    case 'cancel':
        echo "<h1>" . $title . "</h1>\n";
        if ($playerinfo['turns'] < 1)
        {
            echo $langvars['l_by_noturn'] . "<br><br>";
            Tki\Text::gotoMain($pdo_db, $lang);

            $footer = new Tki\Footer();
            $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
            die();
        }

        $res = $old_db->Execute("SELECT * FROM {$old_db->prefix}bounty WHERE bounty_id = ?;", array($bid));
        Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
        if (!$res || $res->RowCount() == 0)
        {
            echo $langvars['l_by_nobounty'] . "<br><br>";
            Tki\Text::gotoMain($pdo_db, $lang);

            $footer = new Tki\Footer();
            $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
            die();
        }

        $bty = $res->fields;
        if ($bty['placed_by'] != $playerinfo['ship_id'])
        {
            echo $langvars['l_by_notyours'] . "<br><br>";
            Tki\Text::gotoMain($pdo_db, $lang);

            $footer = new Tki\Footer();
            $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
            die();
        }

        $del = $old_db->Execute("DELETE FROM {$old_db->prefix}bounty WHERE bounty_id = ?;", array($bid));
        Tki\Db::logDbErrors($pdo_db, $del, __LINE__, __FILE__);
        $cur_time_stamp = date("Y-m-d H:i:s");
        $refund = $bty['amount'];

        $sql = "UPDATE ::prefix::ships SET last_login = :stamp, turns = turns - 1, turns_used = turns_used + 1, credits = credits + :refund WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':stamp', $cur_time_stamp, \PDO::PARAM_INT);
        $stmt->bindParam(':refund', $refund, \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $result = $stmt->execute();
        Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        echo $langvars['l_by_canceled'] . "<br>";
        Tki\Text::gotoMain($pdo_db, $lang);
        die();
    case 'place':
        echo "<h1>" . $title . "</h1>\n";
        $ex = $old_db->Execute("SELECT * FROM {$old_db->prefix}ships WHERE ship_id = ?;", array($bounty_on));
        Tki\Db::logDbErrors($pdo_db, $ex, __LINE__, __FILE__);
        if (!$ex)
        {
            echo $langvars['l_by_notexists'] . "<br><br>";
            Tki\Text::gotoMain($pdo_db, $lang);

            $footer = new Tki\Footer();
            $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
            die();
        }

        $bty = $ex->fields;
        if ($bty['ship_destroyed'] == "Y")
        {
            echo $langvars['l_by_destroyed'] . "<br><br>";
            Tki\Text::gotoMain($pdo_db, $lang);

            $footer = new Tki\Footer();
            $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
            die();
        }

        if ($playerinfo['turns'] < 1)
        {
            echo $langvars['l_by_noturn'] . "<br><br>";
            Tki\Text::gotoMain($pdo_db, $lang);

            $footer = new Tki\Footer();
            $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
            die();
        }

        if ($amount <= 0)
        {
            echo $langvars['l_by_zeroamount'] . "<br><br>";
            Tki\Text::gotoMain($pdo_db, $lang);

            $footer = new Tki\Footer();
            $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
            die();
        }

        if ($bounty_on == $playerinfo['ship_id'])
        {
            echo $langvars['l_by_yourself'] . "<br><br>";
            Tki\Text::gotoMain($pdo_db, $lang);

            $footer = new Tki\Footer();
            $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
            die();
        }

        if ($amount > $playerinfo['credits'])
        {
            echo $langvars['l_by_notenough'] . "<br><br>";
            Tki\Text::gotoMain($pdo_db, $lang);

            $footer = new Tki\Footer();
            $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
            die();
        }

        if ($tkireg->max_bountyvalue != 0)
        {
            $percent = $tkireg->max_bountyvalue * 100;

            $score = Tki\Score::updateScore($pdo_db, $playerinfo['ship_id'], $tkireg, $playerinfo);
            $maxtrans = $score * $score * $tkireg->max_bountyvalue;
            $previous_bounty = 0;
            $pb = $old_db->Execute("SELECT SUM(amount) AS totalbounty FROM {$old_db->prefix}bounty WHERE bounty_on = ? AND placed_by = ?;", array($bounty_on, $playerinfo['ship_id']));
            Tki\Db::logDbErrors($pdo_db, $pb, __LINE__, __FILE__);
            if ($pb)
            {
                $prev = $pb->fields;
                $previous_bounty = $prev['totalbounty'];
            }

            if ($amount + $previous_bounty > $maxtrans)
            {
                $langvars['l_by_toomuch'] = str_replace("[percent]", (string) $percent, $langvars['l_by_toomuch']);
                echo $langvars['l_by_toomuch'] . "<br><br>";
                Tki\Text::gotoMain($pdo_db, $lang);

                $footer = new Tki\Footer();
                $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
                die();
            }
        }

        $insert = $old_db->Execute("INSERT INTO {$old_db->prefix}bounty (bounty_on, placed_by, amount) values (?, ?, ?);", array($bounty_on, $playerinfo['ship_id'], $amount));
        Tki\Db::logDbErrors($pdo_db, $insert, __LINE__, __FILE__);
        $cur_time_stamp = date("Y-m-d H:i:s");

        $sql = "UPDATE ::prefix::ships SET last_login = :stamp, turns = turns - 1, turns_used = turns_used + 1, credits = credits - :amount WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':stamp', $cur_time_stamp, \PDO::PARAM_INT);
        $stmt->bindParam(':amount', $amount, \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $result = $stmt->execute();
        Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        echo $langvars['l_by_placed'] . "<br>";
        Tki\Text::gotoMain($pdo_db, $lang);
        die();
    default:
        echo "<h1>" . $title . "</h1>\n";
        $res = $old_db->Execute("SELECT * FROM {$old_db->prefix}ships WHERE ship_destroyed = 'N' AND ship_id <> ? ORDER BY character_name ASC;", array($playerinfo['ship_id']));
        Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
        echo "<form accept-charset='utf-8' action=bounty.php method=post>";
        echo "<table>";
        echo "<tr><td>" . $langvars['l_by_bountyon'] . "</td><td><select name=bounty_on>";
        while (!$res->EOF)
        {
            if ($bounty_on !== null && $bounty_on == $res->fields['ship_id'])
            {
                $selected = "selected";
            }
            else
            {
                $selected = null;
            }

            $charname = $res->fields['character_name'];
            $ship_id = $res->fields['ship_id'];
            echo "<option value=$ship_id $selected>$charname</option>";
            $res->MoveNext();
        }

        echo "</select></td></tr>";
        echo "<tr><td>" . $langvars['l_by_amount'] . ":</td><td><input type=text name=amount size=20 maxlength=20></td></tr>";
        echo "<tr><td></td><td><input type=submit value=" . $langvars['l_by_place'] . "><input type=reset value=Clear></td>";
        echo "</table>";
        echo "<input type=hidden name=response value=place>";
        echo "</form>";

        $result3 = $old_db->Execute("SELECT bounty_on, SUM(amount) as total_bounty FROM {$old_db->prefix}bounty GROUP BY bounty_on;");
        Tki\Db::logDbErrors($pdo_db, $result3, __LINE__, __FILE__);

        $i = 0;
        if ($result3)
        {
            while (!$result3->EOF)
            {
                $bounties[$i] = $result3->fields;
                $i++;
                $result3->MoveNext();
            }
        }

        $num_bounties = $i;
        if ($num_bounties < 1)
        {
            echo $langvars['l_by_nobounties'] . "<br>";
        }
        else
        {
            echo $langvars['l_by_moredetails'] . "<br><br>";
            echo "<table width=\"100%\" border=0 cellspacing=0 cellpadding=2>";
            echo "<tr bgcolor=\"$tkireg->color_header\">";
            echo "<td><strong>" . $langvars['l_by_bountyon'] . "</strong></td>";
            echo "<td><strong>" . $langvars['l_amount'] . "</td>";
            echo "</tr>";
            $color = $tkireg->color_line1;
            for ($i = 0; $i < $num_bounties; $i++)
            {
                $players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
                $tmp_return = $players_gateway->selectPlayerInfoById($bounties[$i]['bounty_on']);
                $details = $tmp_return['character_name'];

                echo "<tr bgcolor=\"$color\">";
                echo "<td><a href=bounty.php?bounty_on=" . $bounties[$i]['bounty_on'] . "&response=display>" . $details['character_name'] . "</a></td>";
                echo "<td>" . $bounties[$i]['total_bounty'] . "</td>";
                echo "</tr>";

                if ($color == $tkireg->color_line1)
                {
                    $color = $tkireg->color_line2;
                }
                else
                {
                    $color = $tkireg->color_line1;
                }
            }

            echo "</table>";
        }

        echo "<br><br>";
        break;
}

Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
