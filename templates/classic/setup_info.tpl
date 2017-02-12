{*
    The Kabal Invasion - A web-based 4X space game
    Copyright © 2014 The Kabal Invasion development team, Ron Harwood, and the BNT development team.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

    File: setup_info.tpl
*}

{if !isset($variables['body_class'])}
{$variables['body_class'] = "tki"}
{/if}
  <body class="{$variables['body_class']}">
<div class="wrapper">

<div align="center">
<table style="border:0px; border-spacing:0px; font-size:100%; line-height:1.125em;">
  <tr>
    <td><h1>Setup Information System</h1></td>
  </tr>
  <tr>
    <td style="font-color:#fff"><strong>This version of setup info has been written for TKI v0.80</strong></td>
  </tr>
</table>
</div><br>
<br>
<p style="font-size:0.875em; font-color:#ffff00">
<i>This page is intended to help resolve problems in setting up The Kabal Invasion.</i><br>
<i>It contains diagnostic information to help you diagnose any issues.</i></p>
<p style="font-size:0.875em; font-color:#fff">To display all errors, create a file named "dev" in your TKI directory.<br>
If you get any errors or incorrect info returned then save as html and submit it to the github project page.<br>
This information will help us to help you much faster and will help improve our script.<br></p>
<br>
<div style='width:90%; margin:auto; height:1px; background-color:#808080;'></div>
<br>
<div style="text-align:center">
  <center>
  <table style="border:0px; border-spacing:1px; font-size:0.75em" width="80%" bgcolor="#000">
    <tr>
      <td width="100%" colspan="3" align="center" bgcolor="#9999cc">
        <p align="center"><strong><font color="#000">Server Software/Operating System</font></strong></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">System</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['system']}</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Remote Address</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['remote_addr']}</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Server Address</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['server_addr']}</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">PHP MySQLi Module test</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">
{if ($variables['php_module_mysqli'])}
<font color='green'>Installed</font>
{else}
<font color='#ff0000'>Not installed - Please install the php_mysqli module.</font>
{/if}
</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">PHP PDO Module test</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">
{if ($variables['php_module_pdo'])}
<font color='green'>Installed</font>
{else}
<font color='#ff0000'>Not installed - please install the php_pdo module.</font>
{/if}
</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Database - Adodb path test</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">
{if ($variables['adodb_path_test'])}
<font color='green'>Success - Adodb is in the correct location</font>
{else}
<font color='#ff0000'>Failed! - Adodb is not installed in the correct location - please install it at vendor/adodb/adodb-php/</font>
{/if}
</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Database - Adodb connection test</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">
{if ($variables['adodb_conn_test'])}
<font color='green'>Success - Adodb can connect to the database</font>
{else}
<font color='red'>Failed! - Adodb cannot connect to the database: {$variables['adodb_conn_err']}</font>
{/if}
</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Database - PDO connection test</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">
{if ($variables['pdo_conn_test'])}
<font color='green'>Success - PDO can connect to the database</font>
{else}
<font color='red'>Failed! - PDO cannot connect to the database: {$variables['pdo_conn_err']}</font>
{/if}
</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Cookie Test</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">
{if ($variables['cookie_test'])}
<font color="green">Passed - Cookies are set correctly</font>
{else}
<font color="red">Failed! - Cookies cannot be set. Please report this issue to <a href="https://github.com/thekabal/tki/">The TKI github page</a></font>
{/if}
</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Smarty path Test</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">
{if ($variables['smarty_path_test'])}
<font color="green">Passed - Smarty is in the correct location</font>
{else}
<font color="red">Failed! - Smarty is not installed in the correct location - please install it at vendor/smarty/smarty/</font>
{/if}
</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Smarty overall tests</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">
{if ($variables['smarty_test'])}
<font color="green">Passed - Smarty is installed correctly</font>
{else}
<font color="red">{$variables['smarty_test']}Failed! - Please review the following items for specific failures: {$variables['smarty_test_err']}</font>
{/if}
</font></td>
    </tr>
    <tr>
      <td style="background-color:#9999cc; width:75%; height:4px; padding:0px;" colspan="3"></td>
    </tr>
  </table>
  </center>
</div>

<br>
<div style="text-align:center">
  <center>
  <table style="border:0px; border-spacing:1px; font-size:0.75em" width="80%" bgcolor="#000">
    <tr>
      <td width="100%" colspan="3" align="center" bgcolor="#9999cc">
        <p align="center"><strong><font color="#000">Software Versions</font></strong></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">zend_version</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['zend_version']}</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">server_type</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['server_type']}</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">server_version</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['server_version']}</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">php_version</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['php_version']}</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">php_sapi_name</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['php_sapi_name']}</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">MySQL Server Version</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['pdo_server_ver']}</font></td>
    </tr>
    <tr>
      <td style="background-color:#9999cc; width:75%; height:4px; padding:0px;" colspan="3"></td>
    </tr>
  </table>
  </center>
</div>
<br>
<br>
<div style="text-align:center">
  <center>
  <table style="border:0px; border-spacing:1px; font-size:0.75em" width="80%" bgcolor="#000">
    <tr>
      <td width="100%" colspan="3" align="center" bgcolor="#9999cc"><p align="center"><strong><font color="#000">Path / Domain settings</font></strong></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Game path</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000"><strong>{$variables['game_path']}</strong></font></td>
    </tr>
    <tr>
      <td style="background-color:#9999cc; width:75%; height:1px; padding:0px;" colspan="3"></td>
    </tr>
    <tr>
      <td bgcolor="#C0C0C0" width="100%" align="left" colspan="3"><font color="#000">This information is auto configured in classes/SetPaths.php</font></td>
    </tr>
    <tr>
      <td style="background-color:#9999cc; width:75%; height:1px; padding:0px;" colspan="3"></td>
    </tr>
    <tr>
      <td style="background-color:#9999cc; width:75%; height:4px; padding:0px;" colspan="3"></td>
    </tr>
  </table>
  </center>
</div>
<br>
<br>
<div style="text-align:center">
  <center>
  <table style="border:0px; border-spacing:1px; font-size:0.75em" width="80%" bgcolor="#000">
    <tr>
      <td width="100%" colspan="2" align="center" bgcolor="#9999cc">
        <p align="center"><strong><font color="#000">Environment Variables</font></strong></td>
    </tr>
    {section name=envvar loop=$variables['env_vars']}
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">{$variables['env_vars'][envvar].name}</font></td>
      <td width="75%"  bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['env_vars'][envvar].value}</font></td>
    </tr>
    {/section}
    <tr>
      <td style="background-color:#9999cc; width:75%; height:4px; padding:0px;" colspan="2"></td>
    </tr>
  </table>
  </center>
</div>
<br>
<br>
<div style="text-align:center">
  <center>
  <table style="border:0px; border-spacing:1px; font-size:0.75em" width="80%" bgcolor="#000">
    <tr>
      <td width="100%" colspan="3" align="center" bgcolor="#9999cc">
        <p align="center"><strong><font color="#000">Current Config Information</font></strong></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Release Version</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['release_version']}</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Game Name</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">Default Game Name</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Database Type</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['db_type']}</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Database Server Address</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['db_addr']}</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Database Name</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['db_name']}</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Table Prefix</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['db_prefix']}</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Admin Name</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['admin_name']}</font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Admin Email</font></td>
      <td width="75%" colspan="2" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">{$variables['admin_email']}</font></td>
    </tr>
    <tr>
      <td style="background-color:#9999cc; width:75%; height:4px; padding:0px;" colspan="3"></td>
    </tr>
  </table>
  </center>
</div>
<br>
<br>
<div style="text-align:center">
  <center>
  <table style="border:0px; border-spacing:1px; font-size:0.75em" width="80%" bgcolor="#000">
    <tr>
      <td width="100%" colspan="3" align="center" bgcolor="#9999cc">
        <p align="center"><strong><font color="#000">Scheduler Information</font></strong></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Scheduler Ticks</font></td>
      <td width="65%" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">The rate every update happens</font></td>
      <td width="10%" bgcolor="#ccccff" align="center" valign="top"><font color="#000"><strong>{$variables['sched_ticks']} Minutes</strong></font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Scheduler Turns</font></td>
      <td width="65%" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">Turns will happen every</font></td>
      <td width="10%" bgcolor="#ccccff" align="center" valign="top"><font color="#000"><strong>{$variables['sched_turns']} Minutes</strong></font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Scheduler Ports</font></td>
      <td width="65%" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">Ports will regenerate every</font></td>
      <td width="10%" bgcolor="#ccccff" align="center" valign="top"><font color="#000"><strong>{$variables['sched_ports']} Minutes</strong></font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Scheduler Planets</font></td>
      <td width="65%" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">Planets will generate production every</font></td>
      <td width="10%" bgcolor="#ccccff" align="center" valign="top"><font color="#000"><strong>{$variables['sched_planets']} Minutes</strong></font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Scheduler IBANK</font></td>
      <td width="65%" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">Interests on IBANK accounts will be accumulated every</font></td>
      <td width="10%" bgcolor="#ccccff" align="center" valign="top"><font color="#000"><strong>{$variables['sched_ibank']} Minutes</strong></font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Scheduler Rankings</font></td>
      <td width="65%" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">Rankings will be generated every</font></td>
      <td width="10%" bgcolor="#ccccff" align="center" valign="top"><font color="#000"><strong>{$variables['sched_ranking']} Minutes</strong></font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Scheduler News</font></td>
      <td width="65%" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">News will be generated every</font></td>
      <td width="10%" bgcolor="#ccccff" align="center" valign="top"><font color="#000"><strong>{$variables['sched_news']} Minutes</strong></font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Scheduler Rate</font></td>
      <td width="65%" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">Sector Defenses will degrade every</font></td>
      <td width="10%" bgcolor="#ccccff" align="center" valign="top"><font color="#000"><strong>{$variables['sched_degrade']} Minutes</strong></font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Scheduler Apocalypse</font></td>
      <td width="65%" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">The planetary apocalypse will occur every</font></td>
      <td width="10%" bgcolor="#ccccff" align="center" valign="top"><font color="#000"><strong>{$variables['sched_apocalypse']} Minutes</strong></font></td>
    </tr>
    <tr>
      <td width="25%" bgcolor="#ccccff" align="left" valign="top"><font color="#000">Scheduler The Governor</font></td>
      <td width="65%" bgcolor="#C0C0C0" align="left" valign="top"><font color="#000">The Governor will check every</font></td>
      <td width="10%" bgcolor="#ccccff" align="center" valign="top"><font color="#000"><strong>{$variables['sched_thegovernor']} Minutes</strong></font></td>
    </tr>
    <tr>
      <td style="background-color:#9999cc; width:75%; height:4px; padding:0px;" colspan="3"></td>
    </tr>
  </table>
  </center>
</div>
<br>
<br>
<div style='width:100%; margin:auto; height:1px; background-color:#808080;'></div>
<div style="text-align:center">
  <center>
  <table style="border:0px; border-spacing:1px; font-size:0.75em" width="100%" border="0">
    <tbody>
      <tr>
        <td style="padding-bottom:4px;" valign="top" noWrap align="left" width="50%"><font color="white"> Hash: [<font color="yellow">{$variables['hash']}</font>]</font></td>
        <td style="padding-bottom:4px;" valign="top" noWrap align="right" width="50%"><font color="white">Updated on <font color="yellow">{$variables['updated_on']}</font></font></td>
      </tr>
    </tbody>
  </table>
  </center>
</div>
