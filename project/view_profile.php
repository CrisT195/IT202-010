<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
  if (isset($_GET["user"])) {
    $userid = $_GET["user"];
  }
?>

<?php
//scores
  if (isset($userid)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT count(1) as total FROM Users JOIN Scores on Users.id = Scores.user_id WHERE Users.id = :userid ORDER BY score DESC");
    $r = $stmt->execute([":userid" => $userid]);
    $total_pages = 0;
    if ($r) {
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$total_pages = (int)safe_get($result, "total", 0);
    }
    $items_per_page = 10;
    $total_pages = ceil($total_pages/$items_per_page);
    $page = (int)safe_get($_GET, "page", 1);
    if($page < 1){
	$page = 1;
    }
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $offset = ($page - 1) * $items_per_page;

    $results = [];
    $stmt = $db->prepare("SELECT Users.id, email, username, score, Scores.user_id, visibility FROM Users JOIN Scores on Users.id = Scores.user_id WHERE Users.id = :userid ORDER BY score DESC LIMIT :offset, :limit");
    $r = $stmt->execute([":userid" => $userid, ":offset" => $offset, ":limit" => $items_per_page]);
    if($r) {
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
	flash("There was a problem fetching the results");
    }
  }
?>

<?php
//competition history
  $db = getDB();
  $query = "SELECT count(1) as total FROM Competitions JOIN Usercompetitions on Usercompetitions.competition_id = Competitions.id WHERE Usercompetitions.user_id = :userid ORDER BY Competitions.expires DESC";
  $stmt = $db->prepare($query);
  $r = $stmt->execute([":userid" => $userid]);
  $total_pages_comp = 0;
  if($r) {
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      $total_pages_comp = (int)safe_get($result, "total", 0);
  }
  $items_per_page = 10;
  $total_pages_comp = ceil($total_pages_comp/$items_per_page);
  $page = (int)safe_get($_GET, "page", 1);
  if($page < 1) {
      $page = 1;
  }
  $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  $offset = ($page - 1) * $items_per_page;

  $query = "SELECT Competitions.* FROM Competitions JOIN Usercompetitions on Usercompetitions.competition_id = Competitions.id WHERE Usercompetitions.user_id = :userid ORDER BY expires DESC LIMIT :offset, :limit";
  $stmt = $db->prepare($query);
  $r = $stmt->execute([":userid" => $userid, ":offset" => $offset, ":limit" => $items_per_page]);
  $results_comp = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<br>
<div class="container">

<?php if($results[0]["visibility"] == 1): ?>

Username: <?php safer_echo($results[0]["username"]);?> <br>
Email: <?php safer_echo($results[0]["email"]);?> <br><br>
<?php safer_echo($results[0]["username"]);?>'s Scores
  <div class="results">
    <?php if (count($results) > 0): ?>
	<div class="list-group">
	    <?php foreach ($results as $r): ?>
		<div class="list-group-item">
		    <div>
			<span>Score:</span>
			<span><?php safer_echo($r["score"]); ?></span>
		    </div>
		</div>
	    <?php endforeach; ?>
	</div>
    <?php else: ?>
	<p>No results</p>
    <?php endif; ?>
  </div>

  <ul class="pagination">
    <?php if(($page - 1) >= 1): ?>
	<li class="page-item">
	    <a class="page-link" href="<?php safer_echo($_SERVER['PHP_SELF'] . "?page=" . ($page - 1));?>" aria-label="Previous">
		<span aria-hidden="true">&laquo;</span>
	    </a>
	</li>
    <?php endif; ?>
    <?php for($i = 0; $i < $total_pages; $i++):?>
	<li class="page-item"><a class="page-link" href="<?php safer_echo($_SERVER['PHP_SELF'] . "?page=" . ($i+1));?>"><?php safer_echo(($i+1));?></a></li>
    <?php endfor; ?>
    <?php if(($page+1) <= $total_pages):?>
	<li class="page-item">
	    <a class="page-link" href="<?php safer_echo($_SERVER['PHP_SELF'] . "?page=" . ($page+1));?>" aria-label="Next">
		<span aria-hidden="true">&raquo;</span>
	    </a>
	</li>
    <?php endif;?>
  </ul>
</div> <br>


<div class="container">
  <div class="h3">Competition History</div>
  <?php if(count($results_comp) > 0):?>
    <ul class="list-group">
      <?php foreach($results_comp as $c):?>
	<li class="list-group-item">
	  <div class="row">
	    <div class="col"><?php safer_echo(safe_get($c, "title", "N/A"));?></div>
	    <div class="col">Participants: <?php safer_echo(safe_get($c, "participants", 0));?> / <?php safer_echo(safe_get($c, "min_participants", 0));?></div>
	    <div class="col">Ends: <?php safer_echo(safe_get($c, "expires", "N/A"));?></div>
	    <div class="col">Reward: <?php safer_echo(safe_get($c, "points", 0));?></div>
	    <div class="col">
		<button class="btn btn-primary" onClick="location.href='view_competition.php?competition=' + <?php safer_echo(safe_get($c, "id", 1));?>;">View</button>
	    </div>
	  </div>
	</li>
      <?php endforeach;?>
    </ul>
  <?php else:?>
    <p>No competition history available</p>
  <?php endif;?>

<ul class="pagination">
  <?php if(($page - 1) >= 1):?>
    <li class="page-item">
	<a class="page-link" href="<?php safer_echo($_SERVER['PHP_SELF'] . "?page=" . ($page-1));?>" aria-label="Previous">
	    <span aria-hidden="true">&laquo;</span>
	</a>
    </li>
  <?php endif; ?>
  <?php for($i = 0; $i < $total_pages_comp; $i++):?>
    <li class="page-item"><a class="page-link" href="<?php safer_echo($_SERVER['PHP_SELF'] . "?page=" . ($i + 1));?>"><?php safer_echo(($i+1));?></a></li>
  <?php endfor; ?>
  <?php if(($page+1) <= $total_pages_comp):?>
    <li class="page-item">
	<a class="page-link" href="<?php safer_echo($_SERVER['PHP_SELF'] . "?page=" . ($page+1));?>" aria-label="Next">
	    <span aria-hidden="true">&raquo;</span>
	</a>
    </li>
  <?php endif;?>
</ul>


<?php else: ?>
  <p>Sorry! This user is private</p>
<?php endif; ?>
</div>

<?php require(__DIR__ . "/partials/flash.php");
