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
// File: ibank.php

require_once './common.php';

// FUTURE: This should not be hard-coded, but for now, I need to be able to clear the errors
$active_template = 'classic';
Tki\Login::checkLogin($pdo_db, $lang, $langvars, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('ibank', 'common', 'global_includes', 'global_funcs', 'footer', 'news', 'regional'));

$title = $langvars['l_ibank_title'];
$body_class = 'ibank';
Tki\Header::display($pdo_db, $lang, $template, $title, $body_class);

$stmt = $pdo_db->prepare("SELECT * FROM {$pdo_db->prefix}ships WHERE email=:email");
$stmt->bindParam(':email', $_SESSION['username']);
$result = $stmt->execute();
Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);
$playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo_db->prepare("SELECT * FROM {$pdo_db->prefix}ibank_accounts WHERE ship_id=:ship_id");
$stmt->bindParam(':ship_id', $playerinfo['ship_id']);
$result = $stmt->execute();
Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<body class='" . $body_class . "'>";
echo "<center>";
echo '<img src="' . $template->getVariables('template_dir') . '/images/div1.png" alt="" style="width: 600px; height:21px">';
echo '<div style="width:600px; max-width:600px;" class="ibank">';
echo '<table style="width:600px; height:350px;" border="0px">';
echo '<tr><td style="background-image:URL(' . $template->getVariables('template_dir') . '/images/ibankscreen.png); background-repeat:no-repeat;" align="center">';
echo '<table style="width:550px; height:300px;" border="0px">';

if (!$tkireg->allow_ibank)
{
    Bad\Ibank::ibankError($template->getVariables('template_dir'), $langvars, $langvars['l_ibank_malfunction'], "main.php");
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$command = null;
$command = filter_input(INPUT_GET, 'command', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($command)) === 0)
{
    $command = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$amount = null;
$amount = (int) filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_INT);
if (mb_strlen(trim($amount)) === 0)
{
    $amount = false;
}

if ($command == 'login') //main menu
{
    Bad\Ibank::ibankLogin($langvars, $playerinfo, $account);
}
elseif ($command == 'withdraw') //withdraw menu
{
    Bad\Ibank::ibankWithdraw($langvars, $playerinfo, $account);
}
elseif ($command == 'withdraw2') //withdraw operation
{
    Bad\Ibank::ibankWithdraw2($pdo_db, $langvars, $playerinfo, $amount, $account);
}
elseif ($command == 'deposit') //deposit menu
{
    Bad\Ibank::ibankDeposit($pdo_db, $lang, $account, $playerinfo, $langvars);
}
elseif ($command == 'deposit2') //deposit operation
{
    Bad\Ibank::ibankDeposit2($pdo_db, $langvars, $playerinfo, $amount, $account);
}
elseif ($command == 'transfer') //main transfer menu
{
    Bad\Ibank::ibankTransfer($db, $langvars, $playerinfo, $tkireg->ibank_min_turns);
}
elseif ($command == 'transfer2') //specific transfer menu (ship or planet)
{
    Bad\Ibank::ibankTransfer2($db);
}
elseif ($command == 'transfer3') //transfer operation
{
    Bad\Ibank::ibankTransfer3($db);
}
elseif ($command == 'loans') //loans menu
{
    Bad\Ibank::ibankLoans($db, $langvars, $tkireg, $playerinfo);
}
elseif ($command == 'borrow') //borrow operation
{
    Bad\Ibank::ibankBorrow($db, $langvars, $playerinfo, $active_template);
}
elseif ($command == 'repay') //repay operation
{
    Bad\Ibank::ibankRepay($db, $langvars, $playerinfo, $account, $amount, $active_template);
}
elseif ($command == 'consolidate') //consolidate menu
{
    Bad\Ibank::ibankConsolidate($langvars);
}
elseif ($command == 'consolidate2') //consolidate compute
{
    Bad\Ibank::ibankConsolidate2($db, $langvars, $playerinfo);
}
elseif ($command == 'consolidate3') //consolidate operation
{
    Bad\Ibank::ibankConsolidate3($db, $langvars, $playerinfo);
}
else
{
    echo "
    <tr>
        <td width='25%' valign='bottom' align='left'><a href=\"main.php\">" . $langvars['l_ibank_quit'] . "</a></td>
        <td width='50%' style='text-align:left;'>
    <pre style='text-align:left;' class='term'>
  IIIIIIIIII          GGGGGGGGGGGGG    BBBBBBBBBBBBBBBBB
  I::::::::I       GGG::::::::::::G    B::::::::::::::::B
  I::::::::I     GG:::::::::::::::G    B::::::BBBBBB:::::B
  II::::::II    G:::::GGGGGGGG::::G    BB:::::B     B:::::B
    I::::I     G:::::G       GGGGGG      B::::B     B:::::B
    I::::I    G:::::G                    B::::B     B:::::B
    I::::I    G:::::G                    B::::BBBBBB:::::B
    I::::I    G:::::G    GGGGGGGGGG      B:::::::::::::BB
    I::::I    G:::::G    G::::::::G      B::::BBBBBB:::::B
    I::::I    G:::::G    GGGGG::::G      B::::B     B:::::B
    I::::I    G:::::G        G::::G      B::::B     B:::::B
    I::::I     G:::::G       G::::G      B::::B     B:::::B
  II::::::II    G:::::GGGGGGGG::::G    BB:::::BBBBBB::::::B
  I::::::::I     GG:::::::::::::::G    B:::::::::::::::::B
  I::::::::I       GGG::::::GGG:::G    B::::::::::::::::B
  IIIIIIIIII          GGGGGG   GGGG    BBBBBBBBBBBBBBBBB
    </pre>
    <center class='term'>";
    echo $langvars['l_ibank_title'];
    echo "(tm)<br>";
    echo $langvars['l_ibank_humor'];
    echo "<br>&nbsp;</center></td>
            <td width='25%' valign='bottom' align='right'><a href=\"ibank.php?command=login\">" . $langvars['l_ibank_login'] . "</a></td>";
}

echo "</table></td></tr></table></div>";
echo '<img src="' . $template->getVariables('template_dir') . '/images/div2.png" alt="" style="width: 600px; height:21px">';
echo '</center>';

Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
