<?php

require_once 'class.db_connect.php';
require_once 'class.constant.php';

class cdn extends db_connect
{
    private $ftp_url = "";
    private $ftp_server = "";
    private $ftp_user_name = "";
    private $ftp_user_pass = "";
    private $cdn_server = "";
    private $conn_id = false;

    public function __construct($dbo = NULL)
    {
        $this->conn_id = @ftp_connect($this->ftp_server);

        parent::__construct($dbo);
    }

    public function upload($file, $remote_file)
    {
        $remote_file = $this->cdn_server.$remote_file;

        if ($this->conn_id) {

            if (@ftp_login($this->conn_id, $this->ftp_user_name, $this->ftp_user_pass)) {

                // upload a file
                if (@ftp_put($this->conn_id, $remote_file, $file, FTP_BINARY)) {

                    return true;

                } else {

                    return false;
                }
            }
        }
    }

    public function uploadMyPhoto($imgFilename)
    {
        rename($imgFilename, MY_PHOTOS_PATH.basename($imgFilename));

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "fileUrl" => MY_PHOTOS_PATH.basename($imgFilename));

        return $result;
    }

    public function uploadPhoto($imgFilename)
    {
        rename($imgFilename, PHOTO_PATH.basename($imgFilename));

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "fileUrl" => PHOTO_PATH.basename($imgFilename));

        return $result;
    }

    public function uploadCover($imgFilename)
    {
        rename($imgFilename, COVER_PATH.basename($imgFilename));

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "fileUrl" => COVER_PATH.basename($imgFilename));

        return $result;
    }

    public function uploadPostImg($imgFilename)
    {
        rename($imgFilename, POST_PHOTO_PATH.basename($imgFilename));

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "fileUrl" => POST_PHOTO_PATH_URL.basename($imgFilename));

        return $result;
    }

    public function uploadProfileImg($imgFilename)
    {
        rename($imgFilename, PROFILE_PHOTOS_PATH.basename($imgFilename));

        $result = array("error" => false,
            "error_code" => ERROR_SUCCESS,
            "fileUrl" => PROFILE_PATH_URL.basename($imgFilename));

        return $result;
    }

    public function uploadCoverImg($imgFilename)
    {
        rename($imgFilename, LISTING_COVER_PATH.basename($imgFilename));

        $result = array("error" => false,
            "error_code" => ERROR_SUCCESS,
            "fileUrl" => LISTING_COVER_IMAGE_URL.basename($imgFilename));

        return $result;
    }

    public function uploadGalleryImg($imgFilename)
    {
        rename($imgFilename, LISTING_GALLERY_PATH.basename($imgFilename));

        $result = array("error" => false,
            "error_code" => ERROR_SUCCESS,
            "fileUrl" => LISTING_GALLERY_IMAGE_URL.basename($imgFilename));

        return $result;
    }

    public function uploadChatImg($imgFilename)
    {
        rename($imgFilename, CHAT_IMAGE_PATH.basename($imgFilename));

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "fileUrl" => CHAT_IMAGE_URL.basename($imgFilename));

        return $result;
    }
}
