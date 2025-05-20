<?php
session_start();
include './config/db.php';
$created_by = $_SESSION['user_id'];
$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
	header("Location: ./login.php");
	exit();
}

try {
	if ($user_id) {
		$query = "SELECT tasks.*, users.name AS assigned_by_name FROM tasks JOIN users ON tasks.created_by = users.id WHERE tasks.assigned_to = :user_id";
		$stmt = $conn->prepare($query);
		$stmt->execute(['user_id' => $user_id]);
		$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} else {
		$tasks = [];
	}
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>My Tasks | Project Management System</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="./css/style.css">
</head>

<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/sidebar.php" ?>
		<section class="section-1">
			<h4 class="title-2">
				My Tasks
			</h4>
			<?php if (isset($_GET['success'])) { ?>
				<div class="success" role="alert">
					<?php echo stripcslashes($_GET['success']); ?>
				</div>
			<?php } ?>
			<?php if ($tasks != 0) { ?>
				<table class="main-table">
					<tr>
						<th>#</th>
						<th>Title</th>
						<th>Project Name</th>
						<th>Assigned By</th>
						<th>Due Date</th>
						<th>Priority</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
					<?php $i = 0;
					foreach ($tasks as $task) { ?>
						<tr>
							<td><?= ++$i ?></td>
							<td><?= $task['title'] ?></td>
							<td><?= $task['project_name'] ?></td>
							<td><?= htmlspecialchars($task['assigned_by_name']) ?></td>
							<td><?php if ($task['due_date'] == "") echo "No Deadline";
								else echo $task['due_date'];
								?></td>
							<td><?= ucwords($task['priority']) ?></td>
							<td><?= ucwords(str_replace('_', ' ', $task['status'])) ?></td>
							<td>
								<a href="edit-task.php?id=<?= $task['id'] ?>" class="edit-btn">Edit</a>
							</td>
						</tr>
					<?php	} ?>
				</table>
			<?php } else { ?>
				<h3>There are no tasks</h3>
			<?php  } ?>

		</section>
	</div>

	<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(4)");
		active.classList.add("active");
	</script>
</body>

</html>