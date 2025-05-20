<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include './config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ./login.php");
  exit();
}

// Update Profile Data
if (isset($_POST['update'])) {
  $id = $_SESSION['user_id'];
  $name = $_POST['name'];
  $email = $_POST['email'];
  $contact = $_POST['contact'];
  $city = $_POST['city'];
  $designation = $_POST['designation'];
  $stack = $_POST['stack'];
  $password = $_POST['password'];

  try {
    if (!empty($password)) {
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, contact = :contact, city = :city, designation = :designation, stack = :stack, password = :password WHERE id = :id");
      $result = $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':contact' => $contact,
        ':city' => $city,
        ':designation' => $designation,
        ':stack' => $stack,
        ':password' => $hashedPassword,
        ':id' => $id
      ]);
    } else {
      $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, contact = :contact, city = :city, designation = :designation, stack = :stack WHERE id = :id");
      $result = $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':contact' => $contact,
        ':city' => $city,
        ':designation' => $designation,
        ':stack' => $stack,
        ':id' => $id
      ]);
    }
    //Update Session
    if ($result) {
      echo "Update successful!";
      $_SESSION['name'] = $name;
      $_SESSION['email'] = $email;
      $_SESSION['contact'] = $contact;
      $_SESSION['city'] = $city;
      $_SESSION['designation'] = $designation;
      $_SESSION['stack'] = $stack;
      header("Location: Profile.php");
    } else {
      echo "Update failed.";
    }
  } catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
  }

  $success = "Profile updated successfully!";
}

$editMode = isset($_GET['edit']) && $_GET['edit'] == 1;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile | Project Management System</title>
  <link rel="stylesheet" href="./css/Profile.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container">
    <div class="main-body">

      <nav aria-label="breadcrumb" class="main-breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item"><a href="user.php">User</a></li>
          <li class="breadcrumb-item active" aria-current="page">User Profile</li>
        </ol>
      </nav>
      <div class="row gutters-sm d-flex justify-content-center">
        <div class="col-md-4 mb-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex flex-column align-items-center text-center">
                <img src="<?php echo $_SESSION['image'] ?? 'img/user.png'; ?>" alt="Admin" class="rounded-circle" width="150">
                <div class="mt-3">
                  <h4><?php echo $_SESSION['name'] ?? ''; ?></h4>
                  <p class="text-secondary mb-1"><?php echo $_SESSION['designation'] ?? ''; ?></p>
                  <p class="text-muted font-size-sm"><?php echo $_SESSION['city'] ?? ''; ?></p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <form method="POST" action="" class="form-width">
          <div class="col-md-8 form-width-1">
            <div class="card mb-3">
              <div class="card-body profile-body">
                <div class="row d-flex align-items-center">
                  <div class="col-sm-3">
                    <h6 class="mb-0">Full Name</h6>
                  </div>
                  <div class="col-sm-9 text-secondary">
                    <input type="text" class="form-control" name="name" value="<?php echo $_SESSION['name'] ?? ''; ?>" <?= !$editMode ? 'readonly' : '' ?>></input>
                  </div>
                </div>
                <hr>
                <div class="row d-flex align-items-center">
                  <div class="col-sm-3">
                    <h6 class="mb-0">Email</h6>
                  </div>
                  <div class="col-sm-9 text-secondary">
                    <input type="email" class="form-control" name="email" value="<?php echo $_SESSION['email'] ?? ''; ?>" <?= !$editMode ? 'readonly' : '' ?>></input>
                  </div>
                </div>
                <hr>
                <hr>
                <div class="row d-flex align-items-center">
                  <div class="col-sm-3">
                    <h6 class="mb-0">Password</h6>
                  </div>
                  <div class="col-sm-9 text-secondary">
                    <input type="password" class="form-control" name="password" value="<?= $editMode ? '' : '********' ?>" <?= !$editMode ? 'readonly' : '' ?>>
                  </div>
                </div>
                <hr>
                <div class="row d-flex align-items-center">
                  <div class="col-sm-3">
                    <h6 class="mb-0">Contact Number</h6>
                  </div>
                  <div class="col-sm-9 text-secondary">
                    <input type="number" class="form-control" name="contact" value="<?php echo $_SESSION['contact'] ?? ''; ?>" <?= !$editMode ? 'readonly' : '' ?>></input>
                  </div>
                </div>
                <hr>
                <hr>
                <div class="row d-flex align-items-center">
                  <div class="col-sm-3">
                    <h6 class="mb-0">City</h6>
                  </div>
                  <div class="col-sm-9 text-secondary">
                    <input type="text" class="form-control" name="city" value="<?php echo $_SESSION['city'] ?? ''; ?>" <?= !$editMode ? 'readonly' : '' ?>></input>
                  </div>
                </div>
                <hr>
                <hr>
                <div class="row d-flex align-items-center">
                  <div class="col-sm-3">
                    <h6 class="mb-0">Designation</h6>
                  </div>
                  <div class="col-sm-9 text-secondary">
                    <input type="text" class="form-control" name="designation" value="<?php echo $_SESSION['designation'] ?? ''; ?>" <?= !$editMode ? 'readonly' : '' ?>></input>
                  </div>
                </div>
                <hr>
                <hr>
                <div class="row d-flex align-items-center">
                  <div class="col-sm-3">
                    <h6 class="mb-0">Stack</h6>
                  </div>
                  <div class="col-sm-9 text-secondary">
                    <input type="text" class="form-control" name="stack" value="<?php echo $_SESSION['stack'] ?? ''; ?>" <?= !$editMode ? 'readonly' : '' ?>></input>
                  </div>
                </div>
                <hr>
                <hr>
                <div class="row d-flex align-items-center">
                  <div class="col-sm-3">
                    <h6 class="mb-0">Role</h6>
                  </div>
                  <div class="col-sm-9 text-secondary">
                    <input type="text" class="form-control" value="<?= ucwords($_SESSION['role'] ?? '') ?>" disabled>
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-12">
                    <?php if ($editMode): ?>
                      <button type="submit" name="update" class="btn btn-success">Save Changes</button>
                      <a href="Profile.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                      <a class="btn btn-info" href="?edit=1">Edit Profile</a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>

</html>