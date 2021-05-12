<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    flash("You must be logged in to see competitions");
    die(header("Location: login.php"));
}
?>
<?php
    $db = getDB();
    $query = "SELECT count(1) as total FROM Competitions c WHERE expires > CURDATE() AND calced_winner != 1 ORDER BY expires asc";
    $stmt = $db->prepare($query);
    $r = $stmt->execute([":uid" => get_user_id()]);
    $total_pages = 0;
    if($r){
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

    $query = "SELECT c.*, (select count(1) from Usercompetitions uc where uc.competition_id = c.id and uc.user_id = :uid) as `registered` FROM Competitions c WHERE expires > CURDATE() AND calced_winner != 1 ORDER BY expires asc LIMIT :offset, :limit";
    $stmt = $db->prepare($query);
    $r = $stmt->execute([":uid" => get_user_id(), ":offset" => $offset, ":limit" => $items_per_page]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //echo var_export($results, true);
?>
<br>
<div class="container">
<div class="h3">Active Competitions</div>
    <?php if(count($results) > 0):?>
	<ul class="list-group">
	    <?php foreach($results as $c):?>
		<li class="list-group-item">
		    <div class="row">
			<div class="col"><?php safer_echo(safe_get($c, "title", "N/A"));?></div>
			<div class="col">Participants: <?php safer_echo(safe_get($c, "participants", 0));?> / <?php safer_echo(safe_get($c, "min_participants", 0));?></div>
			<div class="col">Ends: <?php safer_echo(safe_get($c, "expires", "N/A"));?></div>
			<div class="col">Reward: <?php safer_echo(safe_get($c, "points", 0));?></div>
			<div class="col">
			    <button class="btn btn-primary" onClick="window.location.href='#';">View
			    </button>
			    <?php if(safe_get($c, "registered", 0) == 0):?>
			    <button id="<?php safer_echo(safe_get($c, 'id', -1));?>" class="btn btn-primary" onclick="join(<?php safer_echo(safe_get($c, 'id', -1));?>)">Join (<?php $cost = (int)safe_get($c, "entry_fee", 0); safer_echo($cost?"Cost: $cost":"Cost: Free");?>)
			    </button>
			    <?php else: ?>
				<button class="btn btn-secondary" disabled="disabled">Registered
				</button>
			    <?php endif;?>
			</div>
		    </div>
		</li>
	    <?php endforeach;?>
	</ul>
    <?php else:?>
	<p>No competitions available yet, please check back later</p>
    <?php endif;?>


<ul class="pagination">
  <?php if(($page - 1) >= 1):?>
    <li class="page-item">
	<a class="page-link" href="<?php safer_echo($_SERVER['PHP_SELF'] . "?page=" . ($page - 1));?>" aria-label="Previous">
	    <span aria-hidden="true">&laquo;</span>
	</a>
    </li>
  <?php endif; ?>
  <?php for($i = 0; $i < $total_pages; $i++):?>
    <li class="page-item"><a class="page-link" href="<?php safer_echo($_SERVER['PHP_SELF'] . "?page=" . ($i + 1));?>"><?php safer_echo(($i+1));?></a></li>
  <?php endfor; ?>
  <?php if(($page +1) <= $total_pages):?>
    <li class="page-item">
	<a class="page-link" href="<?php safer_echo($_SERVER['PHP_SELF'] . "?page=" . ($page+1));?>" aria-label="Next">
	    <span aria-hidden="true">&raquo;</span>
	</a>
    </li>
  <?php endif;?>
</ul>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js">
</script>
<script>
function join(compId){
    $.post("api/join_competition.php", {compId: compId}, (data, status)=>{
	console.log(data, status);
	let resp = JSON.parse(data);
	if(resp.status === 200){
	    let button = document.getElementById(compId);
	    button.disabled = true;
	    button.innerText = "Registered";
	}
	alert(resp.message);
    });
}
</script>
<?php require(__DIR__ . "/partials/flash.php");
