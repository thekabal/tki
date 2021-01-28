<?php declare(strict_types = 1);
/**
 * classes/Reg.php from The Kabal Invasion.
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

class Reg
{
    private string $game_name = '';
    private bool $game_closed = false;
    private bool $account_creation_closed = false;
    private string $release_version = '';
    private string $admin_mail = '';
    private string $email_server = '';
    private int $turns_per_tick = 0;
    private int $sched_ticks = 0;
    private int $sched_turns = 0;
    private int $sched_ports = 0;
    private int $sched_planets = 0;
    private int $sched_ibank = 0;
    private int $sched_ranking = 0;
    private int $sched_news = 0;
    private int $sched_degrade = 0;
    private int $sched_apocalypse = 0;
    private int $sched_thegovernor = 0;
    private int $doomsday_value = 0;
    private int $max_turns = 0;
    private bool $allow_fullscan = true;
    private bool $allow_navcomp = true;
    private bool $allow_ibank = true;
    private bool $allow_genesis_destroy = true;
    private bool $allow_sofa = true;
    private bool $allow_ksm = true;
    private float $ibank_interest = 0;
    private float $ibank_paymentfee = 0;
    private float $ibank_loaninterest = 0;
    private float $ibank_loanfactor = 0;
    private float $ibank_loanlimit = 0;
    private float $ibank_min_turns = 0;
    private float $ibank_svalue = 0;
    private float $ibank_trate = 0;
    private float $ibank_lrate = 0;
    private float $ibank_tconsolidate = 0;
    private float $default_prod_ore = 0;
    private float $default_prod_organics = 0;
    private float $default_prod_goods = 0;
    private float $default_prod_energy = 0;
    private float $default_prod_fighters = 0;
    private float $default_prod_torp = 0;
    private int $ore_price = 0;
    private int $ore_delta = 0;
    private int $ore_rate = 0;
    private float $ore_prate = 0;
    private int $ore_limit = 0;
    private int $organics_price = 0;
    private int $organics_delta = 0;
    private int $organics_rate = 0;
    private float $organics_prate = 0;
    private int $organics_limit = 0;
    private int $goods_price = 0;
    private int $goods_delta = 0;
    private int $goods_rate = 0;
    private float $goods_prate = 0;
    private int $goods_limit = 0;
    private int $energy_price = 0;
    private int $energy_delta = 0;
    private int $energy_rate = 0;
    private float $energy_prate = 0;
    private int $energy_limit = 0;
    private int $dev_genesis_price = 0;
    private int $dev_beacon_price = 0;
    private int $dev_emerwarp_price = 0;
    private int $dev_warpedit_price = 0;
    private int $dev_minedeflector_price = 0;
    private int $dev_escapepod_price = 0;
    private int $dev_fuelscoop_price = 0;
    private int $dev_lssd_price = 0;
    private int $armor_price = 0;
    private int $fighter_price = 0;
    private int $torpedo_price = 0;
    private int $colonist_price = 0;
    private int $torp_dmg_rate = 0;
    private int $max_emerwap = 0;
    private float $fighter_prate  = 0;
    private float $torpedo_prate  = 0;
    private float $credits_prate  = 0;
    private float $colonist_production_rate  = 0;
    private float $colonist_reproduction_rate  = 0;
    private float $interest_rate  = 0;
    private int $base_ore  = 0;
    private int $base_goods  = 0;
    private int $base_organics  = 0;
    private int $base_credits  = 0;
    private string $color_header = '';
    private string $color_line1 = '';
    private string $color_line2 = '';
    private bool $newbie_nice = true;
    private int $newbie_hull = 0;
    private int $newbie_engines = 0;
    private int $newbie_power = 0;
    private int $newbie_computer = 0;
    private int $newbie_sensors = 0;
    private int $newbie_armor = 0;
    private int $newbie_shields = 0;
    private int $newbie_beams = 0;
    private int $newbie_torp_launchers = 0;
    private int $newbie_cloak = 0;
    private int $upgrade_cost = 0;
    private int $upgrade_factor = 0;
    private float $level_factor = 0;
    private int $inventory_factor = 0;
    private float $max_bountyvalue = 0;
    private float $bounty_ratio = 0;
    private int $bounty_minturns = 0;
    private int $fullscan_cost = 0;
    private int $scan_error_factor = 0;
    private int $kabal_unemployment = 0;
    private int $kabal_aggression = 0;
    private int $kabal_planets = 0;
    private int $mine_hullsize = 0;
    private int $max_ewdhullsize = 0;
    private int $max_sectors = 0;
    private int $max_links = 0;
    private int $universe_size = 0;
    private int $max_fed_hull = 0;
    private int $max_ranks = 0;
    private float $rating_combat_factor = 0;
    private int $base_defense = 0;
    private int $colonist_limit = 0;
    private float $organics_consumption = 0;
    private float $starvation_death_rate = 0;
    private int $max_planets_sector = 0;
    private int $max_traderoutes_player = 0;
    private int $min_bases_to_own = 0;
    private bool $team_planet_transfers = false;
    private int $min_value_capture = 0;
    private float $defense_degrade_rate = 0;
    private float $energy_per_fighter = 0;
    private float $space_plague_kills = 0;
    private int $max_credits_without_base = 0;
    private int $port_regenerate = 0;
    private bool $footer_show_debug = false;
    private bool $sched_planet_valid_credits = false;
    private int $max_upgrades_devices = 0;
    private int $max_emerwarp = 0;
    private int $max_genesis = 0;
    private int $max_beacons = 0;
    private int $max_warpedit = 0;
    private bool $bounty_all_special = false;
    private string $link_forums = '';
    private string $admin_name = '';
    private string $admin_ship_name = '';
    private string $admin_zone_name = '';
    private bool $enable_gravatars = false;
    private string $default_template = '';
    private int $max_presets = 0;
    // private string $default_lang = '';

    protected array $vars = array();

    public function __construct(\PDO $pdo_db)
    {
        if ($this->loadFromDb($pdo_db) === false)
        {
            $this->loadFromIni();
        }
    }

    public function loadFromDb(\PDO $pdo_db): ?bool
    {
        // Get the config_values from the DB - This is a pdo operation
        $stmt = "SELECT name,value,type FROM ::prefix::gameconfig";
        $result = $pdo_db->query($stmt);
        Db::logDbErrors($pdo_db, $stmt, __LINE__, __FILE__);

        if ($result !== false) // Result is "false" during no-db status (fresh install or CU after step4/stop)
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

                return null;
            }
        }

        return false;
    }

    public function loadFromIni(): void
    {
        // Slurp in config variables from the ini file directly
        // This is hard-coded for now, but when we get multiple game support, we may need to change this.
        $ini_keys = parse_ini_file('config/classic_config.ini', true, INI_SCANNER_TYPED);
        if (is_array($ini_keys))
        {
            foreach ($ini_keys as $config_line)
            {
                foreach ($config_line as $config_key => $config_value)
                {
                    $this->$config_key = $config_value;
                }
            }
        }
    }

    /**
     * @param mixed $value
     */
    public function __set(string $name, $value): void
    {
        $this->vars[$name] = $value;
    }

    /**
     * @return mixed
     */
    public function &__get(string $name)
    {
        if (!array_key_exists($name, $this->vars)) // When the key *does not* exist, return "null".
        {
            $this->vars[$name] = null;
        }

        return $this->vars[$name];
    }
}
