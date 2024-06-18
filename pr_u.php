<?php
session_start();
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header('Location: /');
    exit;
}

$user = $_SESSION['user']; // Assign the session user to the $user variable

$user_role = isset($user['role']) ? $user['role'] : null;

if ($user_role !== 'us') {
    // If the user is not with the role 'us', redirect to another page
    header('Location: /');
    exit;
}

if (isset($_GET['logout'])) {
    session_unset(); // Clear session data
    session_destroy(); // Destroy the session
    header('Location: index1.php'); // Redirect the user to the main page
    exit; // Terminate the script
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль пользователя</title>
    <!-- Your stylesheet here -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'admin_profile.php'; ?>

    <section class="section about-section gray-bg" id="about">
        <div class="container">
            <div class="row align-items-center flex-row-reverse">
                <div class="col-lg-6">
                    <div class="about-text go-to">
                        <h3 class="dark-color"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <h6 class="theme-color lead">Email: <?php echo htmlspecialchars($user['email']); ?></h6>
                        <!-- Add more user information here as needed -->

                        <!-- Additional user information -->
                        <div class="row mb-3">
    <div class="col-sm-3">
        <h6 class="mb-0">Медицинские ограничения</h6>
    </div>
    <div class="col-sm-9 text-secondary">
        <?php if(isset($user['medical_restrictions'])): ?>
            <span id="medical-restrictions"><?php echo htmlspecialchars($user['medical_restrictions']); ?></span>
            <input type="text" name="medical_restrictions" id="medical-restrictions-input" class="form-control" value="<?php echo htmlspecialchars($user['medical_restrictions']); ?>" style="display: none;">
        <?php else: ?>
            <span id="medical-restrictions">N/A</span>
            <input type="text" name="medical_restrictions" id="medical-restrictions-input" class="form-control" value="" style="display: none;">
        <?php endif; ?>
    </div>
</div>
<hr>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
