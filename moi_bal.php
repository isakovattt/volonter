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
    <title>Мои баллы</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css'>
    <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://unpkg.com/bs-brain@2.0.3/components/testimonials/testimonial-3/assets/css/testimonial-3.css">
 
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <style>
      body {
        margin-top: 20px;
        background: #eee;
      }

      .card {
        box-shadow: 0 20px 27px 0 rgb(0 0 0 / 5%);
      }

      .img-md {
        width: 2rem;
        height: 2rem;
      }
      .img-mdl {
        width: 5rem;
        height: 5rem;
      }
      .btn-link {
        transition-property: color, background-color;
        box-shadow: none;
        border-radius: 0;
        text-decoration: none!important;
      }
    </style>
</head>
<body>
<?php include 'admin_profile.php'; ?>
<div class="container">
    <?php
    // Подключение к базе данных
    require_once 'vendor/connect.php';

    // Получение баллов пользователя с ролью "us" из таблицы applications
    $user_id = $_SESSION['user']['id'];
    $points_query = mysqli_query($connect, "SELECT * FROM applications 
                                           WHERE user_id = '$user_id' 
                                           AND status = 'approved' 
                                           AND EXISTS (
                                               SELECT 1 FROM events 
                                               WHERE events.id = applications.event_id 
                                               AND events.event_date < NOW()
                                           )
                                           ORDER BY created_at DESC");

    // Проверка наличия баллов
    if (mysqli_num_rows($points_query) == 0) {
        echo "<p>У вас нет баллов за прошедшие мероприятия.</p>";
    } else {
        // Отображение каждого балла
        while ($point = mysqli_fetch_assoc($points_query)) {
            // Получение информации о мероприятии
            $event_id = $point['event_id'];
            $event_query = mysqli_query($connect, "SELECT * FROM events WHERE id = '$event_id'");
            $event = mysqli_fetch_assoc($event_query);

            // Получение информации об ответственном за мероприятие
            $responsible_id = $event['responsible_id'];
            $responsible_query = mysqli_query($connect, "SELECT * FROM users WHERE id = '$responsible_id'");
            $responsible = mysqli_fetch_assoc($responsible_query);

            // Вывод карточки мероприятия и баллов
            echo '<div class="card mb-3">';
            echo '<div class="card-body d-flex align-items-center">';

            // Изображение мероприятия
            if (isset($event['photo'])) {
                echo '<img class="img-mdl rounded-circle me-3" src="' . $event['photo'] . '" alt="' . $event['title'] . '" loading="lazy">';
            } else {
                echo '<img class="img-mdl rounded-circle me-3" src="default_event_photo.jpg" alt="" loading="lazy">';
            }

            // Информация о мероприятии
            echo '<div>';
            echo '<a href="#" class="h5 stretched-link btn-link">' . $event['title'] . '</a>';
            echo '<p class="text-muted m-0">Ответственный: ';
            
            // Имя и фото ответственного
            if (isset($responsible['avatar'])) {
                echo '<img class="img-md rounded-circle me-1" src="' . $responsible['avatar'] . '" alt="' . $responsible['full_name'] . '" loading="lazy">';
            } else {
                echo '<img class="img-md rounded-circle me-1" src="default_user_photo.jpg" alt="" loading="lazy">';
            }
            
            echo $responsible['full_name'] . '</p>';
            
            echo '<p class="text-muted m-0">Баллы: ' . $point['points'] . '</p>';
            
        
            echo '<div class="bsb-ratings text-warning mb-3" data-bsb-star="' . $point['rating'] . '" data-bsb-star-off="0"></div>';
            
            echo '<p class="text-muted m-0">Отзыв: ' . $point['review'] . '</p>';
            echo '</div>';

            // Кнопки статуса заявки
            echo '<div class="ms-auto">';
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
