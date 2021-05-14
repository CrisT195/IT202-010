<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//Note: we have this up here, so our update happens before our get/fetch
//that way we'll fetch the updated data and have it correctly reflect on the form below
//As an exercise swap these two and see how things change
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$db = getDB();
//save data if we submitted the form
if (isset($_POST["saved"])) {
    $isValid = true;
    //check if our email changed
    $newEmail = get_email();
    if (get_email() != $_POST["email"]) {
        //TODO we'll need to check if the email is available
        $email = $_POST["email"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where email = :email");
        $stmt->execute([":email" => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;//default it to a failure scenario
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            flash("Email already in use");
            //for now we can just stop the rest of the update
            $isValid = false;
        }
        else {
            $newEmail = $email;
        }
    }
    $newUsername = get_username();
    if (get_username() != $_POST["username"]) {
        $username = $_POST["username"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where username = :username");
        $stmt->execute([":username" => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;//default it to a failure scenario
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            flash("Username already in use");
            //for now we can just stop the rest of the update
            $isValid = false;
        }
        else {
            $newUsername = $username;
        }
    } //
    $vis = 0;
    if(isset($_POST["visibility"])){
	$visibility = $_POST["visibility"];
	if($visibility == "public"){
	    $vis = 1;
	} else {
	    $vis = 0;
	}
    }
	//
    if ($isValid) {
        $stmt = $db->prepare("UPDATE Users set email = :email, username= :username, visibility = :visibility where id = :id");
        $r = $stmt->execute([":email" => $newEmail, ":username" => $newUsername, ":visibility" => $vis, ":id" => get_user_id()]);
        if ($r) {
            flash("Updated profile");
        }
        else {
            flash("Error updating profile");
        }
        //password is optional, so check if it's even set
        //if so, then check if it's a valid reset request
        if (!empty($_POST["password"]) && !empty($_POST["confirm"])) {
            if ($_POST["password"] == $_POST["confirm"]) {
                $password = $_POST["password"];
                $hash = password_hash($password, PASSWORD_BCRYPT);
                //this one we'll do separate
                $stmt = $db->prepare("UPDATE Users set password = :password where id = :id");
                $r = $stmt->execute([":id" => get_user_id(), ":password" => $hash]);
                if ($r) {
                    flash("Reset Password");
                }
                else {
                    flash("Error resetting password");
                }
            }
        }
//fetch/select fresh data in case anything changed
        $stmt = $db->prepare("SELECT email, username, visibility from Users WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => get_user_id()]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $email = $result["email"];
            $username = $result["username"];
	    $visibility = $result["visibility"];
            //let's update our session too
            $_SESSION["user"]["email"] = $email;
            $_SESSION["user"]["username"] = $username;
	    $_SESSION["user"]["visibility"] = $visibility;
        }
    }
    else {
        //else for $isValid, though don't need to put anything here since the specific failure will output the message
    }
}
?>
<?php
  //add scores...
  $db = getDB();
  //fetch count of filtered results
  $query = "SELECT count(1) as total FROM Users JOIN Scores on Users.id = Scores.user_id WHERE Users.id = :id ORDER BY score DESC";
  $stmt = $db->prepare($query);
  $user = get_user_id();
  $r = $stmt->execute([":id" => $user]);
  $total_pages = 0;
  if($r){
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      $total_pages = (int)safe_get($result, "total", 0);
  }
  $items_per_page = 10;
  //calc number of pages
  $total_pages = ceil($total_pages/$items_per_page);
  //get current page (default to 1)
  $page = (int)safe_get($_GET, "page", 1);
  if($page < 1){
      $page = 1;
  }
  $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //determine offset for running the data query
  $offset = ($page - 1) * $items_per_page;

  $results = [];
  $stmt = $db->prepare("SELECT Users.id, username, score, Scores.user_id FROM Users JOIN Scores on Users.id = Scores.user_id WHERE Users.id = :id ORDER BY score DESC LIMIT :offset, :limit");
  $r = $stmt->execute([":id" => $user, ":offset" => $offset, ":limit" => $items_per_page]);
  //flash(var_export($stmt->errorInfo(), true));
  if ($r) {
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  else {
	flash("There was a problem fetching the results");
  }
  //$results = getTopWeeklyScores(); //to test function
?>

<?php
  //competition history...
  $db = getDB();
  $query = "SELECT count(1) as total FROM Competitions JOIN Usercompetitions on Usercompetitions.competition_id = Competitions.id WHERE Usercompetitions.user_id = :id ORDER BY Competitions.expires DESC";
  $stmt = $db->prepare($query);
  $r = $stmt->execute([":id" => get_user_id()]);
  $total_pages_comp = 0;
  if($r){
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_pages_comp = (int)safe_get($result, "total", 0);
  }
  $items_per_page = 10;
  $total_pages_comp = ceil($total_pages_comp/$items_per_page);
  $page = (int)safe_get($_GET, "page", 1);
  if($page < 1){
    $page = 1;
  }
  $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  $offset = ($page - 1) * $items_per_page;

  $query = "SELECT Competitions.* FROM Competitions JOIN Usercompetitions on Usercompetitions.competition_id = Competitions.id WHERE Usercompetitions.user_id = :id ORDER BY expires DESC LIMIT :offset, :limit";
  $stmt = $db->prepare($query);
  $r = $stmt->execute([":id" => get_user_id(), ":offset" => $offset, ":limit" => $items_per_page]);
  $results_comp = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<br>
<div class="container">Your Scores
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
  <?php if(($page-1) >=1):?>
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
		<button class="btn btn-primary" onClick="location.href='view_competition.php?competition=' + <?php safer_echo(safe_get($c, "id", 1));?>;">View
		</button
	    </div>
	  </div>
	</li>
      <?php endforeach;?>
    </ul>
  <?php else:?>
    <p>No competition history available yet, try joining a competition</p>
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
</div>


<br><br>
<div class="container">
    <form method="POST">
	<div class="form-group">
            <label for="email">Email</label>
            <input class="form-control" type="email" name="email" value="<?php safer_echo(get_email()); ?>"/>
	</div>
	<div class="form-group">
            <label for="username">Username</label>
            <input class="form-control" type="text" maxlength="60" name="username" value="<?php safer_echo(get_username()); ?>"/>
	</div>
	<div class="form-group">
            <!-- DO NOT PRELOAD PASSWORD-->
            <label for="pw">Password</label>
            <input class="form-control" type="password" name="password"/>
	</div>
	<div class="form-group">
            <label for="cpw">Confirm Password</label>
            <input class="form-control" type="password" name="confirm"/>
	</div>
	<div class="form-check" style="padding-left:0px;">
	    <label for="vis" class="form-check-label">Make profile public</label>
	    <input style="margin-left: -500px;" type="checkbox" class="form-check-input" name="visibility" value="public" checked="checked">
	</div>
        <input class="btn btn-primary" type="submit" name="saved" value="Save Profile"/>
    </form>
</div>
<?php require(__DIR__ . "/partials/flash.php");
