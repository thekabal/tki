<?php
require_once '../../../vendor/autoload.php';           // Load the auto-loader
ob_start (array('Tki\Compress', 'compress'));

$etag = md5_file (__FILE__); // Generate an md5sum and use it as the etag for the file, ensuring that caches will revalidate if the code itself changes
//header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 604800));
header ("Vary: Accept-Encoding");
header ("Content-type: text/css");
header ("Connection: Keep-Alive");
header ("Cache-Control: public");
header ('ETag: "' . $etag . '"');
?>
body.settings table { border:0; border-spacing:0px; width:800px; font-size:14px; color:#fff; border:#fff 1px solid; padding-left:2px; padding-right:2px;}
body.settings td { color:white; font-size:1.1em; padding:3px}
body.settings th { background-color:#500050; text-align:left}
body.settings tr:nth-child(even) { background-color:#300030}
body.settings tr:nth-child(odd) { background-color:#400040}
body.settings div.special { width:798px; font-size:14px; color:#fff; background-color:#500050; padding-left:2px; padding-top:2px; padding-bottom:2px; border:#fff 1px solid;}
