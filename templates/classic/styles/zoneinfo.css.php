<?php
require_once '../../../vendor/autoload.php';           // Load the auto-loader
ob_start (array('Bnt\Compress', 'compress'));

$etag = md5_file (__FILE__); // Generate an md5sum and use it as the etag for the file, ensuring that caches will revalidate if the code itself changes
//header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 604800));
header ("Vary: Accept-Encoding");
header ("Content-type: text/css");
header ("Connection: Keep-Alive");
header ("Cache-Control: public");
header ('ETag: "' . $etag . '"');
?>
body.zoneinfo table { border:1px solid white; border-spacing:0px; color: #fff; margin-left:20%; margin-right: 20%; padding:0px; width:60%}
body.zoneinfo td { font-size:1.1em}
body.zoneinfo td.name { width: 50%}
body.zoneinfo td.value { width: 50%}
body.zoneinfo td.zonename { text-align:center}
body.zoneinfo th { background-color:#500050; text-align:left}
body.zoneinfo tr:nth-child(even) { background-color:#300030}
body.zoneinfo tr:nth-child(odd) { background-color:#400040}
