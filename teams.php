<?php declare(strict_types = 1);
/**
 * teams.php from The Kabal Invasion.
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

// Added a quick fix for creating a new team with the same name
// FUTURE: This file probably needs to be completely recoded from scratch :(

require_once './common.php';

$login = new Tki\Login();
$login->checkLogin($pdo_db, $lang, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('teams', 'common', 'global_includes', 'global_funcs', 'main', 'footer'));
$title = $langvars['l_team_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

echo "<h1>" . $title . "</h1>\n";

$testing = false; // set to false to get rid of password when creating new team

// Typecast into ints (this also removes all non numbers)
$whichteam = 0;
if (array_key_exists('whichteam', $_REQUEST) === true)
{
    $whichteam = (int) $_REQUEST['whichteam'];
}

$teamwhat = null;
if (array_key_exists('teamwhat', $_REQUEST) === true)
{
    $teamwhat = (int) $_REQUEST['teamwhat'];
}

$confirmleave = null;
if (array_key_exists('confirmleave', $_REQUEST) === true)
{
    $confirmleave = (string) preg_replace('/[^0-9]/', '', $_REQUEST['confirmleave']);
    $confirmleave = (int) $confirmleave;
}

$invited = null;
if (array_key_exists('invited', $_REQUEST) === true)
{
    $invited = preg_replace('/[^0-9]/', '', $_REQUEST['invited']);
}

$teamname = null;
if (array_key_exists('teamname', $_POST) === true)
{
    $teamname = $_POST['teamname'];
}

$teamdesc = null;
if (array_key_exists('teamdesc', $_POST) === true)
{
    $teamdesc = $_POST['teamdesc'];
}

$confirmed = null;
if (array_key_exists('confirmed', $_REQUEST) === true)
{
    $confirmed = preg_replace('/[^0-9]/', '', $_REQUEST['confirmed']);
}

$update = null;
if (array_key_exists('update', $_POST) === true)
{
    $update = $_POST['update'];
}

$who = null;
if (array_key_exists('who', $_REQUEST) === true)
{
    $who = (int) $_REQUEST['who'];
}

$order = null;
if (array_key_exists('order', $_REQUEST) === true)
{
    $order = (string) $_REQUEST['order'];
}

$type = '';
if (array_key_exists('type', $_POST) === true)
{
    $type = (string) $_REQUEST['type'];
}

// Setting up some recordsets.
// I noticed before the rewriting of this page that in some case recordset may be fetched more thant once, which is NOT optimized.

// Get user info.
$result = $db->Execute("SELECT {$db->prefix}ships.*, {$db->prefix}teams.team_name, {$db->prefix}teams.description, {$db->prefix}teams.creator, {$db->prefix}teams.id
            FROM {$db->prefix}ships
            LEFT JOIN {$db->prefix}teams ON {$db->prefix}ships.team = {$db->prefix}teams.id
            WHERE {$db->prefix}ships.email = ?;", array($_SESSION['username']));
Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);
$playerinfo = $result->fields;
$playerinfo['ship_id'] = (int) $playerinfo['ship_id'];

// We do not want to query the database, if it is not necessary.
if ($playerinfo['team_invite'] != 0)
{
    // Get invite info
    $invite = $db->Execute(" SELECT {$db->prefix}ships.ship_id, {$db->prefix}ships.team_invite, {$db->prefix}teams.team_name,{$db->prefix}teams.id
            FROM {$db->prefix}ships
            LEFT JOIN {$db->prefix}teams ON {$db->prefix}ships.team_invite = {$db->prefix}teams.id
            WHERE {$db->prefix}ships.email = ?;", array($_SESSION['username']));
    Tki\Db::logDbErrors($pdo_db, $invite, __LINE__, __FILE__);
    $invite_info = $invite->fields;
}
else
{
    $invite_info = array();
}

$sectors = null;

// Get Team Info
if ($whichteam !== 0)
{
    $result_team = $db->Execute("SELECT * FROM {$db->prefix}teams WHERE id = ?;", array($whichteam));
    Tki\Db::logDbErrors($pdo_db, $result_team, __LINE__, __FILE__);
    $team = $result_team->fields;
}
else
{
    $result_team = $db->Execute("SELECT * FROM {$db->prefix}teams WHERE id = ?;", array($playerinfo['team']));
    Tki\Db::logDbErrors($pdo_db, $result_team, __LINE__, __FILE__);
    $team = $result_team->fields;
}

switch ($teamwhat)
{
    case 1: // Info on single team
        Tki\Team::showInfo($pdo_db, $langvars, $whichteam, true, $playerinfo, $invite_info, $team, $tkireg);
        echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
        break;

    case 2: // Leave the team
        if (!Tki\Team::isTeamMember($team, $playerinfo))
        {
            echo "<strong><font color=red>An error occured</font></strong><br>You are not a member of this Team.";
            echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
            break;
        }

        if ($confirmleave === null)
        {
            echo $langvars['l_team_confirmleave'] . " <strong>" . $team['team_name'] . "</strong> ? <a href=\"teams.php?teamwhat=$teamwhat&confirmleave=1&whichteam=$whichteam\">" . $langvars['l_yes'] . "</a> - <a href=\"teams.php\">" . $langvars['l_no'] . "</a><br><br>";
        }
        elseif ($confirmleave == 1)
        {
            if ($team['number_of_members'] == 1)
            {
                if (!Tki\Team::isTeamOwner($team, $playerinfo))
                {
                    $langvars['l_team_error'] = str_replace("[error]", "<strong><font color=red>An error occured</font></strong><br>", $langvars['l_team_error']);
                    echo $langvars['l_team_error'];
                    echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
                    break;
                }

                $resx = $db->Execute("DELETE FROM {$db->prefix}teams WHERE id = ?;", array($whichteam));
                Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);

                $resy = $db->Execute("UPDATE {$db->prefix}ships SET team='0' WHERE ship_id = ?;", array($playerinfo['ship_id']));
                Tki\Db::logDbErrors($pdo_db, $resy, __LINE__, __FILE__);

                $resz = $db->Execute("UPDATE {$db->prefix}ships SET team_invite = 0 WHERE team_invite = ?;", array($whichteam));
                Tki\Db::logDbErrors($pdo_db, $resz, __LINE__, __FILE__);

                $res = $db->Execute("SELECT DISTINCT sector_id FROM {$db->prefix}planets WHERE owner = ? AND base = 'Y';", array($playerinfo['ship_id']));
                Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
                $i = 0;
                while (!$res->EOF)
                {
                    $row = $res->fields;
                    $sectors[$i] = $row['sector_id'];
                    $i++;
                    $res->MoveNext();
                }

                $resx = $db->Execute("UPDATE {$db->prefix}planets SET team = 0 WHERE owner = ?;", array($playerinfo['ship_id']));
                Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                if ($sectors !== null)
                {
                    foreach ($sectors as $sector)
                    {
                        Tki\Ownership::calc($pdo_db, $sector, $tkireg->min_bases_to_own, $langvars);
                    }
                }

                Tki\Defense::defenseVsDefense($pdo_db, $playerinfo['ship_id'], $langvars);
                Tki\Ship::leavePlanet($pdo_db, $playerinfo['ship_id']);

                $langvars['l_team_onlymember'] = str_replace("[team_name]", "<strong>$team[team_name]</strong>", $langvars['l_team_onlymember']);
                echo $langvars['l_team_onlymember'] . "<br><br>";
                Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::TEAM_LEAVE, $team['team_name']);
            }
            else
            {
                if (Tki\Team::isTeamOwner($team, $playerinfo))
                {
                    echo $langvars['l_team_youarecoord'] . " <strong>$team[team_name]</strong>. " . $langvars['l_team_relinq'] . "<br><br>";
                    echo "<form accept-charset='utf-8' action='teams.php' method=post>";
                    echo "<table><input type=hidden name=teamwhat value=$teamwhat><input type=hidden name=confirmleave value=2><input type=hidden name=whichteam value=$whichteam>";
                    echo "<tr><td>" . $langvars['l_team_newc'] . "</td><td><select name=newcreator>";

                    $res = $db->Execute("SELECT character_name, ship_id, team FROM {$db->prefix}ships WHERE team = ? ORDER BY character_name ASC;", array($whichteam));
                    Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
                    while (!$res->EOF)
                    {
                        $row = $res->fields;
                        if (!Tki\Team::isTeamOwner($team, $row))
                        {
                            echo "<option value='{$row['ship_id']}'>{$row['character_name']}";
                        }

                        $res->MoveNext();
                    }

                    echo "</select></td></tr>";
                    echo "<tr><td><input type=submit value=" . $langvars['l_submit'] . "></td></tr>";
                    echo "</table>";
                    echo "</form>";
                }
                else
                {
                    $resx = $db->Execute("UPDATE {$db->prefix}ships SET team='0' WHERE ship_id = ?;", array($playerinfo['ship_id']));
                    Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                    $resy = $db->Execute("UPDATE {$db->prefix}teams SET number_of_members = number_of_members - 1 WHERE id = ?;", array($whichteam));
                    Tki\Db::logDbErrors($pdo_db, $resy, __LINE__, __FILE__);

                    $res = $db->Execute("SELECT DISTINCT sector_id FROM {$db->prefix}planets WHERE owner = ? AND base = 'Y' AND team <> 0;", array($playerinfo['ship_id']));
                    Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
                    $i = 0;
                    while (!$res->EOF)
                    {
                        $sectors[$i] = $res->fields['sector_id'];
                        $i++;
                        $res->MoveNext();
                    }

                    $resx = $db->Execute("UPDATE {$db->prefix}planets SET team = 0 WHERE owner = ?;", array($playerinfo['ship_id']));
                    Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                    if ($sectors !== null)
                    {
                        foreach ($sectors as $sector)
                        {
                            Tki\Ownership::calc($pdo_db, $sector, $tkireg->min_bases_to_own, $langvars);
                        }
                    }

                    echo $langvars['l_team_youveleft'] . " <strong>" . $team['team_name'] . "</strong>.<br><br>";
                    Tki\Defense::defenseVsDefense($pdo_db, $playerinfo['ship_id'], $langvars);
                    Tki\Ship::leavePlanet($pdo_db, $playerinfo['ship_id']);
                    Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::TEAM_LEAVE, $team['team_name']);
                    Tki\PlayerLog::writeLog($pdo_db, $team['creator'], \Tki\LogEnums::TEAM_NOT_LEAVE, $playerinfo['character_name']);
                }
            }
        }
        elseif ($confirmleave == 2)
        {
            // Owner of a team is leaving and set a new owner
            $res = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE ship_id = ?;", array($newcreator));
            Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
            $newcreatorname = $res->fields;
            echo $langvars['l_team_youveleft'] . " <strong>" . $team['team_name'] . "</strong> " . $langvars['l_team_relto'] . " " . $newcreatorname['character_name'] . ".<br><br>";

            $resx = $db->Execute("UPDATE {$db->prefix}ships SET team = '0' WHERE ship_id = ?;", array($playerinfo['ship_id']));
            Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);

            $resy = $db->Execute("UPDATE {$db->prefix}ships SET team = ? WHERE team = ?;", array($newcreator, $whichteam));
            Tki\Db::logDbErrors($pdo_db, $resy, __LINE__, __FILE__);

            $resz = $db->Execute("UPDATE {$db->prefix}teams SET number_of_members = number_of_members - 1, creator = ? WHERE id = ?;", array($newcreator, $whichteam));
            Tki\Db::logDbErrors($pdo_db, $resz, __LINE__, __FILE__);

            $res = $db->Execute("SELECT DISTINCT sector_id FROM {$db->prefix}planets WHERE owner = ? AND base = 'Y' AND team <> 0;", array($playerinfo['ship_id']));
            Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);

            $i = 0;
            while (!$res->EOF)
            {
                $sectors[$i] = $res->fields['sector_id'];
                $i++;
                $res->MoveNext();
            }

            $resx = $db->Execute("UPDATE {$db->prefix}planets SET team = 0 WHERE owner = ?;", array($playerinfo['ship_id']));
            Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
            if ($sectors !== null)
            {
                foreach ($sectors as $sector)
                {
                    Tki\Ownership::calc($pdo_db, $sector, $tkireg->min_bases_to_own, $langvars);
                }
            }

            Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::TEAM_NEWLEAD, $team['team_name'] . "|" . $newcreatorname['character_name']);
            Tki\PlayerLog::writeLog($pdo_db, $newcreator, \Tki\LogEnums::TEAM_LEAD, $team['team_name']);
        }

        echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
        break;

    case 3: // Join a team
        if ($playerinfo['team'] != 0)
        {
            echo $langvars['l_team_leavefirst'] . "<br>";
        }
        else
        {
            if ($playerinfo['team_invite'] == $whichteam)
            {
                $resx = $db->Execute("UPDATE {$db->prefix}ships SET team = ?, team_invite = 0 WHERE ship_id = ?;", array($whichteam, $playerinfo['ship_id']));
                Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);

                $resy = $db->Execute("UPDATE {$db->prefix}teams SET number_of_members = number_of_members + 1 WHERE id = ?;", array($whichteam));
                Tki\Db::logDbErrors($pdo_db, $resy, __LINE__, __FILE__);

                echo $langvars['l_team_welcome'] . " <strong>" . $team['team_name'] . "</strong>.<br><br>";
                Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::TEAM_JOIN, $team['team_name']);
                Tki\PlayerLog::writeLog($pdo_db, $team['creator'], \Tki\LogEnums::TEAM_NEWMEMBER, $team['team_name'] . "|" . $playerinfo['character_name']);
            }
            else
            {
                echo $langvars['l_team_noinviteto'] . "<br>";
            }
        }

        echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
        break;

    case 4: // Not implemented yet
        echo "Not implemented yet. Sorry! :)<br><br>";
        echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
        break;

    case 5: // Eject member
            // Check if Co-ordinator of team.
            // If not display "An error occured, You are not the leader of this Team." message.
            // Then show link back and break;

        if (Tki\Team::isTeamOwner($team, $playerinfo) === false)
        {
            $langvars['l_team_error'] = str_replace("[error]", "<strong><font color=red>An error occured</font></strong><br>", $langvars['l_team_error']);
            echo $langvars['l_team_error'];
            echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
            break;
        }
        else
        {
            $result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($who));
            Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);
            $whotoexpel = $result->fields;

            if ($confirmed === null)
            {
                echo $langvars['l_team_ejectsure'] . " " . $whotoexpel['character_name'] . "? <a href=\"teams.php?teamwhat=$teamwhat&confirmed=1&who=$who\">" . $langvars['l_yes'] . "</a> - <a href=\"teams.php\">" . $langvars['l_no'] . "</a><br>";
            }
            else
            {
                // Check whether the player we are ejecting might have already left in the meantime
                // should go here if ($whotoexpel[team] ==

                $resx = $db->Execute("UPDATE {$db->prefix}planets SET team = '0' WHERE owner = ?;", array($who));
                Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);

                $resy = $db->Execute("UPDATE {$db->prefix}ships SET team = '0' WHERE ship_id = ?;", array($who));
                Tki\Db::logDbErrors($pdo_db, $resy, __LINE__, __FILE__);

                // No more necessary due to COUNT(*) in previous SQL statement
                $db->Execute("UPDATE {$db->prefix}teams SET number_of_members = number_of_members - 1 WHERE id = ?;", array($whotoexpel['team']));

                Tki\PlayerLog::writeLog($pdo_db, $who, \Tki\LogEnums::TEAM_KICK, $team['team_name']);
                echo $whotoexpel['character_name'] . " " . $langvars['l_team_ejected'] . "<br>";
            }

            echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
        }
        break;

    case 6: // Create team
        if ($playerinfo['team'] != 0)
        {
            echo $langvars['l_team_leavefirst'] . "<br>";
            echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
            break;
        }

        if ($teamname === null)
        {
            echo "<form accept-charset='utf-8' action='teams.php' method='post'>\n";
            echo $langvars['l_team_entername'] . ": ";
            echo "<input type='hidden' name='teamwhat' value='{$teamwhat}'>\n";
            echo "<input type='text' name='teamname' size='40' maxlength='40'><br>\n";
            echo $langvars['l_team_enterdesc'] . ": ";
            echo "<input type='text' name='teamdesc' size='40' maxlength='254'><br>\n";
            echo "<input type='submit' value='" . $langvars['l_submit'] . "'><input type='reset' value='" . $langvars['l_reset'] . "'>\n";
            echo "</form>\n";
            echo "<br><br>\n";
        }
        else
        {
            $teamname = trim(htmlentities($teamname, ENT_HTML5, 'UTF-8'));
            $teamdesc = trim(htmlentities($teamdesc, ENT_HTML5, 'UTF-8'));

            if (!Tki\Team::validateTeam($pdo_db, $teamname, $teamdesc, $playerinfo['ship_id']))
            {
                echo "<span style='color:#f00;'>Team Creation Failed</span><br>Sorry you have either entered an invalid Team name or Team Description.<br>\n";
                echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
                break;
            }

            $number_of_members = 1;
            $sql = "INSERT INTO ::prefix::teams (id, creator, team_name, description, number_of_members) VALUES (:ship_id, :team_creator, :team_name, :description, :number_of_members)";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':team_creator', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':team_name', $teamname, \PDO::PARAM_STR);
            $stmt->bindParam(':description', $teamdesc, \PDO::PARAM_STR);
            $stmt->bindParam(':number_of_members', $number_of_members, \PDO::PARAM_INT);
            $result = $stmt->execute();
            $debug1 = Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            $teamZone = 'Y';
            $sql = "INSERT INTO ::prefix::zones (zone_id, zone_name, owner, team_zone) VALUES (:zone_id, :zone_name, :owner, :team_zone)";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':zone_id', null, \PDO::PARAM_NULL);
            $stmt->bindParam(':zone_name', "{$teamname}\'s Empire", \PDO::PARAM_STR);
            $stmt->bindParam(':owner', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':team_zone', $teamZone, \PDO::PARAM_INT);
            $result = $stmt->execute();
            $debug2 = Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            $sql = "UPDATE ::prefix::ships SET team=:team_id WHERE ship_id=:ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':team_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $result = $stmt->execute();
            $debug3 = Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            echo $langvars['l_team_team'] . " <strong>" . $teamname . "</strong> " . $langvars['l_team_hcreated'] . ".<br><br>";
            Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::TEAM_CREATE, $teamname);
        }

        echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
        break;

    case 7: // Invite a player to a team
        if (Tki\Team::isTeamMember($team, $playerinfo) === false)
        {
            echo "<br>You are not in this team!<br>";
            echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
            break;
        }

        if ($invited === null)
        {
            echo "<form accept-charset='utf-8' action='teams.php' method=post>";
            echo "<table><input type=hidden name=teamwhat value=$teamwhat><input type=hidden name=invited value=1><input type=hidden name=whichteam value=$whichteam>";
            echo "<tr><td>" . $langvars['l_team_selectp'] . ":</td><td><select name=who style='width:200px;'>";
            $res = $db->Execute("SELECT character_name, ship_id, team FROM {$db->prefix}ships WHERE team <> ? AND ship_destroyed ='N' AND turns_used > 0 ORDER BY character_name ASC;", array($whichteam));
            Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
            while (!$res->EOF)
            {
                $row = $res->fields;
                if (Tki\Team::isTeamOwner($team, $row) === false)
                {
                    echo "<option value='{$row['ship_id']}'>{$row['character_name']}";
                }

                $res->MoveNext();
            }

            echo "</select></td></tr>";
            echo "<tr><td><input type='submit' value='" . $langvars['l_submit'] . "'></td></tr>";
            echo "</table>";
            echo "</form>";
        }
        else
        {
            if ($playerinfo['team'] == $whichteam)
            {
                if ($who === null)
                {
                    echo "No player was selected.<br>\n";
                    echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . "<br><br>";
                    break;
                }

                $res = $db->Execute("SELECT character_name,team_invite FROM {$db->prefix}ships WHERE ship_id = ?;", array($who));
                Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
                $newpl = $res->fields;
                if ($newpl['team_invite'])
                {
                    $langvars['l_team_isorry'] = str_replace("[name]", $newpl['character_name'], $langvars['l_team_isorry']);
                    echo $langvars['l_team_isorry'] . "<br><br>";
                }
                else
                {
                    $resx = $db->Execute("UPDATE {$db->prefix}ships SET team_invite = ? WHERE ship_id = ?;", array($whichteam, $who));
                    Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                    echo $langvars['l_team_plinvted'] . "<br>" . $langvars['l_team_plinvted2'] . "<br>";
                    Tki\PlayerLog::writeLog($pdo_db, $who, \Tki\LogEnums::TEAM_INVITE, $team['team_name']);
                }
            }
            else
            {
                echo $langvars['l_team_notyours'] . "<br>";
            }
        }

        echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . "<br><br>";
        break;

    case 8: // Decline invitation to a team
        echo $langvars['l_team_refuse'] . " <strong>" . $invite_info['team_name'] . "</strong>.<br><br>";
        $resx = $db->Execute("UPDATE {$db->prefix}ships SET team_invite = 0 WHERE ship_id = ?;", array($playerinfo['ship_id']));
        Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
        Tki\PlayerLog::writeLog($pdo_db, $team['creator'], \Tki\LogEnums::TEAM_REJECT, $playerinfo['character_name'] . "|" . $invite_info['team_name']);
        echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
        break;

    case 9: // Edit Team
            // Check if Co-ordinator of team.
            // If not display "An error occured, You are not the leader of this Team." message.
            // Then show link back and break;

        if (Tki\Team::isTeamOwner($team, $playerinfo) === false)
        {
            $langvars['l_team_error'] = str_replace("[error]", "<strong><font color=red>An error occured</font></strong><br>", $langvars['l_team_error']);
            echo $langvars['l_team_error'];
            echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
            break;
        }

        if ($update === null)
        {
            echo "<form accept-charset='utf-8' action='teams.php' method='post'>";
            echo $langvars['l_team_edname'] . " . : <br>";
            echo "<input type='hidden' name='teamwhat' value='{$teamwhat}'>";
            echo "<input type='hidden' name='whichteam' value='{$whichteam}'>";
            echo "<input type='hidden' name='update' value='true'>";
            echo "<input type='text' name='teamname' size='40' maxlength='40' value='{$team['team_name']}'><br>";
            echo $langvars['l_team_eddesc'] . " . : <br>";
            echo "<input type='text' name='teamdesc' size='40' maxlength='254' value='{$team['description']}'><br>";
            echo "<input type='submit' value='" . $langvars['l_submit'] . "'><input type='reset' value='{" . $langvars['l_reset'] . "'>";
            echo "</form>";
            echo "<br><br>";
        }
        else
        {
            $teamname = trim(htmlentities($teamname, ENT_HTML5, 'UTF-8'));
            $teamdesc = trim(htmlentities($teamdesc, ENT_HTML5, 'UTF-8'));

            if (Tki\Team::validateTeam($pdo_db, $teamname, $teamdesc, $playerinfo['ship_id']) === false)
            {
                echo "<span style='color:#f00;'>Team Edit Failed</span><br>Sorry you have either entered an invalid Team name or Team Description.<br>\n";
                echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
                break;
            }

            $res = $db->Execute("UPDATE {$db->prefix}teams SET team_name = ?, description = ? WHERE id = ?;", array($teamname, $teamdesc, $whichteam));
            Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
            echo $langvars['l_team_team'] . " <strong>" . $teamname . "</strong> " . $langvars['l_team_hasbeenr'] . "<br><br>";

            // Adding a log entry to all members of the renamed team
            $result_team_name = $db->Execute("SELECT ship_id FROM {$db->prefix}ships WHERE team = ? AND ship_id <> ?;", array($whichteam, $playerinfo['ship_id']));
            Tki\Db::logDbErrors($pdo_db, $result_team_name, __LINE__, __FILE__);
            Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::TEAM_RENAME, $teamname);
            while (!$result_team_name->EOF)
            {
                $teamname_array = $result_team_name->fields;
                Tki\PlayerLog::writeLog($pdo_db, $teamname_array['ship_id'], \Tki\LogEnums::TEAM_M_RENAME, $teamname);
                $result_team_name->MoveNext();
            }
        }

        echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
        break;

    default:
        if ($playerinfo['team'] == 0)
        {
            echo $langvars['l_team_notmember'];
            Tki\Team::displayInviteInfo($langvars, $playerinfo, $invite_info);
        }
        else
        {
            if ($playerinfo['team'] < 0)
            {
                $playerinfo['team'] = -$playerinfo['team'];
                $result = $db->Execute("SELECT * FROM {$db->prefix}teams WHERE id = ?;", array($playerinfo['team']));
                Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);
                $whichteam = $result->fields;
                echo $langvars['l_team_urejected'] . " <strong>" . $whichteam['team_name'] . "</strong><br><br>";
                echo "<br><br><a href=\"teams.php\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_menu'] . ".<br><br>";
                break;
            }

            $sql = "SELECT * FROM ::prefix::teams WHERE id=:player_team";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':player_team', $playerinfo['team'], PDO::PARAM_INT);
            $stmt->execute();
            $whichteam = $stmt->fetch(PDO::FETCH_ASSOC);
            Tki\Db::logDbErrors($pdo_db, $stmt, __LINE__, __FILE__);

            if ($playerinfo['team_invite'])
            {
                $result = $db->Execute("SELECT * FROM {$db->prefix}teams WHERE id = ?;", array($playerinfo['team_invite']));
                Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);
                $whichinvitingteam = $result->fields;
            }

            $isowner = Tki\Team::isTeamOwner($whichteam, $playerinfo);
            Tki\Team::showInfo($pdo_db, $langvars, (int) $playerinfo['team'], $isowner, $playerinfo, $invite_info, $team, $tkireg);
        }

        $res = $db->Execute("SELECT COUNT(*) as total FROM {$db->prefix}teams WHERE admin='N'");
        Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
        $num_res = $res->fields;

        if ($num_res['total'] > 0)
        {
            Tki\Team::displayAllTeams($pdo_db, $langvars, $tkireg, $order, $type);
        }
        else
        {
            echo $langvars['l_team_noteams'] . "<br><br>";
        }
        break;
}

echo "<br><br>";
Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
