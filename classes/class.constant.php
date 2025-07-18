<?php
header('Content-Type: application/json; charset=utf-8');

define("DB_HOST", "localhost");
define("DB_USER", "u833998383_ticket_master");
define("DB_PASS", "u833998383_Ticket_master");
define("DB_NAME","u833998383_ticket_master");

//Url
define("APP_URL","https://appsghar.com/projects/ticket_master/v1");

//Paths
define("PROFILE_PHOTOS_PATH","/home/u833998383/domains/appsghar.com/public_html/projects/ticket_master/uploads/events/");
define("TEMP_PATH","/home/u833998383/domains/appsghar.com/public_html/projects/ticket_master/uploads/temp/");

//Paths for url
define("PROFILE_PATH_URL","/uploads/events/");

//Errors Configurations
define("ERROR_SUCCESS", 0);
define("ERROR_UNKNOWN", 100);
define("ERROR_ACCOUNT_ID",400);
define("REVOKED_ACCESS",500);


//Email Configurations
define("HOST","");
define("EMAIL","");
define("PASSWORD", "");
define("TITLE","");




?>