<?php


require_once 'class.db_connect.php'; 

class helper extends db_connect
{

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

    static function isValidURL($url) {

        return preg_match('|^(http(s)?://)?[a-z0-9-]+\.(.[a-z0-9-]+)+(:[0-9]+)?(/.*)?$|i', $url);
    }

    static function truncate($str, $len)
    {
        $tail = max(0, $len-10);
        $trunk = substr($str, 0, $tail);
        $trunk .= strrev(preg_replace('~^..+?[\s,:]\b|^...~', '...', strrev(substr($str, $tail, $len-$tail))));

        return $trunk;
    }

    static function createShortNames($matches)
    {
        $helper = new helper(null);

        if ($helper->getUserId($matches[1]) != 0) {

            return "@<a class=\"username_link\" href=/$matches[1]>$matches[1]</a>";

        } else {

            return $matches[0];
        }
    }

    static function createHashtags($matches)
    {
        return "<a class=\"username_link\" href=\"/hashtag/?src={$matches[1]}\">#$matches[1]</a>";
    }

    static function createClickableLinks($matches)
    {
        $title = $face = $matches[0];

        $face = helper::truncate($face, 50);

        $matches[0] = str_replace( "www.", "http://www.", $matches[0] );
        $matches[0] = str_replace( "http://http://www.", "http://www.", $matches[0] );
        $matches[0] = str_replace( "https://http://www.", "https://www.", $matches[0] );

        return "<a title=\"$title\" class=\"posted_link\" target=\"_blank\" href=/go/?to=$matches[0]>$face</a>";
    }

    static function createPostClickableLinks($matches)
    {
        if (preg_match('/(?:http?:\/\/)?(?:www\.)?youtu(?:\.be|be\.com)\/(?:watch\?v=)?([\w\-]{6,12})(?:\&.+)?/i', $matches[0], $results)) {

            $video_preview = "http://img.youtube.com/vi/".$results[1]."/0.jpg";

            ob_start();

            ?>

            <div class="post_object">
                <a href="javascript:void(0)" onclick="Video.playYouTube(this, '<?php echo $results[1]; ?>'); return false;">
                    <span class="media_container">
                        <img class="video_preview" src="<?php echo $video_preview; ?>">
                        <span class="video_play"></span>
                    </span>
                </a>
            </div>

            <?php

            return $html = ob_get_clean();

        } else {

            $title = $face = $matches[0];

            $face = helper::truncate($face, 50);

            $matches[0] = str_replace( "www.", "http://www.", $matches[0] );
            $matches[0] = str_replace( "http://http://www.", "http://www.", $matches[0] );
            $matches[0] = str_replace( "https://http://www.", "https://www.", $matches[0] );

            return "<a title=\"$title\" class=\"posted_link\" target=\"_blank\" href=/go/?to=$matches[0]>$face</a>";
        }
    }

    static function processText($text)
    {
        $text = preg_replace_callback('/(?<=^|(?<=[^a-zA-Z0-9-_\.]))@([A-Za-z]+[A-Za-z0-9]+)/u', "helper::createShortNames", $text);
        $text = preg_replace_callback('@(?<=^|(?<=[^a-zA-Z0-9-_\.//]))((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.\,]*(\?\S+)?)?)*)@', "helper::createClickableLinks", $text);

        return $text;
    }

    static function processPostText($text)
    {
        $text = preg_replace_callback('/(?<=^|(?<=[^a-zA-Z0-9-_\.]))@([A-Za-z]+[A-Za-z0-9]+)/u', "helper::createShortNames", $text);
        $text = preg_replace_callback('@(?<=^|(?<=[^a-zA-Z0-9-_\.//]))((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.\,]*(\?\S+)?)?)*)@', "helper::createPostClickableLinks", $text);
        $text = preg_replace_callback('/#(\\w+)/u', "helper::createHashtags", $text);

        return $text;
    }

    static function processCommentText($text)
    {
        $text = preg_replace_callback('/(?<=^|(?<=[^a-zA-Z0-9-_\.]))@([A-Za-z]+[A-Za-z0-9]+)/u', "helper::createShortNames", $text);

        return $text;
    }

    public function getUserLogin($accountId)
    {
        $stmt = $this->db->prepare("SELECT login FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $accountId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            return $row['login'];
        }

        return 0;
    }

    public function getUserPhoto($accountId)
    {
        $stmt = $this->db->prepare("SELECT lowPhotoUrl FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $accountId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            if (strlen($row['lowPhotoUrl']) == 0) {

                return "/img/profile_default_photo.png";

            } else {

                return $row['lowPhotoUrl'];
            }
        }

        return "/img/profile_default_photo.png";
    }

    public function getUserId($username)
    {
        $username = helper::clearText($username);
        $username = helper::escapeText($username);

        $stmt = $this->db->prepare("SELECT id FROM users WHERE login = (:username) LIMIT 1");
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            return $row['id'];
        }

        return 0;
    }

    public function getUserIdByFacebook($fb_id)
    {
        $fb_id = helper::clearText($fb_id);
        $fb_id = helper::escapeText($fb_id);

        $stmt = $this->db->prepare("SELECT id FROM users WHERE fb_id = (:fb_id) LIMIT 1");
        $stmt->bindParam(":fb_id", $fb_id, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            return $row['id'];
        }

        return 0;
    }

    public function getUserIdByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = (:email) LIMIT 1");
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            return $row['id'];
        }

        return 0;
    }

    public function getRestorePoint($hash)
    {
        $hash = helper::clearText($hash);
        $hash = helper::escapeText($hash);

        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM restore_data WHERE hash = (:hash) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":hash", $hash, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            $result = array('error'=> false,
                            'error_code' => ERROR_SUCCESS,
                            'accountId' => $row['accountId'],
                            'hash' => $row['hash'],
                            'email' => $row['email']);
        }

        return $result;
    }

    public function isEmailExists($user_email)
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = (:email) LIMIT 1");
        $stmt->bindParam(':email', $user_email, PDO::PARAM_STR);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                return true;
            }
        }

        return false;
    }

    public function isLoginExists($username)
    {
        if (file_exists("../html/page.".$username.".inc.php")) {

            return true;
        }

        $username = helper::clearText($username);
        $username = helper::escapeText($username);

        $stmt = $this->db->prepare("SELECT id FROM users WHERE login = (:username) LIMIT 1");
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                return true;
            }
        }

        return false;
    }

    static function isCorrectFullname($fullname)
    {
        if (strlen($fullname) > 0) {

            return true;
        }

        return false;
    }

    static function isCorrectLogin($username)
    {
        if (preg_match("/^([a-zA-Z]{4,24})?([a-zA-Z][a-zA-Z0-9_]{4,24})$/i", $username)) {

            return true;
        }

        return false;
    }

    static function isCorrectPassword($password)
    {

        if (preg_match('/^[a-z0-9_]{6,20}$/i', $password)) {

            return true;
        }

        return false;
    }

    static function isCorrectEmail($email)
    {
        if (preg_match('/[0-9a-z_-]+@[-0-9a-z_^\.]+\.[a-z]{2,3}/i', $email)) {

            return true;
        }

        return false;
    }

    static function getLang($language)
    {
        $languages = array("en",
                           "ru",
                           "id");

        $result = "en";

        foreach($languages as $value) {

            if ($value === $language) {

                $result = $language;

                break;
            }
        }

        return $result;
    }

    static function clearText($text)
    {
        $text = trim($text);
        $text = strip_tags($text);
        $text = htmlspecialchars($text);

        return $text;
    }

    static  function escapeText($text)
    {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        $text = $mysqli->real_escape_string($text);

        return $text;
    }

    static function clearInt($value)
    {
        $value = intval($value);

        if ($value < 0) {

            $value = 0;
        }

        return $value;
    }

    static function ip_addr()
    {
        (string) $ip_addr = 'undefined';

        if (isset($_SERVER['REMOTE_ADDR'])) $ip_addr = $_SERVER['REMOTE_ADDR'];

        return $ip_addr;
    }

    static function u_agent()
    {
        (string) $u_agent = 'undefined';

        if (isset($_SERVER['HTTP_USER_AGENT'])) $u_agent = $_SERVER['HTTP_USER_AGENT'];

        return $u_agent;
    }

    static function generateHash($n = 7)
    {
        $key = '';
        $pattern = '123456789abcdefg';
        $counter = strlen($pattern) - 1;

        for ($i = 0; $i < $n; $i++) {

            $key .= $pattern[rand(0, $counter)];
        }

        return $key;
    }

}

