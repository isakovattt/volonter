<?php
    session_start();
    $message = isset($_SESSION['message']) ? $_SESSION['message'] : ''; // Проверяем существование ключа 'message'
    unset($_SESSION['message']); // Удаляем значение 'message' из сессии
  
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <title>Авторизация</title>
    <script>
        function validateForm() {
            var fullName = document.getElementById('full_name').value;
            var regex = /^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u;

            // Check if the input matches the regex and is not only spaces
            if (!regex.test(fullName) || fullName.trim() === '') {
                alert('ФИО может содержать только буквы, пробелы и дефисы, и не может быть пустым или содержать только пробелы.');
                return false;
            }
            return true;
        }
    </script>
    <link rel="stylesheet" href="css/avtoriz.css">
</head>
<body>
    
        <form action="vendor/signup.php" method="post" enctype="multipart/form-data">
            <label>ФИО:</label>
            <input type="text" name="full_name" placeholder="Введите ФИО">
    
            <label>Логин:</label>
            <input type="text" name="login" placeholder="Введите логин">
            <label>Почта:</label>
            <input type="email" name="email" placeholder="Введите почту">
            <label>Изображение профиля:</label>
            <input type="file" name="avatar">
            <label>Пароль:</label>
            <input type="password" name="password" placeholder="Введите пароль">
            <label>Подтверждение пароля:</label>
            <input type="password" name="password_confirm" placeholder="Подтвердите пароль">
        
            <button type="submit">Зарегистрироваться</button>
            <p>
                У вас уже есть аккаунт? - <a href="/"> авторизируйтесь</a>!
            </p>
            <p class="msg">
                <?= $message ?>
            </p>
            <?php unset($_SESSION['message']); ?>
            
            
            
        </form>  
    
</body>
</html>

