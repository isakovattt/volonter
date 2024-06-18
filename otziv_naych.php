<?php
session_start();
require_once 'vendor/connect.php';

// Проверка пользователя на роль и авторизацию
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'st') {
    header('Location: /');
    exit(); // Для прекращения выполнения скрипта после перенаправления
}

// Проверка наличия параметра event_id в URL
if (!isset($_GET['event_id'])) {
    header("Location: ocenka_mer_st.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$event_id = $_GET['event_id'];

// Получение информации о мероприятии
$event_query = mysqli_query($connect, "SELECT * FROM events WHERE id = '$event_id'");
$event = mysqli_fetch_assoc($event_query);

// Получение участников мероприятия с рейтингом, отзывами и начисленными баллами
$participants_query = mysqli_query($connect, "SELECT users.*, applications.rating, applications.review, applications.points 
                                              FROM users 
                                              INNER JOIN applications ON users.id = applications.user_id 
                                              WHERE applications.event_id = '$event_id'");

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $participant_id = $_POST['user_id'];
    $rating = $_POST['rating'];
    $review = $_POST['review'];
    $points = $_POST['points'];
    
    // Обновление данных участника
    $update_query = mysqli_query($connect, "UPDATE applications SET rating = '$rating', review = '$review', points = '$points' WHERE user_id = '$participant_id' AND event_id = '$event_id'");
    
    if ($update_query) {
        header("Location: $_SERVER[PHP_SELF]?event_id=$event_id"); // Редирект на текущую страницу после обновления данных
        exit();
    } else {
        echo "Ошибка при обновлении данных: " . mysqli_error($connect);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подробности мероприятия</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <script>
    function toggleRatingForm(userId) {
        var ratingForm = document.getElementById('ratingForm_' + userId);
        
        if (ratingForm.style.display === 'none') {
            ratingForm.style.display = 'block';
        } else {
            ratingForm.style.display = 'none';
        }
    }

    function goToProfile(userId) {
        window.location.href = 'profile_us.php?user_id=' + userId;
    }
    </script>
</head>
<body>
<?php include 'admin_profile.php'; ?>

<div class="container">
    <h2 class="mt-4">Подробности мероприятия</h2>
    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title"><?php echo $event['title']; ?></h5>
            <p class="card-text">Место: <?php echo $event['location']; ?></p>
            <p class="card-text">Дата: <?php echo $event['event_date']; ?></p>
            <p class="card-text">Время: <?php echo $event['start_time'] . ' - ' . $event['end_time']; ?></p>
            <p class="card-text">Баллы: <?php echo $event['points']; ?></p>
        </div>
    </div>

    <h4 class="mt-4">Участники</h4>
    <div class="row mt-3">
    <?php
    while ($participant = mysqli_fetch_assoc($participants_query)) {
        echo '<div class="card">';
        echo '<div class="card-body d-flex align-items-center justify-content-between">';
        echo '<div class="d-flex align-items-center">';
        echo '<img class="img-md rounded-circle me-3" src="' . $participant['avatar'] . '" alt="Avatar" style="width:100px;height:100px; cursor:pointer;" onclick="goToProfile(' . $participant['id'] . ')">'; // Вывод аватара с обработчиком события
        echo '<div>';
        echo '<h6 class="m-0" style="cursor:pointer;" onclick="goToProfile(' . $participant['id'] . ')">' . $participant['full_name'] . '</h6>';
        echo '<p class="m-0" style="cursor:pointer;" onclick="goToProfile(' . $participant['id'] . ')">Email: ' . $participant['email'] . '</p>';
        echo '</div>';
        echo '</div>';
        
        if ($participant['rating'] !== NULL && $participant['review'] !== NULL && $participant['points'] !== 0) {
            echo '<p id="evaluatedLabel_' . $participant['id'] . '" class="card-text text-success" style="display:block;">Участник оценен</p>';
            echo '<button id="ratingButton_' . $participant['id'] . '" class="btn btn-primary" onclick="toggleRatingForm(' . $participant['id'] . ')">Изменить оценку</button>';
        } else {
            echo '<button id="ratingButton_' . $participant['id'] . '" class="btn btn-primary" onclick="toggleRatingForm(' . $participant['id'] . ')">Оценить</button>';
        }
        
        echo '<form action="" method="POST" id="ratingForm_' . $participant['id'] . '" style="display:none;">';
        echo '<div class="form-group">';
        echo '<label for="rating">Оценка (1-5):</label>';
        echo '<input type="number" class="form-control" id="rating" name="rating" min="1" max="5" value="' . $participant['rating'] . '" required>';
        echo '</div>';
        echo '<div class="form-group">';
        echo '<label for="review">Отзыв:</label>';
        echo '<textarea class="form-control" id="review" name="review" rows="2">' . $participant['review'] . '</textarea>';
        echo '</div>';
        echo '<div class="form-group">';
        echo '<label for="points">Баллы:</label>';
        echo '<input type="number" class="form-control" id="points" name="points" value="' . $participant['points'] . '" required>';
        echo '</div>';
        echo '<input type="hidden" name="user_id" value="' . $participant['id'] . '">';
        echo '<input type="hidden" name="event_id" value="' . $event_id . '">';
        echo '<button type="submit" class="btn btn-primary" id="submitButton_' . $participant['id'] . '" style="display:none;">Отправить</button>';
        echo '</form>';
        
        echo '</div>';
        echo '</div>';
    }
    ?>
    <script>
    function toggleRatingForm(userId) {
        var ratingForm = document.getElementById('ratingForm_' + userId);
        var ratingButton = document.getElementById('ratingButton_' + userId);
        var submitButton = document.getElementById('submitButton_' + userId);
        var evaluatedLabel = document.getElementById('evaluatedLabel_' + userId);
        
        if (ratingForm.style.display === 'none') {
            ratingForm.style.display = 'block';
            ratingButton.style.display = 'none';
            submitButton.style.display = 'block';
            evaluatedLabel.style.display = 'none';
        } else {
            ratingForm.style.display = 'none';
            ratingButton.style.display = 'block';
            submitButton.style.display = 'none';
            evaluatedLabel.style.display = 'block';
        }
    }
    </script>

    </div>
</div>
</body>
</html>

