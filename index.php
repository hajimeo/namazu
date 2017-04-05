<?php
/*
 * PDF, Word, Excel, etc searcher + Ladbrkes intrenet searches
 *
 * TODO:
 *     using onClick isn't graceful.
 *     Drag&drop file upload would not work with mobile
 */
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.inc.php");
include_once('namazu.php');

/**
 * Cookie handling
 */
if (!isset($_REQUEST['ids']) && isset($_COOKIE['c_ids'])) {
    $_REQUEST['ids'] = explode("\t", $_COOKIE['c_ids']);
}
if (isset($_REQUEST['ids'])) {
    //setting cookie before any output
    setcookie("c_ids", implode("\t", $_REQUEST['ids']), time() + 60 * 60 * 24 * 365);
}

$p = new Namazu($_INDEXES);

// If text mode is requested, no HTML
if (isset($_REQUEST['txt'])) {
    $output = "";
    $p->search($output, true);
    print strip_tags($output);
    exit();
}

$checkboxes = $p->getIndexCheckboxes();
?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>Search</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="js/jquery.mobile/jquery.mobile-1.4.4.min.css">
    <script src="js/lib/underscore-min.js"></script>
    <script src="js/lib/base64.js"></script>
    <script src="js/lib/jquery-1.11.1.min.js"></script>
    <script src="js/jquery.mobile/jquery.mobile-1.4.4.min.js"></script>
    <script src="js/html5uploader.js"></script>
    <script src="js/__init__.js"></script>
    <style>
        #box {
            background-color: #f60;
            width: 208px;
            border: 1px solid #f60;
            -webkit-border-radius: 6px;
            -moz-border-radius: 6px;
            border-radius: 6px;
            padding-bottom: 10px;
        }
        #box p {
            padding: 5px;
            margin: 0px;
        }
        #drop {
            width: 208px;
            height: 140px;
            background-color: #f90;
        }
        #status {
            font-size: smaller;
            width: 200px;
            height: 25px;
            color: #fff;
            padding: 5px;
        }
    </style>
</head>
<body onload="new Uploader('drop', 'status', 'uploader.php');">

<div data-role="page">
    <div data-role="header">
        <h1>Document Search+</h1>
    </div>

    <div data-role="main" class="ui-content" id="namazu_result" style="min-height: 400px;">
        <form method="get" name="search_form" id="search_form">
            <div class="ui-grid-b ui-responsive">
                <div class="ui-block-a"><input type="search" name="w" id="w" value="<?=@htmlentities($_REQUEST['w'])?>" placeholder="Search for doc..."></div>
                <div class="ui-block-b" data-role="controlgroup" data-type="horizontal">
                    <a href="#" class="ui-btn ui-icon-search ui-btn-icon-left ui-corner-all" onclick="document.getElementById('search_form').submit();">Search</a>
                    <a href="#" class="ui-btn ui-icon-search ui-btn-icon-left ui-corner-all" onClick="others(document.getElementById('w').value);">Others</a>
                </div>
                <div class="ui-block-c"><a href="#myPopupUpload" data-rel="popup" data-position-to="window" data-transition="fade" class="ui-btn ui-icon-arrow-u ui-btn-icon-left ui-corner-all">Upload a file</a></div>
            </div>
        </form>
        <hr>
        <?php
        if (!empty($_REQUEST['w'])) {
            $output = "";
            $p->search($output);
            print $output;
        }
        ?>

        <div data-role="popup" id="myPopupUpload">
            <div data-role="header">
                <h1>Upload a file</h1>
            </div>

            <div data-role="main" class="ui-content">
                <div id="box">
                    <div id="status">Drag a file into below ...</div>
                    <div id="drop"></div>
                </div>
            </div>

            <div data-role="footer">
                <a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-icon-back ui-btn-icon-left" data-rel="back">Go Back</a>
            </div>
        </div>
    </div>

    <div data-role="footer">
        <h2>Footer (what should i type... copyright?)</h2>
    </div>
</div>

</body>
</html>


