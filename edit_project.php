<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include './config/db.php';
$user_id = $_SESSION['user_id'];
$currentUserRole = $_SESSION['role'];
$created_by = $_SESSION['user_id'];


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ./login.php");
    exit();
}

// Get Project Details By ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $project_id = intval($_GET['id']);
    try {
        $stmt = $conn->prepare("SELECT * FROM projects WHERE id = :id");
        $stmt->execute([
            ':id' => $project_id,
        ]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
    }
}

// Fetch users role based
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

// Update Project Details
if (isset($_POST['submit'])) {
    $project_id = intval($_GET['id']);
    $project_name = $_POST['project_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $client_name = $_POST['client_name'] ?? '';
    $assigned_to = $_POST['assigned_to'] ?? 0;
    $priority = $_POST['priority'] ?? '';
    $status = $_POST['status'] ?? '';
    $estimated_duration = $_POST['estimated_duration'] ?? '';
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;

    $updateStmt = $conn->prepare("UPDATE projects SET project_name = :project_name, description = :description, client_name = :client_name, assigned_to = :assigned_to, priority = :priority, status = :status, start_date = :start_date, end_date = :end_date, estimated_duration = :estimated_duration WHERE id = :id AND created_by = :created_by");

    $updated = $updateStmt->execute([
        ':project_name' => $project_name,
        ':description' => $description,
        ':client_name' => $client_name,
        ':assigned_to' => $assigned_to,
        ':priority' => $priority,
        ':status' => $status,
        ':start_date' => $start_date,
        ':end_date' => $end_date,
        ':estimated_duration' => $estimated_duration,
        ':id' => $project_id,
        ':created_by' => $created_by
    ]);

    if ($updated) {
        header("Location: ./Projects.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project | Project Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <div class="body">
        <?php include "inc/sidebar.php" ?>
        <section class="section-1">
            <h4 class="title">Edit Project <a href="Projects.php">Projects</a></h4>
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
                    <input type="text" name="project_name" value="<?= $project['project_name'] ?? '' ?>" class="input-1" placeholder="Enter Your Project Name"><br>
                </div>
                <div class="input-holder">
                    <label>Description</label>
                    <textarea name="description" class="input-1" placeholder="Enter Your Project Description" required><?= $project['description'] ?? '' ?></textarea><br>
                </div>
                <div class="input-holder">
                    <lable>Client Name</lable>
                    <input type="text" name="client_name" value="<?= $project['client_name'] ?? '' ?>" class="input-1" placeholder="Enter Your Client Name"><br>
                </div>
                <div class="input-holder">
                    <label>Assigned to</label>
                    <select name="assigned_to" class="input-1" required>
                        <option value="0">Select User</option>
                        <?php
                        if (!empty($users)) {
                            foreach ($users as $user) {
                                $selected = (isset($project['assigned_to']) && $user['id'] == $project['assigned_to']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($user['id']) . "' $selected>" . htmlspecialchars($user['name']) . "</option>";
                            }
                        }
                        ?>
                    </select><br>
                </div>
                <div class="input-holder">
                    <label>Priority</label>
                    <select name="priority" class="input-1">
                        <option value="0">Select One</option>
                        <?php
                        $priorities = ['low', 'medium', 'high'];
                        foreach ($priorities as $p) {
                            $selected = ($p == ($project['priority'] ?? '')) ? 'selected' : '';
                            echo "<option value='$p' $selected>" . ucfirst($p) . "</option>";
                        }
                        ?>
                    </select><br>
                </div>
                <div class="input-holder">
                    <label>Status</label>
                    <select name="status" class="input-1">
                        <option value="0">Select One</option>
                        <?php
                        $statuses = [
                            'not_started' => 'Not Started',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                            'hold' => 'Hold'
                        ];
                        foreach ($statuses as $key => $label) {
                            $selected = ($key == ($project['status'] ?? '')) ? 'selected' : '';
                            echo "<option value='$key' $selected>$label</option>";
                        }
                        ?>
                    </select><br>
                </div>
                <div class="input-holder">
                    <lable>Estimated Duration</lable>
                    <input type="text" name="estimated_duration" value="<?= $project['estimated_duration'] ?? '' ?>" class="input-1" placeholder="Estimated Duration"><br>
                </div>
                <div class="input-holder">
                    <lable>Start Date</lable>
                    <input type="date" name="start_date" value="<?= $project['start_date'] ?? '' ?>" class="input-1" placeholder="Start Date"><br>
                </div>
                <div class="input-holder">
                    <lable>End Date</lable>
                    <input type="date" name="end_date" value="<?= $project['end_date'] ?? '' ?>" class="input-1" placeholder="End Date"><br>
                </div>

                <button class="edit-btn" type="submit" name="submit">Add</button>
            </form>
        </section>
    </div>
</body>

</html>