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
// File: planet_report_ce.php

require_once './common.php';

$login = new Tki\Login();
$login->checkLogin($pdo_db, $lang, $tkireg, $template);

$title = $langvars['l_pr_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('planet_report', 'rsmove', 'common', 'global_includes', 'global_funcs', 'footer', 'news', 'regional'));
echo '<h1>' . $title . '</h1>';

echo '<br>';
echo str_replace('[here]', "<a href='planet_report.php'>" . $langvars['l_here'] . '</a>', $langvars['l_pr_click_return']);
echo '<br>';

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$tpcreds = null;
$tpcreds = $_POST['tpcreds']; // FUTURE: tp creds is an array. Filtering will be tricky.
if (strlen(trim($tpcreds)) === 0)
{
    $tpcreds = false;
}

if ($tpcreds !== null && $tpcreds !== false)
{
    Tki\PlanetReportCE::collectCredits($pdo_db, $db, $langvars, $tpcreds, $tkireg);
}
elseif ($buildp !== null && $builds !== null)
{
    $build_bases = new Tki\Bases();
    $build_bases->buildBase($pdo_db, $langvars, $buildp, $builds, $tkireg);
}
else
{
    Tki\PlanetProduction::productionChange($pdo_db, $db, $langvars, $_POST, $tkireg);
}

echo '<br><br>';
Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
