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
.portcosts1 { background-color:#300030; border-style:none; color:#c0c0c0; font-size:1em; width:7em}
.portcosts2 { background-color:#400040; border-style:none; color:#c0c0c0; font-size:1em; width:7em}
body.port table { border:0; border-spacing:0px; color:#fff; width:100%}
body.port td { font-size:1.1em; padding:0px}
body.port th { background-color:#500050; text-align:left}
body.port tr:nth-child(even) { background-color:#300030}
body.port tr:nth-child(odd) { background-color:#400040}
