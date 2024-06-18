<?php
session_start();
$message = isset($_SESSION['message']) ? $_SESSION['message'] : ''; // Проверяем существование ключа 'message'
unset($_SESSION['message']); // Удаляем значение 'message' из сессии

// Подключение к базе данных
require_once 'vendor/connect.php';

// Проверка аутентификации пользователя
if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit(); // Прекращаем выполнение скрипта после перенаправления
}

// Проверяем, отправлен ли поисковый запрос
if (isset($_GET['search_query']) && !empty($_GET['search_query'])) {
    // Получаем поисковый запрос
    $searchQuery = mysqli_real_escape_string($connect, $_GET['search_query']);

    // Формируем SQL запрос с условием поиска по имени пользователя
    $sql = "SELECT id, full_name, email, role FROM users WHERE role IN ('us', 'st') AND (id LIKE '%$searchQuery%' OR full_name LIKE '%$searchQuery%' OR email LIKE '%$searchQuery%' OR role LIKE '%$searchQuery%')";
} else {
    // Формируем SQL запрос без условия поиска
    $sql = "SELECT id, full_name, email, role FROM users WHERE role IN ('us', 'st')";
}

// Выполняем запрос к базе данных
$result = mysqli_query($connect, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>



h1 {
  font-size: 30px;
  color: #fff;
  text-transform: uppercase;
  font-weight: 300;
  text-align: center;
  margin-bottom: 15px;
}
table{
  width:100%;
  table-layout: fixed;
  
}
.tbl-header{
  background-color: rgba(255,255,255,0.3);
  position: sticky;
    top: 0;
    z-index: 999; /* Чтобы заголовок оставался поверх содержимого */
 }
.tbl-content{
  height:300px;
  overflow-x:auto;
  margin-top: 0px;
  border: 1px solid rgba(255,255,255,0.3);
  height: 300px; /* Установите высоту по вашему усмотрению */
    overflow-x: auto;
}
.t{
max-height: 500px; /* Максимальная высота контейнера таблицы */
            overflow-y: auto; /* Включаем вертикальную прокрутку */
            margin: 0 auto; /* Центрируем контейнер таблицы */
            position: relative; /* Позиционируем относительно */
            width: 80%; /* Ширина контейнера */
            max-width: 900px; /* Максимальная ширина контейнера */
        }
        table {
            width: 100%; /* Ширина таблицы */
            border-collapse: collapse; /* Схлопываем границы ячеек */
            margin: 0px auto;
            
            border: 1px solid #ddd;
        }
       
th{
    position: sticky; /* "Закрепляем" заголовки */
  padding: 20px 15px;
  text-align: left;
  font-weight: 500;
  font-size: 12px;
  color: #black;
  text-transform: uppercase;
}
td{
  padding: 15px;
  text-align: left;
  vertical-align:middle;
  font-weight: 300;
  font-size: 12px;
  color: #black;
  border-bottom: solid 1px rgba(255,255,255,0.1);
}


/* demo styles */

@import url(https://fonts.googleapis.com/css?family=Roboto:400,500,300,700);
body{
  background: -webkit-linear-gradient(left, #25c481, #25b7c4);
  background: linear-gradient(to right, #25c481, #25b7c4);
  font-family: 'Roboto', sans-serif;
}
section{
  margin: 50px;
}


/* follow me template */
.made-with-love {
  margin-top: 40px;
  padding: 10px;
  clear: left;
  text-align: center;
  font-size: 10px;
  font-family: arial;
  color: #fff;
}
.made-with-love i {
  font-style: normal;
  color: #F50057;
  font-size: 14px;
  position: relative;
  top: 2px;
}
.made-with-love a {
  color: #fff;
  text-decoration: none;
}
.made-with-love a:hover {
  text-decoration: underline;
}


/* for custom scrollbar for webkit browser*/

::-webkit-scrollbar {
    width: 6px;
} 
::-webkit-scrollbar-track {
    -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3); 
} 
::-webkit-scrollbar-thumb {
    -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3); 
}




.btn-delete {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            background-color: #ffbbff;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
        }

        .btn-delete:hover {
            background-color: #ff3b20;
        }



       /* Стили для элемента поиска */
       #searchInput {
            background: rgba(0, 0, 0, .375) url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAAUCAYAAABvVQZ0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAQBJREFUeNqslI0RgyAMhdENWIEVWMEVXIGO0BW6Ah2hHcGOoCPYEewINFzBe9IA9id37w4kfEZesHHOCSYUqSPJML+RJlELDwN1pMHxMZNMkr8RTgyz2YPH5LmtwXpIHkOFmKhIlxowDmYAycKnHAHYcTCsSpXOJCie6YWDnXKLGeHLN2stGaqDsXXrX3GFcYcLrfhjtKEhffQ792gYT2nT6pJDjCw4z7ZGdGipOIqNbXIwFUARmCbKpMfYxsWJBmCEDoW7+gYUTAU2s3HJrK3AJvMLkqGHFLgWXTckm+SfSQexs+tLRqwVfgvjgMsvMAT689S5M/sk/I14kO5PAQYAuk6L1q+EdHMAAAAASUVORK5CYII=) no-repeat 14px 14px;
            text-indent: 1em;
            display: inline-block;
            border: 0 none;
            width: 0;
            height: 3em;
            border-radius: 3em;
            -webkit-transition: .3s;
            transition: .3s;
            outline: none;
            padding: 1em 1.5em;
            cursor: pointer;
            -webkit-appearance: none;
            font-weight: inherit;
            font-size: inherit;
            font-family: inherit;
            color: #999;
            vertical-align: baseline;
        }
        input[type=text]::-webkit-search-cancel-button {
            -webkit-appearance: none;
        }

        #searchInput:hover,
        #searchInput:focus {
            background: #fff url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAAUCAYAAABvVQZ0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAT5JREFUeNqsVLtOw0AQtIMlRJHCEhUVMg398QEUSZnSfILzCXxDPsFu6XAJHWnTcS1lWsprKdmLxtKwvjVBYaTV7cm+udnX5fPb+yyBSmwhVmK/FfPZLyjUPhI8YtXYi23EOovs7PzyevAbsWeoGg5HNUHsCipX8F9TZDOstVgLPxIsxW6w3sHv6dJ2StkLbh6IPtR/AWRfSIET20H9D2U1hfaAgxY2KMagcBSmg9/rmwx0lBqTzGfHoVfVHxXgXzCjHNRHnnHke4vMGc2q0RBR0GSeCLlpLaJGFWKUszVuib32nih7iTFrjXAPyGnQ48c3Gu5AOVlMtMk6NZuf+FiC+AIhV0T+pBQ5ntXceIJKqKko2duJ2TwoLAz5QTVnagJaXWEO8y/wSMuKH9RTJoCTHyNZFidOUEfNu/8WYAAOXUT04MOtlwAAAABJRU5ErkJggg==) 14px 14px no-repeat;
        }

        #searchInput:focus {
            width: 100%;
            cursor: text;
         border-radius: 5px;}


         /* Дополнительные стили для мобильных устройств */
.made-with-love {
    margin-top: 40px;
    padding: 10px;
    clear: left;
    text-align: center;
    font-size: 10px;
    font-family: arial;
    color: #fff;
}

.made-with-love i {
    font-style: normal;
    color: #F50057;
    font-size: 14px;
    position: relative;
    top: 2px;
}
</style>
</head>
<body>
<?php include 'admin_profile.php'; ?>
<div class="container">
    <?php if (!empty($message)): ?>
        <p style="color: red;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form action="" method="post" name="user_search_form">
        <div class="t">
            <h1>Управление пользователями</h1>
            <input type="text" id="searchInput" placeholder="Поиск...">

            <div class="tbl-header">
                <table cellpadding="0" cellspacing="0" border="0">
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="checkAll"></th>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Change Role</th>
                    </tr>
                    </thead>
                </table>
            </div>
            <div class="tbl-content">
                <table>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><input type="checkbox" name="selected_users[]" value="<?php echo $row['id']; ?>"></td>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['full_name']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['role']; ?></td>
                            <td>
                                <form class="change-role-form" action="vendor/change_role.php" method="post">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <select name="new_role">
                                        <option value="us" <?php if ($row['role'] == 'us') echo 'selected'; ?>>Волонтер</option>
                                        <option value="st" <?php if ($row['role'] == 'st') echo 'selected'; ?>>Старший</option>
                                    </select>
                                    <button type="submit">Сохранить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" name="delete_users" class="btn-delete">Удалить</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем существование элемента с идентификатором searchInput
    var searchInput = document.getElementById('searchInput');
    if (!searchInput) {
        // Создаем элемент input
        searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.id = 'searchInput';
        searchInput.placeholder = 'Поиск...';

        // Находим место, куда добавить элемент
        var container = document.querySelector('.container');
        if (container) {
            // Добавляем элемент перед контейнером
            container.parentNode.insertBefore(searchInput, container);
        } else {
            // Если место для добавления элемента не найдено, добавляем его в конец body
            document.body.appendChild(searchInput);
        }
    }

    // Добавляем обработчик события для нового или существующего элемента searchInput
    searchInput.addEventListener('input', function() {
        var searchTerm = this.value.toLowerCase();
        var tableRows = document.querySelectorAll('.tbl-content tbody tr');
        tableRows.forEach(function(row) {
            var fullName = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            var email = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
            var role = row.querySelector('td:nth-child(5)').textContent.toLowerCase();

            if (fullName.includes(searchTerm) || email.includes(searchTerm) || role.includes(searchTerm)) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        });
    });


    document.getElementById("checkAll").addEventListener("change", function() {
        var checkboxes = document.querySelectorAll('input[name="selected_users[]"]');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = document.getElementById("checkAll").checked;
        });
    });

    document.querySelectorAll("form[action='vendor/change_role.php'][method='post']").forEach(function(form) {
        form.addEventListener("submit", function(event) {
            event.preventDefault(); // Отменяем стандартное поведение формы

            var user_id = this.querySelector("input[name='user_id']").value;
            var new_role = this.querySelector("select[name='new_role']").value;

            fetch("vendor/change_role.php", {
                method: "POST",
                body: JSON.stringify({ user_id: user_id, new_role: new_role }),
                headers: {
                    "Content-Type": "application/json"
                }
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
            })
            .catch(error => console.error('Ошибка:', error));
        });
    });

    document.querySelector("form[name='delete_users'][method='post']").addEventListener("submit", function(event) {
        event.preventDefault(); // Отменяем стандартное поведение формы

        var selectedUsers = [];
        var checkboxes = document.querySelectorAll('input[name="selected_users[]"]:checked');
        checkboxes.forEach(function(checkbox) {
            selectedUsers.push(checkbox.value);
        });

        if (selectedUsers.length > 0) {
            fetch("vendor/delete_users.php", {
                method: "POST",
                body: JSON.stringify(selectedUsers),
                headers: {
                    "Content-Type": "application/json"
                }
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
            })
            .catch(error => console.error('Ошибка:', error));
        } else {
            alert("Выберите пользователей для удаления");
        }
    });
});
</script>
</body>
</html>