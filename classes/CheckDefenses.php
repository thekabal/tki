<?php declare(strict_types = 1);
/**
 * classes/CheckDefenses.php from The Kabal Invasion.
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

class CheckDefenses
{
    public static function sectorFighters(\PDO $pdo_db, string $lang, int $sector, string $calledfrom, int $energyscooped, array $playerinfo, Reg $tkireg): void
    {
        $total_sec_fighters = 0;

        $langvars = \Tki\Translate::load($pdo_db, $lang, array('common',
                                         'footer', 'insignias', 'news',
                                         'sector_fighters', 'universal'));
        echo $langvars['l_sf_attacking'] . "<br>";
        $targetfighters = $total_sec_fighters;
        $playerbeams = \Tki\CalcLevels::abstractLevels($playerinfo['beams'], $tkireg);
        $playerenergy = $playerinfo['ship_energy'];

        if ($calledfrom == 'rsmove.php')
        {
            $playerenergy += $energyscooped;
        }

        if ($playerbeams > (int) $playerinfo['ship_energy'])
        {
            $playerbeams = $playerinfo['ship_energy'];
        }

        $playerenergy = $playerenergy - $playerbeams;
        $playershields = \Tki\CalcLevels::abstractLevels($playerinfo['shields'], $tkireg);

        if ($playershields > $playerenergy)
        {
            $playershields = $playerenergy;
        }

        $playertorpnum = round(pow($tkireg->level_factor, $playerinfo['torp_launchers'])) * 2;

        if ($playertorpnum > $playerinfo['torps'])
        {
            $playertorpnum = $playerinfo['torps'];
        }

        $playertorpdmg = $tkireg->torp_dmg_rate * $playertorpnum;
        $playerarmor = $playerinfo['armor_pts'];
        $playerfighters = $playerinfo['ship_fighters'];
        if (($targetfighters > 0) && ($playerbeams > 0))
        {
            if ($playerbeams > round($targetfighters / 2))
            {
                $temp = round($targetfighters / 2);
                $lost = $targetfighters - $temp;
                $langvars['l_sf_destfight'] = str_replace("[lost]", (string) $lost, $langvars['l_sf_destfight']);
                echo $langvars['l_sf_destfight'] . "<br>";
                $targetfighters = $temp;
                $playerbeams = $playerbeams - $lost;
            }
            else
            {
                $targetfighters = $targetfighters - $playerbeams;
                $langvars['l_sf_destfightb'] = str_replace("[lost]", (string) $playerbeams, $langvars['l_sf_destfightb']);
                echo $langvars['l_sf_destfightb'] . "<br>";
                $playerbeams = 0;
            }
        }

        echo "<br>" . $langvars['l_sf_torphit'] . "<br>";
        if ($targetfighters > 0 && $playertorpdmg > 0)
        {
            if ($playertorpdmg > round($targetfighters / 2))
            {
                $temp = round($targetfighters / 2);
                $lost = $targetfighters - $temp;
                $langvars['l_sf_destfightt'] = str_replace("[lost]", (string) $lost, $langvars['l_sf_destfightt']);
                echo $langvars['l_sf_destfightt'] . "<br>";
                $targetfighters = $temp;
                $playertorpdmg = $playertorpdmg - $lost;
            }
            else
            {
                $targetfighters = $targetfighters - $playertorpdmg;
                $langvars['l_sf_destfightt'] = str_replace("[lost]", (string) $playertorpdmg, $langvars['l_sf_destfightt']);
                echo $langvars['l_sf_destfightt'];
                $playertorpdmg = 0;
            }
        }

        echo "<br>" . $langvars['l_sf_fighthit'] . "<br>";
        if ($playerfighters > 0 && $targetfighters > 0)
        {
            if ($playerfighters > $targetfighters)
            {
                echo $langvars['l_sf_destfightall'] . "<br>";
                $temptargfighters = 0;
            }
            else
            {
                $langvars['l_sf_destfightt2'] = str_replace("[lost]", (string) $playerfighters, $langvars['l_sf_destfightt2']);
                echo $langvars['l_sf_destfightt2'] . "<br>";
                $temptargfighters = $targetfighters - $playerfighters;
            }

            if ($targetfighters > $playerfighters)
            {
                echo $langvars['l_sf_lostfight'] . "<br>";
                $tempplayfighters = 0;
            }
            else
            {
                 $langvars['l_sf_lostfight2'] = str_replace("[lost]", $targetfighters, $langvars['l_sf_lostfight2']);
                 echo $langvars['l_sf_lostfight2'] . "<br>";
                 $tempplayfighters = $playerfighters - $targetfighters;
            }

            $playerfighters = $tempplayfighters;
            $targetfighters = $temptargfighters;
        }

        if ($targetfighters > 0)
        {
            if ($targetfighters > $playerarmor)
            {
                $playerarmor = 0;
                echo $langvars['l_sf_armorbreach'] . "<br>";
            }
            else
            {
                $playerarmor = $playerarmor - $targetfighters;
                $langvars['l_sf_armorbreach2'] = str_replace("[lost]", $targetfighters, $langvars['l_sf_armorbreach2']);
                echo $langvars['l_sf_armorbreach2'] . "<br>";
            }
        }

        $fighterslost = $total_sec_fighters - $targetfighters;
        \Tki\Fighters::destroy($pdo_db, $sector, $fighterslost);
        $langvars['l_sf_sendlog'] = str_replace("[player]", $playerinfo['character_name'], $langvars['l_sf_sendlog']);
        $langvars['l_sf_sendlog'] = str_replace("[lost]", (string) $fighterslost, $langvars['l_sf_sendlog']);
        $langvars['l_sf_sendlog'] = str_replace("[sector]", (string) $sector, $langvars['l_sf_sendlog']);

        \Tki\SectorDefense::messageDefenseOwner($pdo_db, $sector, $langvars['l_sf_sendlog']);
        \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::DEFS_DESTROYED_F, "$fighterslost|$sector");
        $armor_lost = $playerinfo['armor_pts'] - $playerarmor;
        $fighters_lost = $playerinfo['ship_fighters'] - $playerfighters;

        $sql = "UPDATE ::prefix::ships SET ship_energy = :ship_energy, ship_fighters = ship_fighters - :fighters_lost," .
               "armor_pts = armor_pts - :armor_lost, torps = torps - :playertorps WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_energy', $playerenergy, \PDO::PARAM_INT);
        $stmt->bindParam(':fighters_lost', $fighters_lost, \PDO::PARAM_INT);
        $stmt->bindParam(':armor_lost', $armor_lost, \PDO::PARAM_INT);
        $stmt->bindParam(':playertorps', $playertorpnum, \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        $langvars['l_sf_lreport'] = str_replace("[armor]", (string) $armor_lost, $langvars['l_sf_lreport']);
        $langvars['l_sf_lreport'] = str_replace("[fighters]", (string) $fighters_lost, $langvars['l_sf_lreport']);
        $langvars['l_sf_lreport'] = str_replace("[torps]", (string) $playertorpnum, $langvars['l_sf_lreport']);
        echo $langvars['l_sf_lreport'] . "<br><br>";
        if ($playerarmor < 1)
        {
            echo $langvars['l_sf_shipdestroyed'] . "<br><br>";
            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::DEFS_KABOOM, "$sector|$playerinfo[dev_escapepod]");
            $langvars['l_sf_sendlog2'] = str_replace("[player]", $playerinfo['character_name'], $langvars['l_sf_sendlog2']);
            $langvars['l_sf_sendlog2'] = str_replace("[sector]", (string) $sector, $langvars['l_sf_sendlog2']);
            \Tki\SectorDefense::messageDefenseOwner($pdo_db, $sector, $langvars['l_sf_sendlog2']);
            if ($playerinfo['dev_escapepod'] == 'Y')
            {
                $rating = round($playerinfo['rating'] / 2);
                echo $langvars['l_sf_escape'] . "<br><br>";

                $sql = "UPDATE ::prefix::ships SET hull = 0," .
                       "engines = 0, power = 0, computer = 0, sensors = 0," .
                       "beams = 0, torp_launchers = 0, torps = 0, armor = 0," .
                       "armor_pts = 100, cloak = 0, shields = 0, sector = 1," .
                       "ship_ore = 0, ship_organics = 0, ship_energy = 1000," .
                       "ship_colonists = 0, ship_goods = 0, rating = :rating," .
                       "ship_fighters = 100, ship_damage = 0, credits = 1000," .
                       "on_planet = 'N', cleared_defenses = ' ', dev_warpedit = 0, dev_genesis = 0," .
                       "dev_beacon = 0, dev_emerwarp = 0, dev_escapepod = 'N'," .
                       "dev_fuelscoop = 'N', dev_minedeflector = 0," .
                       "ship_destroyed = 'N', dev_lssd = 'N' " .
                       "WHERE ship_id = :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                $stmt->bindParam(':rating', $rating, \PDO::PARAM_INT);
                $stmt->execute();
                \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                $bounty = new \Tki\Bounty();
                $bounty->cancel($pdo_db, $playerinfo['ship_id']);
                \Tki\Text::gotoMain($pdo_db, $lang);
                die();
            }
            else
            {
                $bounty = new \Tki\Bounty();
                $bounty->cancel($pdo_db, $playerinfo['ship_id']);
                $character_object = new \Tki\Character();
                $character_object->kill($pdo_db, $lang, $playerinfo['ship_id'], $tkireg);
                \Tki\Text::gotoMain($pdo_db, $lang);
                die();
            }
        }

        if ($targetfighters > 0)
        {
            $status = 0;
        }
        else
        {
            $status = 2;
        }
    }

    public static function fighters(\PDO $pdo_db, $old_db, string $lang, int $sector, array $playerinfo, \Tki\Reg $tkireg, string $title, string $calledfrom): void
    {
        // Database driven language entries
        $langvars = \Tki\Translate::load($pdo_db, $lang, array(
                                         'check_defenses', 'combat', 'common',
                                         'footer', 'insignias', 'news',
                                         'regional', 'universal'));
        /*
        // Get sectorinfo from database
        $sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db);
        $sectorinfo = $sectors_gateway->selectSectorInfo($sector);
        */

        $result3 = $old_db->Execute("SELECT * FROM {$old_db->prefix}sector_defense WHERE sector_id = ? and defense_type ='F' ORDER BY quantity DESC;", array($sector));
        \Tki\Db::logDbErrors($pdo_db, $result3, __LINE__, __FILE__);

        // Put the defense information into the array defenses
        $num_defenses = 0;
        $total_sec_fighters = 0;
        $owner = true;

        // Detect if this variable exists, and filter it.
        // Returns false if anything wasn't right.
        $response = null;
        $response = filter_input(INPUT_POST, 'response', FILTER_SANITIZE_STRING);
        if (($response === null) || (strlen(trim($response)) === 0))
        {
            $response = false;
        }

        $defenses = array();
        $destination = null;
        if (array_key_exists('destination', $_REQUEST) === true)
        {
            $destination = $_REQUEST['destination'];
        }

        $engage = null;
        if (array_key_exists('engage', $_REQUEST) === true)
        {
            $engage = $_REQUEST['engage'];
        }

        while (!$result3->EOF)
        {
            $row = $result3->fields;
            $defenses[$num_defenses] = $row;
            $total_sec_fighters += $defenses[$num_defenses]['quantity'];
            if ($defenses[$num_defenses]['ship_id'] != $playerinfo['ship_id'])
            {
                $owner = false;
            }

            $num_defenses++;
            $result3->MoveNext();
        }

        if ($num_defenses > 0 && $total_sec_fighters > 0 && !$owner)
        {
            // Find out if the fighter owner and player are on the same team
            // All sector defenses must be owned by members of the same team
            $fm_owner = $defenses[0]['ship_id'];
            $result2 = $old_db->Execute("SELECT * FROM {$old_db->prefix}ships WHERE ship_id = ?;", array($fm_owner));
            \Tki\Db::logDbErrors($pdo_db, $result2, __LINE__, __FILE__);
            $fighters_owner = $result2->fields;
            if ($fighters_owner['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
            {
                switch ($response)
                {
                    case "fight":
                        $resx = $old_db->Execute("UPDATE {$old_db->prefix}ships SET cleared_defenses = ' ' WHERE ship_id = ?;", array($playerinfo['ship_id']));
                        \Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                        echo "<h1>" . $title . "</h1>\n";
                        \Tki\CheckDefenses::sectorFighters($pdo_db, $lang, $sector, $calledfrom, 0, $playerinfo, $tkireg);
                        break;

                    case "retreat":
                        $resx = $old_db->Execute("UPDATE {$old_db->prefix}ships SET cleared_defenses = ' ' WHERE ship_id = ?;", array($playerinfo['ship_id']));
                        \Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                        $cur_time_stamp = date("Y-m-d H:i:s");
                        $resx = $old_db->Execute("UPDATE {$old_db->prefix}ships SET last_login='$cur_time_stamp', turns = turns - 2, turns_used = turns_used + 2, sector=? WHERE ship_id=?;", array($playerinfo['sector'], $playerinfo['ship_id']));
                        \Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                        echo "<h1>" . $title . "</h1>\n";
                        echo $langvars['l_chf_youretreatback'] . "<br>";
                        \Tki\Text::gotoMain($pdo_db, $lang);
                        die();

                    case "pay":
                        $resx = $old_db->Execute("UPDATE {$old_db->prefix}ships SET cleared_defenses = ' ' WHERE ship_id = ?;", array($playerinfo['ship_id']));
                        \Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                        $fighterstoll = (int) round($total_sec_fighters * $tkireg->fighter_price * 0.6);
                        if ($playerinfo['credits'] < $fighterstoll)
                        {
                            echo $langvars['l_chf_notenoughcreditstoll'] . "<br>";
                            echo $langvars['l_chf_movefailed'] . "<br>";

                            // Undo the move
                            $resx = $old_db->Execute("UPDATE {$old_db->prefix}ships SET sector=? WHERE ship_id=?;", array($playerinfo['sector'], $playerinfo['ship_id']));
                            \Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                            $status = 0;
                        }
                        else
                        {
                            $tollstring = number_format($fighterstoll, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
                            $langvars['l_chf_youpaidsometoll'] = str_replace("[chf_tollstring]", $tollstring, $langvars['l_chf_youpaidsometoll']);
                            echo $langvars['l_chf_youpaidsometoll'] . "<br>";
                            $resx = $old_db->Execute("UPDATE {$old_db->prefix}ships SET credits=credits - $fighterstoll WHERE ship_id = ?;", array($playerinfo['ship_id']));
                            \Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                            \Tki\Toll::distribute($pdo_db, $sector, $fighterstoll, (int) $total_sec_fighters);
                            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::TOLL_PAID, "$tollstring|$sector");
                            $status = 1;
                        }
                        break;

                    case "sneak":
                        $resx = $old_db->Execute("UPDATE {$old_db->prefix}ships SET cleared_defenses = ' ' WHERE ship_id = ?;", array($playerinfo['ship_id']));
                        \Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                        $success = \Tki\Scan::success($fighters_owner['sensors'], $playerinfo['cloak']);
                        if ($success < 5)
                        {
                            $success = 5;
                        }

                        if ($success > 95)
                        {
                            $success = 95;
                        }

                        $roll = random_int(1, 100);
                        if ($roll < $success)
                        {
                            // Sector defenses detect incoming ship
                            echo "<h1>" . $title . "</h1>\n";
                            echo $langvars['l_chf_thefightersdetectyou'] . "<br>";
                            \Tki\CheckDefenses::sectorFighters($pdo_db, $lang, $sector, $calledfrom, 0, $playerinfo, $tkireg);
                            break;
                        }
                        else
                        {
                            // Sector defenses don't detect incoming ship
                            $status = 1;
                        }
                        break;

                    default:
                        $interface_string = $calledfrom . '?sector=' . $sector . '&destination=' . $destination . '&engage=' . $engage;
                        $resx = $old_db->Execute("UPDATE {$old_db->prefix}ships SET cleared_defenses = ? WHERE ship_id = ?;", array($interface_string, $playerinfo['ship_id']));
                        \Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
                        $fighterstoll = (int) round($total_sec_fighters * $tkireg->fighter_price * 0.6);
                        echo "<h1>" . $title . "</h1>\n";
                        echo "<form accept-charset='utf-8' action='{$calledfrom}' method='post'>";
                        $langvars['l_chf_therearetotalfightersindest'] = str_replace("[chf_total_sec_fighters]", (string) $total_sec_fighters, $langvars['l_chf_therearetotalfightersindest']);
                        echo $langvars['l_chf_therearetotalfightersindest'] . "<br>";
                        if ($defenses[0]['fm_setting'] == "toll")
                        {
                            $langvars['l_chf_creditsdemanded'] = str_replace("[chf_number_fighterstoll]", number_format($fighterstoll, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), $langvars['l_chf_creditsdemanded']);
                            echo $langvars['l_chf_creditsdemanded'] . "<br>";
                        }

                        $langvars['l_chf_youcanretreat'] = str_replace("[retreat]", "<strong>Retreat</strong>", $langvars['l_chf_youcanretreat']);
                        echo $langvars['l_chf_youcan'] . " <br><input type='radio' name='response' value='retreat'>" . $langvars['l_chf_youcanretreat'] . "<br></input>";
                        if ($defenses[0]['fm_setting'] == "toll")
                        {
                            $langvars['l_chf_inputpay'] = str_replace("[pay]", "<strong>Pay</strong>", $langvars['l_chf_inputpay']);
                            echo "<input type='radio' name='response' checked value='pay'>" . $langvars['l_chf_inputpay'] . "<br></input>";
                        }

                        echo "<input type='radio' name='response' checked value='fight'>";
                        $langvars['l_chf_inputfight'] = str_replace("[fight]", "<strong>Fight</strong>", $langvars['l_chf_inputfight']);
                        echo $langvars['l_chf_inputfight'] . "<br></input>";

                        echo "<input type=radio name=response checked value=sneak>";
                        $langvars['l_chf_inputcloak'] = str_replace("[cloak]", "<strong>Cloak</strong>", $langvars['l_chf_inputcloak']);
                        echo $langvars['l_chf_inputcloak'] . "<br></input><br>";

                        echo "<input type='submit' value='" . $langvars['l_chf_go'] . "'><br><br>";
                        echo "<input type='hidden' name='sector' value='{$sector}'>";
                        echo "<input type='hidden' name='engage' value='1'>";
                        echo "<input type='hidden' name='destination' value='{$destination}'>";
                        echo "</form>";
                        die();
                }

                // Clean up any sectors that have used up all mines or fighters
                $resx = $old_db->Execute("DELETE FROM {$old_db->prefix}sector_defense WHERE quantity <= 0 ");
                \Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
            }
        }
    }

    public static function mines(\PDO $pdo_db, $old_db, string $lang, int $sector, string $title, array $playerinfo, \Tki\Reg $tkireg): void
    {
        // Database driven language entries
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('combat',
                                         'common', 'check_defenses',
                                         'insignias', 'footer', 'news'));
        // Get sectorinfo from database
        //$sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db);
        //$sectorinfo = $sectors_gateway->selectSectorInfo($sector);

        // Put the defense information into the array defenseinfo
        $result3 = $old_db->Execute("SELECT * FROM {$old_db->prefix}sector_defense WHERE sector_id = ? and defense_type ='M'", array($sector));
        \Tki\Db::logDbErrors($pdo_db, $result3, __LINE__, __FILE__);

        // Correct the targetship bug to reflect the player info
        $targetship = $playerinfo;

        $defenses = array();
        $num_defenses = 0;
        $total_sector_mines = 0;
        $owner = true;
        while (!$result3->EOF)
        {
            $row = $result3->fields;
            $defenses[$num_defenses] = $row;
            $total_sector_mines += $defenses[$num_defenses]['quantity'];
            if ($defenses[$num_defenses]['ship_id'] != $playerinfo['ship_id'])
            {
                $owner = false;
            }

            $num_defenses++;
            $result3->MoveNext();
        }

        // Compute the ship average. If it's too low then the ship will not hit mines.
        $shipavg = \Tki\CalcLevels::avgTech($targetship, 'ship');

        // The mines will attack if 4 conditions are met
        //    1) There is at least 1 group of mines in the sector
        //    2) There is at least 1 mine in the sector
        //    3) You are not the owner or on the team of the owner - team 0 dosent count
        //    4) You ship is at least $tkireg->mine_hullsize (setable in config/classic_config.ini) big

        if ($num_defenses > 0 && $total_sector_mines > 0 && !$owner && $shipavg > $tkireg->mine_hullsize)
        {
            // Find out if the mine owner and player are on the same team
            $fm_owner = $defenses[0]['ship_id'];
            $result2 = $old_db->Execute("SELECT * FROM {$old_db->prefix}ships WHERE ship_id = ?;", array($fm_owner));
            \Tki\Db::logDbErrors($pdo_db, $result2, __LINE__, __FILE__);

            $mine_owner = $result2->fields;
            if ($mine_owner['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
            {
                // You hit mines
                echo "<h1>" . $title . "</h1>\n";
                $totalmines = $total_sector_mines;
                // Before we had an issue where if there were a lot of mines in the sector the result will go -
                // I changed the behaivor so that rand will chose a % of mines to attack at will
                // (it will always be at least 5% of the mines or at the very least 1 mine);
                // and if you are very unlucky they will all hit you
                $pren = (random_int(5, 100) / 100);
                $roll = (int) round($pren * $total_sector_mines - 1) + 1;
                $totalmines = $totalmines - $roll;

                // You are hit. Tell the player and put it in the log
                $langvars['l_chm_youhitsomemines'] = str_replace("[chm_roll]", (string) $roll, $langvars['l_chm_youhitsomemines']);
                echo $langvars['l_chm_youhitsomemines'] . "<br>";
                \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::HIT_MINES, "$roll|$sector");

                // Tell the owner that his mines were hit
                $langvars['l_chm_hehitminesinsector'] = str_replace("[chm_playerinfo_character_name]", $playerinfo['character_name'], $langvars['l_chm_hehitminesinsector']);
                $langvars['l_chm_hehitminesinsector'] = str_replace("[chm_roll]", "$roll", $langvars['l_chm_hehitminesinsector']);
                $langvars['l_chm_hehitminesinsector'] = str_replace("[chm_sector]", (string) $sector, $langvars['l_chm_hehitminesinsector']);
                \Tki\SectorDefense::messageDefenseOwner($pdo_db, $sector, $langvars['l_chm_hehitminesinsector']);

                // If the player has enough mine deflectors then subtract the ammount and continue
                if ($playerinfo['dev_minedeflector'] >= $roll)
                {
                    $langvars['l_chm_youlostminedeflectors'] = str_replace("[chm_roll]", (string) $roll, $langvars['l_chm_youlostminedeflectors']);
                    echo $langvars['l_chm_youlostminedeflectors'] . "<br>";
                    $result2 = $old_db->Execute("UPDATE {$old_db->prefix}ships SET dev_minedeflector = dev_minedeflector - ? WHERE ship_id = ?", array($roll, $playerinfo['ship_id']));
                    \Tki\Db::logDbErrors($pdo_db, $result2, __LINE__, __FILE__);
                }
                else
                {
                    if ($playerinfo['dev_minedeflector'] > 0)
                    {
                        echo $langvars['l_chm_youlostallminedeflectors'] . "<br>";
                    }
                    else
                    {
                        echo $langvars['l_chm_youhadnominedeflectors'] . "<br>";
                    }

                    // Shields up
                    $mines_left = $roll - $playerinfo['dev_minedeflector'];
                    $playershields = \Tki\CalcLevels::abstractLevels($playerinfo['shields'], $tkireg);
                    if ($playershields > $playerinfo['ship_energy'])
                    {
                        $playershields = $playerinfo['ship_energy'];
                    }

                    if ($playershields >= $mines_left)
                    {
                        $langvars['l_chm_yourshieldshitforminesdmg'] = str_replace("[chm_mines_left]", (string) $mines_left, $langvars['l_chm_yourshieldshitforminesdmg']);
                        echo $langvars['l_chm_yourshieldshitforminesdmg'] . "<br>";

                        $result2 = $old_db->Execute("UPDATE {$old_db->prefix}ships SET ship_energy = ship_energy - ?, dev_minedeflector = 0 WHERE ship_id = ?", array($mines_left, $playerinfo['ship_id']));
                        \Tki\Db::logDbErrors($pdo_db, $result2, __LINE__, __FILE__);
                        if ($playershields == $mines_left)
                        {
                            echo $langvars['l_chm_yourshieldsaredown'] . "<br>";
                        }
                    }
                    else
                    {
                        // Direct hit
                        echo $langvars['l_chm_youlostallyourshields'] . "<br>";
                        $mines_left = $mines_left - $playershields;
                        if ($playerinfo['armor_pts'] >= $mines_left)
                        {
                            $langvars['l_chm_yourarmorhitforminesdmg'] = str_replace("[chm_mines_left]", (string) $mines_left, $langvars['l_chm_yourarmorhitforminesdmg']);
                            echo $langvars['l_chm_yourarmorhitforminesdmg'] . "<br>";
                            $result2 = $old_db->Execute("UPDATE {$old_db->prefix}ships SET armor_pts = armor_pts - ?, ship_energy = 0, dev_minedeflector = 0 WHERE ship_id = ?", array($mines_left, $playerinfo['ship_id']));
                            \Tki\Db::logDbErrors($pdo_db, $result2, __LINE__, __FILE__);
                            if ($playerinfo['armor_pts'] == $mines_left)
                            {
                                echo $langvars['l_chm_yourhullisbreached'] . "<br>";
                            }
                        }
                        else
                        {
                            // BOOM
                            $pod = $playerinfo['dev_escapepod'];
                            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::SHIP_DESTROYED_MINES, "$sector|$pod");
                            $langvars['l_chm_hewasdestroyedbyyourmines'] = str_replace("[chm_playerinfo_character_name]", $playerinfo['character_name'], $langvars['l_chm_hewasdestroyedbyyourmines']);
                            $langvars['l_chm_hewasdestroyedbyyourmines'] = str_replace("[chm_sector]", (string) $sector, $langvars['l_chm_hewasdestroyedbyyourmines']);
                            \Tki\SectorDefense::messageDefenseOwner($pdo_db, $sector, $langvars['l_chm_hewasdestroyedbyyourmines']);
                            echo $langvars['l_chm_yourshiphasbeendestroyed'] . "<br><br>";

                            // Survival
                            if ($playerinfo['dev_escapepod'] == "Y")
                            {
                                $rating = round($playerinfo['rating'] / 2);
                                echo $langvars['l_chm_luckescapepod'] . "<br><br>";
                                $resx = $old_db->Execute("UPDATE {$old_db->prefix}ships SET hull=0, engines=0, power=0, sensors=0, computer=0, beams=0, torp_launchers=0, torps=0, armor=0, armor_pts=100, cloak=0, shields=0, sector=1, ship_organics=0, ship_ore=0, ship_goods=0, ship_energy=?, ship_colonists=0, ship_fighters=100, dev_warpedit=0, dev_genesis=0, dev_beacon=0, dev_emerwarp=0, dev_escapepod='N', dev_fuelscoop='N', dev_minedeflector=0, on_planet='N', rating=?, cleared_defenses=' ', dev_lssd='N' WHERE ship_id=?", array(100, $rating, $playerinfo['ship_id']));
                                \Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);

                                $bounty = new \Tki\Bounty();
                                $bounty->cancel($pdo_db, $playerinfo['ship_id']);
                            }
                            else
                            {
                                // Or they lose!
                                $bounty = new \Tki\Bounty();
                                $bounty->cancel($pdo_db, $playerinfo['ship_id']);

                                $character_object = new \Tki\Character();
                                $character_object->kill($pdo_db, $lang, $playerinfo['ship_id'], $tkireg);
                            }
                        }
                    }
                }

                \Tki\Mines::explode($pdo_db, $sector, $roll);
            }
        }
    }
}
