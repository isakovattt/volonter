<?php
session_start();
require_once 'vendor/connect.php';

// Проверка пользователя на роль и авторизацию
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'st') {
    header('Location: /');
    exit(); // Для прекращения выполнения скрипта после перенаправления
}

$user_id = $_SESSION['user']['id'];

// Получение мероприятий, где пользователь является ответственным и они уже прошли
$current_date = date('Y-m-d');
$events_query = mysqli_query($connect, "SELECT * FROM events WHERE responsible_id = '$user_id' AND event_date < '$current_date'");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оценить мероприятия</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <style>
        .img-md {
            width: 6rem;
            height: 6rem;
            float: left; /* Добавляем свойство float: left; чтобы изображение было слева */
            margin-right: 10px; /* Добавляем небольшой отступ между изображением и текстом */
        }
    </style>
</head>
<body>
<?php include 'admin_profile.php'; ?>
<div class="container">
    <h2 class="mt-4">Оценить мероприятия</h2>
    <div class="row">
        <?php
        while ($event = mysqli_fetch_assoc($events_query)) {
            echo '<div class="col-md-6">';
            echo '<div class="card mt-3">';
            echo '<div class="card-body">';
            echo '<img class="img-md rounded-circle me-3" src="' . $event['photo'] . '" alt="' . $event['title'] . '" loading="lazy">'; // Перемещаем изображение в начало блока

            echo '<h5 class="card-title">' . $event['title'] . '</h5>';
            echo '<p class="text-muted m-0"><i class="bx bx-map icon"></i> ' . $event['location'] . '</p>';
            echo '<p class="text-muted m-0"><i class="bx bx-calendar icon"></i> ' . $event['event_date'] . '</p>';
            echo '<p class="text-muted m-0"><i class="bx bx-time icon"></i> ' . $event['start_time'] . ' - ' . $event['end_time'] . '</p>';
            echo '<a href="otziv_naych.php?event_id=' . $event['id'] . '" class="btn btn-primary">Подробнее</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
</div>
</body>
</html>
