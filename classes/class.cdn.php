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
        $remote_file = $this->cdn_server . $remote_file;

        if ($this->conn_id && @ftp_login($this->conn_id, $this->ftp_user_name, $this->ftp_user_pass)) {
            return @ftp_put($this->conn_id, $remote_file, $file, FTP_BINARY);
        }

        return false;
    }

    private function safeMove($srcPath, $destDir)
    {
        $filename = basename($srcPath);
        $destPath = $destDir . $filename;

        if (!file_exists($srcPath)) {
            return ["error" => true, "error_code" => 1, "msg" => "Source file does not exist"];
        }

        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }

        if (!@rename($srcPath, $destPath)) {
            return ["error" => true, "error_code" => 2, "msg" => "Failed to move file"];
        }

        return ["error" => false, "error_code" => ERROR_SUCCESS, "fileUrl" => $destPath];
    }

    public function uploadMyPhoto($imgFilename)
    {
        return $this->safeMove($imgFilename, MY_PHOTOS_PATH);
    }

    public function uploadPhoto($imgFilename)
    {
        return $this->safeMove($imgFilename, PHOTO_PATH);
    }

    public function uploadCover($imgFilename)
    {
        return $this->safeMove($imgFilename, COVER_PATH);
    }

    public function uploadPostImg($imgFilename)
    {
        $res = $this->safeMove($imgFilename, POST_PHOTO_PATH);
        if (!$res["error"]) $res["fileUrl"] = POST_PHOTO_PATH_URL . basename($imgFilename);
        return $res;
    }

    public function uploadProfileImg($imgFilename)
    {
        $res = $this->safeMove($imgFilename, PROFILE_PHOTOS_PATH);
        if (!$res["error"]) $res["fileUrl"] = PROFILE_PATH_URL . basename($imgFilename);
        return $res;
    }

    public function uploadCoverImg($imgFilename)
    {
        $res = $this->safeMove($imgFilename, LISTING_COVER_PATH);
        if (!$res["error"]) $res["fileUrl"] = LISTING_COVER_IMAGE_URL . basename($imgFilename);
        return $res;
    }

    public function uploadGalleryImg($imgFilename)
    {
        $res = $this->safeMove($imgFilename, LISTING_GALLERY_PATH);
        if (!$res["error"]) $res["fileUrl"] = LISTING_GALLERY_IMAGE_URL . basename($imgFilename);
        return $res;
    }

    public function uploadChatImg($imgFilename)
    {
        $res = $this->safeMove($imgFilename, CHAT_IMAGE_PATH);
        if (!$res["error"]) $res["fileUrl"] = CHAT_IMAGE_URL . basename($imgFilename);
        return $res;
    }
}
