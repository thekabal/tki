<?php declare(strict_types = 1);
/**
 * news.php from The Kabal Invasion.
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

$link = null;

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'global_includes',
                                'global_funcs', 'combat', 'footer', 'news'));
$title = $langvars['l_news_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

// Default to today's date in case it isn't supplied
$startdate = date('Y/m/d');
if (array_key_exists('startdate', $_GET) && ($_GET['startdate'] !== null))
{
    // The date was supplied so use it
    $startdate = $_GET['startdate'];
}

// Check and validate the date.
$validformat = preg_match('/([0-9]{4})\/([0-9]{2})\/([0-9]{2})$/', $startdate, $regs);
if ($validformat != 1 || checkdate((int) $regs[2], (int) $regs[3], (int) $regs[1]) === false)
{
    // The date wasn't supplied so use today's date
    $startdate = date('Y/m/d');
}

$previousday = Tki\News::previousDay($startdate);
$nextday = Tki\News::nextDay($startdate);

echo "<table width=\"73%\" border=\"0\" cellspacing=\"2\" cellpadding=\"2\">\n";
echo "  <tr>\n";
echo "    <td height=\"73\" width=\"27%\"><img src=\"" . $template->getVariables('template_dir') . "/images/bnnhead.png\" width=\"312\" height=\"123\" alt=\"The TKI Network\"></td>\n";
echo "    <td height=\"73\" width=\"73%\" bgcolor=\"#000\" valign=\"bottom\" align=\"right\">\n";
echo "      <p><font size=\"-1\">" . $langvars['l_news_info_1'] . "<br>" . $langvars['l_news_info_2'] . "<br>" . $langvars['l_news_info_3'] . "<br>" . $langvars['l_news_info_4'] . "<br>" . $langvars['l_news_info_5'] . "<br></font></p>\n";
echo "      <p>" . $langvars['l_news_for'] . " " . htmlentities($startdate, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</p>\n";
echo "    </td>\n";
echo "  </tr>\n";
echo "  <tr>\n";
echo "    <td height=\"22\" width=\"27%\" bgcolor=\"#00001A\">&nbsp;</td>\n";
echo "    <td height=\"22\" width=\"73%\" bgcolor=\"#00001A\" align=\"right\"><a href=\"news.php?startdate={$previousday}\">" . $langvars['l_news_prev'] . "</a> - <a href=\"news.php?startdate={$nextday}\">" . $langvars['l_news_next'] . "</a></td>\n";
echo "  </tr>\n";

// SQL call that selects all of the news items between the start date beginning of day, and the end of day.
$news_gateway = new \Tki\News\NewsGateway($pdo_db); // Build a scheduler gateway object to handle the SQL calls
$row = $news_gateway->selectNewsByDay($startdate);

$news_ticker = array();
if (($row !== null) && (count($row) == 0))
{
    // Nope none found.
    echo "  <tr>\n";
    echo "    <td bgcolor=\"#00001A\" align=\"center\">" . $langvars['l_news_flash'] . "</td>\n";
    echo "    <td bgcolor=\"#00001A\" align=\"right\">" . $langvars['l_news_none'] . "</td>\n";
    echo "  </tr>\n";
}
else
{
    foreach ($row as $item)
    {
        echo "  <tr>\n";
        echo "    <td bgcolor=\"#003\" align=\"center\" style=\"vertical-align:text-top;\">" . $item['headline'] . "</td>\n";
        echo "    <td bgcolor=\"#003\" style=\"vertical-align:text-top;\"><p align=\"justify\">" . $item['newstext'] . "</p><br></td>\n";
        echo "  </tr>\n";
    }
}

echo "</table>\n";
echo "<div style=\"height:16px;\"></div>\n";

if (empty($_SESSION['username']))
{
    echo str_replace('[here]', "<a href='index.php" . $link . "'>" . $langvars['l_here'] . '</a>', $langvars['l_global_mlogin']);
}
else
{
    echo str_replace('[here]', "<a href='main.php" . $link . "'>" . $langvars['l_here'] . '</a>', $langvars['l_global_mmenu']);
}

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
