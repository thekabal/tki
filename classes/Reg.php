<?php declare(strict_types = 1);
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
// File: classes/Reg.php

    /**
     * @property mixed account_creation_closed
     * @property mixed admin_mail
     * @property mixed admin_name
     * @property mixed admin_ship_name
     * @property mixed admin_zone_name
     * @property mixed allow_fullscan
     * @property mixed allow_genesis_destroy
     * @property mixed allow_ibank
     * @property mixed allow_ksm
     * @property mixed allow_navcomp
     * @property mixed allow_sofa
     * @property mixed armor_price
     * @property mixed base_credits
     * @property mixed base_defense
     * @property mixed base_goods
     * @property mixed base_ore
     * @property mixed base_organics
     * @property mixed bounty_all_special
     * @property mixed bounty_minturns
     * @property mixed bounty_ratio
     * @property mixed colonist_limit
     * @property mixed colonist_price
     * @property mixed colonist_production_rate
     * @property mixed colonist_reproduction_rate
     * @property mixed color_header
     * @property mixed color_line1
     * @property mixed color_line2
     * @property mixed credits_prate
     * @property mixed default_lang
     * @property mixed default_prod_energy
     * @property mixed default_prod_fighters
     * @property mixed default_prod_goods
     * @property mixed default_prod_ore
     * @property mixed default_prod_organics
     * @property mixed default_prod_torp
     * @property mixed default_template
     * @property mixed defense_degrade_rate
     * @property mixed dev_beacon_price
     * @property mixed dev_emerwarp_price
     * @property mixed dev_escapepod_price
     * @property mixed dev_fuelscoop_price
     * @property mixed dev_genesis_price
     * @property mixed dev_lssd_price
     * @property mixed dev_minedeflector_price
     * @property mixed dev_warpedit_price
     * @property mixed doomsday_value
     * @property mixed email_server
     * @property mixed enable_gravatars
     * @property mixed energy_delta
     * @property mixed energy_limit
     * @property mixed energy_per_fighter
     * @property mixed energy_prate
     * @property mixed energy_price
     * @property mixed energy_rate
     * @property mixed fighter_prate
     * @property mixed fighter_price
     * @property mixed footer_show_debug
     * @property mixed fullscan_cost
     * @property mixed game_closed
     * @property mixed game_name
     * @property mixed goods_delta
     * @property mixed goods_limit
     * @property mixed goods_prate
     * @property mixed goods_price
     * @property mixed goods_rate
     * @property mixed ibank_interest
     * @property mixed ibank_loanfactor
     * @property mixed ibank_loaninterest
     * @property mixed ibank_loanlimit
     * @property mixed ibank_lrate
     * @property mixed ibank_min_turns
     * @property mixed ibank_paymentfee
     * @property mixed ibank_svalue
     * @property mixed ibank_tconsolidate
     * @property mixed ibank_trate
     * @property mixed interest_rate
     * @property mixed inventory_factor
     * @property mixed level_factor
     * @property mixed link_forums
     * @property mixed max_beacons
     * @property mixed max_bountyvalue
     * @property mixed max_credits_without_base
     * @property mixed max_emerwarp
     * @property mixed max_emerwarp
     * @property mixed max_ewdhullsize
     * @property mixed max_fed_hull
     * @property mixed max_genesis
     * @property mixed max_links
     * @property mixed max_planets_sector
     * @property mixed max_presets
     * @property mixed max_ranks
     * @property mixed max_sectors
     * @property mixed max_traderoutes_player
     * @property mixed max_turns
     * @property mixed max_upgrades_devices
     * @property mixed max_warpedit
     * @property mixed min_bases_to_own
     * @property mixed mine_hullsize
     * @property mixed min_value_capture
     * @property mixed newbie_armor
     * @property mixed newbie_beams
     * @property mixed newbie_cloak
     * @property mixed newbie_computer
     * @property mixed newbie_engines
     * @property mixed newbie_hull
     * @property mixed newbie_nice
     * @property mixed newbie_power
     * @property mixed newbie_sensors
     * @property mixed newbie_shields
     * @property mixed newbie_torp_launchers
     * @property mixed ore_delta
     * @property mixed ore_limit
     * @property mixed ore_prate
     * @property mixed ore_price
     * @property mixed ore_rate
     * @property mixed organics_consumption
     * @property mixed organics_delta
     * @property mixed organics_limit
     * @property mixed organics_prate
     * @property mixed organics_price
     * @property mixed organics_rate
     * @property mixed port_regenrate
     * @property mixed rating_combat_factor
     * @property mixed release_version
     * @property mixed scan_error_factor
     * @property mixed sched_apocalypse
     * @property mixed sched_degrade
     * @property mixed sched_ibank
     * @property mixed sched_news
     * @property mixed sched_planets
     * @property mixed sched_planet_valid_credits
     * @property mixed sched_ports
     * @property mixed sched_ranking
     * @property mixed sched_thegovernor
     * @property mixed sched_ticks
     * @property mixed sched_turns
     * @property mixed space_plague_kills
     * @property mixed starvation_death_rate
     * @property mixed team_planet_transfers
     * @property mixed tkitimer
     * @property mixed torp_dmg_rate
     * @property mixed torpedo_prate
     * @property mixed torpedo_price
     * @property mixed turns_per_tick
     * @property mixed universe_size
     * @property mixed upgrade_cost
     * @property mixed upgrade_factor
     * @property mixed kabal_aggression
     * @property mixed kabal_planets
     * @property mixed kabal_unemployment
     */

namespace Tki;

class Reg
{
    /**
     * @var array
    */
    public $vars = array();

    public function __construct(\PDO $pdo_db)
    {
        // Get the config_values from the DB - This is a pdo operation
        $stmt = "SELECT name,value,type FROM ::prefix::gameconfig";
        $result = $pdo_db->query($stmt);
        Db::logDbErrors($pdo_db, $stmt, __LINE__, __FILE__);

        if ($result !== false) // If the database is not live, this will give false, and db calls will fail silently
        {
            $big_array = $result->fetchAll();
            Db::logDbErrors($pdo_db, 'fetchAll from gameconfig', __LINE__, __FILE__);
            if (!empty($big_array))
            {
                foreach ($big_array as $row)
                {
                    settype($row['value'], $row['type']);
                    $this->vars[$row['name']] = $row['value'];
                }
            }
            else
            {
                // Slurp in config variables from the ini file directly
                // This is hard-coded for now, but when we get multiple game support, we may need to change this.
                $ini_file = 'config/classic_config.ini';
                $ini_keys = parse_ini_file($ini_file, true);
                if ($ini_keys !== false)
                {
                    foreach ($ini_keys as $config_category => $config_line)
                    {
                        foreach ($config_line as $config_key => $config_value)
                        {
                            $this->$config_key = $config_value;
                        }
                    }
                }
            }
        }
        else
        {
            // Slurp in config variables from the ini file directly
            // This is hard-coded for now, but when we get multiple game support, we may need to change this.
            $ini_file = 'config/classic_config.ini';
            $ini_keys = parse_ini_file($ini_file, true);
            if ($ini_keys !== false)
            {
                foreach ($ini_keys as $config_category => $config_line)
                {
                    foreach ($config_line as $config_key => $config_value)
                    {
                        $this->$config_key = $config_value;
                    }
                }
            }
        }
    }

    /**
     * @param mixed $value
     */
    public function __set(string $key, $value): void
    {
        $this->vars[$key] = $value;
    }

    /**
     * @return mixed
     */
    public function &__get(string $key)
    {
        if (array_key_exists($key, $this->vars))
        {
            return $this->vars[$key];
        }
        else
        {
            return null;
        }
    }
}
