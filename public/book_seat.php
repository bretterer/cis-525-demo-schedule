<?php

require __DIR__ . '/app.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];

    $email = $_POST['email'];
    $umid = $_POST['umid'];
    $phoneNumber = $_POST['phoneNumber'];
    $projectTitle = $_POST['project'];
    $timeslot = $_POST['timeslot']; // Note: 'country' is used for timeslot in the form

    $_SESSION['old_data'] = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'umid' => $umid,
        'phone_number' => $phoneNumber,
        'project_title' => $projectTitle,
        'timeslot' => $timeslot
    ];

    if (!validateSubmission($_POST, $db)) {
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

    $stmt = $db->prepare("INSERT INTO bookings (first_name, last_name, email, phone_number, umid, project_title, timeslot) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $firstName, $lastName, $email, $phoneNumber, $umid, $projectTitle, $timeslot);




    try {
        $stmt->execute();
        // store saved booking in session
        $_SESSION['booking'] = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'umid' => $umid,
            'phone_number' => $phoneNumber,
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

    //The first and last name fields cannot be empty and consist of alpha letters only
    if (empty($data['firstName'])) {
        $errors['firstName'] = 'First name is required';
    }

    if (!empty($data['firstName']) && !preg_match("/^[a-zA-Z]*$/", $data['firstName'])) {
        $errors['firstName'] = 'First name must consist of alpha letters only';
    }

    if (empty($data['lastName'])) {
        $errors['lastName'] = 'Last name is required';
    }

    if (!empty($data['lastName']) && !preg_match("/^[a-zA-Z]*$/", $data['lastName'])) {
        $errors['lastName'] = 'Last name must consist of alpha letters only';
    }

    // validate that email is not empty and that it is a valid email
    if (empty($data['email'])) {
        $errors['email'] = 'Email is required';
    }

    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    // UMID must be exactly 8 digits
    if (empty($data['umid'])) {
        $errors['umid'] = 'UMID is required';
    }

    if (!empty($data['umid']) && !preg_match("/^[0-9]{8}$/", $data['umid'])) {
        $errors['umid'] = 'UMID must be exactly 8 digits';
    }

    // validate that phone number is a valid phone number: valid format is 123-456-7890
    if (empty($data['phoneNumber'])) {
        $errors['phoneNumber'] = 'Phone number is required';
    }

    if (!empty($data['phoneNumber']) && !preg_match("/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/", $data['phoneNumber'])) {
        $errors['phoneNumber'] = 'Invalid phone number format, valid format is 123-456-7890';
    }

    if (empty($data['project'])) {
        $errors['project'] = 'Project title is required';
    }

    if (empty($data['timeslot']) || $data['timeslot'] == '') {
        $errors['timeslot'] = 'Timeslot is required';
    }

    if (!empty($data['timeslot']) && !is_numeric($data['timeslot'])) {
        $errors['timeslot'] = 'Invalid timeslot';
    }

    if (!empty($data['timeslot'])) {
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
    }


    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        return false;
    }

    return true;
}
