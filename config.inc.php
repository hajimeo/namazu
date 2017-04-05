<?php
/**
 * This is configuration file to define constants
 * Need to put under the application root directory.
 * Constant name should be easy to guess.
 *
 * XXXX_PATH should end with DIRECTORY_SEPARATOR
 */

error_reporting(E_ALL);
ini_set("memory_limit", "512M");

define("APP_PATH",   dirname(__FILE__).DIRECTORY_SEPARATOR);
define("CLASS_PATH", APP_PATH."classes".DIRECTORY_SEPARATOR);
set_include_path(CLASS_PATH.PATH_SEPARATOR.get_include_path());

define("NAMAZU_BIN", "/usr/bin/env namazu");
define("MAX_DOC_NUM", 100);
define("INDEX_PATH", APP_PATH."indexes". DIRECTORY_SEPARATOR);   // *** Change the permission to 777 if necessary ***
define("UPLOAD_PATH", APP_PATH."uploads".DIRECTORY_SEPARATOR);   // *** Change the permission to 777 if necessary ***

// Acceptable index (directory) names and labels
$_INDEXES = array("docs" => "Docs");
