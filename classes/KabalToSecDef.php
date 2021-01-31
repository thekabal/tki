<?php declare(strict_types = 1);
/**
 * classes/KabalToSecDef.php from The Kabal Invasion.
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

class KabalToSecDef
{
    public static function secDef(\PDO $pdo_db, string $lang, array $langvars, array $playerinfo, int $targetlink, Reg $tkireg): void
    {
        $character_object = new Character();

        // Check for sector defenses
        if ($targetlink > 0)
        {
            $counter = 0;
            $all_sector_fighters = 0;
            $defenses = array();

            $sql = "SELECT * FROM ::prefix::sector_defense WHERE sector_id = :sector_id AND defense_type = 'F' ORDER BY quantity DESC";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $targetlink, \PDO::PARAM_INT);
            $stmt->execute();
            $defenses_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($defenses_present !== false)
            {
                foreach ($defenses_present as $tmp_defense)
                {
                    $defenses[$counter] = $tmp_defense;
                    $all_sector_fighters += $defenses[$counter]['quantity'];
                    $counter++;
                }
            }

            $counter = 0;
            $total_sector_mines = 0;
            $sql = "SELECT * FROM ::prefix::sector_defense WHERE sector_id = :sector_id AND defense_type = 'M'";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $targetlink, \PDO::PARAM_INT);
            $stmt->execute();
            $defenses_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($defenses_present !== false)
            {
                foreach ($defenses_present as $tmp_defenses)
                {
                    $defenses[$counter] = $tmp_defenses;
                    $total_sector_mines += $defenses[$counter]['quantity'];
                    $counter++;
                }
            }

            if ($all_sector_fighters > 0 || $total_sector_mines > 0 || ($all_sector_fighters > 0 && $total_sector_mines > 0)) // Dest link has defenses so lets attack them
            {
                \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "ATTACKING SECTOR DEFENSES $all_sector_fighters fighters and $total_sector_mines mines.");
                $targetfighters = $all_sector_fighters;
                $playerbeams = \Tki\CalcLevels::abstractLevels($playerinfo['beams'], $tkireg);
                if ($playerbeams > $playerinfo['ship_energy'])
                {
                    $playerbeams = $playerinfo['ship_energy'];
                }

                $playerinfo['ship_energy'] = $playerinfo['ship_energy'] - $playerbeams;
                $playershields = \Tki\CalcLevels::abstractLevels($playerinfo['shields'], $tkireg);
                if ($playershields > $playerinfo['ship_energy'])
                {
                    $playershields = $playerinfo['ship_energy'];
                }

                $playertorpnum = round(pow($tkireg->level_factor, $playerinfo['torp_launchers'])) * 2;
                if ($playertorpnum > $playerinfo['torps'])
                {
                    $playertorpnum = $playerinfo['torps'];
                }

                $playertorpdmg = $tkireg->torp_dmg_rate * $playertorpnum;
                $playerarmor = $playerinfo['armor_pts'];
                $playerfighters = $playerinfo['ship_fighters'];
                $totalmines = $total_sector_mines;
                if ($totalmines > 1)
                {
                    $roll = random_int(1, (int) $totalmines);
                }
                else
                {
                    $roll = 1;
                }

                $playerminedeflect = $playerinfo['ship_fighters']; // Kabal keep as many deflectors as fighters

                // Combat - Beams v fighters
                if ($targetfighters > 0 && $playerbeams > 0)
                {
                    if ($playerbeams > round($targetfighters / 2))
                    {
                        $temp = round($targetfighters / 2);
                        $targetfighters = $temp;
                    }
                    else
                    {
                        $targetfighters = $targetfighters - $playerbeams;
                    }
                }

                // Torpedoes v. fighters
                if ($targetfighters > 0 && $playertorpdmg > 0)
                {
                    if ($playertorpdmg > round($targetfighters / 2))
                    {
                        $temp = round($targetfighters / 2);
                        $targetfighters = $temp;
                    }
                }

                // Fighters v. fighters
                if ($playerfighters > 0 && $targetfighters > 0)
                {
                    if ($playerfighters > $targetfighters)
                    {
                        echo $langvars['l_sf_destfightall'];
                        $temptargfighters = 0;
                    }
                    else
                    {
                        $temptargfighters = $targetfighters - $playerfighters;
                    }

                    if ($targetfighters > $playerfighters)
                    {
                        $tempplayfighters = 0;
                    }
                    else
                    {
                        $tempplayfighters = $playerfighters - $targetfighters;
                    }

                    $playerfighters = $tempplayfighters;
                    $targetfighters = $temptargfighters;
                }

                // There are still fighters, so armor v. fighters
                if ($targetfighters > 0)
                {
                    if ($targetfighters > $playerarmor)
                    {
                        $playerarmor = 0;
                    }
                    else
                    {
                        $playerarmor = $playerarmor - $targetfighters;
                    }
                }

                // Get rid of the sector fighters that died
                $fighterslost = $all_sector_fighters - $targetfighters;
                \Tki\Fighters::destroy($pdo_db, $targetlink, $fighterslost);

                // Message the defense owner with what happened
                $langvars['l_sf_sendlog'] = str_replace("[player]", "Kabal $playerinfo[character_name]", $langvars['l_sf_sendlog']);
                $langvars['l_sf_sendlog'] = str_replace("[lost]", (string) $fighterslost, $langvars['l_sf_sendlog']);
                $langvars['l_sf_sendlog'] = str_replace("[sector]", (string) $targetlink, $langvars['l_sf_sendlog']);
                \Tki\SectorDefense::messageDefenseOwner($pdo_db, $targetlink, $langvars['l_sf_sendlog']);

                // Update Kabal after comnbat
                $armor_lost = $playerinfo['armor_pts'] - $playerarmor;
                $fighters_lost = $playerinfo['ship_fighters'] - $playerfighters;
                $energy = $playerinfo['ship_energy'];

                $sql = "UPDATE ::prefix::ships SET ship_energy = :energy, ship_fighters = ship_fighters - :fighters_lost, armor_pts = armor_pts - :armor_lost,torps = torps - :ship_torps WHERE ship_id = :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':energy', $energy, \PDO::PARAM_INT);
                $stmt->bindParam(':ship_fighters', $fighters_lost, \PDO::PARAM_INT);
                $stmt->bindParam(':armor_lost', $armor_lost, \PDO::PARAM_INT);
                $stmt->bindParam(':ship_torps', $playertorpnum, \PDO::PARAM_INT);
                $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                $stmt->execute();
                \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                // Check to see if Kabal is dead
                if ($playerarmor < 1)
                {
                    $langvars['l_sf_sendlog2'] = str_replace("[player]", "Kabal " . $playerinfo['character_name'], $langvars['l_sf_sendlog2']);
                    $langvars['l_sf_sendlog2'] = str_replace("[sector]", (string) $targetlink, $langvars['l_sf_sendlog2']);
                    \Tki\SectorDefense::messageDefenseOwner($pdo_db, $targetlink, $langvars['l_sf_sendlog2']);

                    $bounty = new \Tki\Bounty();
                    $bounty->cancel($pdo_db, $playerinfo['ship_id']);
                    $character_object->kill($pdo_db, $lang, $playerinfo['ship_id'], $tkireg);
                    return;
                }

                // Kabal is still alive, so he hits mines, and logs it
                $langvars['l_chm_hehitminesinsector'] = str_replace("[chm_playerinfo_character_name]", "Kabal " . $playerinfo['character_name'], $langvars['l_chm_hehitminesinsector']);
                $langvars['l_chm_hehitminesinsector'] = str_replace("[chm_roll]", (string) $roll, $langvars['l_chm_hehitminesinsector']);
                $langvars['l_chm_hehitminesinsector'] = str_replace("[chm_sector]", (string) $targetlink, $langvars['l_chm_hehitminesinsector']);
                \Tki\SectorDefense::messageDefenseOwner($pdo_db, $targetlink, $langvars['l_chm_hehitminesinsector']);

                // Deflectors v. mines
                if (!($playerminedeflect >= $roll))
                {
                    $mines_left = $roll - $playerminedeflect;

                    // Shields v. mines
                    if ($playershields >= $mines_left)
                    {
                        $sql = "UPDATE ::prefix::ships SET ship_energy = ship_energy - :mines_left WHERE ship_id = :ship_id";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':mines_left', $mines_left, \PDO::PARAM_INT);
                        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                        $stmt->execute();
                        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                    }
                    else
                    {
                        $mines_left = $mines_left - $playershields;

                        // Armor v. mines
                        if ($playerarmor >= $mines_left)
                        {
                            $sql = "UPDATE ::prefix::ships SET armor_pts = armor_pts - :mines_left, ship_energy=0 WHERE ship_id = :ship_id";
                            $stmt = $pdo_db->prepare($sql);
                            $stmt->bindParam(':mines_left', $mines_left, \PDO::PARAM_INT);
                            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                            $stmt->execute();
                            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                        }
                        else
                        {
                            // Kabal dies, logs the fact that he died
                            $langvars['l_chm_hewasdestroyedbyyourmines'] = str_replace("[chm_playerinfo_character_name]", "Kabal " . $playerinfo['character_name'], $langvars['l_chm_hewasdestroyedbyyourmines']);
                            $langvars['l_chm_hewasdestroyedbyyourmines'] = str_replace("[chm_sector]", (string) $targetlink, $langvars['l_chm_hewasdestroyedbyyourmines']);
                            \Tki\SectorDefense::messageDefenseOwner($pdo_db, $targetlink, $langvars['l_chm_hewasdestroyedbyyourmines']);

                            // Actually kill the Kabal now
                            $bounty = new \Tki\Bounty();
                            $bounty->cancel($pdo_db, $playerinfo['ship_id']);
                            $character_object->kill($pdo_db, $lang, $playerinfo['ship_id'], $tkireg);

                            // Lets get rid of the mines now and return
                            \Tki\Mines::explode($pdo_db, $targetlink, $roll);

                            return;
                        }
                    }
                }

                \Tki\Mines::explode($pdo_db, $targetlink, $roll); // Dispose of the mines now
            }
            else
            {
                // This was called without any sector defenses to attack
                return;
            }
        }
    }
}
