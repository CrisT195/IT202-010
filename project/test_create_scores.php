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
	<label>Score</label>
	<input type="number" min="0" name="score"/>
	<input type="submit" name="save" value="Create"/>
</form>
</div>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$score = $_POST["score"];
	$user = get_user_id();
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Scores (score, user_id) VALUES(:score,:user)");
	$r = $stmt->execute([
		":score"=>$score,
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
