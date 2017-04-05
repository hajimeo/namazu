<?php
/**
 * uploader.php
 *
 * To upload files to tmp location
 */
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.inc.php");

class UploadPage
{

    public function upload($uploaded_dir_name=null)
    {
        global $_INDEXES;

        // Old style file upload (no custom header)
        if (!empty($_FILES)) {
            if(empty($uploaded_dir_name)) {
                $keys = array_keys($_INDEXES);
                $uploaded_dir_name = $keys[0];  //FIXME: no way to specify the target dir from browser.
            }

            if(!is_dir(UPLOAD_PATH . $uploaded_dir_name)) {
                if(!mkdir(UPLOAD_PATH . $uploaded_dir_name, 0755)) {
                    throw new Exception('Could not create '.UPLOAD_PATH . $uploaded_dir_name);
                }
            }
            $new_path = UPLOAD_PATH . $uploaded_dir_name. DIRECTORY_SEPARATOR. $_FILES['upload']['name'];

            if(!move_uploaded_file($_FILES['upload']['tmp_name'], $new_path)) {
                throw new Exception("can not move a temp file into {$new_path}.
                    ".json_encode($_FILES, JSON_PRETTY_PRINT));
            }
            return chmod($new_path, 0666);
        } elseif (!empty($_REQUEST['up'])) {
            if (!empty($_REQUEST['base64'])) {
                $content = base64_decode(file_get_contents('php://input'));
            } else {
                $content = file_get_contents('php://input');
            }

            // $headers = getallheaders();
            $headers = self::emuGetAllHeaders();
            $headers = array_change_key_case($headers, CASE_UPPER);

            $uploaded_file_name = (empty($headers['UP_FILENAME'])) ? @$headers['UP-FILENAME'] : $headers['UP_FILENAME'];

            if(empty($uploaded_file_name) ) {
                throw new Exception('header UP FILENAME is empty.
                '.json_encode($_SERVER, JSON_PRETTY_PRINT));
            }

            if(!empty($uploaded_dir_name)) {
                $uploaded_dir_name = (empty($headers['UP_DIRNAME'])) ? @$headers['UP-DIRNAME'] : $headers['UP_DIRNAME'];
            }
            if(empty($uploaded_dir_name)) {
                $keys = array_keys($_INDEXES);
                $uploaded_dir_name = $keys[0];  //FIXME: no way to specify the target dir from browser.
            }

            if(!is_dir(UPLOAD_PATH . $uploaded_dir_name)) {
                if(!mkdir(UPLOAD_PATH . $uploaded_dir_name, 0755)) {
                    throw new Exception('Could not create '.UPLOAD_PATH . $uploaded_dir_name);
                }
            }
            $new_path = UPLOAD_PATH . $uploaded_dir_name. DIRECTORY_SEPARATOR . $uploaded_file_name;

            if(!file_put_contents($new_path, $content)) {
                throw new Exception("can not save content into {$new_path}.
                ".json_encode($_SERVER, JSON_PRETTY_PRINT));
            }
            return chmod($new_path, 0666);
        }

        // No file to upload
        return false;
    }

    public static function emuGetAllHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $h => $v) {
            if (preg_match('/HTTP_(.+)/', $h, $hp)) {
                $headers[$hp[1]] = $v;
            }
        }

        return $headers;
    }

    // Not in use
    public function printFileList()
    {
        if ($handle = opendir(UPLOAD_PATH)) {
            print "<table id=\"filelist\" border=0>\n";
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    print "<tr>";
                    echo "<td><a href=\"" . UPLOAD_URL . $entry . "\" target=\"_blank\">" . $entry . "</a></td>";
                    echo "<td>&nbsp;Size:" . filesize(UPLOAD_PATH . $entry) . " byte</td>";
                    echo "<td>&nbsp;Modified:" . gmdate("Y-m-d H:i:s", filemtime(UPLOAD_PATH . $entry)) . " GMT</td>";
                    print "</tr>";
                }
            }
            print "</table>\n";
            closedir($handle);
        }
    }
}

$page = new UploadPage();

if (!empty($_REQUEST['up'])) {
    if ($page->upload()) {
        // This file should be called from AJAX only so that don't need to output HTML.
        echo 'done';
    }
    exit();
}
