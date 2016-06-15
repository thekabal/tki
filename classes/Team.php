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
// File: classes/Team.php

namespace Tki;

class Team
{
    public static function sameTeam($attacker_team = null, $attackie_team = null)
    {
        if (($attacker_team != $attackie_team) || ($attacker_team == 0 || $attackie_team == 0))
        {
            return (boolean) false;
        }
        else
        {
            return (boolean) true;
        }
    }

    public static function isTeamMember($team, $playerinfo)
    {
        // Check to see if the player is in a team?  if not return false right there, else carry on.
        if ($playerinfo['team'] == 0)
        {
            return false;
        }

        // Check to see if the player is a member of $team['id'] if so return true, else return false.
        $returnvalue = ($playerinfo['team'] == $team['id']);
        return $returnvalue;
    }

    public static function isTeamOwner($team, $playerinfo)
    {
        // Check to see if the player is in a team?  if not return false right there, else carry on.
        if ($playerinfo['team'] == 0)
        {
            return false;
        }

        // Check to see if the player is the Owner of $team['creator'] if so return true, else return false.
        $returnvalue = ($playerinfo['ship_id'] == $team['creator']);
        return $returnvalue;
    }

    public static function validateTeam(\PDO $pdo_db, $db, $name = null, $desc = null, $creator = null)
    {
        $name = trim($name);
        $desc = trim($desc);
        $creator = (int) $creator;

        if ((is_null($name) || empty ($name)) || (is_null($desc) || empty ($desc)) || (is_null($creator) || empty ($creator)))
        {
            return false;
        }

        if (($res = preg_match('/[^A-Za-z0-9\_\s\-\.\']+/', $name, $matches)) != 0)
        {
            return false;
        }

        if (($res = preg_match('/[^A-Za-z0-9\_\s\-\.\']+/', $desc, $matches)) != 0)
        {
            return false;
        }

        // Just a test to see if an team with a name of $name exists.
        // This is just a temp fix until we find a better one.
        $res = $db->Execute("SELECT COUNT(*) as found FROM {$db->prefix}teams WHERE team_name = ? AND creator != ?;", array($name, $creator));
        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
        $num_res = $res->fields;

        $returnvalue = (!($num_res['found'] > 0));
        return $returnvalue;
    }

    // Rewritten display of teams list
    public static function displayAllTeams(\PDO $pdo_db, $db, $langvars, Reg $tkireg, $order, $type)
    {
        echo "<br><br>" . $langvars['l_team_galax'] . "<br>";
        echo "<table style='width:100%; border:#fff 1px solid;' border='0' cellspacing='0' cellpadding='2'>";
        echo "<tr bgcolor=\"$tkireg->color_header\">";

        if ($type == "d")
        {
            $type = "a";
            $by = "ASC";
        }
        else
        {
            $type = "d";
            $by = "DESC";
        }
        echo "<td><strong><a class='new_link' style='font-size:14px;' href=teams.php?order=team_name&type=$type>" . $langvars['l_name'] . "</a></strong></td>";
        echo "<td><strong><a class='new_link' style='font-size:14px;' href=teams.php?order=number_of_members&type=$type>" . $langvars['l_team_members'] . "</a></strong></td>";
        echo "<td><strong><a class='new_link' style='font-size:14px;' href=teams.php?order=character_name&type=$type>" . $langvars['l_team_coord'] . "</a></strong></td>";
        echo "<td><strong><a class='new_link' style='font-size:14px;' href=teams.php?order=total_score&type=$type>" . $langvars['l_score'] . "</a></strong></td>";
        echo "</tr>";
        $sql_query = "SELECT {$db->prefix}ships.character_name,
                    COUNT(*) as number_of_members,
                    ROUND (SQRT (SUM(POW ({$db->prefix}ships.score, 2)))) as total_score,
                    {$db->prefix}teams.id,
                    {$db->prefix}teams.team_name,
                    {$db->prefix}teams.creator
                    FROM {$db->prefix}ships
                    LEFT JOIN {$db->prefix}teams ON {$db->prefix}ships.team = {$db->prefix}teams.id
                    WHERE {$db->prefix}ships.team = {$db->prefix}teams.id AND admin = 'N'
                    GROUP BY {$db->prefix}teams.team_name";

        // Setting if the order is Ascending or descending, if any.
        // Default is ordered by teams.team_name
        if ($order)
        {
            $sql_query .= " ORDER BY ? ?";
        }
        $sql_query .= ";";

        $res = $db->Execute($sql_query, array($order, $by));
        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
        $color = $tkireg->color_line1;

        while (!$res->EOF)
        {
            $row = $res->fields;
            echo "<tr bgcolor=\"$color\">";
            echo "<td><a href='teams.php?teamwhat=1&whichteam={$row['id']}'>{$row['team_name']}</a></td>";
            echo "<td>{$row['number_of_members']}</td>";

            // This fixes it so that it actually displays the coordinator, and not the first member of the team.
            $res2 = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE ship_id = ?;", array($row['creator']));
            \Tki\Db::LogDbErrors($pdo_db, $res2, __LINE__, __FILE__);
            while (!$res2->EOF)
            {
                $row2 = $res2->fields;
                $res2->MoveNext();
            }

            // If there is a way to redo the original sql query instead, please, do so, but I didnt see a way to.
            echo "<td><a href='mailto.php?name={$row2['character_name']}'>{$row2['character_name']}</a></td>";
            echo "<td>{$row['total_score']}</td>";
            echo "</tr>";
            if ($color == $tkireg->color_line1)
            {
                $color = $tkireg->color_line2;
            }
            else
            {
                $color = $tkireg->color_line1;
            }

            $res->MoveNext();
        }
        echo "</table><br>";
    }

    public static function displayInviteInfo($langvars, $playerinfo, $invite_info)
    {
        if (!$playerinfo['team_invite'])
        {
            echo "<br><br><font color=blue size=2><strong>" . $langvars['l_team_noinvite'] . "</strong></font><br>";
            echo $langvars['l_team_ifyouwant'] . "<br>";
            echo "<a href=\"teams.php?teamwhat=6\">" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_tocreate'] . "<br><br>";
        }
        else
        {
            echo "<br><br><font color=blue size=2><strong>" . $langvars['l_team_injoin'] . " ";
            echo "<a href=teams.php?teamwhat=1&whichteam=$playerinfo[team_invite]>$invite_info[team_name]</a>.</strong></font><br>";
            echo "<a href=teams.php?teamwhat=3&whichteam=$playerinfo[team_invite]>" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_tojoin'] . " <strong>" . $invite_info['team_name'] . "</strong> " . $langvars['l_team_or'] . " <a href=teams.php?teamwhat=8&whichteam=$playerinfo[team_invite]>" . $langvars['l_clickme'] . "</a> " . $langvars['l_team_reject'] . "<br><br>";
        }
    }

    public static function showInfo(\PDO $pdo_db, $db, $langvars, $whichteam, $isowner, $playerinfo, $invite_info, $team, Reg $tkireg)
    {
        // Heading
        echo "<div align=center>";
        echo "<h3><font color=white><strong>$team[team_name]</strong>";
        echo "<br><font size=2>\"<i>$team[description]</i>\"</font></h3>";
        if ($playerinfo['team'] == $team['id'])
        {
            echo "<font color=white>";
            if ($playerinfo['ship_id'] == $team['creator'])
            {
                echo $langvars['l_team_coord'] . " ";
            }
            else
            {
                echo $langvars['l_team_member'] . " ";
            }
            echo $langvars['l_options'] . " <br><font size=2>";
            if (is_team_owner($team, $playerinfo) === true)
            {
                echo "[<a href=teams.php?teamwhat=9&whichteam=$playerinfo[team]>" . $langvars['l_edit'] . "</a>] - ";
            }
            echo "[<a href=teams.php?teamwhat=7&whichteam=$playerinfo[team]>" . $langvars['l_team_inv'] . "</a>] - [<a href=teams.php?teamwhat=2&whichteam=$playerinfo[team]>" . $langvars['l_team_leave'] . "</a>]</font></font>";
        }
        self::displayInviteInfo($langvars, $playerinfo, $invite_info);
        echo "</div>";

        // Main table
        echo "<table border=2 cellspacing=2 cellpadding=2 bgcolor=\"#400040\" width=\"75%\" align=center>";
        echo "<tr>";
        echo "<td><font color=white>" . $langvars['l_team_members'] . "</font></td>";
        echo "</tr><tr bgcolor=$tkireg->color_line2>";
        $result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE team = ?;", array($whichteam));
        \Tki\Db::LogDbErrors($pdo_db, $result, __LINE__, __FILE__);
        while (!$result->EOF)
        {
            $member = $result->fields;
            echo "<td> - " . $member['character_name'] . " (" . $langvars['l_score'] . " " . $member['score'] . ")";
            if ($isowner && ($member['ship_id'] != $playerinfo['ship_id']))
            {
                echo " - <font size=2>[<a href=\"teams.php?teamwhat=5&who=$member[ship_id]\">" . $langvars['l_team_eject'] . "</a>]</font></td>";
            }
            else
            {
                if ($member['ship_id'] == $team['creator'])
                {
                    echo " - " . $langvars['l_team_coord'] . " </td>";
                }
            }
            echo "</tr><tr bgcolor=$tkireg->color_line2>";
            $result->MoveNext();
        }

        // Displays for members name
        $res = $db->Execute("SELECT ship_id, character_name FROM {$db->prefix}ships WHERE team_invite = ?;", array($whichteam));
        \Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
        echo "<td bgcolor=$tkireg->color_line2><font color=white>" . $langvars['l_team_pending'] . " <strong>" . $team['team_name'] . "</strong></font></td>";
        echo "</tr><tr>";
        if ($res->RecordCount() > 0)
        {
            echo "</tr><tr bgcolor=$tkireg->color_line2>";
            while (!$res->EOF)
            {
                $who = $res->fields;
                echo "<td> - $who[character_name]</td>";
                echo "</tr><tr bgcolor=$tkireg->color_line2>";
                $res->MoveNext();
            }
        }
        else
        {
            echo "<td>" . $langvars['l_team_noinvites'] . " <strong>" . $team['team_name'] . "</strong>.</td>";
            echo "</tr><tr>";
        }
        echo "</tr></table>";
    }
}
