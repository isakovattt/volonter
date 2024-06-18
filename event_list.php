<?php
session_start();

// Проверяем, был ли пользователь аутентифицирован
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header('Location: /'); // Перенаправляем пользователя на страницу входа
    exit; // Прерываем выполнение скрипта
}

// Если да, сохраняем его идентификатор в переменной $user_id
$user_id = $_SESSION['user']['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Доступные мероприятия для записи</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css'>
    <!-- Подключаем CSS Boxicons -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css">
    <!-- Подключаем CSS Font Awesome -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" /><style>
     <style>
      body {
        margin-top: 20px;
        background: #eee;
      }

      .card {
  border-radius: 10px; /* Регулируйте радиус скругления здесь */
  box-shadow: 0 20px 27px 0 rgb(0 0 0 / 5%);
}
      .img-md {
        width: 4rem;
        height: 4rem;
      }

      .btn-link {
        transition-property: color, background-color;
        box-shadow: none;
        border-radius: 0;
        text-decoration: none!important;
      }

      .icon {
        margin-right: 5px;
      }
    </style>


</head>
<body>
<?php include 'admin_profile.php'; ?>
<div class="container">

<?php

// Подключение к базе данных
require_once 'vendor/connect.php';

// Запрос к базе данных для получения мероприятий, доступных для записи пользователем с ролью "us"
$current_date = date('Y-m-d H:i:s');
$events_query = mysqli_query($connect, "SELECT events.*, users.full_name AS responsible_full_name, users.avatar AS responsible_avatar 
                                       FROM events 
                                       INNER JOIN users ON events.responsible_id = users.id 
                                       WHERE events.event_date > '$current_date'");

// Проверяем, есть ли доступные для записи мероприятия
if (mysqli_num_rows($events_query) == 0) {
    echo "<p>Нет доступных мероприятий для записи.</p>";
} else {
    // Отображаем каждое доступное мероприятие
    while ($event = mysqli_fetch_assoc($events_query)) {
        // Проверяем доступность мероприятия для пользователя
        $available_for_users = $event['available_for_users'];
        if ($available_for_users === 'all') {
            echo '<div class="card mb-3">';
            echo '<div class="card-body d-flex align-items-center">';

            echo '<a href="event_details.php?id=' . $event['id'] . '">';
            echo '<img class="img-md rounded-circle me-3" img src="' . $event['photo'] . '" alt="' . $event['title'] . '">';
            echo '<div>';

            echo '<h3>' . $event['title'] . '</h3>';
            echo '</a>';
            echo '<p class="text-muted m-0"><i class="bx bx-calendar"></i> ' . $event['event_date'] . '</p>';
            echo '<p class="text-muted m-0"><i class="bx bx-map"></i> ' . $event['location'] . '</p>';
            echo '<p class="text-muted m-0"><i class="bx bx-time"></i> ' . $event['start_time'] . '-' .  $event['end_time'] . '</p>';
            
            // Выводим ФИО и фото ответственного
            echo '<p class="text-muted m-0"><i class="bx bx-user"></i> ' . $event['responsible_full_name'] . '</p>';
            echo '</div>';
            echo '<div class="ms-auto">';
            $check_application_query = mysqli_query($connect, "SELECT * FROM applications WHERE event_id = '{$event['id']}' AND user_id = '$user_id'");
            if (mysqli_num_rows($check_application_query) > 0) {
                $application = mysqli_fetch_assoc($check_application_query);
                $status = $application['status'];

                if ($status == 'pending') {
                    echo '<button disabled>Заявка отправлена</button>';
                    echo '<form action="vendor/cancel_application.php" method="post" onsubmit="return confirmCancel()">';
                    echo '<input type="hidden" name="event_id" value="' . $event['id'] . '">';
                    echo '<button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-trash me-1"></i> Отменить</button>';
                    echo '</form>';
                } elseif ($status == 'approved') {
                    echo '<button disabled>Участник</button>';
                    echo '<form action="vendor/cancel_application.php" method="post" onsubmit="return confirmCancel()">';
                    echo '<input type="hidden" name="event_id" value="' . $event['id'] . '">';
                    echo '<button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-trash me-1"></i> Отменить участие</button>';
                    echo '</form>';
                } elseif ($status == 'rejected') {
                    echo '<form action="vendor/register_for_event.php" method="post">';
                    echo '<input type="hidden" name="event_id" value="' . $event['id'] . '">';
                    echo '<button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-trash me-1"></i> Записаться</button>';
                    echo '</form>';
                }
            } else {
                echo '<form action="vendor/register_for_event.php" method="post">';
                echo '<input type="hidden" name="event_id" value="' . $event['id'] . '">';
                echo '<button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-trash me-1"></i> Записаться</button>';
                echo '</form>';
            }
            echo '</div>'; // Закрываем d-flex
            echo '</div>'; // Закрываем border-top
            echo '</div>'; // Закрываем card
        } elseif ($available_for_users === null) {
            // Если доступно никому
            continue;
        } elseif (strpos($available_for_users, $user_id) !== false) {
            // Если доступно конкретному пользователю
            
            echo '<div class="card-body d-flex align-items-center">';

            echo '<img class="img-md rounded-circle me-3" src="' . $event['photo'] . '" alt="' . $event['title'] . '">';
            echo '<div>';
            echo '<h3>' . $event['title'] . '</h3>';
            echo '<p class="text-muted m-0"><i class="bx bx-calendar"></i> ' . $event['event_date'] . '</p>';
            echo '<p class="text-muted m-0"><i class="bx bx-map"></i> ' . $event['location'] . '</p>';
            echo '<p class="text-muted m-0"><i class="bx bx-time"></i> ' . $event['start_time'] . '-' .  $event['end_time'] . '</p>';
            
            // Выводим ФИО и фото ответственного
            echo '<p class="text-muted m-0"><i class="bx bx-user"></i> ' . $event['responsible_full_name'] . '</p>';
            echo '</div>';
            $check_application_query = mysqli_query($connect, "SELECT * FROM applications WHERE event_id = '{$event['id']}' AND user_id = '$user_id'");
            echo '<div class="ms-auto">';
            if (mysqli_num_rows($check_application_query) > 0) {
                $application = mysqli_fetch_assoc($check_application_query);
                $status = $application['status'];

                if ($status == 'pending') {
                    echo '<button disabled>Заявка отправлена</button>';
                    echo '<form action="vendor/cancel_application.php" method="post" onsubmit="return confirmCancel()">';
                    echo '<input type="hidden" name="event_id" value="' . $event['id'] . '">';
                    echo '<button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-trash me-1"></i> Отменить</button>';
                    echo '</form>';
                } elseif ($status == 'approved') {
                    echo '<button disabled>Участник</button>';
                    echo '<form action="vendor/cancel_application.php" method="post" onsubmit="return confirmCancel()">';
                    echo '<input type="hidden" name="event_id" value="' . $event['id'] . '">';
                    echo '<button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-trash me-1"></i> Отменить участие</button>';
                    echo '</form>';
                } elseif ($status == 'rejected') {
                    echo '<form action="vendor/register_for_event.php" method="post">';
                    echo '<input type="hidden" name="event_id" value="' . $event['id'] . '">';
                    echo '<button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-trash me-1"></i> Записаться</button>';
                    echo '</form>';
                }
            } else {
                echo '<form action="vendor/register_for_event.php" method="post">';
                echo '<input type="hidden" name="event_id" value="' . $event['id'] . '">';
                echo '<button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-trash me-1"></i> Записаться</button>';
                echo '</form>';
            }
            echo '</div>'; // Закрываем d-flex
            echo '</div>'; // Закрываем border-top
            echo '</div>'; // Закрываем card
        }
    }
}
?>
</div> <!-- Закрываем container -->
<script>

function confirmCancel() {
    return confirm("Вы точно хотите отменить участие в мероприятии?");
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
