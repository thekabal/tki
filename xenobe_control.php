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
// File: xenobe_control.php
// FUTURE: Change the table creation for Xenobes to use the new XML schema files

require_once './common.php';
require_once './config/admin_config.php';

$title = $langvars['l_ai_control'];
Tki\Header::display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('xenobe_control', 'common', 'global_includes', 'global_funcs', 'footer', 'news'));
echo "<h1>" . $title . "</h1>\n";

function checked($yesno)
{
    return (($yesno == "Y") ? "CHECKED" : "");
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$menu = null;
$menu = filter_input(INPUT_POST, 'menu', FILTER_SANITIZE_EMAIL);
if (mb_strlen(trim($menu)) === 0)
{
    $menu = false;
}

if ($menu !== null && $menu !== false)
{
    $module = $menu;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$swordfish = null;
$swordfish = filter_input(INPUT_POST, 'swordfish', FILTER_SANITIZE_EMAIL);
if (mb_strlen(trim($swordfish)) === 0)
{
    $swordfish = false;
}

if ($swordfish != ADMIN_PW)
{
    echo "<form accept-charset='utf-8' action=xenobe_control.php method=post>";
    echo "password: <input type=password name=swordfish size=20><br><br>";
    echo "<input type=submit value=submit><input type=reset value=reset>";
    echo "</form>";
}
else
{
    if (empty($module)) // Main menu
    {
        echo "Welcome to the Xenobe Control module for The Kabal Invasion<br><br>";
        echo "Select a function from the list below:<br>";
        echo "<form accept-charset='utf-8' action=xenobe_control.php method=post>";
        echo "<select name=menu>";
        echo "<option value=instruct>Xenobe Instructions</option>";
        echo "<option value=xenobeedit selected>Xenobe Character Editor</option>";
        echo "<option value=createnew>Create A New Xenobe Character</option>";
        echo "<option value=clearlog>Clear All Xenobe Log Files</option>";
        echo "<option value=dropxenobe>Drop and Re-Install Xenobe Database</option>";
        echo "</select>";
        echo "<input type=hidden name=swordfish value=$swordfish>";
        echo "&nbsp;<input type=submit value=submit>";
        echo "</form>";
    }
    else
    {
        $button_main = true;
        // Start of instructions sub
        if ($module == "instruct")
        {
            echo "<h2>Xenobe Instructions</h2>";
            echo "<p>&nbsp;&nbsp;&nbsp; Welcome to the Xenobe Control module.  This is the module that will control the Xenobe players in the game. ";
            echo "It is very simple right now, but will be expanded in future versions. ";
            echo "The ultimate goal of the Xenobe players is to create some interactivity for those games without a large user base. ";
            echo "I need not say that the Xenobe will also make good cannon fodder for those games with a large user base. ";

            echo "<h3>Xenobe Creation</h3>";
            echo "<p>&nbsp;&nbsp;&nbsp; In order to create a Xenobe you must choose the <strong>\"Create A Xenobe Character\"</strong> option from the menu. ";
            echo "This will bring up the Xenobe character creation screen.  There are only a few fields for you to edit. ";
            echo "However, with these fields you will determine not only how your Xenobe will be created, but how he will act in the game. ";
            echo "We will now go over these fields and what they will do. ";

            echo "<p>&nbsp;&nbsp;&nbsp; When creating a new Xenobe character the <strong>Xenobe Name</strong> and the <strong>Shipname</strong> are automatically generated. ";
            echo "You can change these default values by editing these fields before submitting the character for creation. ";
            echo "Take care not to duplicate a current player or ship name, for that will result in creation failure. ";
            echo "<br>&nbsp;&nbsp;&nbsp; The starting <strong>Sector</strong> number will also be randomly generated. ";
            echo "You can change this to any sector.  However, you should take care to use a valid sector number. Otherwise the creation will fail.";
            echo "<br>&nbsp;&nbsp;&nbsp; The <strong>Level</strong> field will default to '3'.  This field refers to the starting tech level of all ship stats. ";
            echo "So a default Xenobe will have it's Hull, Beams, Power, Engine, etc... all set to 3 unless this value is changed. ";
            echo "All appropriate ship stores will be set to the maximum allowed by the given tech level. ";
            echo "So, starting levels of energy, fighters, armor, torps, etc... are all affected by this setting. ";
            echo "<br>&nbsp;&nbsp;&nbsp; The <strong>Active</strong> checkbox will default to checked. ";
            echo "This box refers to if the Xenobe AI system will see this Xenobe and execute it's orders. ";
            echo "If this box is not checked then the Xenobe AI system will ignore this record and the next two fields are ignored. ";
            echo "<br>&nbsp;&nbsp;&nbsp; The <strong>Orders</strong> selection box will default to 'SENTINEL'. ";
            echo "There are three other options available: ROAM, ROAM AND trADE, and ROAM AND HUNT. ";
            echo "These Orders and what they mean will be detailed below. ";
            echo "<br>&nbsp;&nbsp;&nbsp; The <strong>Aggression</strong> selection box will default to 'PEACEFUL'. ";
            echo "There are two other options available: ATTACK SOMETIMES, and ATTACK ALWAYS. ";
            echo "These Aggression settings and what they mean will be detailed below. ";
            echo "<br>&nbsp;&nbsp;&nbsp; Pressing the <strong>Create</strong> button will create the Xenobe and return to the creation screen to create another. ";

            echo "<h3>Xenobe Orders</h3>";
            echo "<p> Here are the Xenobe Order options and what the Xenobe AI system will do for each: ";
            echo "<ul>SENTINEL<br> ";
            echo "This Xenobe will stay in place.  His only interactions will be with those who are in his sector at the time he takes his turn. ";
            echo "The aggression level will determine what those player interactions are.</ul> ";
            echo "<ul>ROAM<br> ";
            echo "This Xenobe will warp from sector to sector looking for players to interact with. ";
            echo "The aggression level will determine what those player interactions are.</ul> ";
            echo "<ul>ROAM AND trADE<br> ";
            echo "This Xenobe will warp from sector to sector looking for players to interact with and ports to trade with. ";
            echo "The Xenobe will trade at a port if possible before looking for player interactions. ";
            echo "The aggression level will determine what those player interactions are.</ul> ";
            echo "<ul>ROAM AND HUNT<br> ";
            echo "This Xenobe has a taste for blood and likes the sport of a good hunt. ";
            echo "Ocassionally (around 1/4th the time) this Xenobe has the urge to go hunting.  He will randomly choose one of the top ten players to hunt. ";
            echo "If that player is in a sector that allows attack, then the Xenobe warps there and attacks. ";
            echo "When he is not out hunting this Xenobe acts just like one with ROAM orders.</ul> ";

            echo "<h3>Xenobe Aggression</h3>";
            echo "<p> Here are the Xenobe Aggression levels and what the Xenobe AI system will do for each: ";
            echo "<ul>PEACEFUL<br> ";
            echo "This Xenobe will not attack players.  He will continue to roam or trade as ordered but will not launch any attacks. ";
            echo "If this Xenobe is a hunter then he will still attack players on the hunt but never otherwise.</ul> ";
            echo "<ul>ATTACK SOMETIMES<br> ";
            echo "This Xenobe will compare it's current number of fighters to a players fighters before deciding to attack. ";
            echo "If the Xenobe's fighters are greater then the player's, then the Xenobe will attack the player.</ul> ";
            echo "<ul>ATTACK ALWAYS<br> ";
            echo "This Xenobe is just mean.  He will attack anyone he comes across regardless of the odds.</ul> ";
        }
        elseif ($module == "xenobeedit")
        {
            echo "<span style=\"font-family : courier, monospace; font-size: 12pt; color: #0f0 \">Xenobe Editor</span><br>";
            echo "<form accept-charset='utf-8' action=xenobe_control.php method=post>";
            if (empty ($user))
            {
                echo "<select size=20 name=user>";
                $res = $db->Execute("SELECT email, character_name, ship_destroyed, active, sector FROM {$db->prefix}ships JOIN {$db->prefix}xenobe WHERE email = xenobe_id ORDER BY sector;");
                Tki\Db::logDbErrors($db, $res, __LINE__, __FILE__);
                while (!$res->EOF)
                {
                    $row = $res->fields;
                    $charnamelist = sprintf("%-20s", $row['character_name']);
                    $charnamelist = str_replace("  ", "&nbsp;&nbsp;", $charnamelist);
                    $sectorlist = sprintf("Sector %'04d&nbsp;&nbsp;", $row['sector']);
                    if ($row['active'] == "Y")
                    {
                        $activelist = "Active &Oslash;&nbsp;&nbsp;";
                    }
                    else
                    {
                        $activelist = "Active O&nbsp;&nbsp;";
                    }

                    if ($row['ship_destroyed'] == "Y")
                    {
                        $destroylist = "Destroyed &Oslash;&nbsp;&nbsp;";
                    }
                    else
                    {
                        $destroylist = "Destroyed O&nbsp;&nbsp;";
                    }

                    printf("<option value=%s>%s %s %s %s</option>", $row['email'], $activelist, $destroylist, $sectorlist, $charnamelist);
                    $res->MoveNext();
                }

                echo "</select>";
                echo "&nbsp;<input type=submit value=Edit>";
            }
            else
            {
                if (empty($operation))
                {
                    $res = $db->Execute("SELECT * FROM {$db->prefix}ships JOIN {$db->prefix}xenobe WHERE email=xenobe_id AND email = ?;", array($user));
                    Tki\Db::logDbErrors($db, $res, __LINE__, __FILE__);
                    $row = $res->fields;
                    echo "<table border=0 cellspacing=0 cellpadding=5>";
                    echo "<tr><td>Xenobe name</td><td><input type=text name=character_name value=\"$row[character_name]\"></td></tr>";
                    echo "<tr><td>Active?</td><td><input type=checkbox name=active value=ON " . checked($row['active']) . "></td></tr>";
                    echo "<tr><td>E-mail</td><td>$row[email]</td></tr>";
                    echo "<tr><td>ID</td><td>$row[ship_id]</td></tr>";
                    echo "<tr><td>Ship</td><td><input type=text name=ship_name value=\"$row[ship_name]\"></td></tr>";
                    echo "<tr><td>Destroyed?</td><td><input type=checkbox name=ship_destroyed value=ON " . checked($row['ship_destroyed']) . "></td></tr>";
                    echo "<tr><td>Orders</td><td>";
                    echo "<select size=1 name=orders>";
                    $oorder0 = $oorder1 = $oorder2 = $oorder3 = "value";
                    if ($row['orders'] == 0)
                    {
                        $oorder0 = "selected=0 value";
                    }

                    if ($row['orders'] == 1)
                    {
                        $oorder1 = "selected=1 value";
                    }

                    if ($row['orders'] == 2)
                    {
                        $oorder2 = "selected=2 value";
                    }

                    if ($row['orders'] == 3)
                    {
                        $oorder3 = "selected=3 value";
                    }

                    echo "<option $oorder0=0>Sentinel</option>";
                    echo "<option $oorder1=1>Roam</option>";
                    echo "<option $oorder2=2>Roam and Trade</option>";
                    echo "<option $oorder3=3>Roam and Hunt</option>";
                    echo "</select></td></tr>";
                    echo "<tr><td>Aggression</td><td>";
                    $oaggr0 = $oaggr1 = $oaggr2 = "value";
                    if ($row['aggression'] == 0)
                    {
                        $oaggr0 = "selected=0 value";
                    }

                    if ($row['aggression'] == 1)
                    {
                        $oaggr1 = "selected=1 value";
                    }

                    if ($row['aggression'] == 2)
                    {
                        $oaggr2 = "selected=2 value";
                    }

                    echo "<select size=1 name=aggression>";
                    echo "<option $oaggr0=0>Peaceful</option>";
                    echo "<option $oaggr1=1>Attack Sometimes</option>";
                    echo "<option $oaggr2=2>Attack Always</option>";
                    echo "</select></td></tr>";
                    echo "<tr><td>Levels</td>";
                    echo "<td><table border=0 cellspacing=0 cellpadding=5>";
                    echo "<tr><td>Hull</td><td><input type=text size=5 name=hull value=\"$row[hull]\"></td>";
                    echo "<td>Engines</td><td><input type=text size=5 name=engines value=\"$row[engines]\"></td>";
                    echo "<td>Power</td><td><input type=text size=5 name=power value=\"$row[power]\"></td>";
                    echo "<td>Computer</td><td><input type=text size=5 name=computer value=\"$row[computer]\"></td></tr>";
                    echo "<tr><td>Sensors</td><td><input type=text size=5 name=sensors value=\"$row[sensors]\"></td>";
                    echo "<td>Armor</td><td><input type=text size=5 name=armor value=\"$row[armor]\"></td>";
                    echo "<td>Shields</td><td><input type=text size=5 name=shields value=\"$row[shields]\"></td>";
                    echo "<td>Beams</td><td><input type=text size=5 name=beams value=\"$row[beams]\"></td></tr>";
                    echo "<tr><td>Torpedoes</td><td><input type=text size=5 name=torp_launchers value=\"$row[torp_launchers]\"></td>";
                    echo "<td>Cloak</td><td><input type=text size=5 name=cloak value=\"$row[cloak]\"></td></tr>";
                    echo "</table></td></tr>";
                    echo "<tr><td>Holds</td>";
                    echo "<td><table border=0 cellspacing=0 cellpadding=5>";
                    echo "<tr><td>Ore</td><td><input type=text size=8 name=ship_ore value=\"$row[ship_ore]\"></td>";
                    echo "<td>Organics</td><td><input type=text size=8 name=ship_organics value=\"$row[ship_organics]\"></td>";
                    echo "<td>Goods</td><td><input type=text size=8 name=ship_goods value=\"$row[ship_goods]\"></td></tr>";
                    echo "<tr><td>Energy</td><td><input type=text size=8 name=ship_energy value=\"$row[ship_energy]\"></td>";
                    echo "<td>Colonists</td><td><input type=text size=8 name=ship_colonists value=\"$row[ship_colonists]\"></td></tr>";
                    echo "</table></td></tr>";
                    echo "<tr><td>Combat</td>";
                    echo "<td><table border=0 cellspacing=0 cellpadding=5>";
                    echo "<tr><td>Fighters</td><td><input type=text size=8 name=ship_fighters value=\"$row[ship_fighters]\"></td>";
                    echo "<td>Torpedoes</td><td><input type=text size=8 name=torps value=\"$row[torps]\"></td></tr>";
                    echo "<tr><td>Armor Pts</td><td><input type=text size=8 name=armor_pts value=\"$row[armor_pts]\"></td></tr>";
                    echo "</table></td></tr>";
                    echo "<tr><td>Devices</td>";
                    echo "<td><table border=0 cellspacing=0 cellpadding=5>";
                    echo "<tr><td>Beacons</td><td><input type=text size=5 name=dev_beacon value=\"$row[dev_beacon]\"></td>";
                    echo "<td>Warp Editors</td><td><input type=text size=5 name=dev_warpedit value=\"$row[dev_warpedit]\"></td>";
                    echo "<td>Genesis Torpedoes</td><td><input type=text size=5 name=dev_genesis value=\"$row[dev_genesis]\"></td></tr>";
                    echo "<tr><td>Mine Deflectors</td><td><input type=text size=5 name=dev_minedeflector value=\"$row[dev_minedeflector]\"></td>";
                    echo "<td>Emergency Warp</td><td><input type=text size=5 name=dev_emerwarp value=\"$row[dev_emerwarp]\"></td></tr>";
                    echo "<tr><td>Escape Pod</td><td><input type=checkbox name=dev_escapepod value=ON " . checked($row['dev_escapepod']) . "></td>";
                    echo "<td>FuelScoop</td><td><input type=checkbox name=dev_fuelscoop value=ON " . checked($row['dev_fuelscoop']) . "></td></tr>";
                    echo "</table></td></tr>";
                    echo "<tr><td>Credits</td><td><input type=text name=credits value=\"$row[credits]\"></td></tr>";
                    echo "<tr><td>Turns</td><td><input type=text name=turns value=\"$row[turns]\"></td></tr>";
                    echo "<tr><td>Current sector</td><td><input type=text name=sector value=\"$row[sector]\"></td></tr>";
                    echo "</table>";
                    echo "<br>";
                    echo "<input type=hidden name=user value=$user>";
                    echo "<input type=hidden name=operation value=save>";
                    echo "<input type=submit value=Save>";
                    // Show Xenobe log data
                    echo "<hr>";
                    echo "<span style=\"font-family : courier, monospace; font-size: 12pt; color: #0f0;\">Log Data For This Xenobe</span><br>";

                    $logres = $db->Execute("SELECT * FROM {$db->prefix}logs WHERE ship_id = ? ORDER BY time DESC, type DESC", array($row['ship_id']));
                    Tki\Db::logDbErrors($db, $logres, __LINE__, __FILE__);
                    while (!$logres->EOF)
                    {
                        $logrow = $logres->fields;
                        $logtype = null;
                        switch ($logrow['type'])
                        {
                            case LOG_XENOBE_ATTACK:
                                $logtype = "Launching an attack on ";
                                break;
                            case LOG_ATTACK_LOSE:
                                $logtype = "We were attacked and lost against ";
                                break;
                            case LOG_ATTACK_WIN:
                                $logtype = "We were attacked and won against ";
                                break;
                        }

                        $logdatetime = mb_substr($logrow['time'], 4, 2) . "/" . mb_substr($logrow['time'], 6, 2) . "/" . mb_substr($logrow['time'], 0, 4) . " " . mb_substr($logrow['time'], 8, 2) . ":" . mb_substr($logrow['time'], 10, 2) . ":" . mb_substr($logrow['time'], 12, 2);
                        echo "$logdatetime $logtype$logrow[data] <br>";
                        $logres->MoveNext();
                    }
                }
                elseif ($operation == "save")
                {
                    // Update database
                    $_ship_destroyed = empty($ship_destroyed) ? "N" : "Y";
                    $_dev_escapepod = empty($dev_escapepod) ? "N" : "Y";
                    $_dev_fuelscoop = empty($dev_fuelscoop) ? "N" : "Y";
                    $_active = empty($active) ? "N" : "Y";
                    $result = $db->Execute("UPDATE {$db->prefix}ships SET character_name = ?, ship_name = ?, ship_destroyed = ?, hull = ?, engines = ?, power = ?, computer = ?, sensors = ?, armor = ?, shields = ?, beams = ?, torp_launchers = ?, cloak = ?, credits = ?, turns = ?, dev_warpedit = ?, dev_genesis = ?, dev_beacon = ?, dev_emerwarp = ?, dev_escapepod = ?, dev_fuelscoop = ?, dev_minedeflector = ?, sector = ?, ship_ore = ?, ship_organics = ?, ship_goods = ?, ship_energy = ?, ship_colonists = ?, ship_fighters = ?, torps = ?, armor_pts = ? WHERE email = ?;", array($character_name, $ship_name, $_ship_destroyed, $hull, $engines, $power, $computer, $sensors, $armor, $shields, $beams, $torp_launchers, $cloak, $credits, $turns, $dev_warpedit, $dev_genesis, $dev_beacon, $dev_emerwarp, $_dev_escapepod, $_dev_fuelscoop, $dev_minedeflector, $sector, $ship_ore, $ship_organics, $ship_goods, $ship_energy, $ship_colonists, $ship_fighters, $torps, $armor_pts, $user));
                    Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);
                    if (!$result)
                    {
                        echo "Changes to Xenobe ship record have FAILED Due to the following Error:<br><br>";
                        echo $db->ErrorMsg() . "<br>";
                    }
                    else
                    {
                        echo "Changes to Xenobe ship record have been saved.<br><br>";
                        $result2 = $db->Execute("UPDATE {$db->prefix}xenobe SET active = ?, orders = ?, aggression = ? WHERE xenobe_id = ?;", array($_active, $orders, $aggression, $user));
                        Tki\Db::logDbErrors($db, $result2, __LINE__, __FILE__);
                        if (!$result2)
                        {
                            echo "Changes to Xenobe activity record have FAILED Due to the following Error:<br><br>";
                            echo $db->ErrorMsg() . "<br>";
                        }
                        else
                        {
                            echo "Changes to Xenobe activity record have been saved.<br><br>";
                        }
                    }
                    echo "<input type=submit value=\"Return to Xenobe editor\">";
                    $button_main = false;
                }
                else
                {
                    echo "Invalid operation";
                }
            }
            echo "<input type=hidden name=menu value=xenobeedit>";
            echo "<input type=hidden name=swordfish value=$swordfish>";
            echo "</form>";
        }
        elseif ($module == "dropxenobe")
        {
            echo "<h1>Drop and Re-Install Xenobe Database</h1>";
            echo "<h3>This will DELETE All Xenobe records from the <i>ships</i> table then DROP and reset the <i>xenobe</i> table</h3>";
            echo "<form accept-charset='utf-8' action=xenobe_control.php method=post>";
            if (empty($operation))
            {
                echo "<br>";
                echo "<h2><font COLOR=Red>Are You Sure?</font></h2><br>";
                echo "<input type=hidden name=operation value=dropxen>";
                echo "<input type=submit value=Drop>";
            }
            elseif ($operation == "dropxen")
            {
                // Delete all xenobe in the ships table
                echo "Deleting xenobe records in the ships table...<br>";
                $resx = $db->Execute("DELETE FROM {$db->prefix}ships WHERE email LIKE '%@xenobe'");
                Tki\Db::logDbErrors($db, $resx, __LINE__, __FILE__);
                echo "deleted.<br>";
                // Drop xenobe table
                echo "Dropping xenobe table...<br>";
                $resy = $db->Execute("DROP TABLE IF EXISTS {$db->prefix}xenobe");
                Tki\Db::logDbErrors($db, $resy, __LINE__, __FILE__);
                echo "dropped.<br>";
                // Create xenobe table
                echo "Re-Creating table: xenobe...<br>";
                $resz = $db->Execute("CREATE table {$db->prefix}xenobe(" .
                                     "xenobe_id char(40) NOT NULL," .
                                     "active enum('Y','N') DEFAULT 'Y' NOT NULL," .
                                     "aggression smallint(5) DEFAULT '0' NOT NULL," .
                                     "orders smallint(5) DEFAULT '0' NOT NULL," .
                                     "PRIMARY KEY (xenobe_id)," .
                                     "KEY xenobe_id (xenobe_id)" .
                                     ")");
                Tki\Db::logDbErrors($db, $resz, __LINE__, __FILE__);
                echo "created.<br>";
            }
            else
            {
                echo "Invalid operation";
            }

            echo "<input type=hidden name=menu value=dropxenobe>";
            echo "<input type=hidden name=swordfish value=$swordfish>";
            echo "</form>";
        }
        elseif ($module == "clearlog")
        {
            echo "<h1>Clear All Xenobe Logs</h1>";
            echo "<h3>This will DELETE All Xenobe log files</h3>";
            echo "<form accept-charset='utf-8' action=xenobe_control.php method=post>";
            if (empty($operation))
            {
                echo "<br>";
                echo "<h2><font COLOR=Red>Are You Sure?</font></h2><br>";
                echo "<input type=hidden name=operation value=clearxenlog>";
                echo "<input type=submit value=Clear>";
            }
            elseif ($operation == "clearxenlog")
            {
                $res = $db->Execute("SELECT email,ship_id FROM {$db->prefix}ships WHERE email LIKE '%@xenobe'");
                Tki\Db::logDbErrors($db, $res, __LINE__, __FILE__);
                while (!$res->EOF)
                {
                    $row = $res->fields;
                    $resx = $db->Execute("DELETE FROM {$db->prefix}logs WHERE ship_id = ?;", array($row['ship_id']));
                    Tki\Db::logDbErrors($db, $resx, __LINE__, __FILE__);
                    echo "Log for ship_id $row[ship_id] cleared.<br>";
                    $res->MoveNext();
                }
            }
            else
            {
                echo "Invalid operation";
            }

            echo "<input type=hidden name=menu value=clearlog>";
            echo "<input type=hidden name=swordfish value=$swordfish>";
            echo "</form>";
        }
        elseif ($module == "createnew")
        {
            echo "<strong>Create A New Xenobe</strong>";
            echo "<br>";
            echo "<form accept-charset='utf-8' action=xenobe_control.php method=post>";
            if (empty($operation))
            {
                // Create Xenobe Name
                $Sylable1 = array("Ak","Al","Ar","B","Br","D","F","Fr","G","Gr","K","Kr","N","Ol","Om","P","Qu","R","S","Z");
                $Sylable2 = array("a","ar","aka","aza","e","el","i","in","int","ili","ish","ido","ir","o","oi","or","os","ov","u","un");
                $Sylable3 = array("ag","al","ak","ba","dar","g","ga","k","ka","kar","kil","l","n","nt","ol","r","s","ta","til","x");
                $sy1roll = Tki\Rand::betterRand(0, 19);
                $sy2roll = Tki\Rand::betterRand(0, 19);
                $sy3roll = Tki\Rand::betterRand(0, 19);
                $character = $Sylable1[$sy1roll] . $Sylable2[$sy2roll] . $Sylable3[$sy3roll];
                $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
                $resultnm = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE character_name = ?;", array($character));
                Tki\Db::logDbErrors($db, $resultnm, __LINE__, __FILE__);
                $namecheck = $resultnm->fields;
                $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
                $nametry = 1;
                // If Name Exists Try Again - Up To Nine Times
                while (($namecheck[0]) && ($nametry <= 9))
                {
                    $sy1roll = Tki\Rand::betterRand(0, 19);
                    $sy2roll = Tki\Rand::betterRand(0, 19);
                    $sy3roll = Tki\Rand::betterRand(0, 19);
                    $character = $Sylable1[$sy1roll] . $Sylable2[$sy2roll] . $Sylable3[$sy3roll];
                    $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
                    $resultnm = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE character_name = ?;", array($character));
                    Tki\Db::logDbErrors($db, $resultnm, __LINE__, __FILE__);
                    $namecheck = $resultnm->fields;
                    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
                    $nametry++;
                }

                // Create Ship Name
                $shipname = "Xenobe-" . $character;

                // Select Random Sector
                $sector = Tki\Rand::betterRand(1, $tkireg->max_sectors);

                // Display Confirmation form
                echo "<td><table border=0 cellspacing=0 cellpadding=5>";
                echo "<tr><td>Xenobe Name</td><td><input type=text size=20 name=character value=$character></td>";
                echo "<td>Level <input type=text size=5 name=xenlevel value=3></td>";
                echo "<td>Ship Name <input type=text size=20 name=shipname value=$shipname></td>";
                echo "<tr><td>Active?<input type=checkbox name=active value=on checked></td>";
                echo "<td>Orders ";
                echo "<select size=1 name=orders>";
                echo "<option selected=0 value=0>Sentinel</option>";
                echo "<option value=1>Roam</option>";
                echo "<option value=2>Roam and Trade</option>";
                echo "<option value=3>Roam and Hunt</option>";
                echo "</select></td>";
                echo "<td>Sector <input type=text size=5 name=sector value=$sector></td>";
                echo "<td>Aggression ";
                echo "<select size=1 name=aggression>";
                echo "<option selected=0 value=0>Peaceful</option>";
                echo "<option value=1>Attack Sometimes</option>";
                echo "<option value=2>Attack Always</option>";
                echo "</select></td></tr>";
                echo "</table>";
                echo "<hr>";
                echo "<input type=hidden name=operation value=createxenobe>";
                echo "<input type=submit value=Create>";
            }
            elseif ($operation == "createxenobe")
            {
                // Update database
                $_active = empty($active) ? "N" : "Y";
                $errflag = 0;
                if ($character === null || $shipname === null)
                {
                    echo "Ship name, and character name may not be blank.<br>";
                    $errflag = 1;
                }

                // Change Spaces to Underscores in shipname
                $shipname = str_replace(" ", "_", $shipname);

                // Create emailname from character
                $emailname = str_replace(" ", "_", $character) . "@xenobe";
                $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
                $result = $db->Execute("SELECT email, character_name, ship_name FROM {$db->prefix}ships WHERE email = ? OR character_name = ? OR ship_name = ?;", array($emailname, $character, $shipname));
                Tki\Db::logDbErrors($db, $result, __LINE__, __FILE__);
                if ($result instanceof ADORecordSet)
                {
                    while (!$result->EOF)
                    {
                        $row = $result->fields;
                        if ($row[0] == $emailname)
                        {
                            echo "ERROR: E-mail address $emailname, is already in use.  ";
                            $errflag = 1;
                        }

                        if ($row[1] == $character)
                        {
                            echo "ERROR: Character name $character, is already in use.<br>";
                            $errflag = 1;
                        }

                        if ($row[2] == $shipname)
                        {
                            echo "ERROR: Ship name $shipname, is already in use.<br>";
                            $errflag = 1;
                        }

                        $result->MoveNext();
                    }
                }

                $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
                if ($errflag == 0)
                {
                    $makepass = null;
                    $syllables ="er,in,tia,wol,fe,pre,vet,jo,nes,al,len,son,cha,ir,ler,bo,ok,tio,nar,sim,ple,bla,ten,toe,cho,co,lat,spe,ak,er,po,co,lor,pen,cil,li,ght,wh,at,the,he,ck,is,mam,bo,no,fi,ve,any,way,pol,iti,cs,ra,dio,sou,rce,sea,rch,pa,per,com,bo,sp,eak,st,fi,rst,gr,oup,boy,ea,gle,tr,ail,bi,ble,brb,pri,dee,kay,en,be,se";
                    $syllable_array = explode(",", $syllables);
                    for ($count = 1; $count <= 4; $count++)
                    {
                        if (Tki\Rand::betterRand() %10 == 1)
                        {
                            $makepass .= sprintf("%0.0f", (Tki\Rand::betterRand() %50) + 1);
                        }
                        else
                        {
                            $makepass .= sprintf("%s", $syllable_array[Tki\Rand::betterRand() %62]);
                        }
                    }
                    if ($xenlevel == null)
                    {
                        $xenlevel = 0;
                    }

                    $maxenergy = Tki\CalcLevels::energy($xenlevel, $level_factor);
                    $maxarmor = Tki\CalcLevels::armor($xenlevel, $level_factor);
                    $maxfighters = Tki\CalcLevels::fighters($xenlevel, $level_factor);
                    $maxtorps = Tki\CalcLevels::torpedoes($xenlevel, $level_factor);
                    $stamp = date("Y-m-d H:i:s");

                    // Add Xenobe record to ships table ... modify if the ships schema changes
                    $thesql = "INSERT INTO {$db->prefix}ships ( `ship_id` , `ship_name` , `ship_destroyed` , `character_name` , `password` , `email` , `hull` , `engines` , `power` , `computer` , `sensors` , `beams` , `torp_launchers` , `torps` , `shields` , `armor` , `armor_pts` , `cloak` , `credits` , `sector` , `ship_ore` , `ship_organics` , `ship_goods` , `ship_energy` , `ship_colonists` , `ship_fighters` , `ship_damage` , `turns` , `on_planet` , `dev_warpedit` , `dev_genesis` , `dev_beacon` , `dev_emerwarp` , `dev_escapepod` , `dev_fuelscoop` , `dev_minedeflector` , `turns_used` , `last_login` , `rating` , `score` , `team` , `team_invite` , `interface` , `ip_address` , `planet_id` , `trade_colonists` , `trade_fighters` , `trade_torps` , `trade_energy` , `cleared_defences` , `lang` , `dev_lssd` )
                               VALUES (NULL,'$shipname','N','$character','$makepass','$emailname',$xenlevel,$xenlevel,$xenlevel,$xenlevel,$xenlevel,$xenlevel,$xenlevel,$maxtorps,$xenlevel,$xenlevel,$maxarmor,$xenlevel,$start_credits,$sector,0,0,0,$maxenergy,0,$maxfighters,0,$start_turns,'N',0,0,0,0,'N','N',0,0, '$stamp',0,0,0,0,'N','127.0.0.1',0,'Y','N','N','Y',NULL,'$default_lang','Y')";
                    $result2 = $db->Execute($thesql);
                    Tki\Db::logDbErrors($db, $result2, __LINE__, __FILE__);
                    if (!$result2)
                    {
                        echo $db->ErrorMsg() . "<br>";
                    }
                    else
                    {
                        echo "Xenobe has been created.<br><br>";
                        echo "password has been set.<br><br>";
                        echo "Ship Records have been updated.<br><br>";
                    }

                    $result3 = $db->Execute("INSERT INTO {$db->prefix}xenobe (xenobe_id, active, aggression, orders) values(?,?,?,?)", array($emailname, $_active, $aggression, $orders));
                    Tki\Db::logDbErrors($db, $result3, __LINE__, __FILE__);
                    if (!$result3)
                    {
                        echo $db->ErrorMsg() . "<br>";
                    }
                    else
                    {
                        echo "Xenobe Records have been updated.<br><br>";
                    }
                }

                echo "<input type=submit value=\"Return to Xenobe Creator \">";
                $button_main = false;
            }
            else
            {
                echo "Invalid operation";
            }

            echo "<input type=hidden name=menu value=createnew>";
            echo "<input type=hidden name=swordfish value=$swordfish>";
            echo "</form>";
        }
        else
        {
            echo "Unknown function";
        }

        if ($button_main)
        {
            echo "<br><br>";
            echo "<form accept-charset='utf-8' action=xenobe_control.php method=post>";
            echo "<input type=hidden name=swordfish value=$swordfish>";
            echo "<input type=submit value=\"Return to main menu\">";
            echo "</form>";
        }
    }
}
Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
