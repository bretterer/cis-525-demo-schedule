<?php

use Carbon\Carbon;

require __DIR__ . '/app.php';

$sql = "SELECT
            b.id,
            b.first_name,
            b.last_name,
            b.email,
            b.umid,
            b.project_title,
            a.start_time,
            a.end_time
        FROM
            bookings b
        JOIN
            availability a ON b.timeslot = a.id";

$stmt = $db->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

$stmt->close();

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Scheduling System</title>

    <link rel="stylesheet" href="css/app.css">
</head>

<body>
    <div class="container">
        <div class="box">
            <div class="box-header">
                <h1 class="text-center">Welcome to Demo Scheduling System</h1>
                <p>View Student Bookings</p>

            </div>
            <div class="box-body">

                <table class="table">
                    <thead>
                        <tr>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Email</th>
                            <th>UMID</th>
                            <th>Project Title</th>
                            <th>Timeslot</th>
                        </tr>
                    </thead>
                    <tbody>

                            <?php foreach ($bookings as $booking) : ?>
                                <tr>
                                    <td><?php echo $booking['first_name']; ?></td>
                                    <td><?php echo $booking['last_name']; ?></td>
                                    <td><?php echo $booking['email']; ?></td>
                                    <td><?php echo $booking['umid']; ?></td>
                                    <td><?php echo $booking['project_title']; ?></td>
                                    <td><?php echo Carbon::parse($booking['start_time'])->format('F j, Y g:i A'); ?> ( <?php echo Carbon::parse($booking['start_time'])->diffInMinutes(Carbon::parse($booking['end_time'])); ?> minutes )</td>
                                </tr>
                            <?php endforeach; ?>

                    </tbody>
                </table>



            </div>
            <div class="button-container margin-15">
                <a href="/" class="button-link">Back to Booking</a>
            </div>
        </div>
    </div>


</body>

</html>

<?

