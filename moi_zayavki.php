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
    <title>Мои заявки</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css'>
    <!-- Подключаем CSS Boxicons -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css">
    <!-- Подключаем CSS Font Awesome -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
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

    // Получение заявок пользователя с ролью "us" по новизне
    $user_id = $_SESSION['user']['id'];
    $current_date = date('Y-m-d H:i:s');
    $applications_query = mysqli_query($connect, "SELECT * FROM applications 
                                                  INNER JOIN events ON applications.event_id = events.id 
                                                  WHERE applications.user_id = '$user_id' AND events.event_date > '$current_date' 
                                                  ORDER BY applications.created_at DESC");

    // Проверка наличия заявок
    if (mysqli_num_rows($applications_query) == 0) {
        echo "<p>У вас нет заявок на мероприятия.</p>";
    } else {
        // Отображение каждой заявки
        while ($application = mysqli_fetch_assoc($applications_query)) {
            // Определение статуса заявки
            $status = $application['status'];

            // Вывод карточки мероприятия
            echo '<div class="card mb-3">';
            echo '<div class="card-body d-flex align-items-center">';

            // Изображение мероприятия
            echo '<img class="img-md rounded-circle me-3" src="' . $application['photo'] . '" alt="' . $application['title'] . '" loading="lazy">';

            // Информация о мероприятии
            echo '<div>';
            echo '<a href="event_details.php?id=' . $application['event_id'] . '" class="h5 stretched-link btn-link">' . $application['title'] . '</a>';
echo '<p class="text-muted m-0"><i class="bx bx-map icon"></i>' . $application['location'] .'  </p>';
echo '<p class="text-muted m-0"><i class="bx bx-calendar icon"></i>' . $application['event_date'] .'</p>';
echo '<p class="text-muted m-0"><i class="bx bx-time icon"></i>' . $application['start_time'] . '-' . $application['end_time'] . '</p>';
echo '</div>';

            // Кнопки статуса заявки
            echo '<div class="ms-auto">';
            if ($status == 'approved') {
                echo '<span class="btn btn-sm btn-success btn-outline-light"><i class="fa fa-check" aria-hidden="true"></i> Одобрена</span>';
                echo '<a href="cancel_application.php?application_id=' . $application['id'] . '" class="btn btn-sm btn-danger btn-outline-light"><i class="fa fa-times" aria-hidden="true"></i> Отменить</a>';
            } elseif ($status == 'pending') {
                echo '<span class="btn btn-sm btn-warning btn-outline-light"><i class="fa fa-hourglass-half" aria-hidden="true"></i> В ожидании</span>';
                echo '<a href="cancel_application.php?application_id=' . $application['id'] . '" class="btn btn-sm btn-danger btn-outline-light"><i class="fa fa-times" aria-hidden="true"></i> Отменить</a>';
            } elseif ($status == 'rejected') {
                echo '<span class="btn btn-sm btn-danger btn-outline-light"><i class="fa fa-times" aria-hidden="true"></i> Отклонена</span>';
            }
            echo '</div>'; // Закрываем d-flex
            echo '</div>'; // Закрываем border-top
            echo '</div>'; // Закрываем card
        }
    }
    ?>
</div> <!-- Закрываем container -->
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>
