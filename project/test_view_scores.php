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
if (isset( $_GET["scoreid"])) {
    $scoreid = $_GET["scoreid"];
}
?>
<?php
//fetching
$result = [];
if (isset($scoreid)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT Users.id, score, user_id, Users.username, Scores.id FROM Users JOIN Scores on Users.id = Scores.user_id where Scores.id = :scoreid");
    $r = $stmt->execute([":scoreid" => $scoreid]);
//    flash(var_export($r, true));
//    flash(var_export($stmt->errorInfo(), true));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
//var_dump($_SESSION);
?>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
            <?php safer_echo($result["username"]); ?>
        </div>
        <div class="card-body">
            <div>
                <p>Stats</p>
                <div>Score: <?php safer_echo($result["score"]); ?></div>
		<div>Score id: <?php safer_echo($result["id"]); ?></div>
                <div>User id: <?php safer_echo($result["user_id"]); ?></div>
                <div>Owned by: <?php safer_echo($result["username"]); ?></div>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up username...</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");
