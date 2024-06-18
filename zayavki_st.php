<?php
session_start();

// Проверяем, был ли пользователь аутентифицирован
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header('Location: /'); // Перенаправляем пользователя на страницу входа
    exit; // Прерываем выполнение скрипта
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заявки участников</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <!-- Подключаем CSS Font Awesome -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        body {
            margin-top: 20px;
            background: #eee;
        }

        .card {
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
            text-decoration: none !important;
        }
    </style>
</head>
<body>
<?php include 'admin_profile.php'; ?>
<div class="container">
    <div class="row">
        <?php
        // Подключение к базе данных
        require_once 'vendor/connect.php';
        $user_id = $_SESSION['user']['id'];
// Отображение сообщения, если оно есть
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-info" role="alert">';
    echo $_SESSION['message'];
    echo '</div>';
    unset($_SESSION['message']); // Очищаем сообщение из сессии после отображения
}// Получение мероприятий, за которые ответственен пользователь и которые еще не прошли
$current_date = date('Y-m-d');
$events_query = mysqli_query($connect, "SELECT * FROM events WHERE responsible_id = '$user_id' AND event_date >= '$current_date'");

// Проверка наличия мероприятий
if (mysqli_num_rows($events_query) == 0) {
    echo "<p>Нет мероприятий, за которые вы отвечаете и которые еще не прошли.</p>";
} else {
    // Отображение заявок участников на каждое мероприятие
    while ($event = mysqli_fetch_assoc($events_query)) {
        // Получение заявок на текущее мероприятие
        $event_id = $event['id'];
        $applications_query = mysqli_query($connect, "SELECT * FROM applications WHERE event_id = '$event_id'");

        // Отображение названия мероприятия
        echo '<div class="col-sm-6 col-md-6 mb-3">';
        echo '<div class="card">';
        echo '<div class="card-body">';
        echo '<h5 class="card-title">' . $event['title'] . '</h5>';

        // Отображение заявок участников
        if (mysqli_num_rows($applications_query) == 0) {
            echo "<p>Нет заявок на данное мероприятие.</p>";
        } else {
            while ($application = mysqli_fetch_assoc($applications_query)) {
                // Получение информации о пользователе
                $applicant_id = $application['user_id'];
                $user_query = mysqli_query($connect, "SELECT * FROM users WHERE id = '$applicant_id'");
                $user = mysqli_fetch_assoc($user_query);

                // Отображение информации о заявке
                echo '<div class="d-flex align-items-center mb-3">';
                echo '<img class="img-md rounded-circle me-3" src="' . $user['avatar'] . '" alt="Profile Picture" loading="lazy">';
                echo '<div>';
                echo '<h6 class="m-0">' . $user['full_name'] . '</h6>';
                echo '<p class="m-0">' . $user['email'] . '</p>';
                echo '</div>';
                echo '</div>';

                // Кнопки подтверждения и отклонения заявки
                echo '<div class="text-center">';
                if ($application['status'] === 'pending') {
                    echo '<a href="vendor/confirm_application.php?application_id=' . $application['id'] . '" class="btn btn-success btn-sm mx-2">Подтвердить участие</a>';
                    echo '<a href="vendor/confirm_application.php?reject_application_id=' . $application['id'] . '" class="btn btn-danger btn-sm mx-2">Отклонить</a>';
                } elseif ($application['status'] === 'approved') {
                    echo '<a href="#" class="btn btn-primary btn-sm mx-2">Участник</a>';
                    echo '<a href="vendor/confirm_application.php?cancel_application_id=' . $application['id'] . '" class="btn btn-danger btn-sm mx-2">Отменить участие</a>';
                } elseif ($application['status'] === 'rejected') {
                    echo '<span class="text-danger mx-2">Отказано</span>';
                    echo '<a href="vendor/confirm_application.php?cancel_rejection_id=' . $application['id'] . '" class="btn btn-warning btn-sm mx-2">Отменить</a>';
                }
                echo '</div>';
            }
        }

        echo '</div>'; // Закрываем card-body
        echo '</div>'; // Закрываем card
        echo '</div>'; // Закрываем col-sm-6
    }
}
?>
    </div> <!-- Закрываем row -->
</div> <!-- Закрываем container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
