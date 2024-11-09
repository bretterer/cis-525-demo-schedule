<?php

use Carbon\Carbon;

require __DIR__ . '/app.php';

$sql = "SELECT a.id, a.start_time, a.end_time, a.available_seats, (a.available_seats - COUNT(b.id)) AS remaining_seats
        FROM availability a
        LEFT JOIN bookings b ON a.id = b.timeslot
        WHERE a.start_time > NOW()
        GROUP BY a.id, a.start_time, a.end_time, a.available_seats
        HAVING remaining_seats > 0";

$stmt = $db->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$availabilities = [];
while ($row = $result->fetch_assoc()) {
    $availabilities[] = $row;
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
                <p>Register to demo your project below</p>
                <?php if (isset($_SESSION['error'])) : ?>
                    <div class="alert alert-danger">
                        <p><?php echo $_SESSION['error']['text']; ?></p>
                        <code><?php echo $_SESSION['error']['message']; ?></code>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['booking'])) : ?>
                    <div class="alert alert-success">
                        <p>Booking successful!</p>
                        <p>First Name: <?php echo $_SESSION['booking']['first_name']; ?></p>
                        <p>Last Name: <?php echo $_SESSION['booking']['last_name']; ?></p>
                        <p>Email: <?php echo $_SESSION['booking']['email']; ?></p>
                        <p>UMID: <?php echo $_SESSION['booking']['umid']; ?></p>
                        <p>Project Title: <?php echo $_SESSION['booking']['project_title']; ?></p>
                        <p>Timeslot: <?php echo Carbon::parse($availabilities[$_SESSION['booking']['timeslot'] - 1]['start_time'])->format('F j, Y - g:i a'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="box-body">
                <div class="form-container">
                    <form class="flex flex-grow flex-column" action="book_seat.php" method="POST">
                        <div class="flex-grow">
                            <div class="form-row">
                                <div>
                                    <label for="firstName">First Name:</label>
                                    <input type="text" id="firstName" name="firstName" <?php if (isset($_SESSION['old_data'])) echo 'value="' . $_SESSION['old_data']['first_name'] . '"'; ?>>
                                </div>
                                <div>
                                    <label for="lastName">Last Name:</label>
                                    <input type="text" id="lastName" name="lastName" <?php if (isset($_SESSION['old_data'])) echo 'value="' . $_SESSION['old_data']['last_name'] . '"'; ?>>
                                </div>
                            </div>

                            <div class="form-row">
                                <div>
                                    <label for="email">Email:</label>
                                    <input type="email" id="email" name="email" <?php if (isset($_SESSION['old_data'])) echo 'value="' . $_SESSION['old_data']['email'] . '"'; ?>>
                                </div>
                                <div>
                                    <label for="umid">UMID:</label>
                                    <input type="text" id="umid" name="umid" <?php if (isset($_SESSION['old_data'])) echo 'value="' . $_SESSION['old_data']['umid'] . '"'; ?>>
                                </div>
                            </div>

                            <div class="form-row full-width">
                                <div>
                                    <label for="project">Project Title:</label>
                                    <input type="text" id="project" name="project" <?php if (isset($_SESSION['old_data'])) echo 'value="' . $_SESSION['old_data']['project_title'] . '"'; ?>>
                                </div>
                            </div>


                            <div class="form-row full-width">
                                <div>
                                    <label for="timeslot">Timeslot:</label>
                                    <select id="timeslot" name="timeslot">
                                        <option value="">Select a Time</option>
                                        <?php
                                        foreach ($availabilities as $availability) {
                                            $start = Carbon::parse($availability['start_time'])->format('F j, Y - g:i a');
                                            $duration = Carbon::parse($availability['start_time'])->diffInMinutes(Carbon::parse($availability['end_time']));
                                            echo "<option value='{$availability['id']}' " .
                                                ($availability['remaining_seats'] == 0 ? 'disabled' : '') .
                                                (isset($_SESSION['old_data']['timeslot']) && $_SESSION['old_data']['timeslot'] == $availability['id'] ? 'selected' : '') .
                                                ">{$start} (Slot: {$duration} Minutes)</option>";
                                        }
                                        ?>

                                    </select>
                                </div>

                            </div>

                            <div class="form-row full-width">

                            <?php if (isset($_SESSION['error']) && $_SESSION['error']['e']->getCode() == 1062) : ?>
                                <div class="alert alert-danger">You have already booked a slot</div>
                                <div>
                                    <input type="checkbox" id="override" name="override">
                                    <label for="override">Replace your booking with this one</label>
                                </div>
                            <?php endif; ?>

                            </div>
                        </div>

                        <div class="button-container">
                            <button type="submit" class="button-link">Book Slot</button>
                        </div>
                    </form>
                </div>
                <div>
                    <div class="flex flex-grow flex-column">
                        <div class="flex-grow">
                            <?php
                            foreach ($availabilities as $availability):
                                $start = Carbon::parse($availability['start_time'])->format('M j, Y - g:i a');
                                $end = Carbon::parse($availability['end_time'])->format('g:i a');
                            ?>

                                <div class="slot">
                                    <div class="date-time"><?php echo $start; ?> – <?php echo $end; ?></div>
                                    <div class="seats <?php echo ($availability['remaining_seats'] == 0 ? 'full' : ''); ?>"><?php echo $availability['remaining_seats'] . ' ' . ($availability['remaining_seats'] == 1 ? 'seat' : 'seats') . ' remaining'; ?></div>
                                </div>
                            <?php
                            endforeach;
                            ?>

                        </div>

                        <div class="button-container">
                            <a href="bookings.php" class="button-link">View all Students Registered</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>

</html>

<?

// clear session
unset($_SESSION['error']);
unset($_SESSION['booking']);
unset($_SESSION['old_data']);
