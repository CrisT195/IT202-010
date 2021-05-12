<?php
session_start();//we can start our session here so we don't need to worry about it on other pages
require_once(__DIR__ . "/db.php");
//this file will contain any helpful functions we create
//I have provided two for you
function is_logged_in() {
    return isset($_SESSION["user"]);
}

function has_role($role) {
    if (is_logged_in() && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] == $role) {
                return true;
            }
        }
    }
    return false;
}

function get_username() {
    if (is_logged_in() && isset($_SESSION["user"]["username"])) {
        return $_SESSION["user"]["username"];
    }
    return "";
}

function get_email() {
    if (is_logged_in() && isset($_SESSION["user"]["email"])) {
        return $_SESSION["user"]["email"];
    }
    return "";
}

function get_user_id() {
    if (is_logged_in() && isset($_SESSION["user"]["id"])) {
        return $_SESSION["user"]["id"];
    }
    return -1;
}

function safer_echo($var) {
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}

/*** Attempts to safely retrieve a key from an array, otherwise returns the default
 * @param $arr
 * @param $key
 * @param string $default
 * @return mixed|string
 */
function safe_get($arr, $key, $default = "")
{
    if (is_array($arr) && isset($arr[$key])) {
        return $arr[$key];
    }
    return $default;
}

//for flash feature
function flash($msg) {
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $msg);
    }
    else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $msg);
    }

}

function getMessages() {
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}

//end flash

function get_points_balance(){
	$uid = get_user_id();
	$db = getDB();
	$query = "SELECT IFNULL(points,0) as `points` from Userstats where user_id = :id";
	$stmt = $db->prepare($query);
	$r = $stmt->execute([":id"=>$uid]);
	if($r){
	    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
	    if(isset($stats["points"])){
		return (int)$stats["points"];
	    }
	}
	return 0;
}

function changePoints($user_id, $points, $reason){
    $db = getDB();
    $query = "INSERT INTO PointsHistory (user_id, points_change, reason) VALUES (:uid, :change, :reason)";
    $stmt = $db->prepare($query);
    $r = $stmt->execute([":uid" => $user_id, ":change" => $points, ":reason" => $reason]);
    if ($r) {
	$query = "INSERT IGNORE INTO Userstats (user_id) VALUES (:uid)";
	$stmt = $db->prepare($query);
	$r = $stmt->execute([":uid" => $user_id]);


        $query = "UPDATE Userstats set points = IFNULL((SELECT sum(points_change) FROM PointsHistory where user_id = :uid),0) WHERE user_id = :uid";
        $stmt = $db->prepare($query);
        $r = $stmt->execute([":uid" => $user_id]);

        //refresh session data
        $_SESSION["user"]["points"] = get_points_balance();
        return $r;
    }
    return false;
}

//scoreboards
function getTopWeeklyScores() {
	$results = [];
	$db = getDB();
	$user = get_user_id();
	$stmt = $db->prepare("SELECT score, username FROM Users JOIN Scores on Users.id = Scores.user_id WHERE Scores.created >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY score DESC LIMIT 10");
	$r = $stmt->execute();
	if ($r) {
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	else {
		flash("There was a problem fetching the results...");
	}
	return $results;
}

function getTopMonthlyScores() {
        $results = [];
        $db = getDB();
        $user = get_user_id();
        $stmt = $db->prepare("SELECT score, username FROM Users JOIN Scores on Users.id = Scores.user_id WHERE Scores.created >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) ORDER BY score DESC LIMIT 10");
        $r = $stmt->execute();
        if ($r) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        else {
                flash("There was a problem fetching the results");
        }
	return $results;
}

function getTopOverallScores() {
        $results = [];
        $db = getDB();
        $user = get_user_id();
//	$stmt = $db->prepare("SELECT Users.id, username, score, Scores.user_id FROM Users JOIN Scores on Users.id = Scores.user_id WHERE Users.id = :id ORDER BY score DESC LIMIT 10");
        //$stmt = $db->prepare("SELECT TOP (10) score, Users.id, username, Scores.user_id FROM Users JOIN Scores on Users.id = Scores.user_id ORDER BY score");
	$stmt = $db->prepare("SELECT score, username FROM Users JOIN Scores on Users.id = Scores.user_id ORDER BY score DESC LIMIT 10");
        $r = $stmt->execute();
        if ($r) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        else {
                flash("There was a problem fetching the results");
        }
	return $results;
}


?>
