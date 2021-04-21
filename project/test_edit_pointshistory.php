<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
	//this will redirect to login and kill the rest fo this script (preventing it from executing)
	flash("You don't have permission to access this page");
	die(header("Location: login.php"));
}
?>

<?php
//we'll put this at the top so both php block have access to it
if(isset($_GET["username"])){
	$username = $_GET["username"];
	echo $username;
} else {
	echo "get username failure";
}
?>

<?php
//saving
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$points_change = $_POST["points_change"];
	$reason = $_POST["reason"];
	$user_id = get_user_id();
	$db = getDB();
	if(isset($username)){
		$stmt = $db->prepare("UPDATE PointsHistory set points_change=:points_change,reason=:reason where user_id=:user_id");
		//$stmt = $db->prepare("INSERT INTO Scores (score, user_id) VALUES(:score, :state,:user)");
		$r = $stmt->execute([
			":points_change"=>$points_change,
			":reason"=>$reason,
			":user_id"=>$user_id
		]);
		if($r){
			flash("Updated successfully with user_id: " . $user_id);
		}
		else{
			$e = $stmt->errorInfo();
			flash("Error updating: " . var_export($e, true));
		}
	}
	else{
		flash("Username isn't set, we need Username in order to update");
	}
}
?>

<?php
//fetching
$result = [];
if(isset($username)){
	$username = $_GET["username"];
	$db = getDB();
	$stmt = $db->prepare("SELECT Users.id, username, points_change, reason FROM Users JOIN PointsHistory on Users.id = PointsHistory.user_id WHERE username like :username");
	$r = $stmt->execute([":username"=>$username]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form method="POST">
	<label>Points change</label>
	<input type="number" min="0" name="points_change" value="<?php echo $result['points_change'];?>" />
	<label>Reason</label>
	<input type="text" name="reason" value="<?php echo $result['reason'];?>" />
	<input type="submit" name="save" value="Update"/>
</form>

<?php require(__DIR__ . "/partials/flash.php");
