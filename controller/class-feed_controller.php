<?php

/**
 * @author Andy Walpole
 * @date 21/2/2012
 * 
 */


class Feed_Controller extends Feed_Model {

    function __construct() {


    } // end construct

    public function unzip($file) {

        $archive = new PclZip(DIR_PATH."zip/$file");

        $list = $archive->extract(PCLZIP_OPT_PATH, "feeds", PCLZIP_OPT_REMOVE_ALL_PATH);

        if (!$list[0]['filename']) {

            die("Error : ".$archive->errorInfo(true));

        } else {

            $path = AH_DIR_PATH."zip/$file";
            unlink($path);
            return $list[0]['filename'];

        }
    }


    public function parse_csv($file) {

        $csv = new File_CSV_DataSource;

        $feed = $file;

        if ($csv->load($feed)) {

            return $csv->getrawArray();

        } else {

            return false;

        } // end if ($csv->sadfsad
    }


    public function parse_xml($file) {

        $xml = simplexml_load_file($file);

        return $xml;

    }


    public function get_file_extension($file_name) {

        return pathinfo($file_name, PATHINFO_EXTENSION);

    }

    public function get_file_basename($file_name) {

        return pathinfo($file_name, PATHINFO_BASENAME);

    }


    //http://stackoverflow.com/questions/4732846/download-gzip-file-via-php-curl
    private function get_gzip($url, $new_file, $ext) {

        $new_file = DIR_PATH."feeds/$new_file.$ext";

        $remote = gzopen($url, "rb");
        $home = fopen($new_file, "w");

        while ($string = gzread($remote, 4096)) {
            fwrite($home, $string, strlen($string));
        }

        gzclose($remote);
        fclose($home);

        if ($home !== null) {

            return $new_file;

        } else {

            return false;

        }


    }


    private function get_zip($data) {

        $zip = new ZipArchive();
        $filename = $data;

        if ($zip->open($filename, ZIPARCHIVE::CREATE || ZIPARCHIVE::OVERWRITE)) {

            $zip->extractTo(DIR_PATH.'feeds');
            $zip->close();

            return true;

        } else {

            return false;

        }
    }


    protected function parse_feed($url) {

        switch ($url) {

            case (preg_match('/^.*\/zip\/*/', $url) ? true : false):
                // Do stuff for zip file here

                // create temp random file name
                $random = uniqid().".zip";

                if ($this->grab_file($url, $random)) {

                    $file_name = $this->unzip("$random");

                    switch ($file_name) {

                        case ($this->get_file_extension($file_name) === "xml") ? true:
                            false;
                            //$this->parse_xml($file_name);
                            return true;
                            break;

                        case ($this->get_file_extension($file_name) === "csv") ? true:
                            false;
                            //$this->parse_csv($file_name);
                            return true;
                            break;

                        default:
                            return "This module only uses feeds in the XML or CSV format";
                            break;

                    } //end switch statement
                }

                break;

            case (preg_match('/^.*\/gzip\/*/', $url) ? true : false):
                // Do stuff for gzip file here

                $new_file = uniqid();

                switch ($url) {

                    case (preg_match('/^.*\/xml\/*/', $url) ? true : false):
                        //$this->get_gzip($url, $new_file, "xml");
                        return true;
                        break;

                    case (preg_match('/^.*\/csv\/*/', $url) ? true : false):
                        // $this->get_gzip($url, $new_file, "csv");
                        return true;
                        break;

                    default:
                        return "Only XML or CSV files";
                        break;

                } // end switch statement

                break;

            default:
                return "Sorry, this file is an unknown compression type";
                break;

        } # end switch

    }


} // end class
