<?php

// Get all notifications for a specific user
function get_all_my_notifications($conn, $id)
{
	$sql = "SELECT * FROM notifications WHERE recipient = ?";
	$stmt = $conn->prepare($sql);
	$stmt->execute([$id]);

	if ($stmt->rowCount() > 0) {
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	return [];
}

// Count unread notifications
function count_notification($conn, $id)
{
	$sql = "SELECT COUNT(*) FROM notifications WHERE recipient = ? AND is_read = 0";
	$stmt = $conn->prepare($sql);
	$stmt->execute([$id]);

	return $stmt->fetchColumn();
}

// Insert new notification
function insert_notification($conn, $data)
{
	$sql = "INSERT INTO notifications (message, recipient, title, url) VALUES (?, ?, ?, ?)";
	$stmt = $conn->prepare($sql);
	return $stmt->execute($data);
}

// Mark notification as read
function notification_make_read($conn, $recipient_id, $notification_id)
{
	$sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND recipient = ?";
	$stmt = $conn->prepare($sql);
	return $stmt->execute([$notification_id, $recipient_id]);
}
