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
// File: admin/bans_editor.php

if (strpos($_SERVER['PHP_SELF'], 'bans_editor.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

echo "<strong>" . $langvars['l_admin_ban_editor'] . "</strong><p>";
if (empty($command))
{
    echo "<form accept-charset='utf-8' action=admin.php method=post>";
    echo "<input type='hidden' name=swordfish value=" . $_POST['swordfish'] . ">";
    echo "<input type='hidden' name=command value=showips>";
    echo "<input type='hidden' name=menu value='bans_editor.php'>";
    echo "<input type=submit value=\"" . $langvars['l_admin_show_ip'] . "\">";
    echo "</form>";

    $res = $db->Execute("SELECT ban_mask FROM {$db->prefix}ip_bans");
    Tki\Db::logDbErrors($pdo_db, $db, $res, __LINE__, __FILE__);
    while (!$res->EOF)
    {
        $bans[] = $res->fields['ban_mask'];
        $res->MoveNext();
    }

    if (empty ($bans))
    {
        echo "<strong>" . $langvars['l_admin_no_bans'] . "</strong>";
    }
    else
    {
        echo "<table border=1 cellspacing=1 cellpadding=2 width=100% align=center>" .
             "<tr bgcolor=$tkireg->color_line2><td align=center colspan=7><strong><font color=white>" .
             $langvars['l_admin_active_bans'] .
             "</font></strong>" .
             "</td></tr>" .
             "<tr align=center bgcolor=$tkireg->color_line2>" .
             "<td><font size=2 color=white><strong>" . $langvars['l_admin_ban_mask'] . "</strong></font></td>" .
             "<td><font size=2 color=white><strong>" . $langvars['l_admin_affected_players'] . "</strong></font></td>" .
             "<td><font size=2 color=white><strong>" . $langvars['l_admin_email'] . "</strong></font></td>" .
             "<td><font size=2 color=white><strong>" . $langvars['l_admin_operation'] . "</strong></font></td>" .
             "</tr>";

        $curcolor = $tkireg->color_line1;

        foreach ($bans as $ban)
        {
            echo "<tr bgcolor=" . $curcolor . ">";
            if ($curcolor == $tkireg->color_line1)
            {
                $curcolor = $tkireg->color_line2;
            }
            else
            {
                $curcolor = $tkireg->color_line1;
            }

            $printban = str_replace("%", "*", $ban);
            echo "<td align=center><font size=2 color=white>" . $printban . "</td>" .
                 "<td align=center><font size=2 color=white>";

            $res = $db->Execute("SELECT character_name, ship_id, email FROM {$db->prefix}ships WHERE ip_address LIKE ?;", array($ban));
            Tki\Db::logDbErrors($pdo_db, $db, $res, __LINE__, __FILE__);
            unset($players);
            while (!$res->EOF)
            {
                $players[] = $res->fields;
                $res->MoveNext();
            }

            if (empty($players))
            {
                echo $langvars['l_none'];
            }
            else
            {
                foreach ($players as $player)
                {
                    echo "<strong>" . $player['character_name'] . "</strong><br>";
                }
            }

            echo "<td align=center><font size=2 color=white>";

            if (empty($players))
            {
                echo $langvars['l_n_a'];
            }
            else
            {
                foreach ($players as $player)
                {
                    echo $player['email'] . "<br>";
                }
            }

            echo "<td align=center nowrap valign=center><font size=2 color=white>" .
                 "<form accept-charset='utf-8' action=admin.php method=post>" .
                 "<input type='hidden' name=swordfish value=" . $_POST['swordfish'] . ">" .
                 "<input type='hidden' name=command value=unbanip>" .
                 "<input type='hidden' name=menu value='bans_editor.php'>" .
                 "<input type='hidden' name=ban value=" . $ban . ">" .
                 "<input type=submit value=" . $langvars['l_admin_remove'] . ">" .
                 "</form>";
        }

        echo "</table><p>";
    }
}
elseif ($command == 'showips')
{
    $res = $db->Execute("SELECT DISTINCT ip_address FROM {$db->prefix}ships");
    Tki\Db::logDbErrors($pdo_db, $db, $res, __LINE__, __FILE__);
    while (!$res->EOF)
    {
        $ips[] = $res->fields['ip_address'];
        $res->MoveNext();
    }

    echo "<table border=1 cellspacing=1 cellpadding=2 width=100% align=center>" .
         "<tr bgcolor=" . $tkireg->color_line2 . "><td align=center colspan=7><strong><font color=white>" .
         $langvars['l_admin_players_sorted'] .
         "</font></strong>" .
         "</td></tr>" .
         "<tr align=center bgcolor=" . $tkireg->color_line2 . ">" .
         "<td><font size=2 color=white><strong>" . $langvars['l_admin_ip_address'] . "</strong></font></td>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_admin_players'] . "</strong></font></td>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_admin_email'] . "</strong></font></td>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_admin_operations'] . "</strong></font></td>" .
         "</tr>";

    $curcolor = $tkireg->color_line1;

    foreach ($ips as $ip)
    {
        echo "<tr bgcolor=$curcolor>";
        if ($curcolor == $tkireg->color_line1)
        {
            $curcolor = $tkireg->color_line2;
        }
        else
        {
            $curcolor = $tkireg->color_line1;
        }

        echo "<td align=center><font size=2 color=white>" . $ip . "</td>" .
             "<td align=center><font size=2 color=white>";

        $res = $db->Execute("SELECT character_name, ship_id, email FROM {$db->prefix}ships WHERE ip_address=?;", array($ip));
        Tki\Db::logDbErrors($pdo_db, $db, $res, __LINE__, __FILE__);
        unset($players);
        while (!$res->EOF)
        {
            $players[] = $res->fields;
            $res->MoveNext();
        }

        foreach ($players as $player)
        {
            echo "<strong>" . $player['character_name'] . "</strong><br>";
        }

        echo "<td align=center><font size=2 color=white>";

        foreach ($players as $player)
        {
            echo $player['email'] . "<br>";
        }

        echo "<td align=center nowrap valign=center><font size=2 color=white>" .
             "<form accept-charset='utf-8' action=admin.php method=post>" .
             "<input type='hidden' name=swordfish value=" . $_POST['swordfish'] . ">" .
             "<input type='hidden' name=command value=banip>" .
             "<input type='hidden' name=menu value='bans_editor.php'>" .
             "<input type='hidden' name=ip value=" . $ip . ">" .
             "<input type=submit value=" . $langvars['l_admin_ban'] . ">" .
             "</form>" .
             "<form accept-charset='utf-8' action=admin.php method=post>" .
             "<input type='hidden' name=swordfish value=" . $_POST['swordfish'] . ">" .
             "<input type='hidden' name=command value=unbanip>" .
             "<input type='hidden' name=menu value='bans_editor.php'>" .
             "<input type='hidden' name=ip value=" . $ip . ">" .
             "<input type=submit value=" . $langvars['l_admin_unban'] . ">" .
             "</form>";
    }

    echo "</table><p>" .
         "<form accept-charset='utf-8' action=admin.php method=post>" .
         "<input type='hidden' name=swordfish value=" . $_POST['swordfish'] . ">" .
         "<input type='hidden' name=menu value='bans_editor.php'>" .
         "<input type=submit value=\"" . $langvars['l_admin_return_bans_menu'] . "\">" .
         "</form>";
}
elseif ($command == 'banip')
{
    $ip = $_POST['ip'];
    echo "<strong>Banning ip : " . $ip . "<p>";
    echo "<font size=2 color=white>" . $langvars['l_admin_select_ban_type'] . "<p>";

    $ipparts = explode(".", $ip);

    echo "<table border=0>" .
         "<tr><td align=right>" .
         "<form accept-charset='utf-8' action=admin.php method=post>" .
         "<input type='hidden' name=swordfish value=" . $_POST['swordfish'] . ">" .
         "<input type='hidden' name=menu value='bans_editor.php'>" .
         "<input type='hidden' name=command value=banip2>" .
         "<input type='hidden' name=ip value=" . $ip . ">" .
         "<input type=radio name=class value=" . $langvars['l_admin_i_checked'] . ">" .
         "<td><font size=2 color=white>" . $langvars['l_admin_ip_only'] . ": " . $ip . "</td>" .
         "<tr><td>" .
         "<input type=radio name=class value=A>" .
         "<td><font size=2 color=white>" . $langvars['l_admin_class_a'] . ": " . $ipparts[0].$ipparts[1].$ipparts[2] . ".*</td>" .
         "<tr><td>" .
         "<input type=radio name=class value=B>" .
         "<td><font size=2 color=white>" . $langvars['l_admin_class_b'] . ": " . $ipparts[0].$ipparts[1] . ".*</td>" .
         "<tr><td><td><br><input type=submit value=" . $langvars['l_admin_ban'] . ">" .
         "</table>" .
         "</form>";

    echo "<form accept-charset='utf-8' action=admin.php method=post>" .
         "<input type='hidden' name=swordfish value=" . $_POST['swordfish'] . ">" .
         "<input type='hidden' name=menu value='bans_editor.php'>" .
         "<input type=submit value=\"" . $langvars['l_admin_return_bans_menu'] . "\">" .
         "</form>";
}
elseif ($command == 'banip2')
{
    $ip = $_POST['ip'];
    $ipparts = explode(".", $ip);

    if ($class == 'A')
    {
        $banmask = "$ipparts[0].$ipparts[1].$ipparts[2].%";
    }
    elseif ($class == 'B')
    {
        $banmask = "$ipparts[0].$ipparts[1].%";
    }
    else
    {
        $banmask = $ip;
    }

    $printban = str_replace("%", "*", $banmask);
    echo "<font size=2 color=white><strong>" . $langvars['l_admin_ban_success'] . " " . $printban . "</strong>.<p>";

    $resx = $db->Execute("INSERT INTO {$db->prefix}ip_bans values (NULL, ?);", array($banmask));
    Tki\Db::logDbErrors($pdo_db, $db, $resx, __LINE__, __FILE__);
    $res = $db->Execute("SELECT DISTINCT character_name FROM {$db->prefix}ships, {$db->prefix}ip_bans WHERE ip_address LIKE ban_mask");
    Tki\Db::logDbErrors($pdo_db, $db, $res, __LINE__, __FILE__);
    echo $langvars['l_admin_affected_players'] . " :<p>";
    while (!$res->EOF)
    {
        echo " - " . $res->fields['character_name'] . "<br>";
        $res->MoveNext();
    }

    echo "<form accept-charset='utf-8' action=admin.php method=post>" .
         "<input type='hidden' name=swordfish value=" . $_POST['swordfish'] . ">" .
         "<input type='hidden' name=menu value='bans_editor.php'>" .
         "<input type=submit value=\"" . $langvars['l_admin_return_bans_menu'] . "\">" .
         "</form>";
}
elseif ($command == 'unbanip')
{
    $ip = $_POST['ip'];
    if ($ban !== null)
    {
        $res = $db->Execute("SELECT * FROM {$db->prefix}ip_bans WHERE ban_mask=?;", array($ban));
        Tki\Db::logDbErrors($pdo_db, $db, $res, __LINE__, __FILE__);
    }
    else
    {
        $res = $db->Execute("SELECT * FROM {$db->prefix}ip_bans WHERE ? LIKE ban_mask;", array($ip));
        Tki\Db::logDbErrors($pdo_db, $db, $res, __LINE__, __FILE__);
    }

    $nbbans = $res->RecordCount();
    while (!$res->EOF)
    {
        $res->fields['print_mask'] = str_replace("%", "*", $res->fields['ban_mask']);
        $bans[] = $res->fields;
        $res->MoveNext();
    }

    if ($ban !== null)
    {
        $resx = $db->Execute("DELETE FROM {$db->prefix}ip_bans WHERE ban_mask=?;", array($ban));
        Tki\Db::logDbErrors($pdo_db, $db, $resx, __LINE__, __FILE__);
    }
    else
    {
        $resx = $db->Execute("DELETE FROM {$db->prefix}ip_bans WHERE ? LIKE ban_mask;", array($ip));
        Tki\Db::logDbErrors($pdo_db, $db, $resx, __LINE__, __FILE__);
    }

    $query_string = "ip_address LIKE '" . $bans[0]['ban_mask'] ."'";
    for ($i = 1; $i < $nbbans; $i++)
    {
        $query_string = $query_string . " OR ip_address LIKE '" . $bans[$i]['ban_mask'] . "'";
    }

    $res = $db->Execute("SELECT DISTINCT character_name FROM {$db->prefix}ships WHERE ?;", array($query_string));
    Tki\Db::logDbErrors($pdo_db, $db, $res, __LINE__, __FILE__);
    $nbplayers = $res->RecordCount();
    while (!$res->EOF)
    {
        $players[] = $res->fields['character_name'];
        $res->MoveNext();
    }

    echo "<font size=2 color=white><strong>Successfully removed " . $nbbans . "bans</strong> :<p>";

    foreach ($bans as $ban)
    {
        echo " - " . $ban['print_mask'] . "<br>";
    }

    echo "<p><strong>" . $langvars['l_admin_affected_players'] . " :</strong><p>";
    if (empty ($players))
    {
        echo " - " . $langvars['l_none'] . "<br>";
    }
    else
    {
        foreach ($players as $player)
        {
            echo " - " . $player . "<br>";
        }
    }

    echo "<form accept-charset='utf-8' action=admin.php method=post>" .
         "<input type='hidden' name=swordfish value=" . $_POST['swordfish'] . ">" .
         "<input type='hidden' name=menu value='bans_editor.php'>" .
         "<input type=submit value=\"" . $langvars['l_admin_return_bans_menu'] . "\">" .
         "</form>";
}
