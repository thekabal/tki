<?php declare(strict_types = 1);
/**
 * ibank.php from The Kabal Invasion.
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

require_once './common.php';
$login = new Tki\Login();
$login->checkLogin($pdo_db, $lang, $tkireg, $tkitimer, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'footer',
                                'ibank', 'insignias', 'news', 'regional',
                                'universal'));
$title = $langvars['l_ibank_title'];
$body_class = 'ibank';

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title, $body_class);

$players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

$ibank_gateway = new Tki\Ibank\IbankGateway($pdo_db);
$bank_account = $ibank_gateway->selectIbankAccount($playerinfo['ship_id']);

echo "<body class='" . $body_class . "'>";
echo "<center>";
echo '<img src="' . $template->getVariables('template_dir') . '/images/div1.png" alt="" style="width: 600px; height:21px">';
echo '<div style="width:600px; max-width:600px;" class="ibank">';
echo '<table style="width:600px; height:350px;" border="0px">';
echo '<tr><td style="background-image:URL(' . $template->getVariables('template_dir') . '/images/ibankscreen.png); background-repeat:no-repeat;" align="center">';
echo '<table style="width:550px; height:300px;" border="0px">';

if (!$tkireg->allow_ibank)
{
    Tki\Ibank::ibankError($pdo_db, $langvars['l_ibank_malfunction'], "main.php", $lang, $tkireg, $tkitimer, $template);
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$command = null;
$command = filter_input(INPUT_GET, 'command', FILTER_SANITIZE_STRING);
if (($command === null) || (strlen(trim($command)) === 0))
{
    $command = false;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$amount = null;
$amount = (int) filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_INT);
if ($amount === 0)
{
    $amount = 0;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$dplanet_id = null;
$dplanet_id = (int) filter_input(INPUT_POST, 'dplanet_id', FILTER_SANITIZE_NUMBER_INT);
if ($dplanet_id === 0)
{
    $dplanet_id = 0;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$minimum = (int) filter_input(INPUT_POST, 'minimum', FILTER_SANITIZE_NUMBER_INT);

if ($minimum === 0)
{
    $minimum = 0;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$maximum = (int) filter_input(INPUT_POST, 'maximum', FILTER_SANITIZE_NUMBER_INT);
if ($maximum === 0)
{
    $maximum = 0;
}

if ($command == 'login') // Main menu
{
    Tki\Ibank::ibankLogin($langvars, $playerinfo, $bank_account);
}
elseif ($command == 'withdraw') // Withdraw menu
{
    Tki\IbankWithdraw::before($langvars, $bank_account);
}
elseif ($command == 'withdraw2') // Withdraw operation
{
    Tki\IbankWithdraw::after($pdo_db, $lang, $playerinfo, $amount, $bank_account, $tkireg, $tkitimer, $template);
}
elseif ($command == 'deposit') // Deposit menu
{
    Tki\IbankDeposit::before($pdo_db, $lang, $bank_account, $playerinfo);
}
elseif ($command == 'deposit2') // Deposit operation
{
    Tki\IbankDeposit::after($pdo_db, $lang, $playerinfo, $amount, $bank_account, $tkireg, $tkitimer, $template);
}
elseif ($command == 'transfer') // Main transfer menu
{
    Tki\IbankTransferMain::main($pdo_db, $langvars, $playerinfo, $tkireg);
}
elseif ($command == 'transfer2') // Specific transfer menu (ship or planet)
{
    Tki\IbankTransferSpecific::specific($pdo_db, $lang, $langvars, $tkireg, $tkitimer, $playerinfo, $ship_id, $splanet_id, $dplanet_id, $template);
}
elseif ($command == 'transfer3') // Transfer operation
{
    Tki\IbankTransferFinal::final($pdo_db, $lang, $playerinfo, $ship_id, $splanet_id, $dplanet_id, $amount, $tkireg, $tkitimer, $template);
}
elseif ($command == 'loans') // Loans menu
{
    Tki\Ibank::ibankLoans($pdo_db, $langvars, $tkireg, $playerinfo, $bank_account);
}
elseif ($command == 'borrow') // Borrow operation
{
    Tki\Ibank::ibankBorrow($pdo_db, $lang, $tkireg, $tkitimer, $playerinfo, $bank_account, $amount, $template);
}
elseif ($command == 'repay') // Repay operation
{
    Tki\Ibank::ibankRepay($pdo_db, $lang, $playerinfo, $bank_account, $amount, $tkireg, $tkitimer, $template);
}
elseif ($command == 'consolidate') // Consolidate menu
{
    Tki\IbankConsolidate::before($pdo_db, $lang, $tkireg, $dplanet_id);
}
elseif ($command == 'consolidate2') // Consolidate compute
{
    Tki\IbankConsolidate::after($pdo_db, $lang, $langvars, $playerinfo, $tkireg, $tkitimer, $dplanet_id, $minimum, $maximum, $template);
}
elseif ($command == 'consolidate3') // Consolidate operation
{
    Tki\IbankConsolidate::third($pdo_db, $lang, $playerinfo, $tkireg, $tkitimer, $dplanet_id, $minimum, $maximum, $template);
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


$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
