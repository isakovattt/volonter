<?php
// Подключаемся к базе данных
require_once 'vendor/connect.php';
// Проверяем, была ли сессия уже запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user'])) {
    include 'admin_profile.php';
}
// Запрос на выборку последних 4 новостей по дате
$sql = "SELECT id, title, date, image, content FROM news ORDER BY date DESC LIMIT 3";

$result = $connect->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Page Title</title>
    <!-- Подключаем скомпилированный CSS файл -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v6.0.0-beta1/css/all.css">

</head>
<body>
<div class="comp1-container">

<header>
	<div class="overlay">
<h1>Волонтеры</h1>
<h3>Мы - сообщество студентов, объединённых стремлением делать добро.</h3>
<p>Присоединяйтесь к нам, чтобы вместе творить добро, развиваться и делать мир лучше!</p>
<br>
	
<?php
    // Скрываем кнопку входа для авторизованных пользователей
    if (!isset($_SESSION['user'])) {
        echo "<a href='index.php' class='login-link'>Войти в аккаунт</a>";
    }
    ?>
		</div>
</header>
</div>
<?php
// Запрос на выборку последних трех мероприятий по дате
$sqlEvents = "SELECT id, title, event_date AS date, photo AS image, description AS content FROM events ORDER BY event_date DESC LIMIT 3";
$resultEvents = $connect->query($sqlEvents);
?>

<div class="comp4-container shared-container">
    <div class="cards" id="eventCards">
        <?php
        // Проверяем наличие результатов запроса
        if ($resultEvents->num_rows > 0) {
            // Выводим данные о мероприятиях
            while ($row = $resultEvents->fetch_assoc()) {
                echo "<li>";
                echo "<a href='event_details.php?id=" . $row['id'] . "' class='card'>";
                echo "<img src='" . $row["image"] . "' alt='Event Image' class='card__image'>";
                echo "<div class='card__overlay'>";
                echo "<div class='card__header'>";
                echo "<svg class='card__arc' xmlns='http://www.w3.org/2000/svg'><path /></svg>";                     
                echo "<div class='card__header-text'>";
                echo "<h3 class='card__title'>" . $row["title"] . "</h3>";            
                echo "<span class='card__status'>" . $row["date"] . "</span>";
                echo "</div>";
                echo "</div>";
                echo "<p class='card__description'>" . $row["content"] . "</p>";
                echo "</div>";
                echo "</a>";
                echo "</li>";
            }
        } else {
            echo "Мероприятий пока нет.";
        }
        ?>
    </div>
</div>
<div class="comp2-container">

<ol>
    <?php
    // Подключаемся к базе данных
    require_once 'vendor/connect.php';
    
    // Подсчет количества пользователей с ролью "us"
    $sqlUsers = "SELECT COUNT(*) as userCount FROM users WHERE role = 'us'";
    $resultUsers = $connect->query($sqlUsers);
    $rowUsers = $resultUsers->fetch_assoc();
    $userCount = $rowUsers['userCount'];

    // Подсчет количества мероприятий
    $sqlEvents = "SELECT COUNT(*) as eventCount FROM events";
    $resultEvents = $connect->query($sqlEvents);
    $rowEvents = $resultEvents->fetch_assoc();
    $eventCount = $rowEvents['eventCount'];

    // Подсчет общего количества часов мероприятий
    $sqlHours = "SELECT SUM(TIMESTAMPDIFF(HOUR, start_time, end_time)) as totalHours FROM events";
    $resultHours = $connect->query($sqlHours);
    $rowHours = $resultHours->fetch_assoc();
    $totalHours = $rowHours['totalHours'];

    // Вывод карточек с информацией о пользователях, мероприятиях и часах
    echo "<li>";
    echo "<div class='icon'><i class='bx bx-user'></i></div>"; // Иконка пользователей
    echo "<div class='title'>$userCount ............. Пользователей</div>";
    echo "</li>";
    
    echo "<li>";
    echo "<div class='icon'><i class='bx bx-calendar'></i></div>"; // Иконка мероприятий
    echo "<div class='title'>$eventCount ............. Мероприятий</div>";
    echo "</li>";
    
    echo "<li>";
    echo "<div class='icon'><i class='bx bx-time'></i></div>"; // Иконка часов мероприятий
    echo "<div class='title'>$totalHours Часов мероприятий</div>";
    echo "</li>";
    
    ?>
</ol>

</div>
<div class="comp3-container shared-container">

<div class="cards" id="newsCards">
    <?php
    // Проверяем наличие результатов запроса
    if ($result->num_rows > 0) {
        // Выводим данные о новостях
        while ($row = $result->fetch_assoc()) {
            echo "<li>";
            echo "<a href='index.php?id=" . $row['id'] . "' class='card'>";
            echo "<img src='" . $row["image"] . "' alt='News Image' class='card__image'>";
            echo "<div class='card__overlay'>";
            echo "<div class='card__header'>";
            echo "<svg class='card__arc' xmlns='http://www.w3.org/2000/svg'><path /></svg>";                     
            echo "<div class='card__header-text'>";
            echo "<h3 class='card__title'>" . $row["title"] . "</h3>";            
            echo "<span class='card__status'>" . $row["date"] . "</span>";
            echo "</div>";
            echo "</div>";
            echo "<p class='card__description'>" . $row["content"] . "</p>";
            echo "</div>";
            echo "</a>";
            echo "</li>";
        }
    } else {
        echo "Новостей пока нет.";
    }

   
    ?>
    
</div>
<div class="comp5-container">
<footer class="footer">
    <div class="waves">
      <div class="wave" id="wave1"></div>
      <div class="wave" id="wave2"></div>
      <div class="wave" id="wave3"></div>
      <div class="wave" id="wave4"></div>
    </div>
    <ul class="social-icon">
      <li class="social-icon__item"><a class="social-icon__link" href="#">
          <ion-icon name="logo-facebook"></ion-icon>
        </a></li>
      <li class="social-icon__item"><a class="social-icon__link" href="#">
          <ion-icon name="logo-twitter"></ion-icon>
        </a></li>
      <li class="social-icon__item"><a class="social-icon__link" href="#">
          <ion-icon name="logo-linkedin"></ion-icon>
        </a></li>
      <li class="social-icon__item"><a class="social-icon__link" href="#">
          <ion-icon name="logo-instagram"></ion-icon>
        </a></li>
    </ul>
    
     </footer>
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
  </div>
</body>
</div>
</html>