<?php declare(strict_types = 1);
/**
 * scheduler/sched_ibank.php from The Kabal Invasion.
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

// FUTURE: PDO, better debugging, better output formatting
$langvars = Tki\Translate::load($pdo_db, $lang, array('scheduler'));

$exponinter = pow($tkireg->ibank_interest + 1, $multiplier);
$expoloan = pow($tkireg->ibank_loaninterest + 1, $multiplier);

echo "<strong>" . $langvars['l_sched_ibank_title'] . "</strong><p>";

$ibank_result = $db->Execute("UPDATE {$db->prefix}ibank_accounts SET balance = balance * ?, loan = loan * ?", array($exponinter, $expoloan));
Tki\Db::logDbErrors($pdo_db, $ibank_result, __LINE__, __FILE__);
$langvars['l_sched_ibank_note'] = str_replace("[multiplier]", $multiplier, $langvars['l_sched_ibank_note']);
echo $langvars['l_sched_ibank_note'] . "<p>";

$multiplier = 0;
