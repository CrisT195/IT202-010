<?php require_once(__DIR__ . "/partials/nav.php");
//require(__DIR__ . "/lib/helpers.php");
?>

<?php
  if (isset($_GET["competition"])) {
    $competitionid = $_GET["competition"];
  }
?>

<?php
  $result = [];
  if (isset($competitionid)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Competitions WHERE id = :competitionid");
    $r = $stmt->execute([":competitionid" => $competitionid]);
    if ($r) {
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
	flash("Error displaying competition");
    } else {
	//scores
	$start = safe_get($result, "created", "");
	$end = safe_get($result, "expires", "");
	$query = "SELECT s.user_id, SUM(s.score) as total, Users.email, Users.username, Users.visibility FROM Scores s JOIN (Usercompetitions uc JOIN Users on uc.user_id = Users.id) on uc.user_id = s.user_id WHERE s.created BETWEEN :start AND :end AND uc.competition_id = :cid GROUP BY user_id order by total desc limit 10";
//	$stmt = $db->prepare("SELECT s.user_id, SUM(s.score) as total from Scores s JOIN Usercompetitions uc on s.user_id WHERE s.created BETWEEN :start AND :end AND uc.competition_id = :cid group by user_id order by total desc limit 10");
	$stmt = $db->prepare($query);
	$r = $stmt->execute([":cid" => $competitionid, ":start" => $start, ":end" => $end]);
	if ($r) {
	    $topscores = $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
    }
   }
  }
?>

<?php if (isset($result) && !empty($result)): ?>
 <div class="container">
  <ul class="list-group">
    <li class="list-group-item">
	<div class="row">
	    <div class="col"><?php safer_echo(safe_get($result, "title", "N/A"));?></div>
	    <div class="col">Participants: <?php safer_echo(safe_get($result, "participants", 0));?> / <?php safer_echo(safe_get($result, "min_participants", 0));?></div>
	    <div class="col">Ends: <?php safer_echo(safe_get($result, "expires", "N/A"));?></div>
	    <div class="col">Reward: <?php safer_echo(safe_get($result, "points", 0));?></div>
	</div>
    </li>
  </ul>

<!-- display top 10 score -->
  <p>Top Scores</p>
  <div class="results">
    <?php if (isset($topscores) && count($topscores) > 0):?>
	<ul class="list-group">
	    <?php foreach ($topscores as $t): ?>
		<li class="list-group-item">
		    <div class="row">
			<div class="col"><?php safer_echo(safe_get($t, "username", "N/A"));?></div>
			<div class="col"><?php safer_echo(safe_get($t, "total", 0));?></div>
			<?php if(safe_get($t, "visibility", 1)):?>
			    <div class="col"><?php safer_echo(safe_get($t, "email", ""));?></div>
			<?php else: ?>
			    <div class="col">Private</div>
			<?php endif; ?>
		    </div>
		</li>
	    <?php endforeach; ?>
	</ul>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
  </div>

</div>

<?php else:?>
  <p>Error looking up competition...</p>
<?php endif;?>

<?php require(__DIR__ . "/partials/flash.php");
