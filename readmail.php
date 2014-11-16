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
// File: readmail.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $langvars, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('readmail', 'common', 'global_includes', 'global_funcs', 'footer', 'planet_report'));
$title = $langvars['l_readm_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

echo "<h1>" . $title . "</h1>\n";

$res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email=?", array($_SESSION['username']));
Tki\Db::logDbErrors($pdo_db, $db, $res, __LINE__, __FILE__);
$playerinfo = $res->fields;

if (!array_key_exists('action', $_GET))
{
    $_GET['action'] = null;
}

if ($_GET['action'] == "delete")
{
    $resx = $db->Execute("DELETE FROM {$db->prefix}messages WHERE ID=? AND recp_id = ?;", array($ID, $playerinfo['ship_id']));
    Tki\Db::logDbErrors($pdo_db, $db, $resx, __LINE__, __FILE__);
}
elseif ($_GET['action'] == "delete_all")
{
    $resx = $db->Execute("DELETE FROM {$db->prefix}messages WHERE recp_id = ?;", array($playerinfo['ship_id']));
    Tki\Db::logDbErrors($pdo_db, $db, $resx, __LINE__, __FILE__);
}

$cur_D = date("Y-m-d");
$cur_T = date("H:i:s");

$res = $db->Execute("SELECT * FROM {$db->prefix}messages WHERE recp_id = ? ORDER BY sent DESC;", array($playerinfo['ship_id']));
Tki\Db::logDbErrors($pdo_db, $db, $res, __LINE__, __FILE__);
?>
<div align="center">
  <table border="0" cellspacing="0" width="70%" bgcolor="silver" cellpadding="0">
    <tr>
      <td width="100%">
        <div align="center">
          <center>
          <table border="0" cellspacing="1" width="100%">
            <tr>
              <td width="100%" bgcolor="black">
                <div align="center">
                  <table border="1" cellspacing="1" width="100%" bgcolor="gray" bordercolorlight="black" bordercolordark="silver">
                    <tr>
                      <td width="75%" align="left"><font color="white" size="2"><strong><?php echo $langvars['l_readm_center']; ?> (<span style='color:#00C0C0;'>Subspace</span>)</strong></font></td>
                      <td width="21%" align="center" nowrap><font color="white" size="2"><?php echo "$cur_D"; ?>&nbsp;<?php echo "$cur_T"; ?></font></td>
                      <td width="4%" align="center" bordercolorlight="black" bordercolordark="gray"><a href="main.php"><img alt="Click here to return to the main menu" src="<?php echo $template->getVariables('template_dir'); ?>/images/close.png" width="16" height="14" border="0"></a></td>
                    </tr>
                  </table>
                </div>
              </td>
            </tr>

<?php
if ($res->EOF)
{
//  echo $langvars['l_readm_nomessage'];
    ?>
            <tr>
              <td width="100%" bgcolor="black" bordercolorlight="black" bordercolordark="silver">
                <div align="center">
                  <table border="1" cellspacing="1" width="100%" bgcolor="white" bordercolorlight="black" bordercolordark="silver">
                    <tr>
                      <td width="100%" align="center" bgcolor="white"><font color="red"><?php echo $langvars['l_readm_nomessage']; ?></font></td>
                    </tr>
                  </table>
                </div>
              </td>
            </tr>
    <?php
}
else
{
    $line_counter = true;
    while (!$res->EOF)
    {
        $msg = $res->fields;
        $result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($msg['sender_id']));
        Tki\Db::logDbErrors($pdo_db, $db, $result, __LINE__, __FILE__);
        $sender = $result->fields;
//      $isAdmin = isAdmin($sender);
        ?>
            <tr>
              <td width="100%" align="center" bgcolor="black" height="4"></td>
            </tr>
            <tr>
              <td width="100%" bgcolor="black" bordercolorlight="black" bordercolordark="silver">
                <div align="center">
                  <table border="0" cellspacing="1" width="100%" bgcolor="gray" cellpadding="0">
                    <tr>
                      <td width="20%" style="text-align:left;"><font color="white" size="2"><strong><?php echo $langvars['l_readm_sender']; ?></strong></td>
                      <td width="55%" style="text-align:left;"><font color="yellow" size="2">
        <?php
        echo "<span style='vertical-align:middle;'>{$sender['character_name']}</span>";
        //if ($isAdmin === true)
        //{
        //    echo "&nbsp;<img style='width:64px; height:16px; border:none; padding:0px; vertical-align:text-bottom;' src='<?php echo $template->getVariables('template_dir'); ?>/images/validated_administrator2.gif' alt='Validated as Admin' />";
        //}
        ?>
        </font></td>
                      <td width="21%" align="center"><font color="white" size="2"><?php echo $msg['sent']; ?></font></td>
                      <td width="4%" align="center" bordercolorlight="black" bordercolordark="gray"><a class="but" href="readmail.php?action=delete&ID=<?php echo $msg['ID']; ?>"><img src="<?php echo $template->getVariables('template_dir'); ?>/images/close.png" width="16" height="14" border="0"></a></td>
                    </tr>
                  </table>
                </div>
              </td>
            </tr>
            <tr>
              <td width="100%" bgcolor="black" bordercolorlight="black" bordercolordark="silver">
                <div align="center">
                  <table border="0" cellspacing="1" width="100%" bgcolor="gray" cellpadding="0">
                    <tr>
                      <td width="20%" style="text-align:left;"><font color="white" size="2"><strong><?php echo $langvars['l_readm_captn']; ?></strong></font></td>
                      <td width="80%" style="text-align:left;"><font color="yellow" size="2"><?php echo $sender['ship_name']; ?></font></td>
                    </tr>
                  </table>
                </div>
              </td>
            </tr>
            <tr>
              <td width="100%" bgcolor="black" bordercolorlight="black" bordercolordark="silver">
                <div align="center">
                  <table border="0" cellspacing="1" width="100%" bgcolor="gray" cellpadding="0">
                    <tr>
                      <td width="20%" style="text-align:left;"><font color="white" size="2"><strong>Subject</strong></font></td>
                      <td width="80%" style="text-align:left;"><strong><font color="yellow" size="2"><?php echo $msg['subject']; ?></font></strong></td>
                    </tr>
                  </table>
                </div>
              </td>
            </tr>
            <tr>
              <td width="100%" bgcolor="black" bordercolorlight="black" bordercolordark="silver">
                <div align="center">
                  <table border="1" cellspacing="1" width="100%" bgcolor="white" bordercolorlight="black" bordercolordark="silver">
                    <tr>
                      <td width="100%" style="text-align:left; vertical-align:text-top;"><font color="black" size="2"><?php echo $msg['message']; ?></font></td>
                    </tr>
                  </table>
                </div>
              </td>
            </tr>
            <tr>
              <td width="100%" align="center" bgcolor="black" bordercolorlight="black" bordercolordark="silver">
                <div align="center">
                  <table border="1" cellspacing="1" width="100%" bgcolor="gray" bordercolorlight="black" bordercolordark="silver" cellpadding="0">
                    <tr>
                      <td width="100%" align="center" valign="middle"><a class="but" href="readmail.php?action=delete&ID=<?php echo $msg['ID']; ?>"><?php echo $langvars['l_readm_del']; ?></a> |
        <a class="but" href="mailto.php?to=<?php echo $sender['character_name']; ?>&subject=<?php echo $msg['subject']; ?>"><?php echo $langvars['l_readm_repl']; ?></a>
                      </td>
                    </tr>
                  </table>
                </div>
              </td>
            </tr>
        <?php
        $res->MoveNext();
    }
}
?>
            <tr>
              <td width="100%" align="center" bgcolor="black" height="4"></td>
            </tr>
            <tr>
              <td width="100%" align="center" bgcolor="#000" height="4">
                <div align="center">
                  <table border="1" cellspacing="1" width="100%" bgcolor="#808080" bordercolorlight="#000" bordercolordark="#C0C0C0" height="8">
                    <tr>
                      <td width="50%"><p align="left"><font color="#fff" size="2">Mail Reader </font></td>
                      <td width="50%"><p align="right"><font color="#fff" size="2"><a class="but" href="readmail.php?action=delete_all">Delete All</a></font></td>
                    </tr>
                  </table>
                </div>
              </td>
            </tr>
          </table>
          </center>
        </div>
      </td>
    </tr>
  </table>
</div>
<br>
<?php
Tki\Text::gotoMain($pdo_db, $lang, $langvars);
Tki\Footer::display($pdo_db, $lang, $tkireg, $template, $langvars);
