<?php
session_start();
include './config/db.php';
$created_by = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
	header("Location: ./login.php");
	exit();
}

//fetch and count user on role based
try {
	$stmt = $conn->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
	$stmt->execute();
	$roleCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$admins = $projectManagers = $employees = 0;

	foreach ($roleCounts as $row) {
		switch (strtolower($row['role'])) {
			case 'admin':
				$admins = $row['count'];
				break;
			case 'project_manager':
				$projectManagers = $row['count'];
				break;
			case 'employee':
				$employees = $row['count'];
				break;
		}
	}
} catch (PDOException $e) {
}


//fetch and count project
$countProjects = ['total_projects' => 0];

try {
	$stmt = $conn->prepare("SELECT COUNT(*) as total FROM projects");
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($result && isset($result['total'])) {
		$countProjects['total_projects'] = $result['total'];
	}
} catch (PDOException $e) {
	$countProjects['total_projects'] = 0;
}


//status based tasks fetch
$today = date('Y-m-d');

try {
	$stmt = $conn->prepare("
        SELECT 
            COUNT(*) AS total_tasks,
            SUM(status = 'in_progress') AS in_progress,
            SUM(status = 'not_started') AS not_started,
            SUM(status = 'completed') AS completed,
            SUM(status = 'hold') AS hold,
            SUM(due_date = :today) AS due_today,
            SUM(due_date < :today AND status NOT IN ('completed','hold')) AS overdue
        FROM tasks
        WHERE created_by = :created_by
    ");
	$stmt->execute([':created_by' => $created_by, ':today' => $today]);
	$counts = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	$counts = [
		'total_tasks' => 0,
		'in_progress' => 0,
		'not_started' => 0,
		'completed' => 0,
		'hold' => 0,
		'due_today' => 0,
		'overdue' => 0
	];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Dashboard | Project Management System</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
</head>

<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/sidebar.php" ?>
		<section class="section-1">
			<div class="dashboard">
				<div class="dashboard-item">
					<i class="fa fa-users"></i>
					<span><?php echo $admins; ?> Admins</span>
				</div>
				<div class="dashboard-item">
					<i class="fa fa-users"></i>
					<span><?php echo $projectManagers; ?> Project Managers</span>
				</div>
				<div class="dashboard-item">
					<i class="fa fa-users"></i>
					<span><?php echo $employees; ?> Employees</span>
				</div>
				<div class="dashboard-item">
					<i class="fa fa-tasks"></i>
					<span><?= $countProjects['total_projects'] ?> Projects</span>
				</div>
				<div class="dashboard-item">
					<i class="fa fa-tasks"></i>
					<span><?= $counts['total_tasks'] ?> All Tasks</span>
				</div>
				<div class="dashboard-item">
					<i class="fa fa-spinner"></i>
					<span><?= $counts['in_progress'] ?> Tasks In Progress</span>
				</div>
				<div class="dashboard-item">
					<i class="fa fa-square-o"></i>
					<span><?= $counts['not_started'] ?> Tasks Not Started</span>
				</div>
				<div class="dashboard-item">
					<i class="fa fa-check-square-o"></i>
					<span><?= $counts['completed'] ?> Tasks Completed</span>
				</div>
				<div class="dashboard-item">
					<i class="fa fa-clock-o"></i>
					<span><?= $counts['hold'] ?> Tasks Hold</span>
				</div>
				<div class="dashboard-item">
					<i class="fa fa-exclamation-triangle"></i>
					<span><?= $counts['due_today'] ?> Tasks Due Today</span>
				</div>
				<div class="dashboard-item">
					<i class="fa fa-window-close-o"></i>
					<span><?= $counts['overdue'] ?> Tasks Overdue</span>
				</div>
			</div>
		</section>
	</div>

	<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(1)");
		active.classList.add("active");
	</script>

	<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>

	<script>
		Pusher.logToConsole = true;
		var pusher = new Pusher('0efd8a2b41e70eba694f', {
			cluster: 'ap2'
		});
		var channel = pusher.subscribe('my-channel');
		channel.bind('my-event', function(data) {
			alert(JSON.stringify(data));
		});
	</script>
</body>

</html>