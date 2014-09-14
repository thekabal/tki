 <?php
/*

class Translate

created by n3curatu

author website :http://www.rowebdesign.info

author email bogdan.izdrail@gmail.com;

*/

class translate {

    ///language from what we translate
    public $translate_from;

    ////language in what we whant to translate
    public $translate_into;

    ///debug the code
    public $debug;

    public function __construct($from , $to) {

        /*

        this function is for debuging code

        */

        $this->debug = false;

        ini_set("display_errors",$this->debug);

        if (!$from) {

            $this->translate_from = "en";

        } else {

            $this->translate_from = $from;

        }

        if (!$to) {

            $this->translate_into = "it";

        } else {

            $this->translate_into = $to;

        }

    }

    public function TranslateUrl($word) {

        if (!$word) {

            die("you need to adda a translate word");
        }
        ///we need to encode the word that we want to translate

        $word = urlencode($word);

        $url = "http://translate.google.com/?sl=". $this->translate_from ."&tl=". $this->translate_into ."&js=n&prev=_t&hl=it&ie=UTF-8&eotf=1&text=". $word ."";

        return $url;

    }

    public function get($word) {

        $dom  = new DOMDocument();

        $html =  $this->curl_download($this->TranslateUrl($word));

        $dom->loadHTML($html);

        $xpath = new DOMXPath($dom);

        $tags = $xpath->query('//*[@id="result_box"]');

        foreach ($tags as $tag) {

            $var = trim($tag->nodeValue);

            if (!$var) {
                ///we wil make an autoupdate sistem hear in the future
                die("Problem with Google translate Word");
            } else {
                return ($var);

            }

        }

    }

    /*
        function for downloading the gooogle page content for translating
    */

    public function curl_download($Url) {

        // is cURL installed yet?
        if (!function_exists('curl_init')) {

            if (function_exists('file_get_contents')) {
                return file_get_contents($Url);

            } else {

                die("Your server dosen't support curl or file get contents");

            }

        }

        // OK cool - then let's create a new cURL resource handle
        $ch = curl_init();
        // Now set some options (most are optional)
        // Set URL to download
        curl_setopt($ch, CURLOPT_URL, $Url);

        // Set a referer

        // User agent
        curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");

        // Include header in result? (0 = yes, 1 = no)
        curl_setopt($ch, CURLOPT_HEADER, 0);

        // Should cURL return or print out the data? (true = return, false = print)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        // Download the given URL, and return output
        $output = curl_exec($ch);

        // Close the cURL resource, and free system resources
        curl_close($ch);

        return $output;
    }

}
