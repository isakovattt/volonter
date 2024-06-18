<?php
// Проверяем, была ли сессия уже запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'vendor/connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/css/bootstrap.min.css'>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js'></script>
    <title>Новости</title>
    <style>
        .news-container {
            display: flex;
            flex-wrap: wrap;
        }
        .news-item {
            width: 300px;
            margin: 10px;
            border: 1px solid #ccc;
            padding: 10px;
            background-color: white;
            border-radius: 8px;
        }
        
        .news-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .news-date {
            font-size: 14px;
            color: #666;
        }
        .news-image {
            width: 100%; /* Ширина изображения будет 100% от родительского контейнера */
            height: 200px; /* Фиксированная высота изображения */
            object-fit: cover; /* Обрезаем изображение до подходящего размера */
            margin-bottom: 10px;
            border-radius: 8px;
        }
        .modal {
        display: none; /* По умолчанию скрыто */
        position: fixed; /* Фиксированное положение */
        z-index: 9999; /* Очень высокий индекс, чтобы быть поверх всего */
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto; /* Добавляет прокрутку, если контент слишком большой */
        background-color: rgba(0, 0, 0, 0.4); /* Черный полупрозрачный фон */
    }

    /* Стили для содержимого модального окна */
    .modal-content {
        background-color: #fefefe; /* Белый фон */
        margin: 15% auto; /* Центрирование по вертикали и горизонтали */
        padding: 20px;
        border: 1px solid #888;
        width: 80%; /* Ширина содержимого */
        max-width: 600px; /* Максимальная ширина содержимого */
        position: relative; /* Позиционирование относительно родительского элемента */
        border-radius: 8px; /* Скругление углов */
    }

    /* Стили для кнопки закрытия */
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
    #searchNews {
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

input[type=search]::-webkit-search-cancel-button {
    -webkit-appearance: none;
}

#searchNews:hover,
#searchNews:focus {
    background: #fff url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAAUCAYAAABvVQZ0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAT5JREFUeNqsVLtOw0AQtIMlRJHCEhUVMg398QEUSZnSfILzCXxDPsFu6XAJHWnTcS1lWsprKdmLxtKwvjVBYaTV7cm+udnX5fPb+yyBSmwhVmK/FfPZLyjUPhI8YtXYi23EOovs7PzyevAbsWeoGg5HNUHsCipX8F9TZDOstVgLPxIsxW6w3sHv6dJ2StkLbh6IPtR/AWRfSIET20H9D2U1hfaAgxY2KMagcBSmg9/rmwx0lBqTzGfHoVfVHxXgXzCjHNRHnnHke4vMGc2q0RBR0GSeCLlpLaJGFWKUszVuib32nih7iTFrjXAPyGnQ48c3Gu5AOVlMtMk6NZuf+FiC+AIhV0T+pBQ5ntXceIJKqKko2duJ2TwoLAz5QTVnagJaXWEO8y/wSMuKH9RTJoCTHyNZFidOUEfNu/8WYAAOXUT04MOtlwAAAABJRU5ErkJggg==) 14px 14px no-repeat;
        
    } 
    #searchNews:focus {
        width: 100%;
            cursor: text;
         border-radius: 5px;}
        
    
         .modal-body input[type="text"],
        .modal-body input[type="file"],
        .modal-body input[type="date"],
        .modal-body textarea {
            width: 100%; /* Делаем элемент на всю ширину */
            padding: 10px; /* Добавляем отступ */
            margin-bottom: 10px; /* Отступ снизу */
            border: 1px solid #ddd; /* Граница */
            border-radius: 5px; /* Скругленные углы */
            box-sizing: border-box;
        }
        .modal-footer button {
            width: 100%;
        }
    </style>
</head>
<body>
    
<?php include 'admin_profile.php'; ?>

<input type="text" type="search" id="searchNews" placeholder="Поиск по новостям...">

<div class="news-container">
    <?php
    // Запрос на выборку всех новостей из базы данных
    $sql = "SELECT id, title, date, image, content FROM news ORDER BY date DESC";

    $result = $connect->query($sql);

    // Проверка наличия результатов запроса
    if ($result->num_rows > 0) {
        // Вывод данных о новостях
        while($row = $result->fetch_assoc()) {
            echo "<div class='news-item'>";
            echo "<img src='" . $row["image"] . "' alt='News Image' class='news-image'>";

            echo "<h2 class='news-title'>" . $row["title"] . "</h2>";                
            echo "<p class='news-date'> " . $row["content"] . "</p>";
            echo "<p class='news-date'>" . $row["date"] . "</p>";
            
            // Проверяем роль пользователя
            if ($_SESSION['user']['role'] === 'ad') {
                // Если пользователь - администратор, отображаем кнопки "Редактировать" и "Удалить"
                echo "<div class='action-buttons' >";
                echo "<button class='btn btn-primary' onclick='editNews(" . $row["id"] . ")'>Редактировать</button>";
                echo "<button class='btn btn-primary' style='margin-left: 55px;' onclick='deleteNews(" . $row["id"] . ")'>Удалить</button>";
                echo "</div>";
            }
            
            echo "</div>";
        }
    } else {
        echo "Новостей нет.";
    }

    // Закрытие соединения с базой данных
    $connect->close();
    ?>
</div>
<div id="searchNotFound" style="display: none; color: red;">По вашему запросу ничего не найдено</div>

<!-- Модальное окно для редактирования новости -->
<div id="editModal" class="modal" tabindex="-1" style="display: none;">
<div class="modal-dialog">    
<div class="modal-content">
<div class="modal-header">
        <h5 class="modal-title">Редактирование новости</h5>
          <span class="close">&times;</span>  
    </div>

      <div class="modal-body">
        <form id="editForm">
                <input type="hidden" id="editId" name="editId">
                <label for="editTitle">Заголовок новости:</label><br>
                <input type="text" id="editTitle" name="editTitle" required><br><br>
                
                <label for="editImage">Фотография:</label><br>
                <input type="file" id="editImage" name="editImage" accept="image/*"><br><br>
                
                <label for="editDate">Дата новости:</label><br>
                <input type="date" id="editDate" name="editDate" required><br><br>
                
                <label for="editContent">Содержание новости:</label><br>
                <textarea id="editContent" name="editContent" rows="3" cols="40" required></textarea><br><br>
                </div>
                <div class="modal-footer">
                

                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        
    </div> 
    </div>
    </div>
    </div>


    <script>

document.getElementById('searchNews').addEventListener('input', function() {
    var searchTerm = this.value.toLowerCase();
    var newsItems = document.querySelectorAll('.news-item');
    var searchNotFound = document.getElementById('searchNotFound');
    var found = false; // Флаг для обозначения наличия результатов поиска

    newsItems.forEach(function(item) {
        var title = item.querySelector('.news-title').textContent.toLowerCase();
        var content = item.querySelector('.news-date').textContent.toLowerCase();

        if (title.includes(searchTerm) || content.includes(searchTerm)) {
            item.style.display = 'block';
            found = true; // Найдены результаты поиска
        } else {
            item.style.display = 'none';
        }
    });

    // Показываем или скрываем блок "По вашему запросу ничего не найдено"
    if (!found) {
        searchNotFound.style.display = 'block';
    } else {
        searchNotFound.style.display = 'none';
    }
});

        // JavaScript для управления модальным окном
        var modal = document.getElementById("editModal");
        var span = document.getElementsByClassName("close")[0];
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
        span.onclick = function() {
            modal.style.display = "none";
        }

        // JavaScript для отправки данных на сервер при сохранении
        document.getElementById("editForm").onsubmit = function(event) {
            event.preventDefault(); // Предотвращаем отправку формы по умолчанию
            var formData = new FormData(this);
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "vendor/process_edit_news.php", true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    console.log(xhr.responseText);
                    modal.style.display = "none";
                }
            };
            xhr.send(formData);
        };

        // JavaScript для обработки клика по кнопке "Редактировать"
function editNews(newsId) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var data = JSON.parse(xhr.responseText);
            document.getElementById("editId").value = data.id;
            document.getElementById("editTitle").value = data.title;
            document.getElementById("editDate").value = data.date;
            document.getElementById("editContent").value = data.content;
            modal.style.display = "block"; // Показываем модальное окно
        }
    };
    xhr.open("GET", "vendor/get_news_edit.php?id=" + newsId, true);
    xhr.send();
}


        function deleteNews(id) {
            if (confirm("Вы уверены, что хотите удалить эту новость?")) {
                window.location.href = "vendor/process_delete.php?id=" + id;
            }
        }
    </script>
</body>
</html>