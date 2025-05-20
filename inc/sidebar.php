<nav class="side-bar">
	<?php
	if ($_SESSION['role'] == "admin") {
	?>
		<!-- admin Navigation Bar -->
		<ul id="navList">
			<li>
				<a href="index.php">
					<i class="fa fa-tachometer" aria-hidden="true"></i>
					<span>Dashboard</span>
				</a>
			</li>
			<li>
				<a href="user.php">
					<i class="fa fa-users" aria-hidden="true"></i>
					<span>Manage Users</span>
				</a>
			</li>
			<li>
				<a href="Projects.php">
					<i class="fa fa-tasks" aria-hidden="true"></i>
					<span>All Projects</span>
				</a>
			</li>
			<li>
				<a href="create_task.php">
					<i class="fa fa-plus" aria-hidden="true"></i>
					<span>Create Task</span>
				</a>
			</li>
			<li>
				<a href="tasks.php">
					<i class="fa fa-tasks" aria-hidden="true"></i>
					<span>All Tasks</span>
				</a>
			</li>
			<li>
				<a href="notifications.php">
					<i class="fa fa-bell" aria-hidden="true"></i>
					<span>Notifications</span>
				</a>
			</li>
			<li>
				<a href="logout.php">
					<i class="fa fa-sign-out" aria-hidden="true"></i>
					<span>Logout</span>
				</a>
			</li>
		</ul>
	<?php } else if ($_SESSION['role'] == "project_manager") { ?>
		<!-- project manager Navigation Bar -->
		<ul id="navList">
			<li>
				<a href="index.php">
					<i class="fa fa-tachometer" aria-hidden="true"></i>
					<span>Dashboard</span>
				</a>
			</li>
			<li>
				<a href="user.php">
					<i class="fa fa-users" aria-hidden="true"></i>
					<span>Users</span>
				</a>
			</li>
			<li>
				<a href="Projects.php">
					<i class="fa fa-tasks" aria-hidden="true"></i>
					<span>All Projects</span>
				</a>
			</li>
			<li>
				<a href="create_task.php">
					<i class="fa fa-plus" aria-hidden="true"></i>
					<span>Create Task</span>
				</a>
			</li>
			<li>
				<a href="my_task.php">
					<i class="fa fa-tasks" aria-hidden="true"></i>
					<span>My Tasks</span>
				</a>
			</li>
			<li>
				<a href="tasks.php">
					<i class="fa fa-tasks" aria-hidden="true"></i>
					<span>All Tasks</span>
				</a>
			</li>
			<li>
				<a href="notifications.php">
					<i class="fa fa-bell" aria-hidden="true"></i>
					<span>Notifications</span>
				</a>
			</li>
			<li>
				<a href="logout.php">
					<i class="fa fa-sign-out" aria-hidden="true"></i>
					<span>Logout</span>
				</a>
			</li>
		</ul>
	<?php } else if ($_SESSION['role'] == "employee") { ?>
		<!-- employee Navigation Bar -->
		<ul id="navList">
			<li>
				<a href="index.php">
					<i class="fa fa-tachometer" aria-hidden="true"></i>
					<span>Dashboard</span>
				</a>
			</li>
			<li>
				<a href="user.php">
					<i class="fa fa-users" aria-hidden="true"></i>
					<span>Users</span>
				</a>
			</li>
			<li>
				<a href="Projects.php">
					<i class="fa fa-tasks" aria-hidden="true"></i>
					<span>All Projects</span>
				</a>
			</li>
			<li>
				<a href="my_task.php">
					<i class="fa fa-tasks" aria-hidden="true"></i>
					<span>My Tasks</span>
				</a>
			</li>
			<li>
				<a href="notifications.php">
					<i class="fa fa-bell" aria-hidden="true"></i>
					<span>Notifications</span>
				</a>
			</li>
			<li>
				<a href="logout.php">
					<i class="fa fa-sign-out" aria-hidden="true"></i>
					<span>Logout</span>
				</a>
			</li>
		</ul>
	<?php } ?>
</nav>