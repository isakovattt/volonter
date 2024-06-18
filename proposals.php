<?php
session_start();
require_once 'vendor/connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'us') {
    header('Location: /');
    exit();
}

$user_id = $_SESSION['user']['id'];
$current_date = date('Y-m-d');
$proposed_events_query = "
    SELECT pe.id as proposal_id, pe.status, e.id as event_id, e.title, e.photo, e.location, e.start_time, e.end_time, e.points, 
           e.volunteers_needed, e.description, e.volunteer_tasks, e.volunteer_requirements, e.event_date, 
           u.full_name as responsible_name 
    FROM proposed_events pe
    JOIN events e ON pe.event_id = e.id
    JOIN users u ON e.responsible_id = u.id
    WHERE pe.volunteer_id = '$user_id' AND e.event_date > '$current_date'
";

$result = $connect->query($proposed_events_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заявки на мероприятия</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
    <style>
      body {
        margin-top: 20px;
        background: #eee;
      }

      .card {
  border-radius: 10px; /* Регулируйте радиус скругления здесь */
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
    </style>
</head>
<body>
<?php include 'admin_profile.php'; ?>
<section class="container py-5">
    <h2 class="h4 block-title text-center mt-2">Мои предложения</h2>
    
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="card mb-3">
                    <div class="card-body d-flex align-items-center">
                    <a href="event_details.php?id=<?php echo $row['event_id']; ?>">
        <img class="img-md rounded-circle me-3" src="<?php echo $row['photo']; ?>" alt="<?php echo $row['title']; ?>" class="img-fluid mb-3">
        <div>
                            <h5 class="card-title"><?php echo $row['title']; ?></h5>
                            </a>
                            <p class="text-muted m-0"><i class="bx bx-map icon"></i> <?php echo $row['location']; ?></p>
                            
                            <p class="text-muted m-0"><i class="bx bx-calendar icon"></i> <?php echo $row['event_date']; ?></p>
                            <p class="text-muted m-0"><i class="bx bx-time icon"></i> <?php echo $row['start_time']; ?> - <?php echo $row['end_time']; ?></p>
                            <p class="text-muted m-0"><i class="bx bx-user icon"></i> <?php echo $row['responsible_name']; ?></p>
                            
 </div>
                            <div class="ms-auto">
                                <?php if ($row['status'] === 'ожидание') { ?>
                                    <a href="vendor/update_status.php?action=confirm&proposal_id=<?php echo $row['proposal_id']; ?>" class="btn btn-success btn-sm mx-2">Подтвердить участие</a>
                                    <a href="vendor/update_status.php?action=reject&proposal_id=<?php echo $row['proposal_id']; ?>" class="btn btn-danger btn-sm mx-2">Отклонить</a>
                                <?php } elseif ($row['status'] === 'принято') { ?>
                                    <span class="text-success mx-2">Участник</span>
                                    <a href="vendor/update_status.php?action=cancel&proposal_id=<?php echo $row['proposal_id']; ?>" class="btn btn-warning btn-sm mx-2">Отменить участие</a>
                                <?php } elseif ($row['status'] === 'отклонено') { ?>
                                    <span class="text-danger mx-2">Отказано</span>
                                    <a href="vendor/update_status.php?action=cancel&proposal_id=<?php echo $row['proposal_id']; ?>" class="btn btn-warning btn-sm mx-2">Отменить отказ</a>
                                <?php } ?>
                            </div>
                        
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p>У вас нет заявок на актуальные мероприятия.</p>";
        }
        ?>
   
</section>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
