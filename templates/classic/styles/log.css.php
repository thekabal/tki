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
body.log.a:active { color: #040658}
body.log.a:link { color: #040658}
body.log.a:visited { color: #040658}
body.log { background-color:#000; background-image: url('../images/bgoutspace1.png'); color:#c0c0c0}
