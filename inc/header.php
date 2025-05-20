<?php
include './config/db.php';
$created_by = $_SESSION['user_id'];
$username = $_SESSION['name'];

if (!isset($_SESSION['user_id'])) {
	header("Location: ./login.php");
	exit();
}

$stmt = $conn->prepare("SELECT * FROM notifications WHERE name = :username ORDER BY created_at DESC LIMIT 10");
$stmt->bindParam(':username', $username);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch unread count
$stmtUnread = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE (name = :username) AND is_read = 0");
$stmtUnread->bindParam(':username', $username);
$stmtUnread->execute();
$unreadCount = $stmtUnread->fetchColumn();
?>

<header class="header">
	<div class="header-left">
		<label for="checkbox">
			<i id="navbtn" class="fa fa-bars" aria-hidden="true"></i>
		</label>
		<h2 class="u-name"><a href="index.php">Project Management System</a>
		</h2>
	</div>
	<div class="header-right">
		<span class="notification" id="notificationBtn">
			<i class="fa fa-bell" aria-hidden="true"></i>
			<span class="unread-count" id="notificationNum"></span>
		</span>
		<a href='Profile.php'>
			<div class="user-p">
				<img src="<?php echo $_SESSION['image'] ?? 'img/user.png'; ?>" alt="Profile Image">
			</div>
		</a>
	</div>
</header>
<div class="notification-bar" id="notificationBar">
	<ul id="notifications">

	</ul>
</div>

<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
<script>
	const initialNotifications = <?php echo json_encode($notifications); ?>;
	const initialUnreadCount = <?php echo $unreadCount; ?>;
</script>
<script>
	document.addEventListener('DOMContentLoaded', () => {
		const notifNum = document.getElementById('notificationNum');
		if (initialUnreadCount > 0) {
			notifNum.innerText = initialUnreadCount;
		}

		const notificationsList = document.getElementById('notifications');
		initialNotifications.forEach(notif => {
			const li = document.createElement('li');
			li.textContent = notif.message;
			notificationsList.appendChild(li);
		});
	});
</script>


<script>
	const pusher = new Pusher('0efd8a2b41e70eba694f', {
		cluster: 'ap2',
		encrypted: true
	});
	const channel = pusher.subscribe('my-channel');

	channel.bind('my-event', function(data) {
		const notifNum = document.getElementById('notificationNum');
		notifNum.innerText = data.unreadCount > 0 ? data.unreadCount : '';

		const notificationsList = document.getElementById('notifications');
		const newMessage = document.createElement('li');
		newMessage.textContent = data.message;
		notificationsList.prepend(newMessage);
	});

	// Show/hide notification bar
	document.getElementById('notificationBtn').addEventListener('click', function () {
		const notifBar = document.getElementById('notificationBar');
		if (notifBar.style.display === 'none' || notifBar.style.display === '') {
			notifBar.style.display = 'block';
			document.getElementById('notificationNum').innerText = '';
		} else {
			notifBar.style.display = 'none';
		}
	});
</script>
