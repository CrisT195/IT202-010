<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we use this to safely get the email to display
$email = "";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
    $email = $_SESSION["user"]["email"];
}
?>
<p>Welcome, <?php echo $email; ?></p>
<?php include('typingGame.html'); ?>

<?php
if(isset($_SESSION["user"]) && isset($_SESSION["user"]["id"]) && isset($_POST["score"]) ) {
//	$score = $_GET["score"];
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
//$score = $_POST['score'];
//$sql = "INSERT INTO Scores (score, user_id) VALUES(:score,:user)");
//print_r($_SESSION);
?>

<?php require(__DIR__ . "/partials/flash.php");

