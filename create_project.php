<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include './config/db.php';
$user_id = $_SESSION['user_id'];
$currentUserRole = $_SESSION['role'];

if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit();
}

// Fetch users based on role
try {
    if ($currentUserRole === 'admin') {
        $roleToFetch = 'project_manager';
    } elseif ($currentUserRole === 'project_manager') {
        $roleToFetch = 'employee';
    } else {
        $roleToFetch = null;
    }

    if ($roleToFetch) {
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE role = :role");
        $stmt->execute(['role' => $roleToFetch]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $users = [];
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Create Project 
if (isset($_POST['submit'])) {
    $project_name = $_POST['project_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $client_name = $_POST['client_name'] ?? '';
    $assigned_to = $_POST['assigned_to'] ?? 0;
    $priority = $_POST['priority'] ?? '';
    $status = $_POST['status'] ?? '';
    $estimated_duration = $_POST['estimated_duration'] ?? '';
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $created_by = $user_id;

    try {
        $stmt = $conn->prepare("INSERT INTO projects (
            project_name, description, client_name, assigned_to, priority, status, 
            estimated_duration, start_date, end_date, created_by, created_at
        ) VALUES (
            :project_name, :description, :client_name, :assigned_to, :priority, :status,
            :estimated_duration, :start_date, :end_date, :created_by, NOW()
        )");

        $stmt->execute([
            ':project_name' => $project_name,
            ':description' => $description,
            ':client_name' => $client_name,
            ':assigned_to' => $assigned_to,
            ':priority' => $priority,
            ':status' => $status,
            ':estimated_duration' => $estimated_duration,
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':created_by' => $created_by
        ]);

        header("Location: Projects.php");
        exit;
    } catch (PDOException $e) {
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Project | Project Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <div class="body">
        <?php include "inc/sidebar.php" ?>
        <section class="section-1">
            <h4 class="title">Add Project <a href="Projects.php">Projects</a></h4>
            <form class="form-1"
                method="POST">
                <?php if (isset($_GET['error'])) { ?>
                    <div class="danger" role="alert">
                        <?php echo stripcslashes($_GET['error']); ?>
                    </div>
                <?php } ?>

                <?php if (isset($_GET['success'])) { ?>
                    <div class="success" role="alert">
                        <?php echo stripcslashes($_GET['success']); ?>
                    </div>
                <?php } ?>
                <div class="input-holder">
                    <lable>Project Name</lable>
                    <input type="text" name="project_name" class="input-1" placeholder="Enter Your Project Name"><br>
                </div>
                <div class="input-holder">
                    <lable>Description</lable>
                    <textarea type="text" name="description" class="input-1" placeholder="Enter Your Project Description" required></textarea><br>
                </div>
                <div class="input-holder">
                    <lable>Client Name</lable>
                    <input type="text" name="client_name" class="input-1" placeholder="Enter Your Client Name"><br>
                </div>
                <div class="input-holder">
                    <lable>Assigned to</lable>
                    <select name="assigned_to" class="input-1" required>
                        <option value="0">Select User</option>
                        <?php if ($users != 0) {
                            foreach ($users as $user) {
                        ?>
                                <option value="<?= $user['id'] ?>"><?= $user['name'] ?></option>
                        <?php }
                        } ?>
                    </select><br>
                </div>
                <div class="input-holder">
                    <lable>Priority</lable>
                    <select name="priority" class="input-1">
                        <option value="0">Select One</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select><br>
                </div>
                <div class="input-holder">
                    <lable>Status</lable>
                    <select name="status" class="input-1">
                        <option value="0">Select One</option>
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="hold">Hold</option>
                    </select><br>
                </div>
                <div class="input-holder">
                    <lable>Estimated Duration</lable>
                    <input type="text" name="estimated_duration" class="input-1" placeholder="Estimated Duration"><br>
                </div>
                <div class="input-holder">
                    <lable>Start Date</lable>
                    <input type="date" name="start_date" class="input-1" placeholder="Start Date"><br>
                </div>
                <div class="input-holder">
                    <lable>End Date</lable>
                    <input type="date" name="end_date" class="input-1" placeholder="End Date"><br>
                </div>

                <button class="edit-btn" type="submit" name="submit">Add</button>
            </form>
        </section>
    </div>
</body>

</html>