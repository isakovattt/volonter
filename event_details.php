<?php
session_start();

// Проверяем, был ли пользователь аутентифицирован
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header('Location: /'); // Перенаправляем пользователя на страницу входа
    exit; // Прерываем выполнение скрипта
}

// Подключение к базе данных
require_once 'vendor/connect.php';

// Проверяем, передан ли идентификатор мероприятия через GET-запрос
if (isset($_GET['id'])) {
    $event_id = $_GET['id'];
    
    // Запрос к базе данных для получения информации о мероприятии
    $event_query = mysqli_query($connect, "SELECT * FROM events WHERE id = '$event_id'");
    
    // Проверяем, найдено ли мероприятие с указанным идентификатором
    if (mysqli_num_rows($event_query) > 0) {
        $event = mysqli_fetch_assoc($event_query);
    } else {
        // Если мероприятие не найдено, перенаправляем пользователя на страницу с ошибкой или обратно на страницу с мероприятиями
        header('Location: /mer_st.php'); // Предполагается, что events.php - это страница со списком мероприятий
        exit;
    }
} else {
    // Если идентификатор мероприятия не был передан, перенаправляем пользователя на страницу с мероприятиями
    header('Location: /mer_st.php'); // Предполагается, что events.php - это страница со списком мероприятий
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $event['title']; ?></title>
    <link rel="stylesheet" href="css/edit.css">
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css'>
    <style>
        .img-md {
        width: 2.3rem;
        height: 2.3rem;
      }
        </style>
</head>
<body>
    
<?php include 'admin_profile.php'; ?>

<div class="main-body">
<?php
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-info" role="alert">';
        echo $_SESSION['message'];
        echo '</div>';
        unset($_SESSION['message']); // Очищаем сообщение из сессии после отображения
    }
    ?>
<div class="row gutters-sm">
            <div class="col-md-4 mb-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex flex-column align-items-center text-center">
                  <img src="<?php echo $event['photo']; ?>" alt="<?php echo $event['title']; ?>" class="img-fluid">
                        <div class="mt-3">
                      <h4><?php echo $event['title']; ?></h4>
                      <p class="text-secondary mb-1"><?php echo $event['description']; ?></p>
                      </div>
                  </div>
                  </div>
					</div>
				</div>
                <div class="col-md-8">
              <div class="card mb-3">
                <div class="card-body">
                  <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0">Дата</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                    <?php echo $event['event_date']; ?>
                    </div>
                  </div>
                  <hr>
                  <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0">Время</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                    <?php echo $event['start_time'] . ' - ' . $event['end_time']; ?>
                    </div>
                  </div>
                  <hr>
                  <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0">Место</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                    <?php echo $event['location']; ?>
                    </div>
                  </div>
                  <hr>
                  <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0">Баллы за мероприятие</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                    <?php echo $event['points']; ?>
                    </div>
                  </div>
                  <hr>
                  <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0">Необходимое количество волонтеров</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                    <?php echo $event['volunteers_needed']; ?>
                    </div>
                  </div>
                  <hr>
                  <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0">Задачи волонтёров</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                    <?php echo $event['volunteer_tasks']; ?></div>
                  </div>
                  <hr>
                  <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0">Требования к волонтёрам</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                    <?php echo $event['volunteer_requirements']; ?> </div>
                  </div>
                  <hr>
                  
                  <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0">Ответственный</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                      <?php
                        $responsible_id = $event['responsible_id'];
                        $responsible_query = mysqli_query($connect, "SELECT * FROM users WHERE id = '$responsible_id'");
                        $responsible = mysqli_fetch_assoc($responsible_query);
                        if (isset($responsible['avatar'])) {
                            echo '<img class="img-md rounded-circle me-1" src="' . $responsible['avatar'] . '" alt="' . $responsible['full_name'] . '" loading="lazy">';
                        } else {
                            echo '<img class="img-md rounded-circle me-1" src="default_user_photo.jpg" alt="" loading="lazy">';
                        }
                        echo '<span>' . $responsible['full_name'] . '</span>';
                      ?>
                    </div>
                  </div>
                </div>
              </div>
              
              <?php if ($_SESSION['user']['role'] == 'st') { ?>
              <div class="row gutters-sm">
                  <div class="col-sm-6 mb-3">
                      <div class="card h-100">
                          <div class="card-body">
                              <h6 class="d-flex align-items-center mb-3"><i class="material-icons text-info mr-2">Заявки на мероприятие</i></h6>

                              <?php
                              // Запрос к базе данных для получения заявок на данное мероприятие, которые еще не приняты или отклонены
                              $applications_query = mysqli_query($connect, "SELECT * FROM applications WHERE event_id = '$event_id' AND (status = 'pending' OR status = 'rejected')");

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
                                      echo '<a href="profile_us.php?user_id=' . $user['id'] . '" class="text-decoration-none">';
                                      echo '<img class="img-md rounded-circle me-3" src="' . $user['avatar'] . '" alt="Profile Picture" loading="lazy">';
                                      echo '<div>';
                                      echo '<h6 class="m-0">' . $user['full_name'] . '</h6>';
                                      echo '<p class="m-0">' . $user['email'] . '</p>';
                                      echo '</a>';
                                      echo '</div>';

                                      echo '</div>';

                                      // Кнопки подтверждения и отклонения заявки
                                      echo '<div class="text-center">';
                                      if ($application['status'] === 'pending') {
                                          echo '<a href="vendor/confirm_application.php?application_id=' . $application['id'] . '" class="btn btn-success btn-sm mx-2">Подтвердить участие</a>';
                                          echo '<a href="vendor/confirm_application.php?reject_application_id=' . $application['id'] . '" class="btn btn-danger btn-sm mx-2">Отклонить</a>';
                                      } elseif ($application['status'] === 'rejected') {
                                          echo '<span class="text-danger mx-2">Отказано</span>';
                                          echo '<a href="vendor/confirm_application.php?cancel_rejection_id=' . $application['id'] . '" class="btn btn-warning btn-sm mx-2">Отменить</a>';
                                      }
                                      echo '</div>';
                                  }
                              }
                              ?>

                          </div>
                      </div>
                  </div>
                  <div class="col-sm-6 mb-3">
                      <div class="card h-100">
                          <div class="card-body">
                              <h6 class="d-flex align-items-center mb-3"><i class="material-icons text-info mr-2">Участники</i></h6>

                              <?php
                              // Запрос к базе данных для получения принятых заявок на данное мероприятие
                              $applications_query = mysqli_query($connect, "SELECT * FROM applications WHERE event_id = '$event_id' AND status = 'approved'");

                              if (mysqli_num_rows($applications_query) == 0) {
                                  echo "<p>Нет принятых заявок на данное мероприятие.</p>";
                              } else {
                                  while ($application = mysqli_fetch_assoc($applications_query)) {
                                      // Получение информации о пользователе
                                      $applicant_id = $application['user_id'];
                                      $user_query = mysqli_query($connect, "SELECT * FROM users WHERE id = '$applicant_id'");
                                      $user = mysqli_fetch_assoc($user_query);

                                      // Отображение информации о заявке
                                      echo '<div class="d-flex align-items-center mb-3">';
                                      echo '<a href="profile_us.php?user_id=' . $user['id'] . '" class="text-decoration-none">';
                                      echo '<img class="img-md rounded-circle me-3" src="' . $user['avatar'] . '" alt="Profile Picture" loading="lazy">';
                                      echo '<div>';
                                      echo '<h6 class="m-0">' . $user['full_name'] . '</h6>';
                                      echo '<p class="m-0">' . $user['email'] . '</p>';
                                      echo '</a>';
                                      echo '</div>';
                                      echo '</div>';

                                      // Кнопка "Отменить участие"
                                      echo '<div class="text-center">';
                                      echo '<a href="vendor/confirm_application.php?cancel_application_id=' . $application['id'] . '" class="btn btn-danger btn-sm mx-2">Отменить участие</a>';
                                      echo '</div>';
                                  }
                              }
                              ?>
                          </div>
                      </div>
                  </div>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
