<?php
// This is CLI script
if (!debug_backtrace() && php_sapi_name() === 'cli') {
    include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.inc.php");

    if(!`which mknmz`) {
        echo "Please install Namazu";
        exit;
    }

    $mknmzrc = dirname(__FILE__).DIRECTORY_SEPARATOR."mknmzrc";
    if(!is_readable($mknmzrc)) {
        echo "Please create {$mknmzrc}";
        exit;
    }

    if(empty($_INDEXES)) {
        exit;
    }

    foreach($_INDEXES as $dir => $label) {
        $index_dir = INDEX_PATH.$dir;
        if(!is_dir($index_dir)) {
            if(!mkdir($index_dir, 0755)) {
                echo "WARN: Index {$index_dir} isn't readable.";
                continue;
            }
        }

        $uploaded_dir = UPLOAD_PATH.$dir;
        if(!is_dir($uploaded_dir)) {
            echo "WARN: Uploaded {$uploaded_dir} isn't readable.";
            continue;
        }

        system("mknmz -f /home/hajime/namazu/mknmzrc -O {$index_dir} {$uploaded_dir}");
    }
}
