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
if (isset($_GET["historyid"])) {
 $historyid = $_GET["historyid"];
}
?>
<?php
//fetching
$result = [];
if (isset($historyid)) {
 $db = getDB();
 $stmt = $db->prepare("SELECT Users.id, points_change, reason, user_id, Users.username, PointsHistory.id FROM Users JOIN PointsHistory on Users.id = PointsHistory.user_id where PointsHistory.id = :historyid");
 $r = $stmt->execute([":historyid" => $historyid]);
 $result = $stmt->fetch(PDO::FETCH_ASSOC);
 if (!$result) {
  $e = $stmt->errorInfo();
  flash($e[2]);
 }
}
?>
<?php if (isset($result) && !empty($result)): ?>
 <div class="card">
  <div class="card-title">
   <?php safer_echo($result["username"]); ?>
  </div>
  <div class="card-body">
   <div>
    <p>Stats</p>
    <div>Points change: <?php safer_echo($result["points_change"]); ?></div>
    <div>Reason: <?php safer_echo($result["reason"]); ?></div>
    <div>User id: <?php safer_echo($result["user_id"]); ?></div>
    <div>Owned by: <?php safer_echo($result["username"]); ?></div>
   </div>
  </div>
 </div>
<?php else: ?>
 <p>Error looking up username...</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");
