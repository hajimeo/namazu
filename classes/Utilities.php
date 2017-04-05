<?php

/**
 * Utilities class (static methods only)
 * Comprehensive error handling should be done in calling class.
 */
class Utilities
{
    public static function getFullPathFromDir($dirFullPath, $filter = array(), $depth = 1)
    {
        $fileFullPaths = array();

        if (!is_readable($dirFullPath)) {
            return false;
        }

        $handle = opendir($dirFullPath);
        $dirFullPath = Utilities::addDirSlash($dirFullPath);

        while (false !== ($file = readdir($handle))) {
            if ('.' == $file || '..' == $file) {
                continue;
            }

            $newFullPath = $dirFullPath . $file;

            if (is_dir($newFullPath) && $depth != 1) {
                if (is_readable($newFullPath)) {
                    // TODO: (low) Haven't tested $depth. Sorry.
                    $depth--; # if original depth = 0, it becomes negative.
                    $tmpSubFullPathe = Utilities::getFullPathFromDir($newFullPath, $filter, $depth);
                    $fileFullPaths = array_merge($fileFullPaths, $tmpSubFullPathe);
                }
            } else {
                if (!is_dir($newFullPath)) {
                    if (is_array($filter) && count($filter) > 0) {
                        $extension = Utilities::getFileExt($file);

                        if (in_array($extension, $filter)) {
                            $fileFullPaths[] = $newFullPath;
                        }
                    } else {
                        $fileFullPaths[] = $newFullPath;
                    }
                }
            }
        }

        closedir($handle);

        return $fileFullPaths;
    }

    public static function addDirSlash($dirPath)
    {
        if (substr($dirPath, -1) != DIRECTORY_SEPARATOR) {
            $dirPath .= DIRECTORY_SEPARATOR;
        }
        return $dirPath;
    }

    public static function getDirName($path)
    {
        $lastChar = substr($path, -1);
        if ($lastChar == "/" || $lastChar == "\\") {
            return substr($path, 0, -1);
        }
        return dirname($path);
    }

    public static function stripslashes($string, $force = false)
    {
        return ($force || get_magic_quotes_gpc()) ? stripcslashes($string) : $string;
    }

    public static function getFileExt($filename)
    {
        return end(explode(".", $filename));
    }

    /**
     * remove HTML tag
     *
     * @param string $html HTML text
     * @param string $except excepting tag name like "<a><b><br>" but using this would cause performance issue
     * @return string
     */
    public static function removeHtml($html, $except = null)
    {
        $htmltmp = $html;
        // delete Javascript/object tags
        $pattern = "@<script[^>]*?>.*?</script>@is";
        $htmltmp = preg_replace($pattern, "", $htmltmp);
        $pattern = "@<style[^>]*?>.*?</style>@is";
        $htmltmp = preg_replace($pattern, "", $htmltmp);

        if (empty($htmltmp)) {
            $htmltmp = $html;
        }

        // TODO: strip_tags is suspecious but this might cause performance issue (or incorrect)
        if (!empty($except)) {
            $except = preg_replace('@><@', '|', $except);
            $except = preg_replace('@[<>]@', '', $except);
            $htmltmp = preg_replace('@<(?!/*(' . $except . '))[^><]+>@is', '', $htmltmp);
        } else {
            $htmltmp = preg_replace('@<[^><]+>@s', '', $htmltmp);
        }

        return (trim($htmltmp));
    }

    public static function getLinks($html, $linkOnly = true)
    {
        $matches = null;
        // TODO: is this good enough?
        $pattern = "@<a href=['\"](.*?)['\"].*?>(.*?)</a>@si";
        $result = preg_match_all($pattern, $html, $matches);

        if ($result && $linkOnly) {
            return $matches[1];
        }

        return $matches;
    }

    public static function getElementByTag($tag, $html, $removeHtml = false)
    {
        $pattern = "@<" . $tag . "[^>]*>(.+)</" . $tag . ">@i";
        $result = preg_match($pattern, $html, $matches);

        if (!$result) {
            //TODO: I'm not sure if 's' is necessary or not...
            $pattern = "@<" . $tag . "[^>]*>(.+)</" . $tag . ">@si";
            $result = preg_match($pattern, $html, $matches);
        }

        if ($result && $removeHtml === true) {
            $matches[1] = Utilities::removeHtml($matches[1]);
        }

        return ($result) ? trim($matches[1]) : null;
    }

    public static function getElementByTagId($tag, $id, $html, $removeHtml = false)
    {
        $pattern = "@<" . $tag . "(\\s+.+|^>)?\\s+id=['\"]" . $id . "['\"][^>]*>(.+)</" . $tag . ">@si";
        $result = preg_match($pattern, $html, $matches);

        if ($result && $removeHtml === true) {
            $matches[2] = Utilities::removeHtml($matches[2]);
        }

        return ($result) ? trim($matches[2]) : null;
    }

    public static function summarize($text, $words = 64, $length = null, $trim = true)
    {
        $text_array = split(' ', $text, ($words + 1));
        $return = implode(' ', array_slice($text_array, 0, $words));

        if ($trim) {
            $return = trim($return);
        }

        if ($length > 0 && strlen($return) > $length) {
            $return = mb_substr($return, 0, $length);
        }

        if (strlen($return) < strlen($text)) {
            $return .= "...";
        }

        return $return;
    }

    public static function summarizeR($text, $words = 64, $length = null, $trim = true)
    {
        $text_array = split(' ', $text);
        $return = implode(' ', array_slice($text_array, (0 - $words), $words));

        if ($trim) {
            $return = trim($return);
        }

        if ($length > 0 && strlen($return) > $length) {
            $return = mb_substr($return, (0 - $length));
        }

        if (strlen($return) < strlen($text)) {
            $return = "..." . $return;
        }

        return $return;
    }

    // TODO: Trying to summarize the text by query
    public static function searchOut($text, $query)
    {
        $query = preg_replace('/[\.\\\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:]/', ' ', $query);
        $return = "";
        $queries = Utilities::getWords($query);
        $i = 0;

        foreach ($queries as $word) {
            $word = str_replace("@", "\@", $word);
            $pattern = "@(.*?)(" . $word . ")(.*)@i";

            if (!empty($return)) {
                if (preg_match($pattern, $return, $matches)) {
                    continue;
                }
            }

            $result = preg_match($pattern, $text, $matches);

            if ($result) {
                // TODO: dirty fix
                $string = Utilities::summarizeR($matches[1], 6, 32, false) . $matches[2] . Utilities::summarize(
                        $matches[3],
                        18,
                        96,
                        false
                    );

                if (!empty($string)) {
                    $return .= $string . "\n";

                    $i++;
                    if ($i == 3) {
                        break;
                    } //for performance, checking only the first 3
                }
            }
        }

        return $return;
    }

    public static function getWords($str)
    {
        $str = preg_replace("/[\"\+\*]/", " ", $str);
        $str = preg_replace("/(AND|OR)/", " ", $str);
        $str = trim($str);
        $ary = preg_split("/[\s]+/", $str);
        $ary = array_unique($ary);

        return $ary;
    }

    /**
     * Remove HTML tags except 'br'
     * Under construction but almost done
     */
    public static function textize($string, $length = null)
    {
        // If it's HTML, using body only
        if (stripos($string, "<body") !== false) {
            $stringTmp = Utilities::getElementByTag("body", $string);
            if (!empty($stringTmp)) {
                $string = $stringTmp;
            }
        }

        $regAllBlockTags = "p|button|div|dl|fieldset|form|frameset|h[1-9]|head|html|iframe|img|layer|legend|object|ol|li|select|option";

        // TODO: this regex is bad.
        $string = preg_replace('@(</)(' . $regAllBlockTags . ')(>)(\S)@i', '$1$2$3<br>$4', $string); // not good...
        $string = preg_replace('@(\S)(<)(' . $regAllBlockTags . ')@i', '$1<br>$2$3', $string); // not good...
        $string = preg_replace('@(</br>|<br/>|<br .*?>|</br .*?>)@i', '<br>', $string); // not good...

        $string = Utilities::removeHtml($string, "<br>");
        $string = preg_replace('@&nbsp;@', ' ', $string);
        $string = preg_replace('@\s+@', ' ', $string);
        $string = preg_replace('@(<br>)(\s+)@i', '<br>', $string); // not good...
        $string = preg_replace('@(<br>)+@', '<br>', $string);
        $string = str_replace("<br>", "\n", $string);
        $string = str_replace("\n\n", "\n", $string);
//		$string = html_entity_decode($string);
        $string = trim($string);

        if ($length > 0 && strlen($string) > $length) {
            $string = mb_substr($string, 0, $length);
            //$theLastSpacePos = strrpos($string, " ");
            //if ($theLastSpacePos !== false)
            //	$string = mb_substr($string, 0, $theLastSpacePos);
        }

        return $string;
    }

    // Under construction
    // TODO: it doesn't handle sub directory properly (it works but...)
    public static function buildUrl($removeFileName = false)
    {
        $url = (('on' == @$_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
        $url .= (($removeFileName === true) ? Utilities::getDirName($_SERVER['REQUEST_URI']) : $_SERVER['REQUEST_URI']);

        if (substr($url, -1) == "/") {
            $url = substr($url, 0, -1);
        }

        return $url;
    }

    public static function echoMemory($label = null)
    {
        return $label . "\t Time:" . microtime(true) . "\t Memory:" . memory_get_usage() . "\n";
    }

    public static function isUrl($url)
    {
        $info = @parse_url($url);
        return (!empty($info['scheme'])) && (!empty($info['host'])) && ($info['scheme'] == 'http' || $info['scheme'] == 'https');
        //return (@parse_url($url)) ? true : false;
    }

    public static function wget(
        $url,
        &$output,
        $cookie = null,
        $baseUrl = null,
        $user = null,
        $pass = null,
        $isDebug = false
    ) {
        if (empty($url)) {
            $output = "URL is empty.";
            return false;
        }

        if (!Utilities::isUrl($url)) {
            if (empty($baseUrl)) {
                $output = "URL is not full URL but base URL is empty.";
                return false;
            }

            if (substr($url, 0, 1) == "/") {
                $url_ary = parse_url($baseUrl);
                $url = $url_ary['scheme'] . "://" . $url_ary['host'] . $url;
            } else {
                if (substr($baseUrl, -1) == "/") {
                    $url = $baseUrl . $url;
                } else {
                    $url = $baseUrl . "/" . $url;
                }
            }
        }

        $tmpfname = tempnam("/tmp", "WGF"); // WGet File
        $tmplname = tempnam("/tmp", "WGL"); // WGet Log

        $command = "wget --no-check-certificate -O " . $tmpfname . " -a " . $tmplname;
        if (!empty($cookie)) {
            $command .= " --load-cookies=\"" . str_replace(
                    '"',
                    '',
                    $cookie
                ) . "\""; # not checking if cookie is readable to show error in wget.
            $command .= " --save-cookies=\"" . str_replace('"', '', $cookie) . "\" --keep-session-cookies";
        }
        if (!empty($baseUrl)) {
            $command .= " --referer=\"" . str_replace('"', '', $baseUrl) . "\"";
        }
        if (!empty($user)) {
            $command .= " --http-user=\"" . str_replace('"', '', $user) . "\"";
        }
        if (!empty($pass)) {
            $command .= " --http-password=\"" . str_replace('"', '', $pass) . "\"";
        }
        $command .= " \"" . str_replace('"', '', $url) . "\"";

        file_put_contents($tmplname, "\nCommand: " . $command . "\n", FILE_APPEND);

        $result = system($command);
        sleep(3); // just in case
        $output = file_get_contents($tmpfname);

        if ($result !== false && !empty($output)) {
            if (!$isDebug) {
                unlink($tmpfname);
            }
            if (!$isDebug) {
                unlink($tmplname);
            }
            return true;
        } else {
            $output = file_get_contents($tmplname);
            if (!$isDebug) {
                unlink($tmpfname);
            }
            if (!$isDebug) {
                unlink($tmplname);
            }
            return false;
        }
    }

    public static function getHtmlFromUrl($url)
    {
        if (!Utilities::isUrl($url)) {
            return false;
        }
        //$contents = file_get_contents($url);
        $contents = "";
        $info = @parse_url($url);
        $port = (!empty($info['port'])) ? $info['port'] : 80;

        if (!empty($info['path'])) {
            $fp = fsockopen($info['host'], $port, $errno, $errstr, 30);
            if (!$fp) {
                return false;
            } else {
                $out = "GET " . $info['path'] . " HTTP/1.1\r\n";
                $out .= "Host: " . $info['host'] . "\r\n";
                $out .= "User-Agent: Mozilla/5.0\r\n";
                $out .= "Connection: Close\r\n\r\n";
                fwrite($fp, $out);

                while (!feof($fp)) {
                    $headers = fgets($fp, 8192);
                    if (trim($headers) == '') {
                        break;
                    }
                }
                while (!feof($fp)) {
                    $contents .= fgets($fp);
                }
                fclose($fp);

                $tests = split("<\!DOCTYPE", $contents, 2);
                if (count($tests) == 2) {
                    $contents = "<!DOCTYPE" . $tests[1];
                }
            }
        }

        return $contents;
    }

    public static function saveObj($to, $object = null)
    {
        //TODO: make it nicer
        return file_put_contents($to, serialize($object));
    }

    public static function loadObj($from)
    {
        $str = file_get_contents($from);
        if (empty($str)) {
            return null;
        }

        return unserialize($str);
    }

    public static function auth($realm)
    {
        header("HTTP/1.0 401 Unauthorized");
        header("WWW-authenticate: Basic realm=\"" . $realm . "\"");
        die("Password or User name is invalid.");
    }

    // TODO: this crypt is not strong, also output is different between Win and Xnix
    public static function crypt($string)
    {
        return md5($string);
    }
}

?>
