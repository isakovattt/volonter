<?php
session_start();

// Подключение к базе данных
require_once 'vendor/connect.php';

// Проверка аутентификации пользователя
if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit(); // Для прекращения выполнения скрипта после перенаправления
}

// Получаем ID пользователя из сессии
$user_id = $_SESSION['user']['id'];
$dobrye_dela_query = mysqli_query($connect, "SELECT COUNT(*) AS dobrye_dela_count, SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time)) / 3600) AS total_hours, SUM(applications.points) AS total_points FROM applications 
    JOIN events ON applications.event_id = events.id 
    WHERE applications.user_id = '$user_id' AND applications.status = 'approved'");

if (!$dobrye_dela_query) {
    die('Ошибка выполнения запроса: ' . mysqli_error($connect));
}

$dobrye_dela_data = mysqli_fetch_assoc($dobrye_dela_query);
$dobrye_dela_count = $dobrye_dela_data['dobrye_dela_count'];
$total_hours = ($dobrye_dela_data['total_hours'] !== null) ? ceil($dobrye_dela_data['total_hours']) : 0;
$total_points = $dobrye_dela_data['total_points'];

$stmt = $connect->prepare("SELECT u.full_name, u.email, u.login, u.role, u.avatar, AVG(a.rating) AS average_rating FROM users u LEFT JOIN applications a ON u.id = a.user_id WHERE u.id = ? GROUP BY u.id");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $full_name = $user_data['full_name'];
    $email = $user_data['email'];
    
    // Замена значений роли на более человекопонятные
    $role = $user_data['role'];
    if ($role === 'ad') {
        $role = 'Администратор';
    } elseif ($role === 'us') {
        $role = 'Волонтер';
    } elseif ($role === 'st') {
        $role = 'Ответственный за мероприятие';
    }
    
    $login = $user_data['login'];
    $avatar = $user_data['avatar']; // Путь к изображению из базы данных
    $average_rating = ($user_data['average_rating'] !== null) ? round($user_data['average_rating'], 0) : 0; // Округляем среднюю оценку
} else {
    echo "Данные пользователя не найдены";
    exit();
}

$user_id = $_SESSION['user']['id'];
$current_date = date('Y-m-d');

$applications_query = mysqli_query($connect, "SELECT applications.*, events.title, events.location, events.event_date, events.start_time, events.end_time, events.photo 
                                              FROM applications 
                                              INNER JOIN events ON applications.event_id = events.id 
                                              WHERE applications.user_id = '$user_id' 
                                              AND applications.status = 'approved'
                                              ORDER BY events.event_date DESC");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_application'])) {
    $application_id = $_POST['cancel_application'];
    $delete_query = mysqli_query($connect, "DELETE FROM applications WHERE event_id = '$application_id' AND user_id = '$user_id'");
    if ($delete_query) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Ошибка при удалении мероприятия.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Мои баллы</title>
    <link rel="stylesheet" href="css/edit.css">
    <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/bs-brain@2.0.3/components/testimonials/testimonial-3/assets/css/testimonial-3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.0/css/boxicons.min.css" integrity="sha512-pVCM5+SN2+qwj36KonHToF2p1oIvoU3bsqxphdOIWMYmgr4ZqD3t5DjKvvetKhXGc/ZG5REYTT6ltKfExEei/Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.3.45/css/materialdesignicons.css" integrity="sha256-NAxhqDvtY0l4xn+YVa6WjAcmd94NNfttjNsDmNatFVc=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css'>
    <style>
        .avatar-md {
            width: 64px;
            height: 64px;
        }
        .icon {
            font-size: 20px;
            margin-right: 5px;
        }
    </style>
    <!-- Отображение диалогового окна -->
    <script>
        function confirmCancel() {
            if (confirm("Вы точно хотите отменить участие в мероприятии?")) {
                return true;
            } else {
                return false;
            }
        }
    </script>
</head>
<body>
<?php include 'admin_profile.php'; ?>
<div class="container">
    <div class="main-body">
        <div class="row gutters-sm">
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column align-items-center text-center">
                            <!-- Добавляем возможность изменения изображения -->
                            <label for="avatar-input">
                                <img src="<?php echo $avatar; ?>" alt="<?php echo $full_name; ?>" class="rounded-circle" width="150">
                            </label>
                            <!-- Скрытое поле для загрузки нового изображения -->
                            <input type="file" id="avatar-input" style="display: none;">
                            <div class="mt-3">
                                <h4><?php echo $full_name; ?></h4>
                                <p class="text-secondary mb-1"><?php echo $role; ?></p>
                                <p class="text-muted font-size-sm"><?php echo $email; ?></p>
                                <?php if ($role === 'Волонтер'): ?>
                                <div class="bsb-ratings text-warning mb-3" data-bsb-star="<?php echo $average_rating; ?>" data-bsb-star-off="<?php echo 5 - $average_rating; ?>"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($role === 'Волонтер'): ?>
                <div class="card mt-3">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-github mr-2 icon-inline"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>Добрых дел: </h6>
                            <span class="text-secondary"><?php echo $dobrye_dela_count; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-github mr-2 icon-inline"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>Кол-во часов: </h6>
                            <span class="text-secondary"><?php echo $total_hours; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-github mr-2 icon-inline"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>Баллы: </h6>
                            <span class="text-secondary"><?php echo $total_points; ?></span>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-body">
                    <div class="row">
    <?php
    if (mysqli_num_rows($applications_query) == 0) {
        echo "<p>Вы еще не приняты ни на одно мероприятие.</p>";
    } else {
        while ($application = mysqli_fetch_assoc($applications_query)) {
            echo '<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">';
                echo '<div class="card">';
                echo '<div class="card-body">';
                echo '<div class="d-flex align-items-center">';
                echo '<div><img src="' . $application['photo'] . '" alt="' . $application['title'] . '" style="max-width: 100%;" class="avatar-md rounded-circle img-thumbnail" /></div>';
                echo '<div class="flex-1 ms-3">';
                echo '<h5 class="font-size-16 mb-1"><a href="event_details.php?id=' . $application['event_id'] . '" class="text-dark">' . $application['title'] . '</a></h5>';

                echo '<span class="badgрe badge-soft-success mb-0"><i class="bx bxs-map icon"></i>' . $application['location'] . '</span>';
                echo '<p class="text-muted mb-0 mt-2"><i class="bx bx-calendar icon"></i>' . $application['event_date'] . '</p>';
                echo '<p class="text-muted mb-0 mt-2"><i class="bx bx-time icon"></i>' . $application['start_time'] . '-' . $application['end_time'] .  '</p>';

                // Проверка, прошло ли мероприятие и было ли оценено
                if (strtotime($application['event_date']) < strtotime($current_date)) {
                    if ($application['points'] !== null && $application['points'] !== 0 && $application['rating'] !== null) {
                        // Если мероприятие оценено, отображаем баллы и рейтинг
                        echo '<div class="mt-3 pt-1">';
                        echo '<span class="text-muted m-0">Начисленные баллы: ' . $application['points'] . '</span>';
                        echo '<div class="bsb-ratings text-warning mb-3" data-bsb-star="' . $application['rating'] . '" data-bsb-star-off="' . (5 - $application['rating']) . '"></div>';
                        echo '</div>';
                    } else {
                        // Если мероприятие еще не оценено
                        echo '<div class="mt-3 pt-1">';
                        echo '<p class="text-muted m-0">Мероприятие еще не оценили</p>';
                        echo '</div>';
                    }
                }

                echo '</div>';
                echo '</div>';
                if (strtotime($application['event_date']) >= strtotime($current_date)) {
                    echo '<div class="mt-3 pt-1">';
                    // Добавлена проверка перед отправкой формы с помощью JavaScript
                    echo '<form method="post" onsubmit="return confirmCancel()">';
                    echo '<input type="hidden" name="cancel_application" value="' . $application['event_id'] . '">';
                    echo '<button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-trash me-1"></i> Отменить</button>';
                    echo '</form>';
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        }
        ?>
    </div>
</div>   
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.bundle.min.js'></script>

</body>
</html>
