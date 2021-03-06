<?php declare(strict_types = 1);
/**
 * create_universe.php from The Kabal Invasion.
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

$index_page = true; // This prevents sessions from being started before DB exists
require_once './common.php';

// Set timelimit to infinite
set_time_limit(0);

$variables = array();

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$swordfish = null;
$swordfish = filter_input(INPUT_POST, 'swordfish', FILTER_SANITIZE_URL);
if (($swordfish === null) || (strlen(trim($swordfish)) === 0))
{
    $swordfish = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$step = null;
$step = (int) filter_input(INPUT_POST, 'step', FILTER_SANITIZE_NUMBER_INT);

if ($step === 0)
{
    $step = false;
}

if ($swordfish === null || $swordfish === false) // If no swordfish password has been entered and/or it did not clear the filter, we are on the first step
{
    $step = "1";
}

if (($swordfish !== false) && ($swordfish != \Tki\SecureConfig::ADMIN_PASS)) // If a swordfish password is not null and it does not match (bad pass), redirect to step 1 (default or 0.php)
{
    $variables['goodpass'] = false;
    include_once 'create_universe/0.php';
}
else // If swordfish is set and matches (good pass)
{
    $variables['goodpass'] = true;

    // Determine current step, next step, and number of steps
    $step_finder = new Tki\BigBang();
    $create_universe_info = $step_finder->findStep('');
    natsort($create_universe_info['files']);
    $loader_file = $create_universe_info['files'][$step];
    $filename = 'create_universe/' . $loader_file;
    if (file_exists($filename))
    {
        include_once $filename;
    }
}
