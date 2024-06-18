<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список мероприятий</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 20px;
        }

        .modal {
    display: none;
    position: fixed;
    z-index: 3; /* Устанавливаем более высокий уровень z-index */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}
.modal-content {
    background-color: #ffffff;
    margin: 15% auto;
    padding: 20px;
    border: none; /* Убираем границу */
    border-radius: 10px; /* Добавляем скругления краёв */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Добавляем тень */
    width: 80%;
    max-width: 600px;
}
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

        /* Стили для таблицы */
        .events-table-container {
            max-height: 400px; /* Максимальная высота контейнера таблицы */
            overflow-y: auto; /* Включаем вертикальную прокрутку */
            margin: 20px auto; /* Центрируем контейнер таблицы */
            width: 90%; /* Ширина контейнера */
            max-width: 1200px; /* Максимальная ширина контейнера */
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #ffffff;
        }
        table {
            width: 100%; /* Ширина таблицы */
            border-collapse: collapse; /* Схлопываем границы ячеек */
            background-color: #ffffff;
            table-layout: fixed; /* Фиксируем ширину столбцов */
        }
        th, td {
            padding: 12px; /* Внутренний отступ */
            text-align: left; /* Выравнивание текста */
            border-bottom: 1px solid #ddd; /* Горизонтальная линия разделителя */
            word-wrap: break-word; /* Перенос слов */
        }
        th {
            background-color: #007bff;
            color: #fff;
            position: sticky; /* "Закрепляем" заголовки */
            top: 0; /* Верхняя граница относительно родительского контейнера */
            z-index: 2;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #d8d8d8;
        }
        button[type='submit'], button.edit-button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button[type='submit']:hover, button.edit-button:hover {
            background-color: #0056b3;
        }

        /* Стили для элемента поиска */
        #searchInput {
            display: block;
            width: 80%;
            max-width: 600px;
            margin: 20px auto;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f0f8ff;
        }
        #searchInput:focus {
            outline: none;
            border-color: #007bff;
        }
        .hidden {
    display: none;
}
        /* Стили для кнопки удаления */
        .delete-button-container {
            text-align: center; /* Выравнивание по центру */
            margin-top: 20px; /* Отступ сверху */
        }
        /* Стили для формы в модальном окне */
        .modal-content form input[type="text"],
        .modal-content form input[type="date"],
        .modal-content form input[type="time"],
        .modal-content form input[type="number"],
        .modal-content form textarea,
        .modal-content form select {
            width: 100%; /* Делаем элемент на всю ширину */
            padding: 10px; /* Добавляем отступ */
            margin-bottom: 10px; /* Отступ снизу */
            border: 1px solid #ddd; /* Граница */
            border-radius: 5px; /* Скругленные углы */
            box-sizing: border-box; /* Учитываем padding и border в общей ширине */
        }
        .modal-content form button[type="submit"] {
            width: 100%; /* Делаем кнопку на всю ширину */
            padding: 12px; /* Добавляем отступ */
            background-color: #007bff; /* Цвет фона */
            color: white; /* Цвет текста */
            border: none; /* Убираем границу */
            border-radius: 5px; /* Скругленные углы */
            cursor: pointer; /* Курсор в виде руки */
        }
        .modal-content form button[type="submit"]:hover {
            background-color: #0056b3; /* Цвет фона при наведении */
        }
    </style>
</head>
<body>
<?php include 'admin_profile.php'; ?>
<?php

require_once 'vendor/connect.php';

// Получение списка студентов
$sql = "SELECT * FROM users WHERE role = 'st'";
$students_result = $connect->query($sql);
$students = array();
if ($students_result->num_rows > 0) {
    while ($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Обработка запроса на редактирование мероприятия
if (isset($_POST['edit_event'])) {
    // код обновления данных мероприятия
}

// Обработка запроса на удаление мероприятий
if (isset($_POST['delete_events'])) {
    // Получаем выбранные для удаления мероприятия
    $deleteIds = $_POST['delete_ids'];
    // Преобразуем массив ID в строку для SQL-запроса
    $deleteIdsStr = implode(',', $deleteIds);
    // Запрос на удаление выбранных мероприятий из базы данных
    $deleteSql = "DELETE FROM events WHERE id IN ($deleteIdsStr)";
    if ($connect->query($deleteSql) === TRUE) {
        $_SESSION['message'] = "Выбранные мероприятия успешно удалены.";
    } else {
        $_SESSION['message'] = "Ошибка при удалении мероприятий: " . $connect->error;
    }
}

// Запрос на выборку мероприятий из базы данных
$sql = "SELECT events.*, users.full_name AS responsible_name FROM events 
        LEFT JOIN users ON events.responsible_id = users.id 
        ORDER BY event_date";
$result = $connect->query($sql);

// Проверка наличия результатов запроса
if ($result->num_rows > 0) {
    // Вывод формы с таблицей мероприятий
    echo "<input type='text' id='searchInput' name='search_query' placeholder='Поиск...'>"; // Поле поиска
    
    echo "<div class='events-table-container'>";
    
    echo "<table border='1'>
            <thead>
                <tr>
                    <th>Выбрать</th>
                    <th>Название</th>
                    <th>Дата мероприятия</th>
                    <th>Местоположение</th>
                    <th>Ответственный</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>";

    // Вывод данных о мероприятиях
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        // Добавляем чекбокс для выбора мероприятия для удаления
        echo "<td><input type='checkbox' name='delete_ids[]' value='" . $row['id'] . "'></td>";
        echo "<td>" . $row["title"] . "</td>";
        echo "<td>" . $row["event_date"] . "</td>";
        echo "<td>" . $row["location"] . "</td>";
        echo "<td>" . $row["responsible_name"] . "</td>";
        // Добавляем кнопку редактирования с вызовом модального окна
        echo "<td><button type='button' class='edit-button' data-id='" . $row['id'] . "'>Редактировать</button></td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    echo "</div>"; // Закрываем контейнер таблицы

    // Кнопка удаления выбранных мероприятий
    echo "<div class='delete-button-container'>
            <button type='submit' name='delete_events'>Удалить выбранные</button>
        </div>";
} else {
    echo "Нет доступных мероприятий.";
}

// Закрытие соединения с базой данных
$connect->close();
?>
<!-- Модальное окно для редактирования мероприятия -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Редактирование мероприятия</h2>
        <form id="editEventForm" method="post" action="vendor/process_edit_event.php">
            <input type="hidden" id="editEventId" name="editEventId">
            <label for="editTitle">Название мероприятия:</label><br>
            <input type="text" id="editTitle" name="editTitle" required><br><br>
            
            <label for="editLocation">Местоположение:</label><br>
            <input type="text" id="editLocation" name="editLocation" required><br><br>
            
            <label for="editEventDate">Дата мероприятия:</label><br>
            <input type="date" id="editEventDate" name="editEventDate" required><br><br>
            
            <label for="editStartTime">Начальное время:</label><br>
            <input type="time" id="editStartTime" name="editStartTime" required><br><br>
            
            <label for="editEndTime">Конечное время:</label><br>
            <input type="time" id="editEndTime" name="editEndTime" required><br><br>
            
            <label for="editPoints">Баллы:</label><br>
            <input type="number" id="editPoints" name="editPoints" required><br><br>
            
            <label for="editVolunteersNeeded">Количество волонтеров:</label><br>
            <input type="number" id="editVolunteersNeeded" name="editVolunteersNeeded" required><br><br>
            
            <label for="editDescription">Описание мероприятия:</label><br>
            <textarea id="editDescription" name="editDescription" rows="5" cols="40" required></textarea><br><br>
            
            <label for="editVolunteerTasks">Задачи волонтеров:</label><br>
            <textarea id="editVolunteerTasks" name="editVolunteerTasks" rows="2" cols="40" required></textarea><br><br>
            
            <label for="editVolunteerRequirements">Требования к волонтерам:</label><br>
            <textarea id="editVolunteerRequirements" name="editVolunteerRequirements" rows="2" cols="40" required></textarea><br><br>
            <label for="editResponsible">Ответственный (ФИО):</label><br>
            <select id="editResponsible" name="editResponsible" required>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo $student['id']; ?>"><?php echo $student['full_name']; ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <button type="submit">Сохранить изменения</button>
        </form>
    </div>
</div>

<script>
    // JavaScript для управления модальным окном редактирования
    function openEditModal(eventId) {
        // Получаем данные о мероприятии из базы данных и заполняем форму редактирования
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var data = JSON.parse(xhr.responseText);
                document.getElementById("editEventId").value = data.id;
                document.getElementById("editTitle").value = data.title;
                document.getElementById("editEventDate").value = data.event_date;
                document.getElementById("editLocation").value = data.location;
                document.getElementById("editStartTime").value = data.start_time;
                document.getElementById("editEndTime").value = data.end_time;
                document.getElementById("editPoints").value = data.points;
                document.getElementById("editVolunteersNeeded").value = data.volunteers_needed;
                document.getElementById("editDescription").value = data.description;
                document.getElementById("editVolunteerTasks").value = data.volunteer_tasks;
                document.getElementById("editVolunteerRequirements").value = data.volunteer_requirements;
                document.getElementById("editResponsible").value = data.responsible_id;

                // Очищаем список перед загрузкой
                var responsibleSelect = document.getElementById("editResponsible");
                responsibleSelect.innerHTML = "";

                // Заполняем список ответственных лиц
                loadResponsibles(data.responsible_id);

                // Показываем модальное окно
                document.getElementById('editModal').style.display = 'block';
            }
        };
        xhr.open("GET", "vendor/get_event_data.php?id=" + eventId, true);
        xhr.send();
    }

    // Функция для загрузки вариантов ответственных лиц
    function loadResponsibles(selectedResponsibleId) {
        var responsibleSelect = document.getElementById("editResponsible");

        // Очищаем список перед загрузкой
        responsibleSelect.innerHTML = "<option value=''>Выберите ответственного</option>";

        // Запрос на получение данных об ответственных лицах
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    var responsibles = JSON.parse(xhr.responseText);

                    // Добавляем варианты в выпадающий список
                    responsibles.forEach(function(responsible) {
                        var option = document.createElement("option");
                        option.text = responsible.full_name;
                        option.value = responsible.id;

                        // Устанавливаем выбранным вариантом ответственное лицо, если оно соответствует переданному ID
                        if (responsible.id == selectedResponsibleId) {
                            option.selected = true;
                        }

                        responsibleSelect.add(option);
                    });
                } else {
                    console.error("Ошибка загрузки данных об ответственных лицах: " + xhr.status);
                }
            }
        };
        xhr.open("GET", "vendor/get_responsibles.php", true);
        xhr.send();
    }

    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById('searchInput').addEventListener('input', function() {
            var searchText = this.value.toLowerCase();
            var rows = document.querySelectorAll('table tbody tr');
            rows.forEach(function(row) {
                var cells = row.querySelectorAll('td');
                var found = false;
                cells.forEach(function(cell) {
                    if (cell.textContent.toLowerCase().includes(searchText)) {
                        found = true;
                    }
                });
                if (found) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        });

        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', function() {
                var eventId = this.getAttribute('data-id');
                openEditModal(eventId);
            });
        });
    });

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
</script>
</body>
</html>
