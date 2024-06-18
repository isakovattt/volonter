<?php
session_start();
require_once 'vendor/connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'st') {
    header('Location: /');
    exit();
}

$user_id = $_SESSION['user']['id'];

$min_rating = isset($_GET['min_rating']) ? $_GET['min_rating'] : 0;
$searchValue = isset($_GET['q']) ? $_GET['q'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT u.id, u.avatar, u.full_name, u.email, u.status, IFNULL(AVG(a.rating), 0) AS avg_rating, IFNULL(SUM(a.points), 0) AS earned_points,
        IFNULL((SELECT SUM(e.points) FROM applications a LEFT JOIN events e ON a.event_id = e.id WHERE a.user_id = u.id AND a.points != 0), 0) AS possible_points
        FROM users u
        LEFT JOIN applications a ON u.id = a.user_id
        WHERE u.role = 'us'";

if (!empty($searchValue)) {
    $sql .= " AND u.full_name LIKE '%$searchValue%'";
}

if (!empty($status)) {
    $sql .= " AND u.status = '$status'";
}

$sql .= " GROUP BY u.id";

if ($min_rating > 0) {
    $sql .= " HAVING AVG(a.rating) >= $min_rating";
}

$sql .= " ORDER BY avg_rating DESC";

$result = $connect->query($sql);
$current_user_id = $_SESSION['user']['id'];

$sql_events = "SELECT * FROM events WHERE responsible_id = $current_user_id AND event_date > NOW()";
$result_events = $connect->query($sql_events);

// Создаем массив для хранения мероприятий
$events = array();

// Проверяем, что запрос выполнен успешно
if ($result_events->num_rows > 0) {
    // Проходимся по результатам запроса и добавляем их в массив
    while ($row_event = $result_events->fetch_assoc()) {
        $events[] = $row_event;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Authors</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/bs-brain@2.0.3/components/testimonials/testimonial-3/assets/css/testimonial-3.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{
    margin-top:20px;
    background:#eee;
}
.team-card-style-1, .team-card-style-3, .team-card-style-5 {
  position: relative;
  max-width: 360px;
  text-align: center;
  background:#fff;
 box-shadow: 0 22px 36px -12px rgba(64, 64, 64, .13);
}
.team-contact-link {
  display: block;
  margin-top: 4px;
  transition: all 0.25s;
  font-size: 12px;
  font-weight: 700;
  text-decoration: none;
}
.team-contact-link > i {
  display: inline-block;
  font-size: 1.1em;
  vertical-align: middle;
}
.team-card-style-1 .team-position, .team-card-style-3 .team-position, .team-card-style-4 .team-position {
  display: block;
  margin-bottom: 8px;
  color: #8c8c8c;
  font-size: 12px;
  font-weight: 700;
  opacity: 0.6;
}
.team-card-style-3 .team-name, .team-card-style-4 .team-name, .team-card-style-5 .team-name {
  margin-bottom: 16px;
  font-size: 18px;
  font-weight: 600;
}
.team-thumb > img {
    border-radius: 50%;
            width: 100%;
            height: 100px; /* Задайте здесь фиксированную высоту */
            object-fit: cover;
}
.team-card-style-1 {
  padding-bottom: 36px;
}
.team-card-style-1 > * {
  position: relative;
  z-index: 5;
}
.team-card-style-1::before {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 0;
  transition: all 0.3s 0.12s;
  content: '';
  opacity: 0;
}
.team-card-style-1 .team-card-inner {
  margin-bottom: 16px;
  padding-top: 48px;
  padding-right: 16px;
  padding-bottom: 20px;
  padding-left: 16px;
  background-color: #fff;
  box-shadow: 0 22px 36px -12px rgba(64, 64, 64, .13);
}
.team-card-style-1 .team-thumb {
  width: 108px;
  height: 108px;
  margin: auto;
  margin-bottom: 16px;
  border-radius: 50%;
  overflow: hidden;
}
.team-card-style-1 .team-social-bar {
  margin-top: 16px;
  margin-bottom: 8px;
  transform: scale(0.8);
}
.team-card-style-1 .team-contact-link {
  transition-delay: 0.12s;
  color: #8c8c8c;
  opacity: 0.6;
}
.team-card-style-1 .team-contact-link:hover {
  color: #8c8c8c;
  opacity: 1;
}
.team-card-style-1 .team-card-inner, .team-card-style-1 .team-thumb, .team-card-style-1 .team-social-bar {
  transition: all 0.3s 0.12s;
}
.team-card-style-1 .team-position, .team-card-style-1 .team-name {
  transition: color 0.3s 0.12s;
}
.team-card-style-1 .team-name {
  margin-bottom: 0;
  font-size: 20px;
  font-weight: bold;
}
.no-touchevents .team-card-style-1:hover::before {
  height: 100%;
  box-shadow: 0 22px 36px -12px rgba(64, 64, 64, .13);
  opacity: 1;
}
.no-touchevents .team-card-style-1:hover .team-card-inner {
  background-color: transparent;
  box-shadow: none;
}
.no-touchevents .team-card-style-1:hover .team-thumb {
  transform: scale(1.1);
}
.no-touchevents .team-card-style-1:hover .team-social-bar {
  transform: scale(1);
}
.no-touchevents .team-card-style-1:hover .team-contact-link, .no-touchevents .team-card-style-1:hover .team-position, .no-touchevents .team-card-style-1:hover .team-name {
  color: #fff;
}
.no-touchevents .team-card-style-1:hover .team-contact-link {
  opacity: 1;
}
.team-card-style-2 {
  position: relative;
}
.team-card-style-2 > img {
  display: block;
  width: 100%;
}
.team-card-style-2::before, .team-card-style-2::after {
  display: block;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  transition: opacity 0.35s 0.12s;
  content: '';
  z-index: 1;
}
.team-card-style-2::before {
  background-color: rgba(0, 0, 0, .25);
}
.team-card-style-2::after {
  opacity: 0;
}
.team-card-style-2 .team-card-inner {
  position: absolute;
  top: 50%;
  width: 100%;
  padding: 20px;
  transform: translateY(-45%);
  transition: all 0.35s 0.12s;
  text-align: center;
  opacity: 0;
  z-index: 5;
}
.team-card-style-2 .team-name, .team-card-style-2 .team-position, .team-card-style-2 .team-contact-link {
  color: #fff;
}
.team-card-style-2 .team-name {
  margin-bottom: 5px;
  font-size: 20px;
  font-weight: bold;
}
.team-card-style-2 .team-position {
  display: block;
  margin-bottom: 16px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}
.team-card-style-2 .team-social-bar {
  margin-top: 16px;
  margin-bottom: 8px;
}
.team-card-style-2 .team-contact-link {
  opacity: 1;
}
.team-card-style-2:hover::before {
  opacity: 0;
}
.team-card-style-2:hover::after {
  opacity: 0.7;
}
.team-card-style-2:hover .team-card-inner {
  transform: translateY(-50%);
  opacity: 1;
}
.team-card-style-3, .team-card-style-4 {
  position: relative;
  padding-top: 30px;
  padding-right: 20px;
  padding-bottom: 38px;
  padding-left: 20px;
  transition: all 0.35s;
  border: 1px solid #e7e7e7;
}
.team-card-style-3 .team-thumb, .team-card-style-4 .team-thumb {
  width: 90px;
  margin: auto;
  margin-bottom: 17px;
}
.team-card-style-3 .team-position, .team-card-style-4 .team-position {
  margin-bottom: 0;
}
.team-card-style-3 .team-contact-link, .team-card-style-4 .team-contact-link {
  color: #404040;
  font-weight: 600;
}
.team-card-style-3 .team-contact-link > i, .team-card-style-4 .team-contact-link > i {
  color: #8c8c8c !important;
}
.team-card-style-3 .team-contact-link:hover, .team-card-style-4 .team-contact-link:hover {
  color: rgba(64, 64, 64, .6);
}
.team-card-style-3 .team-social-bar-wrap, .team-card-style-4 .team-social-bar-wrap {
  position: absolute;
  bottom: -18px;
  left: 0;
  width: 100%;
}
.team-card-style-3 .team-social-bar-wrap > .team-social-bar, .team-card-style-4 .team-social-bar-wrap > .team-social-bar {
  display: table;
  margin: auto;
  background-color: #fff;
  box-shadow: 0 12px 20px 1px rgba(64, 64, 64, .11);
}
.team-card-style-3:hover, .team-card-style-4:hover {
  border-color: transparent;
  box-shadow: 0 12px 20px 1px rgba(64, 64, 64, .09);
}
.team-card-style-4 {
  padding-top: 24px;
  padding-bottom: 31px;
  padding-left: 24px;
}
.team-card-style-4 .team-name {
  margin-bottom: 5px;
}
.team-card-style-4 .team-social-bar-wrap {
  position: relative;
  bottom: auto;
  left: auto;
  margin-top: 20px;
}
.team-card-style-4 .team-social-bar-wrap > .team-social-bar {
  margin: 0;
}
.team-card-style-5 {
  padding-bottom: 24px;
  transition: box-shadow 0.35s 0.12s;
}
.team-card-style-5 .team-thumb {
  position: relative;
  margin-bottom: 24px;
}
.team-card-style-5 .team-thumb::after {
  display: block;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  transition: opacity 0.35s 0.12s;
  background-color: #ac32e4;
  content: '';
  opacity: 0;
  z-index: 1;
}
.team-card-style-5 .team-card-inner {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  padding: 16px;
  padding-bottom: 26px;
  transform: translateY(10px);
  transition: all 0.35s 0.12s;
  text-align: center;
  opacity: 0;
  z-index: 5;
}
.team-card-style-5 .team-contact-link, .team-card-style-5 .team-contact-link:hover {
  color: #fff;
}
.team-card-style-5 .sb-style-6.sb-light-skin, .team-card-style-5 .sb-style-7.sb-light-skin {
  border-color: rgba(255, 255, 255, .35);
}
.team-card-style-5 .team-name {
  margin-bottom: 6px;
  padding: 0 16px;
}
.team-card-style-5 .team-position {
  display: block;
  padding: 0 16px;
  transition: color 0.35s 0.12s;
}
.team-card-style-5:hover {
  box-shadow: 0 12px 20px 1px rgba(64, 64, 64, .09);
}
.team-card-style-5:hover .team-thumb::after {
  opacity: 0.7;
}
.team-card-style-5:hover .team-card-inner {
  transform: translateY(0);
  opacity: 1;
}
.team-card-style-5:hover .team-position {
  color: #ac32e4;
}
.team-card-style-3 .team-social-bar-wrap>.team-social-bar, .team-card-style-4 .team-social-bar-wrap>.team-social-bar {
    display: table;
    margin: auto;
    background-color: #fff;
    -webkit-box-shadow: 0 12px 20px 1px rgba(64,64,64,0.11);
    box-shadow: 0 12px 20px 1px rgba(64,64,64,0.11);
}
.social-btn {
    display: inline-block;
    width: 36px;
    height: 36px;
    margin: 0;
    -webkit-transition: all .3s;
    transition: all .3s;
    font-size: 18px;
    line-height: 36px;
    vertical-align: middle;
    text-align: center !important;
    text-decoration: none;
}
.sb-twitter {
    color: #55acee !important;
}
.sb-github {
    color: #4183c4 !important;
}
.sb-linkedin {
    color: #0976b4 !important;
}
.sb-skype {
    color: #00aff0 !important;
}
.sb-style-2, .sb-style-3, .sb-style-4, .sb-style-5 {
  margin-right: 10px;
  margin-bottom: 10px;
  border-radius: 50%;
  background-color: #f5f5f5;
}
.sb-style-2.sb-light-skin, .sb-style-3.sb-light-skin, .sb-style-4.sb-light-skin, .sb-style-5.sb-light-skin {
  background-color: rgba(255, 255, 255, .1);
}
.sb-style-2:hover, .sb-style-3:hover, .sb-style-4:hover, .sb-style-5:hover, .sb-style-2.hover, .sb-style-3.hover, .sb-style-4.hover, .sb-style-5.hover {
  background-color: #fff;
  box-shadow: 0 12px 20px 1px rgba(64, 64, 64, .11);
}
    </style>
</head>
<body>
<?php include 'admin_profile.php'; ?>
<section class="container py-5">
    <h2 class="h4 block-title text-center mt-2">Рейтинг волонтеров</h2>
    <form id="filterForm" method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="mb-4">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="min_rating">Выберите минимальный рейтинг:</label>
                    <select class="form-control" id="min_rating" name="min_rating">
                        <?php for ($i = 5; $i >= 0; $i--) { ?>
                            <option value="<?php echo $i; ?>" <?php if ($min_rating == $i) echo 'selected'; ?>><?php echo ($i == 0) ? "Не имеет значения" : $i; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="search">Поиск по имени:</label>
                    <input type="text" id="search" class="form-control" name="q" placeholder="Введите имя" value="<?php echo $searchValue; ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="status">Фильтр по статусу:</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">Все</option>
                        <option value="готов по праздникам и выходным" <?php if (strtolower($status) === 'готов по праздникам и выходным') echo 'selected'; ?>>Готов по праздникам и выходным</option>
                        <option value="готов в определенные дни недели" <?php if (strtolower($status) === 'готов в определенные дни недели') echo 'selected'; ?>>Готов в определенные дни недели</option>
                        <option value="готов в любой момент" <?php if (strtolower($status) === 'готов в любой момент') echo 'selected'; ?>>Готов в любой момент</option>
                        <option value="готов в утреннее/дневное/вечернее время" <?php if (strtolower($status) === 'готов в утреннее/дневное/вечернее время') echo 'selected'; ?>>Готов в утреннее/дневное/вечернее время</option>
                    </select>
                </div>
            </div>
        </div>
    </form>
    <div class="row pt-3" id="volunteers">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $row_id = $row['id'];
                ?>
                <div class="col-lg-3 col-sm-6 mb-30 pb-2">
                    <a href="profile_us.php?user_id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="team-card-style-3 mx-auto">
                            <div class="team-thumb"><img src="<?php echo $row['avatar']; ?>" alt="Author Picture"></div>
                            <h4 class="team-name">
            <?php echo $row['full_name']; ?>
            <?php
// Определяем иконку и цвет в соответствии со статусом
$status_icon = '';
$status_color = '';

switch ($row['status']) {
    case 'Готов по праздникам и выходным':
        $status_icon = '<i class="bi bi-circle-fill text-success"></i>';
        break;
    case 'Готов в определенные дни недели':
        $status_icon = '<i class="bi bi-circle-fill text-warning"></i>';
        break;
    case 'Готов в любой момент':
        $status_icon = '<i class="bi bi-circle-fill text-primary"></i>';
        break;
    case 'Готов в утреннее/дневное/вечернее время':
        $status_icon = '<i class="bi bi-circle-fill text-danger"></i>';
        break;
    default:
        $status_icon = '';
        break;
}

echo $status_icon;
?>
        </h4> <p><?php echo $row['email']; ?></p>
                            <div class="bsb-ratings text-warning mb-3" data-bsb-star="<?php echo round($row['avg_rating']); ?>" data-bsb-star-off="<?php echo 5 - round($row['avg_rating']); ?>"></div>
                            <p>Рейтинг: <?php echo round($row['avg_rating'], 2); ?></p>
                            <p><?php echo sprintf("%s из %s возможных баллов", $row['earned_points'], ($row['possible_points'] == 0) ? '0' : $row['possible_points']); ?></p>
                            </a>
                            <button type="button" class="btn btn-primary send-application" 
                            data-volunteer-id="<?php echo $row_id; ?>" 
                            data-bs-toggle="modal" 
                            data-bs-target="#exampleModal_<?php echo $row_id; ?>">
                        Предложить мероприятие
                    </button>                        
                          </div>
                   
                    
                    <!-- Модальное окно -->
                    <div class="modal fade" id="exampleModal_<?php echo $row_id; ?>" tabindex="-1" aria-labelledby="exampleModalLabel_<?php echo $row_id; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel_<?php echo $row_id; ?>">Выбор мероприятия</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                                </div>
                                <div class="modal-body">
                                    <select class="form-control event-select" name="event_id">
                                        <?php
                                        if (!empty($events)) {
                                            foreach ($events as $event) {
                                                echo "<option value='" . $event['id'] . "'>" . $event['title'] . " - " . $event['event_date'] . "</option>";
                                            }
                                        } else {
                                            echo "<option value='' disabled>Нет доступных мероприятий</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                    <button type="button" class="btn btn-primary send-event" data-volunteer-id="<?php echo $row_id; ?>">Отправить</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "Пользователи не найдены.";
        }
        ?>
    </div>
</section>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).on('click', '.send-event', function() {
    var volunteerId = $(this).data('volunteer-id');
    var eventId = $(this).closest('.modal').find('.event-select').val()
    $.ajax({
        url: 'vendor/check_participation.php',
        type: 'POST',
        data: {
            event_id: eventId,
            volunteer_id: volunteerId
        },
        success: function(response) {
            if (response == 'participation') {
                alert('Пользователь уже является участником мероприятия или отправлял заявку.');
            } else if (response == 'application') {
                alert('Вы уже отправили заявку на это мероприятие.');
            } else if (response == 'success') {
                var confirmAction = confirm('Пользователь еще не участвует в этом мероприятии. Хотите отправить заявку?');
                if (confirmAction) {
                    $.ajax({
                        url: 'vendor/send_application.php',
                        type: 'POST',
                        data: {
                            event_id: eventId,
                            volunteer_id: volunteerId
                        },
                        success: function(response) {
                            if (response === 'success') {
                                alert('Заявка успешно отправлена.');
                            } else {
                                alert('Ошибка при отправке заявки: ' + response);
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('Ошибка при отправке заявки: ' + error);
                        }
                    });
                }
            }
        },
        error: function(xhr, status, error) {
            alert('Ошибка при выполнении запроса: ' + error);
        }
    });
});
</script>

<script>
    // JavaScript для отправки формы при изменении значений полей
    document.getElementById('min_rating').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    document.getElementById('search').addEventListener('input', function() {
        document.getElementById('filterForm').submit();
    });

    document.getElementById('status').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
</script>
</body>
</html>