<?php

require __DIR__ . '/app.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];

    $email = $_POST['email'];
    $umid = $_POST['umid'];
    $projectTitle = $_POST['project'];
    $timeslot = $_POST['timeslot']; // Note: 'country' is used for timeslot in the form

    $_SESSION['old_data'] = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'umid' => $umid,
        'project_title' => $projectTitle,
        'timeslot' => $timeslot
    ];

    if(!validateSubmission($_POST, $db)) {
        // var_dump($_SESSION);
        header('Location: /');
        return;
    }

    if (array_key_exists('override', $_POST) && $_POST['override'] == 'on') {
        $stmt = $db->prepare("DELETE FROM bookings WHERE umid = ?");
        $stmt->bind_param("s", $umid);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $db->prepare("INSERT INTO bookings (first_name, last_name, email, umid, project_title, timeslot) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $firstName, $lastName, $email, $umid, $projectTitle, $timeslot);




    try {
        $stmt->execute();
        // store saved booking in session
        $_SESSION['booking'] = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'umid' => $umid,
            'project_title' => $projectTitle,
            'timeslot' => $timeslot
        ];
        unset($_SESSION['error']);
        unset($_SESSION['errors']);
        unset($_SESSION['old_data']);
        // redirect back to index page with session data
        header('Location: /');

    } catch (Exception $e) {
        // store error in session
        $_SESSION['error'] = [
            'text' => 'Failed to book seat',
            'message' => $e->getMessage(),
            'e' => $e,
        ];



        // redirect back to index page with session data
        header('Location: /');
    }

    $stmt->close();
    $db->close();

}

function validateSubmission($data, $db)
{
    $errors = [];

    if (empty($data['firstName'])) {
        $errors['firstName'] = 'First name is required';
    }

    if (empty($data['lastName'])) {
        $errors['lastName'] = 'Last name is required';
    }

    if (empty($data['email'])) {
        $errors['email'] = 'Email is required';
    }

    //validate that its an email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    if (empty($data['umid'])) {
        $errors['umid'] = 'UMID is required';
    }

    if (empty($data['project'])) {
        $errors['project'] = 'Project title is required';
    }

    if (empty($data['timeslot']) || $data['timeslot'] == '') {
        $errors['timeslot'] = 'Timeslot is required';
    }
    // check to see if timeslot has at least 1 seat left, if not, add to errors
    $sql = "SELECT a.id, a.start_time, a.end_time, a.available_seats, (a.available_seats - COUNT(b.id)) AS remaining_seats
        FROM availability a
        LEFT JOIN bookings b ON a.id = b.timeslot
        WHERE a.id = ?
        GROUP BY a.id, a.start_time, a.end_time, a.available_seats";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $data['timeslot']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result['remaining_seats'] <= 0) {
        $errors['timeslot'] = 'Timeslot is full';
    }


    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        return false;
    }

    return true;
}