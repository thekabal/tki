<?php declare(strict_types = 1);
/**
 * classes/Team.php from The Kabal Invasion.
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

namespace Tki;

class Team
{
    public static function isSameTeam(int $attacker_team = 0, int $attackie_team = 0): bool
    {
        return !(($attacker_team != $attackie_team) || ($attacker_team === 0 || $attackie_team === 0));
    }

    public static function isTeamMember(array $team, array $playerinfo): bool
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

    public static function isTeamOwner(array $team, array $playerinfo): bool
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

    public static function validateTeam(\PDO $pdo_db, string $name, string $desc, int $creator = null): bool
    {
        $name = trim($name);
        $desc = trim($desc);

        if (empty($name) || empty($desc) || empty($creator))
        {
            return false;
        }

        $res_new = preg_match('/[^A-Za-z0-9\_\s\-\.\']+/', $name, $matches);
        if ($res_new != 0)
        {
            return false;
        }

        $res_new2 = preg_match('/[^A-Za-z0-9\_\s\-\.\']+/', $desc, $matches);
        if ($res_new2 != 0)
        {
            return false;
        }

        // Just a test to see if an team with a name of $name exists.
        // This is just a temp fix until we find a better one.
        $sql = "SELECT COUNT(*) as found FROM ::prefix::teams WHERE team_name=:team_name AND creator <>:creator";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':team_name', $name, \PDO::PARAM_STR);
        $stmt->bindParam(':creator', $creator, \PDO::PARAM_INT);
        $stmt->execute();
        $num_res = $stmt->fetch(\PDO::FETCH_ASSOC);

        $returnvalue = (!($num_res['found'] > 0));
        return $returnvalue;
    }

    // Display list of teams
    public static function displayAllTeams(\PDO $pdo_db, array $langvars, Reg $tkireg, ?string $order, string $type): void
    {
        echo "<br><br>" . $langvars['l_team_galax'] . "<br>";
        echo "<table style='width:100%; border:#fff 1px solid;' border='0' cellspacing='0' cellpadding='2'>";
        echo "<tr bgcolor=\"$tkireg->color_header\">";

        if ($type == "d")
        {
            $type = "a";
            $sort_by = "ASC";
        }
        else
        {
            $type = "d";
            $sort_by = "DESC";
        }

        echo "<td><strong><a class='new_link' style='font-size:14px;' href=teams.php?order=team_name&type=$type>" . $langvars['l_name'] . "</a></strong></td>";
        echo "<td><strong><a class='new_link' style='font-size:14px;' href=teams.php?order=number_of_members&type=$type>" . $langvars['l_team_members'] . "</a></strong></td>";
        echo "<td><strong><a class='new_link' style='font-size:14px;' href=teams.php?order=character_name&type=$type>" . $langvars['l_team_coord'] . "</a></strong></td>";
        echo "<td><strong><a class='new_link' style='font-size:14px;' href=teams.php?order=total_score&type=$type>" . $langvars['l_score'] . "</a></strong></td>";
        echo "</tr>";

        $sql = "SELECT ::prefix::ships.character_name, " .
               "COUNT(*) as number_of_members, " .
               "ROUND(SQRT(SUM(POW(::prefix::ships.score, 2)))) as total_score, " .
               "::prefix::teams.id, ::prefix::teams.team_name, ::prefix::teams.creator " .
               "FROM ::prefix::ships " .
               "LEFT JOIN ::prefix::teams ON ::prefix::ships.team = ::prefix::teams.id " .
               "WHERE ::prefix::ships.team = ::prefix::teams.id " .
               "AND admin = 'N' GROUP BY ::prefix::teams.team_name";

        if ($order === null)
        {
            $stmt = $pdo_db->prepare($sql);
            $stmt->execute();
            $somethingteam = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            \Tki\Db::logDbErrors($pdo_db, $stmt, __LINE__, __FILE__);
        }
        else
        {
            // This is not currently working - not sure why the SQL fails
            // $sql = $sql . " ORDER BY :order :sort_by";
            $stmt = $pdo_db->prepare($sql);
            // $stmt->bindParam(':order', $order, \PDO::PARAM_STR);
            // $stmt->bindParam(':sort_by', $sort_by, \PDO::PARAM_STR);
            $stmt->execute();
            $somethingteam = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            \Tki\Db::logDbErrors($pdo_db, $stmt, __LINE__, __FILE__);
        }

        $color = $tkireg->color_line1;
        if ($somethingteam !== false)
        {
            foreach ($somethingteam as $row)
            {
                echo "<tr bgcolor=\"$color\">";
                echo "<td><a href='teams.php?teamwhat=1&whichteam={$row['id']}'>{$row['team_name']}</a></td>";
                echo "<td>{$row['number_of_members']}</td>";
                echo "<td><a href='mailto.php?name={$row['character_name']}'>{$row['character_name']}</a></td>";
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
            }
        }

        echo "</table><br>";
    }

    public static function displayInviteInfo(array $langvars, array $playerinfo, array $invite_info): void
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

    public static function showInfo(\PDO $pdo_db, array $langvars, int $whichteam, bool $isowner, array $playerinfo, array $invite_info, array $team, Reg $tkireg): void
    {
        // Heading
        echo "<div align=center><h3><font color=white><strong>$team[team_name]</strong>";
        echo "<br><font size=2>\"<i>$team[description]</i>\"</font></h3>";
        if ($playerinfo['team'] == $team['id'])
        {
            echo "<font color=white>";
            $tmp_output = $langvars['l_team_member'] . " ";
            if ($playerinfo['ship_id'] == $team['creator'])
            {
                $tmp_output = $langvars['l_team_coord'] . " ";
            }

            echo $tmp_output;

            echo $langvars['l_options'] . " <br><font size=2>";
            if (self::isTeamOwner($team, $playerinfo) === true)
            {
                echo "[<a href=teams.php?teamwhat=9&whichteam=$playerinfo[team]>" . $langvars['l_edit'] . "</a>] - ";
            }

            echo "[<a href=teams.php?teamwhat=7&whichteam=$playerinfo[team]>" . $langvars['l_team_inv'] . "</a>] - [<a href=teams.php?teamwhat=2&whichteam=$playerinfo[team]>" . $langvars['l_team_leave'] . "</a>]</font></font>";
        }

        self::displayInviteInfo($langvars, $playerinfo, $invite_info);
        echo "</div>";

        // Main table
        echo "<table border=2 cellspacing=2 cellpadding=2 bgcolor=\"#400040\" width=\"75%\" align=center><tr>";
        echo "<td><font color=white>" . $langvars['l_team_members'] . "</font></td></tr><tr bgcolor=$tkireg->color_line2>";

        $sql = "SELECT * FROM ::prefix::ships " .
               "WHERE ::prefix::ships.team = :whichteam ";

        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':whichteam', $whichteam, \PDO::PARAM_INT);
        $stmt->execute();
        $somethingteam = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        \Tki\Db::logDbErrors($pdo_db, $stmt, __LINE__, __FILE__);

        if ($somethingteam !== false)
        {
            foreach ($somethingteam as $member)
            {
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
            }
        }

        $sql2 = "SELECT ship_id, character_name FROM ::prefix::ships " .
               "WHERE team_invite = :whichteam ";

        $stmt2 = $pdo_db->prepare($sql2);
        $stmt2->bindParam(':whichteam', $whichteam, \PDO::PARAM_INT);
        $stmt2->execute();
        $somethingteam2 = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
        \Tki\Db::logDbErrors($pdo_db, $stmt2, __LINE__, __FILE__);

        echo "<td bgcolor=$tkireg->color_line2><font color=white>" . $langvars['l_team_pending'] . " <strong>" . $team['team_name'] . "</strong></font></td>";
        echo "</tr><tr>";

        if ($somethingteam2 !== false)
        {
            foreach ($somethingteam2 as $who)
            {
                echo "</tr><tr bgcolor=$tkireg->color_line2>";
                echo "<td> - $who[character_name]</td>";
                echo "</tr><tr bgcolor=$tkireg->color_line2>";
            }
        }
        else
        {
            echo "<td>" . $langvars['l_team_noinvites'] . " <strong>" . $team['team_name'] . "</strong>.</td></tr><tr>";
        }

        echo "</tr></table>";
    }
}
