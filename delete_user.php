<?php
session_start();

// Проверяем, был ли пользователь аутентифицирован как администратор
if (!isset($_SESSION['user']) || empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'ad') {
    header('Location: /'); // Перенаправляем пользователя на главную страницу
    exit; // Прерываем выполнение скрипта
}

// Подключение к базе данных
require_once 'vendor/connect.php';

// Обработка поиска пользователя
if (isset($_POST['search_user'])) {
    $search_query = mysqli_real_escape_string($connect, $_POST['search_user']);
    $sql = "SELECT * FROM users WHERE role IN ('us', 'st') AND (full_name LIKE '%$search_query%' OR email LIKE '%$search_query%')";
} else {
    $sql = "SELECT * FROM users WHERE role IN ('us', 'st')";
}

// Получение пользователей с ролью "Волонтер" или "Ответственный"
$result = mysqli_query($connect, $sql);

// Обработка удаления пользователя
if (isset($_POST['delete_user'])) {
    $delete_ids = isset($_POST['delete_ids']) ? $_POST['delete_ids'] : [];
    foreach ($delete_ids as $user_id) {
        $user_id = mysqli_real_escape_string($connect, $user_id);
        mysqli_query($connect, "DELETE FROM users WHERE id='$user_id'");
    }
    $_SESSION['message'] = 'Пользователи успешно удалены.';
    header('Location: delete_user.php');
    exit;
}

// Обработка изменения роли пользователя
if (isset($_POST['change_role'])) {
    $user_id = mysqli_real_escape_string($connect, $_POST['user_id']);
    $new_role = mysqli_real_escape_string($connect, $_POST['new_role']);
    mysqli_query($connect, "UPDATE users SET role='$new_role' WHERE id='$user_id'");
    $_SESSION['message'] = 'Роль пользователя успешно изменена.';
    header('Location: delete_user.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление пользователей</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">
    <style>
        body {
            background: #74ebd5;
            background: -webkit-linear-gradient(to right, #74ebd5, #ACB6E5);
            background: linear-gradient(to right, #74ebd5, #ACB6E5);
            min-height: 100vh;
        }

        .container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .table-container {
            max-height: 300px;
            overflow-y: auto;
        }

        .table-container table thead th {
            position: sticky;
            top: 0;
            background: #f1f1f1;
        }

        .table-container table {
            width: 100%;
        }

        .btn-delete {
            background-color: #007bff;
            color: white;
            width: 100%;
        }

        .btn-delete:hover {
            background-color: #0056b3;
        }

        .table-container {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<?php include 'admin_profile.php'; ?>

<div class="container mt-5">
    <h1>Удаление пользователей</h1>

    <form action="delete_user.php" method="post" class="mb-3">
        <input type="text" id="search_user" name="search_user" placeholder="Поиск по пользователям" class="form-control">
    </form>

    <form action="delete_user.php" method="post">
        <div class="table-container">
            <table id="user_table" class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Выбрать</th>
                        <th scope="col">Имя пользователя</th>
                        <th scope="col">Email</th>
                        <th scope="col">Роль</th>
                        <th scope="col">Изменить роль</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><input type="checkbox" name="delete_ids[]" value="<?php echo $row['id']; ?>"></td>
                            <td><?php echo $row['full_name']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['role']; ?></td>
                            <td>
                                <form action="delete_user.php" method="post">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <select name="new_role" class="form-control">
                                        <option value="us" <?php if ($row['role'] == 'us') echo 'selected'; ?>>Волонтер</option>
                                        <option value="st" <?php if ($row['role'] == 'st') echo 'selected'; ?>>Ответственный</option>
                                    </select>
                                    <button type="submit" name="change_role" class="btn btn-primary btn-sm mt-1">Сохранить</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="text-center">
            <button type="submit" name="delete_user" class="btn btn-delete mt-3">Удалить выбранных пользователей</button>
        </div>
    </form>

    <p class="mt-3"><?= $_SESSION['message'] ?? '' ?></p>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

<script>
    document.getElementById('search_user').addEventListener('input', function() {
        var searchValue = this.value.toLowerCase();
        var rows = document.getElementById('user_table').getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        var hasResults = false;

        for (var i = 0; i < rows.length; i++) {
            var name = rows[i].getElementsByTagName('td')[1].innerText.toLowerCase();
            var email = rows[i].getElementsByTagName('td')[2].innerText.toLowerCase();
            if (name.includes(searchValue) || email.includes(searchValue)) {
                rows[i].style.display = '';
                hasResults = true;
            } else {
                rows[i].style.display = 'none';
            }
        }

        document.querySelector('thead').style.display = hasResults ? '' : 'none';
    });
</script>

</body>
</html>
