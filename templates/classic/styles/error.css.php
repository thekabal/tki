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
body.error { background: url(../images/error.jpg) no-repeat center center fixed; background-size: cover}
div.error_content { float:right; text-align:left; width: 80%}
div.error_location { float:left; width: 20%}
div.error_text { background: rgb(0, 0, 0); background: rgba(0, 0, 0, 0.7); width:60%; margin: 0px auto; padding-left:1em}
p.error_footer { clear:both}
p.error_return { }
p.error_text { }
