<?php
session_start();
$message = isset($_SESSION['message']) ? $_SESSION['message'] : ''; // Проверяем существование ключа 'message'
unset($_SESSION['message']); // Удаляем значение 'message' из сессии
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание новости</title>
    
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


    <h2>Формирование новости</h2>
    <form action="vendor/process_news.php" method="post" enctype="multipart/form-data" onsubmit="return validateNews()">
        
    <label for="title">Заголовок новости:</label><br>
        <input type="text" id="title" name="title" required><br><br>
        
        <label for="image">Фотография:</label><br>
        <input type="file" id="image" name="image" accept="image/*"><br><br>
        
        <label for="date">Дата новости:</label><br>
        <input type="date" id="date" name="date" required><br><br>
        
        <label for="content">Содержание новости:</label><br>
        <textarea id="content" name="content" rows="6" cols="40" required></textarea><br><br>
        
        <button type="submit">Создать</button>
        <p class="msg">
                <?= $message ?>
            </p>
            <?php unset($_SESSION['message']); ?>
    </form>

</body>
</html>
