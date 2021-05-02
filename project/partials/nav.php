<!-- CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

<!-- jQuery and JS bundle w/ Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>

<link rel="stylesheet" href="static/css/styles.css">
<?php
//we'll be including this on most/all pages so it's a good place to include anything else we want on those pages
require_once(__DIR__ . "/../lib/helpers.php");
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
<div class="container-fluid">
<ul class="navbar-nav mr-auto">  <!--class="nav"-->
    <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
    <?php if (!is_logged_in()): ?>
        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
        <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
    <?php endif; ?>
    <?php if (has_role("Admin")): ?>
	<li class="nav-item dropdown">
	   <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		Admin
	   </a>
	   <div class="dropdown-menu" aria-labelledby="navbarDropdown">
	   	<a class="nav-link bg-dark" href="test_create_scores.php">Create Score</a>
                <a class="nav-link bg-dark" href="test_list_scores.php">View Score</a>
                <a class="nav-link bg-dark" href="test_create_pointshistory.php">Create history</a>
                <a class="nav-link bg-dark" href="test_list_pointshistory.php">View history</a>
	   </div>
	</li>
    <?php endif; ?>
    <?php if (is_logged_in(false)): ?>
        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
	<li class="nav-item"><a class="nav-link" href="create_competition.php">Create</a><li>
	<li class="nav-item"><a class="nav-link" href="list_competitions.php">Competitions</a><li>
	<li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
    <?php endif; ?>
</ul>
<?php if (is_logged_in(false)): ?>
    <span class="navbar-text">Points: <?php echo get_points_balance(); ?></span>
<?php endif; ?>
</div>
</nav>
