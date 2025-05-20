<?php
session_start();
include './config/db.php';
$created_by = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit();
}

// Delete Project by id
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $query = "DELETE FROM projects WHERE id = $deleteId";
    $result = $conn->query($query);
    header("Location: Projects.php");
}

// Fetch all projects
try {
    $stmt = $conn->prepare("SELECT projects.*, users.name AS assigned_user_name FROM projects LEFT JOIN users ON projects.assigned_to = users.id ORDER BY projects.id DESC");
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $projects = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects | Project Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <div class="body">
        <?php include "inc/sidebar.php" ?>
        <section class="section-1">
            <h4 class="title-2">
                All Projects
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <h4 class="title">Manage Projects <a href="create_project.php">Add Project</a></h4>
                <?php endif; ?>
            </h4>
            <?php if (isset($_GET['success'])) { ?>
                <div class="success" role="alert">
                    <?php echo stripcslashes($_GET['success']); ?>
                </div>
            <?php } ?>
            <?php if ($projects != 0) { ?>
                <table class="main-table">
                    <tr>
                        <th>#</th>
                        <th>Project Name</th>
                        <th>Client Name</th>
                        <th>Assigned To</th>
                        <th>End Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <th>Action</th>
                        <?php endif; ?>
                    </tr>
                    <?php $i = 0;
                    foreach ($projects as $project) { ?>
                        <tr>
                            <td><?= ++$i ?></td>
                            <td><?= $project['project_name'] ?></td>
                            <td><?= $project['client_name'] ?></td>
                            <td><?= htmlspecialchars($project['assigned_user_name']) ?></td>
                            <td><?= $project['end_date'] ?></td>
                            <td><?= ucwords($project['priority']) ?></td>
                            <td><?= ucwords(str_replace('_', ' ', $project['status'])) ?></td>

                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <td>
                                    <a href="edit_project.php?id=<?= $project['id'] ?>" class="edit-btn">Edit</a>
                                    <a href="?delete_id=<?= $project['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php    } ?>
                </table>
            <?php } else { ?>
                <h3>Empty</h3>
            <?php  } ?>
        </section>
    </div>

    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(3)");
        active.classList.add("active");
    </script>
</body>

</html>