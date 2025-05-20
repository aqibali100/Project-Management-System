<?php
session_start();
include './config/db.php';

if (!isset($_SESSION['user_id'])) {
	header("Location: ./login.php");
	exit();
}

// Delete user by id
if (isset($_GET['delete_id'])) {
	$deleteId = $_GET['delete_id'];
	$query = "DELETE FROM users WHERE id = $deleteId";
	$result = $conn->query($query);
	header("Location: user.php");
}

// Fetch all users
$fetch_users = "SELECT * FROM users";
$users = $conn->query($fetch_users);

?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Users | Project Management System</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
</head>

<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/sidebar.php" ?>
		<section class="section-1">

			<?php if ($_SESSION['role'] === 'admin'): ?>
				<h4 class="title">Manage Users <a href="add-user.php">Add User</a></h4>
			<?php endif; ?>

			<h3 class="title">All Users</h3>
			<?php if (isset($_GET['success'])) { ?>
				<div class="success" role="alert">
					<?php echo stripcslashes($_GET['success']); ?>
				</div>
			<?php } ?>
			<?php if ($users != 0) { ?>
				<table class="main-table">
					<tr>
						<th>#</th>
						<th>Full Name</th>
						<th>Email</th>
						<th>Contact</th>
						<th>role</th>
						<th>city</th>
						<th>designation</th>
						<th>stack</th>
						<?php if ($_SESSION['role'] === 'admin'): ?>
							<th>Action</th>
						<?php endif; ?>
					</tr>
					<?php $i = 0;
					foreach ($users as $user) { ?>
						<tr>
							<td><?= ++$i ?></td>
							<td><?= $user['name'] ?></td>
							<td><?= $user['email'] ?></td>
							<td><?= $user['contact'] ?></td>
							<td><?= ucwords(str_replace('_', ' ', $user['role'])) ?></td>
							<td><?= $user['city'] ?></td>
							<td><?= $user['designation'] ?></td>
							<td><?= $user['stack'] ?></td>

							<?php if ($_SESSION['role'] === 'admin'): ?>
								<td>
									<a href="edit-user.php?id=<?= $user['id'] ?>" class="edit-btn">Edit</a>
									<a href="?delete_id=<?= $user['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
								</td>
							<?php endif; ?>
						</tr>
					<?php	} ?>
				</table>
			<?php } else { ?>
				<h3>Empty</h3>
			<?php  } ?>

		</section>
	</div>

	<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(2)");
		active.classList.add("active");
	</script>
</body>

</html>