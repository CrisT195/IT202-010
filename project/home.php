<?php require_once(__DIR__ . "/partials/nav.php");
//require(__DIR__ . "/lib/helpers.php");
?>
<?php
//we use this to safely get the email to display
$email = "";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
    $email = $_SESSION["user"]["email"];
}
?>
<div style="text-align: center;">
  <?php if(is_logged_in()):?>
    <p>Welcome, <?php echo $email; ?></p>
  <?php else:?>
    <p>You are not logged in. Your score will not be recorded.</p>
  <?php endif;?>
</div>
<?php include('typingGame.html'); ?>

<?php
if(isset($_SESSION["user"]) && isset($_SESSION["user"]["id"]) && isset($_POST["score"]) ) {
	$score = $_POST["score"];

	$user = get_user_id();
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Scores (score, user_id) VALUES(:score,:user)");
	$r = $stmt->execute([
		":score"=>$score,
		":user"=>$user
	]);
	if($r){
		changePoints($user, $score, "completed game");
//		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
//		flash("Error creating: " . var_export($e, true));
	}
}

?>

<!-- <?php
//$weekly_results = getTopWeeklyScores();
//$monthly_results = getTopMonthlyScores();
//$overall_results = getTopOverallScores();
?> -->

<br>
<div class="container">
<div class="row">
<!--
<div class="col-4">
    <h2 class="sub-header">Top Weekly</h2>
    <div class="table-responsive">
	<table class="table table-striped">
	    <thead>
		<tr>
		    <th class="col-md-1">Username</th>
		    <th class="col-md-2">Score</th>
		</tr>
	    </thead>
	    <tbody>
	      <?php if(count($weekly_results) > 0): ?>
	    	  <?php foreach ($weekly_results as $w): ?>
		    <tr>
		      <td class="col-md-1"><?php safer_echo($w["username"]) ?></td>
		      <td class="col-md-2"><?php safer_echo($w["score"])</td>
		    </tr>
		  <?php endforeach; ?>
	      <?php else: ?>
		<p>No results</p>
	      <?php endif; ?>
	    </tbody>
	</table>
    </div>
</div>
-->
<div class="col-4">
    <h2 class="sub-header">Top Monthly</h2>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="col-md-1">Username</th>
                    <th class="col-md-2">Score</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-md-1">output username</td>
                    <td class="col-md-2">output score</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="col-4">
    <h2 class="sub-header">Top Overall</h2>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="col-md-1">Username</th>
                    <th class="col-md-2">Score</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-md-1">output username</td>
                    <td class="col-md-2">output score</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</div>
</div>

<?php require(__DIR__ . "/partials/flash.php");

