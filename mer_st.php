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
    <title>Управление доступом к мероприятиям</title>
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

      /* Стили для чекбокса поиска */
      #all_users_checkbox {
        margin-bottom: 10px;
      }

      /* Стили для выпадающего списка */
      .user-dropdown {
        width: 100%;
        height: 150px;
        margin-bottom: 10px;
        border: 1px solid #ced4da;
        border-radius: 5px;
        overflow-y: auto;
      }

      /* Стили для кнопки "Изменить/Сохранить" */
     
      .message {
            background-color: #e0f7fa;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #00bcd4;
            color: #00796b;
        }
        .update-btn {
  background-color: #007bff; /* Голубой цвет */
  border-color: #007bff; /* Цвет границы */
  color: #fff; /* Белый цвет текста */
}

.update-btn:hover {
  background-color: #0056b3; /* Темный голубой при наведении */
  border-color: #0056b3; /* Цвет границы при наведении */
}
    </style>


</head>
<body>
<?php include 'admin_profile.php'; ?>
<div class="container">

<?php
// Проверяем наличие сообщения и выводим его, если оно есть
if (isset($_SESSION['message'])) {
    echo '<div class="message">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']); // Очищаем сообщение после вывода
}

// Подключение к базе данных
require_once 'vendor/connect.php';

// Запрос к базе данных для получения мероприятий, где пользователь является ответственным и мероприятие еще не прошло
$events_query = mysqli_query($connect, "SELECT * FROM events WHERE responsible_id = '$user_id' AND event_date > NOW()");

// Проверяем, есть ли у пользователя мероприятия, где он является ответственным
if (mysqli_num_rows($events_query) == 0) {
    echo "<p>У вас нет назначенных мероприятий.</p>";
} else {
    // Отображение каждого мероприятия в виде квадратика
    while ($event = mysqli_fetch_assoc($events_query)) {
       
        echo '<div class="card mb-3">';
        echo '<div class="card-body d-flex align-items-center">';
        
        // Отображаем изображение мероприятия
        echo '<a href="event_details.php?id=' . $event['id'] . '">';
        echo '<img class="img-md rounded-circle me-3" src="' . $event['photo'] . '" alt="' . $event['title'] . '" style="max-width: 100%;">';
       
        echo '<div>';

        // Отображаем название мероприятия
        echo '<h3>' . $event['title'] . '</h3>';
        echo '</a>';
        // Отображаем информацию о доступности мероприятия
        echo '<p class="text-muted m-0">Мероприятие доступно: ';
        if ($event['available_for_users'] == 'all') {
            echo 'всем пользователям';
        } else {
            // Проверяем, были ли выбранные пользователи
            if (!empty($event['available_for_users'])) {
                $available_users = explode(",", $event['available_for_users']);
                $first = true; // Флаг для первого имени пользователя
                foreach ($available_users as $user_id) {
                    $user_query = mysqli_query($connect, "SELECT full_name FROM users WHERE id = '$user_id'");
                    $user = mysqli_fetch_assoc($user_query);
                    // Добавляем запятую перед именем пользователя, кроме первого
                    if ($first) {
                        $first = false;
                    } else {
                        echo ', ';
                    }
                    echo '<span>' . $user['full_name'] . '</span>';
                }
            } else {
                echo 'Никому. Настройте доступ мероприятия';
            }
        }
        echo '</p>';

        // Проверяем, было ли обновление доступа к этому мероприятию
        if (isset($_POST['update_access']) && isset($_POST['event_id']) && $_POST['event_id'] == $event['id']) {
            // Если да, выводим форму для выбора пользователей и кнопку "Сохранить"
            echo '<form action="vendor/update_event_access.php" method="post">';
            echo '<input type="search" id="user_search" placeholder="Поиск пользователей">';
            echo '<select class="user-dropdown" name="selected_users[]" multiple>';
            
            // Получаем список всех пользователей
            $users_query = mysqli_query($connect, "SELECT id, full_name FROM users WHERE role = 'us'");
            while ($user = mysqli_fetch_assoc($users_query)) {
                $selected = (in_array($user['id'], explode(",", $event['available_for_users']))) ? 'selected' : '';
                echo '<option value="' . $user['id'] . '" ' . $selected . '>' . $user['full_name'] . '</option>';
            }
            
            echo '</select>';
            echo '<input type="hidden" name="event_id" value="' . $event['id'] . '">';
            echo '<div class="button-group">';
            echo '<input type="checkbox" name="all_users" id="all_users_checkbox" ';
            if ($event['available_for_users'] == 'all') {
                echo 'checked';
            }
            echo '>';
            echo '<label for="all_users_checkbox">Выбрать всех пользователей</label><br>';
            echo '<button type="submit" name="update_access" class="btn btn-sm btn-warning btn-outline-light">Сохранить</button>';

            echo '</div>';
            echo '</form>';
        } else {
            // Иначе, выводим кнопку "Изменить"
            echo '<div class="ms-auto">';
            echo '<form action="mer_st.php" method="post">';
            echo '<input type="hidden" name="event_id" value="' . $event['id'] . '">';
            echo '<div class="button-group">';
            echo '<button type="submit" name="update_access" class="btn btn-sm btn-warning btn-outline-light">Изменить</button>';

            echo '</div>';
            echo '</form>';
            echo '</div>'; // Закрываем кнопку "Изменить"
        }

        echo '</div>'; // Закрываем d-flex
        echo '</div>'; // Закрываем border-top
        echo '</div>'; // Закрываем card
        
    }
}
?>

<script>
    // Обработка события ввода текста в поле поиска пользователей
    document.getElementById('user_search').addEventListener('input', function() {
        var searchValue = this.value.toLowerCase();
        var options = document.querySelectorAll('.user-dropdown option');
        options.forEach(function(option) {
            var text = option.innerText.toLowerCase();
            if (text.indexOf(searchValue) !== -1) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>
