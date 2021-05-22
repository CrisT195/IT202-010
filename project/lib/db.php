<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
function getDB(){
    global $db;
    //returns an existing connection or creates a new one if needed and assigns it to $db
    if(!isset($db)) {
        try{
            require_once(__DIR__. "/config.php");
            $connection_string = "mysql:host=$dbhost;dbname=$dbdatabase;charset=utf8mb4";
            //using the PDO connector create a new connect to the DB
            $db = new PDO($connection_string, $dbuser, $dbpass);
        }
    catch(Exception $e){
            var_export($e);
            $db = null;
        }
    }
    return $db;
}
?>
