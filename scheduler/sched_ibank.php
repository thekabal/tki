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
// File: sched_ibank.php

// FUTURE: PDO, better debugging, better output formatting
$langvars = Tki\Translate::load($pdo_db, $lang, array('scheduler'));

$exponinter = pow($tkireg->ibank_interest + 1, $multiplier);
$expoloan = pow($tkireg->ibank_loaninterest + 1, $multiplier);

echo "<strong>" . $langvars['l_sched_ibank_title'] . "</strong><p>";

$ibank_result = $db->Execute("UPDATE {$db->prefix}ibank_accounts SET balance = balance * ?, loan = loan * ?", array($exponinter, $expoloan));
Tki\Db::LogDbErrors($pdo_db, $ibank_result, __LINE__, __FILE__);
$langvars['l_sched_ibank_note'] = str_replace("[multiplier]", $multiplier, $langvars['l_sched_ibank_note']);
echo $langvars['l_sched_ibank_note'] . "<p>";

$multiplier = 0;
