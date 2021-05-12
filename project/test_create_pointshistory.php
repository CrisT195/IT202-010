<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
	//this will redirect to login and kill the rest of this script (prevent it from executing)
	flash("You don't have permission to access this page");
	die(header("Location: login.php"));
}
?>
<div class="container">
<form method="POST">
	<label>Points change</label>
	<input type="number" min="0" name="points_change"/>
	<label>Reason</label>
	<input type="text" name="reason" />
	<input type="submit" name="save" value="Create"/>
</form>
</div>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$points_change = $_POST["points_change"];
	$user = get_user_id();
	$reason = $_POST["reason"];
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO PointsHistory (points_change, user_id, reason) VALUES(:points_change,:user,:reason)");
	$r = $stmt->execute([
		":points_change"=>$points_change,
		":reason"=>$reason,
		":user"=>$user
 	]);
 	if($r){
  		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
}
?>
<?php require(__DIR__ . "/partials/flash.php");
