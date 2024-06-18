<?php
session_start();
require_once 'vendor/connect.php';
if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit(); // Для прекращения выполнения скрипта после перенаправления
}
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
$stmt = $connect->prepare("SELECT u.full_name, u.email, u.login, u.role, u.avatar, u.gender, u.languages, u.availability, u.hobbies, u.medical_restrictions, u.physical_activity, u.computer_skills, u.leadership_qualities, AVG(a.rating) AS average_rating FROM users u LEFT JOIN applications a ON u.id = a.user_id WHERE u.id = ? GROUP BY u.id");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $full_name = $user_data['full_name'];
    $email = $user_data['email'];
   $role = $user_data['role'];
    if ($role === 'ad') {
        $role = 'Администратор';
    } elseif ($role === 'us') {
        $role = 'Волонтер';
    } elseif ($role === 'st') {
        $role = 'Ответственный за мероприятие';
    }
        $login = $user_data['login'];
        $gender = $user_data['gender']; // Получаем значение пола из базы данных
        $languages = $user_data['languages']; // Получаем значение знания языков из базы данных
       
        $avatar = $user_data['avatar']; // Путь к изображению из базы данных
    $average_rating = ($user_data['average_rating'] !== null) ? round($user_data['average_rating'], 0) : 0; // Округляем среднюю оценку
    // Определяем иконку статуса
    $status_icon = '';
    $status_color = '';
    // Определение статуса пользователя
    $user_status_query = mysqli_query($connect, "SELECT status FROM users WHERE id = '$user_id'");
    if ($user_status_query) {
        $user_status_data = mysqli_fetch_assoc($user_status_query);
        $user_status = $user_status_data['status'];
    } else {
        // Обработка ошибки запроса, если необходимо
        $user_status = "Статус не установлен";
    }
    switch ($role) {
        case 'Волонтер':
            switch ($user_status) {
                case 'Готов в любой момент':
                    $status_icon = '<i class="bi bi-circle-fill text-primary"></i>';
                    break;
                case 'Готов по праздникам и выходным':
                    $status_icon = '<i class="bi bi-circle-fill text-success"></i>';
                    break;
                case 'Готов в утреннее/дневное/вечернее время':
                    $status_icon = '<i class="bi bi-circle-fill text-danger"></i>';
                    break;
                case 'Готов в определенные дни недели':
                    $status_icon = '<i class="bi bi-circle-fill text-warning"></i>';
                    break;
                default:
                    $status_icon = '';
                    break;
            }
            break;
        // Добавьте другие варианты статуса, если необходимо
        default:
            // Если статус не определен, оставляем пустую иконку
            $status_icon = '';
            break;
    }
} else {
    echo "Данные пользователя не найдены";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Мои баллы</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://unpkg.com/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="css/edit.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/bs-brain@2.0.3/components/testimonials/testimonial-3/assets/css/testimonial-3.css">
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css'>
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
                            <label for="avatar-input">
                                <img src="<?php echo $avatar; ?>" alt="<?php echo $full_name; ?>" class="rounded-circle" width="150">
                            </label>
                            <input type="file" id="avatar-input" style="display: none;">
                            <div class="mt-3">
                            <h4><?php echo $full_name; ?> <?php echo $status_icon; ?></h4>
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
                      <form id="edit-form" method="post" action="vendor/save_profile.php">
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">ФИО</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                     <span id="full-name"><?php echo $full_name; ?></span>
                                     <input type="text" name="full_name" class="form-control" id="full-name-input" value="<?php echo $full_name; ?>" style="display: none;">
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Логин</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                     <span id="login"><?php echo $login; ?></span>
                                    <input type="text" name="login" class="form-control" id="login-input" value="<?php echo $login; ?>" style="display: none;">
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Email</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                     <span id="email"><?php echo $email; ?></span>
                                     <input type="email" name="email" class="form-control" id="email-input" value="<?php echo $email; ?>" style="display: none;">
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-12">
                                    <button type="button" class="btn btn-info edit-btn">Редактировать</button>
                                    <button type="submit" class="btn btn-success save-btn" style="display: none;">Сохранить</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php if ($role === 'Волонтер'): ?>
                <div class="row">
						<div class="col-sm-12">
							<div class="card">
                            <div class="card-body">
    <h5 class="d-flex align-items-center mb-3">Статус</h5>
    <form id="status-form" method="post" action="vendor/save_status.php">
        <div class="row">
            <div class="col-sm-9 text-secondary">
                <?php
                $user_id = $_SESSION['user']['id'];
                // Добавьте код для извлечения статуса пользователя из базы данных
                $user_status_query = mysqli_query($connect, "SELECT status FROM users WHERE id = '$user_id'");
                if ($user_status_query) {
                    $user_status_data = mysqli_fetch_assoc($user_status_query);
                    $user_status = $user_status_data['status'];
                } else {
                    // Обработка ошибки запроса, если необходимо
                    $user_status = "Статус не установлен";
                }
                ?>
            </div>
        </div>
        <hr>
        <select name="status" class="form-control" id="status">
    <option value="Готов в любой момент" <?php if($user_status == 'Готов в любой момент') echo 'selected'; ?>>Готов в любой момент</option>
    <option value="Готов по праздникам и выходным" <?php if($user_status == 'Готов по праздникам и выходным') echo 'selected'; ?>>Готов по праздникам и выходным</option>
    <option value="Готов в утреннее/дневное/вечернее время" <?php if($user_status == 'Готов в утреннее/дневное/вечернее время') echo 'selected'; ?>>Готов в утреннее/дневное/вечернее время</option>
    <option value="Готов в определенные дни недели" <?php if($user_status == 'Готов в определенные дни недели') echo 'selected'; ?>>Готов в определенные дни недели</option>
</select>

    </form>
</div>

							</div>
						</div>
					</div>
             
                        <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
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
<div class="row">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-info edit-volunteer-btn">Редактировать</button>
                                <button type="submit" class="btn btn-success save-volunteer-btn" style="display: none;">Сохранить</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.bundle.min.js'></script>
<script>
  document.querySelector('.edit-volunteer-btn').addEventListener('click', function() {
    document.getElementById('gender').style.display = 'none';
    document.getElementById('gender-input').style.display = 'block';
    document.getElementById('languages').style.display = 'none';
    document.getElementById('languages-input').style.display = 'block';
    document.getElementById('availability').style.display = 'none';
    document.getElementById('availability-input').style.display = 'block';
    document.getElementById('hobbies').style.display = 'none';
    document.getElementById('hobbies-input').style.display = 'block';
    document.getElementById('medical-restrictions').style.display = 'none';
    document.getElementById('medical-restrictions-input').style.display = 'block';
    document.getElementById('physical-activity').style.display = 'none';
    document.getElementById('physical-activity-input').style.display = 'block';
    document.getElementById('computer-skills').style.display = 'none';
    document.getElementById('computer-skills-input').style.display = 'block';
    document.getElementById('leadership-qualities').style.display = 'none';
    document.getElementById('leadership-qualities-input').style.display = 'block';
    
        this.style.display = 'none';
        document.querySelector('.save-volunteer-btn').style.display = 'block';
    });

    document.querySelector('.save-volunteer-btn').addEventListener('click', function() {
        document.getElementById('gender').innerText = document.getElementById('gender-input').value;
    document.getElementById('gender').style.display = 'block';
    document.getElementById('gender-input').style.display = 'none';
    document.getElementById('languages').innerText = document.getElementById('languages-input').value;
    document.getElementById('languages').style.display = 'block';
    document.getElementById('languages-input').style.display = 'none';
    document.getElementById('hobbies').innerText = document.getElementById('hobbies-input').value;
    document.getElementById('hobbies').style.display = 'block';
    document.getElementById('hobbies-input').style.display = 'none';
    document.getElementById('availability').innerText = document.getElementById('availability-input').value;
    document.getElementById('availability').style.display = 'block';
    document.getElementById('availability-input').style.display = 'none';
    document.getElementById('medical-restrictions').innerText = document.getElementById('medical-restrictions-input').value;
    document.getElementById('medical-restrictions').style.display = 'block';
    document.getElementById('medical-restrictions-input').style.display = 'none';
    document.getElementById('physical-activity').innerText = document.getElementById('physical-activity-input').value;
    document.getElementById('physical-activity').style.display = 'block';
    document.getElementById('physical-activity-input').style.display = 'none';
    document.getElementById('computer-skills').innerText = document.getElementById('computer-skills-input').value;
    document.getElementById('computer-skills').style.display = 'block';
    document.getElementById('computer-skills-input').style.display = 'none';
    document.getElementById('leadership-qualities').innerText = document.getElementById('leadership-qualities-input').value;
    document.getElementById('leadership-qualities').style.display = 'block';
    document.getElementById('leadership-qualities-input').style.display = 'none';
    
        // Добавьте аналогичные строки для остальных полей анкеты
        this.style.display = 'none';
        document.querySelector('.edit-volunteer-btn').style.display = 'block';
        document.getElementById('volunteer-form').submit();
    });
    document.getElementById('avatar-input').addEventListener('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(event) {
                var imgData = event.target.result;
                var formData = new FormData();
                formData.append('avatar', file);
                fetch('vendor/save_avatar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(result => {
                     if (result === 'success') {
                        document.querySelector('.rounded-circle').src = imgData;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            };
            reader.readAsDataURL(file);
        }
    });
    document.querySelector('.edit-btn').addEventListener('click', function() {
        document.getElementById('full-name').style.display = 'none';
        document.getElementById('full-name-input').style.display = 'block';
        document.getElementById('login').style.display = 'none';
        document.getElementById('login-input').style.display = 'block';
        document.getElementById('email').style.display = 'none';
        document.getElementById('email-input').style.display = 'block';
        this.style.display = 'none';
        document.querySelector('.save-btn').style.display = 'block';
    });
    document.querySelector('.save-btn').addEventListener('click', function() {
        document.getElementById('full-name').innerText = document.getElementById('full-name-input').value;
        document.getElementById('full-name').style.display = 'block';
        document.getElementById('full-name-input').style.display = 'none';
        document.getElementById('login').innerText = document.getElementById('login-input').value;
        document.getElementById('login').style.display = 'block';
        document.getElementById('login-input').style.display = 'none';
        document.getElementById('email').innerText = document.getElementById('email-input').value;
        document.getElementById('email').style.display = 'block';
        document.getElementById('email-input').style.display = 'none';
        this.style.display = 'none';
        document.querySelector('.edit-btn').style.display = 'block';
        document.getElementById('edit-form').submit();
    });
    document.getElementById('status').addEventListener('change', function() {
    var selectedStatus = this.value; // Получаем выбранный статус
    fetch('vendor/save_status.php', { // Отправляем выбранный статус на сервер
        method: 'POST',
        body: new FormData(document.getElementById('status-form')) // Отправляем данные формы
    })
    .then(response => {
        if (response.ok) {
            // Если запрос успешен, обновляем страницу
            window.location.reload();
        } else {
            // Если возникла ошибка, выводим сообщение об ошибке
            console.error('Ошибка при сохранении статуса');
        }
    })
    .catch(error => {
        // Если возникла ошибка, выводим сообщение об ошибке
        console.error('Ошибка:', error);
    });
});
    
</script>
</body>
</html>
