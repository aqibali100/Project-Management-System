<?php
session_start();
include './config/db.php';
include "./app/Model/Notification.php";
$created_by = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
	header("Location: ./login.php");
	exit();
}
$notifications = get_all_my_notifications($conn, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html>

<head>
	<title>Notifications</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">

</head>

<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/sidebar.php" ?>
		<section class="section-1">
			<h4 class="title">All Notifications</h4>
			<?php if (isset($_GET['success'])) { ?>
				<div class="success" role="alert">
					<?php echo stripcslashes($_GET['success']); ?>
				</div>
			<?php } ?>
			<?php if ($notifications != 0) { ?>
				<table class="main-table">
					<tr>
						<th>#</th>
						<th>Message</th>
						<th>Type</th>
						<th>Date</th>
					</tr>
					<?php $i = 0;
					foreach ($notifications as $notification) { ?>
						<tr>
							<td><?= ++$i ?></td>
							<td><?= $notification['message'] ?></td>
							<td><?= $notification['type'] ?></td>
							<td><?= $notification['date'] ?></td>
						</tr>
					<?php	} ?>
				</table>
			<?php } else { ?>
				<h3>You have zero notification</h3>
			<?php  } ?>

		</section>
	</div>


	<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(4)");
		active.classList.add("active");
	</script>


</body>

</html>