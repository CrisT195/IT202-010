<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>


<?php
//we'll put this at the top so both php block have access to it
 if(isset($_GET["scoreid"])){
	$scoreid = $_GET["scoreid"];
//	echo $scoreid;
} else {
	echo "get score failure ";
}
?>

<?php
//saving
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$score = $_POST["score"];
	$user_id = get_user_id();
	$db = getDB();
	if(isset($scoreid)){
		$stmt = $db->prepare("UPDATE Scores set score=:score where id=:scoreid");
		//$stmt = $db->prepare("INSERT INTO Scores (score, user_id) VALUES(:score, :state,:user)");
		$r = $stmt->execute([
			":score"=>$score,
			":scoreid"=>$scoreid
		]);
		if($r){
			flash("Updated successfully with user_id: " . $scoreid);
		}
		else{
			$e = $stmt->errorInfo();
			flash("Error updating: " . var_export($e, true));
		}
	}
	else{
		flash("Username isn't set, we need a Username in order to update");
	}
}
?>

<?php
//fetching
$result = [];
if(isset($scoreid)){
	$scoreid = $_GET["scoreid"];
	$db = getDB();
	$stmt = $db->prepare("SELECT Users.id, username, score FROM Users JOIN Scores on Users.id = Scores.user_id WHERE Scores.id like :scoreid");
	$r = $stmt->execute([":scoreid"=>$scoreid]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container">
<form method="POST">
	<label>Score</label>
	<input type="number" min="0" name="score" value="<?php echo $result["score"];?>" />
	<input type="submit" name="save" value="Update"/>
</form>
</div>

<?php require(__DIR__ . "/partials/flash.php");
