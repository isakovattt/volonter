<?php
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Создание мероприятия</title>
   
    <style>
        
body {
    background-color: #f0f5ff; /* Белый с небесным оттенком */
    
    color: #1e1e1e; /* Черный */
    
    
    justify-content: center; /* Выравнивание по горизонтали */
    align-items: center; /* Выравнивание по вертикали */
    height: 100vh; /* Высота равная высоте окна браузера */
}

h2 {
    color: #1e1e1e; /* Черный */
    text-align: center;
    margin-bottom: 20px;
}

form {
    background-color: #fff; /* Белый */
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Тень */
    width: 90%; /* Ширина формы на всех устройствах */
    max-width: 500px; /* Максимальная ширина формы */
    margin: auto; /* Центрирование формы */
}

label {
    font-weight: bold;
    color: #1e1e1e; /* Черный */
}

input[type="text"],
input[type="date"],
input[type="time"],
input[type="number"],
textarea,
select {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
}

textarea {
    resize: vertical; /* Разрешить изменение высоты по вертикали */
}

button[type="submit"] {
    background-color: #007bff; /* Синий */
    color: #fff; /* Белый */
    border: none;
    border-radius: 5px;
    padding: 10px 20px;
    cursor: pointer;
    transition: background-color 0.3s;
    width: 100%;
}

button[type="submit"]:hover {
    background-color: #0056b3; /* Темно-синий */
}

button[type="submit"]:focus {
    outline: none;
}

p {
    color: #007bff; /* Синий */
    text-align: center;
}

    </style>   
</head>
<body>
<?php include 'admin_profile.php'; ?>
<h2>Создание мероприятия</h2>
<form action="vendor/process_event.php" method="post" enctype="multipart/form-data" onsubmit="return validateTime();">
    
   
    <label for="title">Название:</label><br>
        <input type="text" id="title" name="title" required><br><br>
        
        <label for="photo">Фото:</label><br>
        <input type="file" id="photo" name="photo" accept="image/*"><br><br>
        
        <label for="location">Местоположение:</label><br>
        <input type="text" id="location" name="location" required><br><br>
        
        <label for="event_date">Дата мероприятия:</label><br>
        <input type="date" id="event_date" name="event_date" required min="<?php echo date('Y-m-d'); ?>"><br><br>

        
        <label for="start_time">Начало:</label><br>
        <input type="time" id="start_time" name="start_time" required><br><br>

        <label for="end_time">Конец:</label><br>
        <input type="time" id="end_time" name="end_time" required><br><br>
        <label for="points">Баллы:</label><br>
        <input type="number" id="points" name="points" required><br><br>
        
        <label for="volunteers_needed">Количество волонтеров:</label><br>
        <input type="number" id="volunteers_needed" name="volunteers_needed" required><br><br>
        
        <label for="description">Описание мероприятия:</label><br>
        <textarea id="description" name="description" rows="1" cols="40" required></textarea><br><br>
        
        <label for="volunteer_tasks">Задачи волонтеров:</label><br>
        <textarea id="volunteer_tasks" name="volunteer_tasks" rows="1" cols="40" required></textarea><br><br>
        
        <label for="volunteer_requirements">Требования к волонтерам:</label><br>
        <textarea id="volunteer_requirements" name="volunteer_requirements" rows="1" cols="40" required></textarea><br><br>
        
        <label for="responsible_id">Ответственный за мероприятие:</label><br>
        <select id="responsible_id" name="responsible_id" required>
        <?php
            // Подключение к базе данных
            require_once 'vendor/connect.php';

            // Запрос на выборку студентов с ролью "st"
            $sql = "SELECT id, full_name FROM users WHERE role = 'st'";
            $result = $connect->query($sql);

            // Проверка наличия результатов запроса
            if ($result->num_rows > 0) {
                // Вывод опций выпадающего списка
                while($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row["id"] . "'>" . $row["full_name"] . "</option>";
                }
            } else {
                echo "<option value=''>No students found</option>";
            }

            // Закрытие соединения с базой данных
            $connect->close();
            
            ?>
        </select><br><br>
        <?php
if (isset($_SESSION['message'])) {
    echo "<p>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']); // Удаляем сообщение из сессии, чтобы оно не появлялось повторно
}
?>
        
        
        <button type="submit">Создать </button>
    </form>
    <script>
        function validateTime() {
            var startTime = document.getElementById('start_time').value;
            var endTime = document.getElementById('end_time').value;

            if (startTime >= endTime) {
                alert("Время начала должно быть раньше времени окончания мероприятия.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
