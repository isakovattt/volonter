<?php
    session_start();
    if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
        header('Location: /');
        exit; 
     }
 $user_role = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : null;
   if (isset($_GET['logout'])) {
        session_unset(); // Очищаем данные сессии
        session_destroy(); // Разрушаем сессию
        header('Location: index1.php'); // Перенаправляем пользователя на главную страницу
        exit; // Завершаем скрипт
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="css/bok.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css">
</head>
<body>
    <nav> 
        <div class="logo">
            <i class="bx bx-menu menu-icon"></i>
            <span class="logo-name"> Волонтеры ДГТУ</span>
        </div>

    <div class="sidebar">
        <div class="logo">
            <i class="bx bx-menu menu-icon"></i>
            <span class="logo-name"> Волонтеры ДГТУ</span>
            
        </div>

        <div class="sidebar-content">
            <ul class="lists">
            <li class="list">
            <div class="profile-info">
                <?php 
                    if(isset($_SESSION['user']['role'])) {
                        if($_SESSION['user']['role'] === 'us') {
                            echo '<a href="pr_u.php">';
                        } else {
                            echo '<a href="profile.php">';
                        }
                    } else {
                        echo '<a href="profile.php">';
                    }
                ?>
                    <img src="<?php echo isset($_SESSION['user']['avatar']) ? $_SESSION['user']['avatar'] : ''; ?>" alt="Avatar">
                    <span style="display: block;"><?php echo isset($_SESSION['user']['full_name']) ? $_SESSION['user']['full_name'] : ''; ?></span>
                    <span style="display: block;"><?php echo isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : ''; ?></span>
                </a>
    </div>
</li>


<?php if ($user_role == 'ad' || $user_role == 'us' || $user_role == 'st'): ?>
     <li class="list">
        <a href="index1.php" class="nav-link">
        <i class='bx bx-home icon'></i>
            <span class="link">Главная</span>
        </a>
    </li>
<?php endif; ?>


<?php if ($user_role == 'ad'): ?>
    <li class="list">
    <a href="news.php" class="nav-link">
        <i class='bx bx-news icon' ></i>
        <span class="link">Новости</span>
    </a>
</li>

    <li class="list">
    <a href="events.php" class="nav-link">
        <i class='bx bxs-face icon' ></i>
        <span class="link">Мероприятия</span>
    </a>
</li>
<li class="list">
        <a href="sozdanienovosti.php" class="nav-link" id="create-news-link">
            <i class='bx bx-edit-alt icon' ></i>
            <span class="link">Формирование новости</span>
        </a>
    </li>
    <li class="list">
        <a href="create_event.php" class="nav-link">
            <i class='bx bx-edit-alt icon' ></i>
            <span class="link">Формирование мероприятия</span>
        </a>
    </li>
    <li class="list">
        <a href="delete_user.php" class="nav-link">
            <i class='bx bxs-user-x icon' ></i>
            <span class="link">Удаление пользователей</span>
        </a>
    </li>
<?php elseif ($user_role == 'us'): ?>
    <li class="list">
        <a href="event_list.php" class="nav-link">
        <i class='bx bxs-face icon' ></i>
            <span class="link">Мероприятия</span>
        </a>
    </li>
    <li class="list">
        <a href="moi_meropriyatiya_us.php" class="nav-link">
            <i class='bx bx-calendar-event icon' ></i>
            <span class="link">Мои мероприятия</span>
        </a>
    </li>
    <li class="list">
        <a href="moi_zayavki.php" class="nav-link">
            <i class='bx bx-list-ul icon' ></i>
            <span class="link">Мои заявки</span>
        </a>
    </li>
    <li class="list">
        <a href="proposals.php" class="nav-link">
            <i class='bx bx-message-square-detail icon' ></i>
            <span class="link">Предложения</span>
        </a>
    </li>
    <li class="list">
    <a href="profile.php" class="nav-link">
        <i class='bx bx-edit icon'></i>
        <span class="link">Редактировать профиль</span>
    </a>
</li>
    <?php elseif ($user_role == 'st'): ?>
    
    <li class="list">
        <a href="mer_st.php" class="nav-link">
            <i class='bx bx-calendar-event icon' ></i>
            <span class="link">Мои мероприятия</span>
        </a>
    </li>
     
    <li class="list">
        <a href="ocenka_mer_st.php" class="nav-link">
            <i class='bx bx-trophy icon' ></i>
            <span class="link">Оценка волонтеров</span>
        </a>
    </li>
    <li class="list">
        <a href="rating_students.php" class="nav-link">
            <i class='bx bx-star icon'></i>
            <span class="link">Рейтинг студентов</span>
        </a>
    </li>
    <li class="list">
    <a href="profile.php" class="nav-link">
        <i class='bx bx-edit icon'></i>
        <span class="link">Редактировать профиль</span>
    </a>
</li>

  
<?php endif; ?>

            </ul>
            <div class="bottom-cotent">
            <li class="list">
            <a href="index1.php?logout=1" class="nav-link">
                    <i class='bx bxs-user-x icon' ></i>
                    <span class="link">Выход</span>
                    </a>
                </li>
            </div>
        </div>
    </div>
    
    </nav>
    <div class="overlay"></div>
    <script>
        const navBar = document.querySelector("nav"),
        menuBtns = document.querySelectorAll(".menu-icon"),
        overlay = document.querySelector(".overlay");
        menuBtns.forEach(menuBtn => {
            menuBtn.addEventListener("click", () => {
                navBar.classList.toggle("open");
            });
        });
        overlay.addEventListener("click", () => {
            navBar.classList.toggle("open");
        });
    </script>
     
</body>
</html>
