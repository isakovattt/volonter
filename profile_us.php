<?php
session_start();
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header('Location: /'); // Перенаправляем пользователя на страницу входа
    exit; 
}
require_once 'vendor/connect.php';
$gender = '';
$languages = '';
$user_data = [
    'availability' => '',
    'hobbies' => '',
    'medical_restrictions' => '',
    'physical_activity' => '',
    'computer_skills' => '',
    'leadership_qualities' => ''
];

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $user_query = mysqli_query($connect, "SELECT * FROM users WHERE id = '$user_id'");
    if (mysqli_num_rows($user_query) > 0) {
        $user = mysqli_fetch_assoc($user_query);

        $gender = $user['gender'];
        $languages = $user['languages'];
        $user_data = [
            'availability' => $user['availability'],
            'hobbies' => $user['hobbies'],
            'medical_restrictions' => $user['medical_restrictions'],
            'physical_activity' => $user['physical_activity'],
            'computer_skills' => $user['computer_skills'],
            'leadership_qualities' => $user['leadership_qualities']
        ];
        $event_participation_query = mysqli_query($connect, "
            SELECT COUNT(*) AS event_participation_count
            FROM applications
            WHERE user_id = '$user_id'
            AND status = 'approved'
        ");
        $event_participation_data = mysqli_fetch_assoc($event_participation_query);
        $event_participation_count = $event_participation_data['event_participation_count'];
      $total_points_query = mysqli_query($connect, "
            SELECT SUM(points) AS total_points_earned
            FROM applications
            WHERE user_id = '$user_id'
            AND status = 'approved'
        ");
        $total_points_data = mysqli_fetch_assoc($total_points_query);
        $total_points_earned = $total_points_data['total_points_earned'];

         $total_hours_query = mysqli_query($connect, "
            SELECT SUM(TIMESTAMPDIFF(HOUR, events.start_time, events.end_time)) AS total_hours_participated
            FROM events
            JOIN applications ON events.id = applications.event_id
            WHERE applications.user_id = '$user_id'
            AND applications.status = 'approved'
        ");
        $total_hours_data = mysqli_fetch_assoc($total_hours_query);
        $total_hours_participated = $total_hours_data['total_hours_participated'];
        $average_rating_query = mysqli_query($connect, "
            SELECT AVG(rating) AS average_rating
            FROM applications
            WHERE user_id = '$user_id'
            AND status = 'approved'
            AND rating IS NOT NULL
        ");
        $average_rating_data = mysqli_fetch_assoc($average_rating_query);
        $average_rating = $average_rating_data['average_rating'];
 $reviews_query = mysqli_query($connect, "
            SELECT reviews.*, users.full_name, users.avatar, events.title 
            FROM applications AS reviews
            JOIN events ON reviews.event_id = events.id
            JOIN users ON events.responsible_id = users.id
            WHERE reviews.user_id = '$user_id'
            AND reviews.review IS NOT NULL
            AND reviews.review != ''
        ");

    } else {
         header('Location: /'); // Предполагается, что это главная страница сайта
        exit;
    }
} else {
     header('Location: /'); // Предполагается, что это главная страница сайта
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $user['full_name']; ?></title>
    <link rel="stylesheet" href="css/edit.css">
   
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css'>
    <style>
        .img-md {
        width: 2.3rem;
        height: 2.3rem;
      }
      body{
    color: #6F8BA4;
    margin-top:20px;
}
.section {
    padding: 100px 0;
    position: relative;
}

.gray-bg {
    background-color: #f5f5f5;
}
img {
    max-width: 100%;
}
img {
    vertical-align: middle;
    border-style: none;
}

.about-text h3 {
  font-size: 45px;
  font-weight: 700;
  margin: 0 0 6px;
}
@media (max-width: 767px) {
  .about-text h3 {
    font-size: 35px;
  }
}
.about-text h6 {
  font-weight: 600;
  margin-bottom: 15px;
}
@media (max-width: 767px) {
  .about-text h6 {
    font-size: 18px;
  }
}
.about-text p {
  font-size: 18px;
  max-width: 450px;
}
.about-text p mark {
  font-weight: 600;
  color: #20247b;
}

.about-list {
  padding-top: 10px;
}
.about-list .media {
  padding: 5px 0;
}
.about-list label {
  color: #20247b;
  font-weight: 600;
  width: 88px;
  margin: 0;
  position: relative;
}
.about-list label:after {
  content: "";
  position: absolute;
  top: 0;
  bottom: 0;
  right: 11px;
  width: 1px;
  height: 12px;
  background: #20247b;
  -moz-transform: rotate(15deg);
  -o-transform: rotate(15deg);
  -ms-transform: rotate(15deg);
  -webkit-transform: rotate(15deg);
  transform: rotate(15deg);
  margin: auto;
  opacity: 0.5;
}
.about-list p {
  margin: 0;
  font-size: 15px;
}

@media (max-width: 991px) {
  .about-avatar {
    margin-top: 30px;
  }
}

.about-section .counter {
  padding: 22px 20px;
  background: #ffffff;
  border-radius: 10px;
  box-shadow: 0 0 30px rgba(31, 45, 61, 0.125);
}
.about-section .counter .count-data {
  margin-top: 10px;
  margin-bottom: 10px;
}
.about-section .counter .count {
  font-weight: 700;
  color: #20247b;
  margin: 0 0 5px;
}
.about-section .counter p {
  font-weight: 600;
  margin: 0;
}
mark {
    background-image: linear-gradient(rgba(252, 83, 86, 0.6), rgba(252, 83, 86, 0.6));
    background-size: 100% 3px;
    background-repeat: no-repeat;
    background-position: 0 bottom;
    background-color: transparent;
    padding: 0;
    color: currentColor;
}
.theme-color {
    color: #fc5356;
}
.dark-color {
    color: #20247b;
}
    </style>
</head>
<body>
    
<?php include 'admin_profile.php'; ?>
    <section class="section about-section gray-bg" id="about">

        <div class="container">
            <div class="row align-items-center flex-row-reverse">
                <div class="col-lg-6">
                    <div class="about-text go-to">
                        <h3 class="dark-color"> <?php echo $user['full_name']; ?></h3>
                        <h6 class="theme-color lead">Email: <?php echo $user['email']; ?></h6>
                        <p>
                            <div class="bg-white rounded shadow-sm p-4 mb-4 clearfix graph-star-rating">
                            <h5 class="d-flex align-items-center mb-3">Анкета</h5>
                    <form id="volunteer-form" method="post" action="vendor/save_volunteer_profile.php">
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Пол</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <span id="gender"><?php echo $gender; ?></span>
                                <select name="gender" id="gender-input" class="form-control" style="display: none;">
                                    <option value="Женский" <?php if ($gender === 'Женский') echo 'selected'; ?>>Женский</option>
                                    <option value="Мужской" <?php if ($gender === 'Мужской') echo 'selected'; ?>>Мужской</option>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Знание языков</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <span id="languages"><?php echo $languages; ?></span>
                                <input type="text" name="languages" id="languages-input" class="form-control" value="<?php echo $languages; ?>" style="display: none;">
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
    <div class="col-sm-3">
        <h6 class="mb-0">Удобное время для участия</h6>
    </div>
    <div class="col-sm-9 text-secondary">
        <span id="availability"><?php echo $user_data['availability']; ?></span>
        <input type="text" name="availability" id="availability-input" class="form-control" value="<?php echo $user_data['availability']; ?>" style="display: none;">
    </div>
</div>
<hr>
<div class="row mb-3">
    <div class="col-sm-3">
        <h6 class="mb-0">Хобби</h6>
    </div>
    <div class="col-sm-9 text-secondary">
        <span id="hobbies"><?php echo $user_data['hobbies']; ?></span>
        <input type="text" name="hobbies" id="hobbies-input" class="form-control" value="<?php echo $user_data['hobbies']; ?>" style="display: none;">
    </div>
</div>
<hr>
<div class="row mb-3">
    <div class="col-sm-3">
        <h6 class="mb-0">Медицинские ограничения</h6>
    </div>
    <div class="col-sm-9 text-secondary">
        <span id="medical-restrictions"><?php echo $user_data['medical_restrictions']; ?></span>
        <input type="text" name="medical_restrictions" id="medical-restrictions-input" class="form-control" value="<?php echo $user_data['medical_restrictions']; ?>" style="display: none;">
    </div>
</div>
<hr>
<div class="row mb-3">
    <div class="col-sm-3">
        <h6 class="mb-0">Физическая активность</h6>
    </div>
    <div class="col-sm-9 text-secondary">
        <span id="physical-activity"><?php echo $user_data['physical_activity']; ?></span>
        <input type="text" name="physical_activity" id="physical-activity-input" class="form-control" value="<?php echo $user_data['physical_activity']; ?>" style="display: none;">
    </div>
</div>
<hr>
<div class="row mb-3">
    <div class="col-sm-3">
        <h6 class="mb-0">Компьютерные навыки</h6>
    </div>
    <div class="col-sm-9 text-secondary">
        <span id="computer-skills"><?php echo $user_data['computer_skills']; ?></span>
        <input type="text" name="computer_skills" id="computer-skills-input" class="form-control" value="<?php echo $user_data['computer_skills']; ?>" style="display: none;">
    </div>
</div>
<hr>
<div class="row mb-3">
    <div class="col-sm-3">
        <h6 class="mb-0">Лидерские качества</h6>
    </div>
    <div class="col-sm-9 text-secondary">
        <span id="leadership-qualities"><?php echo $user_data['leadership_qualities']; ?></span>
        <input type="text" name="leadership_qualities" id="leadership-qualities-input" class="form-control" value="<?php echo $user_data['leadership_qualities']; ?>" style="display: none;">
    </div>
</div>
                    </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-avatar">
                        <?php if (isset($user['avatar'])) : ?>
                            <img src="<?php echo $user['avatar']; ?>" title="<?php echo $user['full_name']; ?>" alt="<?php echo $user['full_name']; ?>">
                        <?php else : ?>
                            <img src="https://bootdey.com/img/Content/avatar/avatar7.png" title="<?php echo $user['full_name']; ?>" alt="<?php echo $user['full_name']; ?>">
                        <?php endif; ?>
                    </div>
                </div>
            </div>


            <div class="counter">
                <div class="row">
                    <div class="col-6 col-lg-3">
                        <div class="count-data text-center">
                            <h6 class="count h2" data-to="<?php echo $event_participation_count; ?>" data-speed="500"><?php echo $event_participation_count; ?></h6>
                            <p class="m-0px font-w-600">Мероприятий</p>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
    <div class="count-data text-center">
        <h6 class="count h2" data-to="<?php echo $total_points_earned; ?>" data-speed="150"><?php echo $total_points_earned > 0 ? $total_points_earned : '0'; ?></h6>
        <p class="m-0px font-w-600">Баллов</p>
    </div>
</div>
<div class="col-6 col-lg-3">
    <div class="count-data text-center">
        <h6 class="count h2" data-to="<?php echo $total_hours_participated; ?>" data-speed="850"><?php echo $total_hours_participated > 0 ? $total_hours_participated : '0'; ?></h6>
        <p class="m-0px font-w-600">Часов</p>
    </div>
</div>
                    <div class="col-6 col-lg-3">
                        <div class="count-data text-center">
                            <h6 class="count h2" data-to="<?php echo $average_rating; ?>" data-speed="190"><?php echo $average_rating !== null ? number_format($average_rating, 1) : '0'; ?></h6>

                            <p class="m-0px font-w-600">Рейтинг</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded shadow-sm p-4 mb-4 clearfix graph-star-rating">
            <h5 class="mb-0 mb-4">Рейтинг</h5>
                                <div class="graph-star-rating-header">

                                <p class="text-black mb-4 mt-2">Рейтинг <?php echo $average_rating !== null ? number_format($average_rating, 1) : '0.0'; ?> из 5</p>

 </div>
                                <div class="graph-star-rating-body">
                                    <div class="rating-list">
                                        <div class="rating-list">



                                            <?php
                                            // Массив для хранения количества оценок каждой звезды
                                            $rating_counts = array();

                                            // Запрос к базе данных для получения общего количества оценок для всех звезд
                                            $total_rating_query = mysqli_query($connect, "
                                            SELECT COUNT(*) AS total_ratings
                                            FROM applications
                                            WHERE user_id = '$user_id'
                                            AND status = 'approved'
                                            AND rating IS NOT NULL
                                        ");
                                            $total_rating_data = mysqli_fetch_assoc($total_rating_query);
                                            $total_ratings = $total_rating_data['total_ratings'];

                                            // Запрос к базе данных для получения количества оценок для каждой звезды
                                            for ($i = 5; $i >= 1; $i--) {
                                                $rating_query = mysqli_query($connect, "
                                            SELECT COUNT(*) AS rating_count
                                            FROM applications
                                            WHERE user_id = '$user_id'
                                            AND status = 'approved'
                                            AND rating = $i
                                            AND rating IS NOT NULL
                                        ");
                                                $rating_data = mysqli_fetch_assoc($rating_query);
                                                $rating_counts[$i] = $rating_data['rating_count'];
                                            }

                                            // Вычисляем процентное соотношение для каждой звезды
                                            $rating_percentages = array();
                                            foreach ($rating_counts as $star => $count) {
                                                $rating_percentages[$star] = $count > 0 ? ($count / $total_ratings) * 100 : 0;
                                            }
                                            ?>

                                            <!-- Выводим блоки для каждой звезды -->
                                            <?php foreach ($rating_percentages as $star => $percentage) : ?>
                                                <div class="rating-list">
                                                    <div class="rating-list-left text-black">
                                                        <?php echo $star; ?> звезд<?php echo $star == 1 ? 'а' : ($star < 5 ? 'ы' : ''); ?>
                                                    </div>
                                                    <div class="rating-list-center">
                                                        <div class="progress">
                                                            <div style="width: <?php echo $percentage; ?>%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="<?php echo $percentage; ?>" role="progressbar" class="progress-bar bg-primary">

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="rating-list-right text-black"><?php echo number_format($percentage, 2); ?>%</div>
                                                </div>
                                            <?php endforeach; ?>



                                    </div>
                                </div>
                    </div>
        
        </div>

            <div class="bg-white rounded shadow-sm p-4 mb-4 restaurant-detailed-ratings-and-reviews">
                <h5 class="mb-1">Отзывы</h5>
                <div class="reviews-members pt-4 pb-4">
                    <?php if (mysqli_num_rows($reviews_query) > 0) : ?>
                        <?php while ($review = mysqli_fetch_assoc($reviews_query)) : ?>
                            <div class="media">
                                <a href="#">
                                    <img alt="Generic placeholder image" src="<?php echo $review['avatar']; ?>" class="mr-3 rounded-pill" style="max-width: 70px; max-height: 70px;">
                                </a>
                                <div class="media-body">
                                    <div class="reviews-members-header">
                                        <span class="star-rating float-right">
                                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                <i class="icofont-ui-rating <?php echo $i <= $review['rating'] ? 'active' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </span>
                                        <h6 class="mb-1"><a class="text-black" href="#"><?php echo $review['full_name']; ?></a></h6>
                                        <?php
                                        // Массив с названиями месяцев на русском языке
                                        $months = array(
                                            1 => 'январь',
                                            2 => 'февраль',
                                            3 => 'март',
                                            4 => 'апрель',
                                            5 => 'май',
                                            6 => 'июнь',
                                            7 => 'июль',
                                            8 => 'август',
                                            9 => 'сентябрь',
                                            10 => 'октябрь',
                                            11 => 'ноябрь',
                                            12 => 'декабрь'
                                        );
                                        ?>

                                        <!-- Вывод даты с названием месяца на русском языке -->
                                        <p class="text-gray">
                                            <?php
                                            // Получаем числовой месяц из даты
                                            $month_number = date('n', strtotime($review['created_at']));
                                            // Выводим название месяца из массива
                                            echo $months[$month_number] . ' ' . date('Y', strtotime($review['created_at']));
                                            ?>
                                        </p>
                                        <p class="text-gray">
                                            <?php echo $review['title']; ?>
                                        </p>

                                    </div>
                                    <div class="reviews-members-body">
                                        <p><?php echo $review['review']; ?></p>
                                    </div>
                                    <div class="reviews-members-footer">
                                        <!-- Дополнительные опции, если необходимо -->
                                    </div>
                                </div>
                            </div>
                            <hr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <p>Пока нет отзывов на этого волонтера</p>
                    <?php endif; ?>
                </div>
            </div>
            <hr>

    </section>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.bundle.min.js'></script>
</body>

</html>