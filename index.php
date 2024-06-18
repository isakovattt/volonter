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
    <link rel="stylesheet" href="css/avtoriz.css">
</head>
<body>
    
        <form action="vendor/signin.php" method="post">
           
                <label>Логин:</label>
                <input type="text" name="login" placeholder="Введите логин">
        
                <label>Пароль:</label>
                <input type="password" name="password" placeholder="Введите пароль">
            
            <button type="submit">Войти</button>
            <p>
                У вас нет аккаунта? - <a href="/registr.php"> зарегистрируйтесь</a>!
            </p>
            <p class="msg">
                <?= $message ?>
            </p>
            <?php unset($_SESSION['message']); ?>
            
        </form>
    
</body>
</html>
