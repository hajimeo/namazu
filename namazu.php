<?php
/*
 * Call Namazu to search share documents
 * Acceptable $_REQUEST are:
 * 		w   = search Word
 * 		ids = Index Directoris array
 * 		num = Returning number
 * 		txt = return with simple text format
 */
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.inc.php");

class Namazu
{
    var $w = "";
    var $ids = array();
    var $num = MAX_DOC_NUM;
    var $_INDEXES = array();

    function __construct($indexes)
    {
        $this->_INDEXES = $indexes;
        $this->w = (!empty($_REQUEST['w'])) ? escapeshellarg($_REQUEST['w']) : "";
        $this->ids = (!empty($_REQUEST['ids']) && is_array($_REQUEST['ids'])) ? $_REQUEST['ids'] : array_keys(
            $this->_INDEXES
        );
        $this->num = (!empty($_REQUEST['num']) && is_numeric(
                $_REQUEST['num']
            ) && $_REQUEST['num'] < MAX_DOC_NUM && $_REQUEST['num'] > 0) ? $_REQUEST['num'] : MAX_DOC_NUM;
    }

    function getElementByTag($html, $tag, $class = null, $id = null)
    {
        if (!empty($id)) {
            $pattern = "@<" . $tag . "(\\s+.+|^>)?\\s+id=['\"]" . $id . "['\"][^>]*>(.+?)</" . $tag . ">@si";
            $result = preg_match($pattern, $html, $matches);
        } else {
            if (!empty($class)) {
                $pattern = "@<" . $tag . "(\\s+.+|^>)?\\s+class=['\"]" . $class . "['\"][^>]*>(.+?)</" . $tag . ">@si";
                $result = preg_match($pattern, $html, $matches);
            } else {
                $pattern = "@<" . $tag . "[^>]*>(.+?)</" . $tag . ">@si";
                $result = preg_match($pattern, $html, $matches);
            }
        }

        return ($result) ? trim($matches[0]) : null;
    }

    /**
     * TODO: this method might be broken. Need more tests
     * Should be faster and accurate than getElementByTag()
     */
    function getElementByTag2($html, $tag, $class = null, $id = null)
    {
        require_once 'classes/simple_html_dom.php';

        $obj = str_get_html($html);
        if ($id) {
            $rtn = $obj->find('$tag[id=$id]');
        } else {
            if ($class) {
                $rtn = $obj->find('$tag[class=$class]');
            } else {
                $rtn = $obj->find($tag);
            }
        }

        return print_r($rtn, true);
    }

    /**
     * @param string $output to display (HTML)
     * @param bool   $simple If true, output less information
     * @return null|integer exec return code
     */
    function search(&$output, $simple = false)
    {
        $index = "";
        foreach ($this->ids as $index_dir) {
            if (in_array(strtolower($index_dir), array_keys($this->_INDEXES)) && is_readable(
                    INDEX_PATH . strtolower($index_dir)
                )
            ) {
                $index .= INDEX_PATH . strtolower($index_dir) . " ";
            }
        }

        //$cmd = "/usr/bin/namazu -n ".$this->num." -h ".$this->w." ".$index;
        $cmd = NAMAZU_BIN . " -s -n " . $this->num . " -h " . $this->w . " " . $index;
        $outputs = array();
        $return = null;
        //print "<!-- ".$cmd." -->\n";
        $execRtn = exec($cmd, $outputs, $return);
        //print "<!-- ".$execRtn." -->\n";
        //print "<!-- ".print_r($outputs, true)." -->\n";
        $output_tmp = implode("\n", $outputs);
        $output = $this->getElementByTag($output_tmp, "dl");
        //print "<!-- ".print_r($output, true)." -->\n";
        $output = str_replace(APP_PATH, "", $output);
        $output = preg_replace('/<a href=([^>]*?)>/i', '<a href=$1 target="_blank">', $output);

        if (!$simple) {
            $output_header = $this->getElementByTag($output_tmp, "div", "namazu-result-header");
            $output = $output_header . "\n" . $output;
        }

        return $return;
    }

    function getIndexCheckboxes($class="index_checkbox")
    {
        $checkboxes = [];
        foreach ($this->_INDEXES as $key => $label) {
            $checked = (!empty($this->ids) && in_array($key, $this->ids)) ? "checked=checked" : "";
            $checkboxes[] = "<label class=\"{$class}\" title=\"{$key}\"><input type=\"checkbox\" name=\"ids[]\" value=\"{$key}\" {$checked}/>{$label}</label>";
        }

        return $checkboxes;
    }
}
