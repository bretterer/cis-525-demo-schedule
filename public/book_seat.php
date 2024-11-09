<?php

require __DIR__ . '/app.php';


$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$email = $_POST['email'];
$umid = $_POST['umid'];
$project = $_POST['project'];


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];

    $email = $_POST['email'];
    $umid = $_POST['umid'];
    $projectTitle = $_POST['project'];
    $timeslot = $_POST['timeslot']; // Note: 'country' is used for timeslot in the form

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
        // redirect back to index page with session data
        header('Location: /');

    } catch (Exception $e) {
        // store error in session
        $_SESSION['error'] = [
            'text' => 'Failed to book seat',
            'message' => $e->getMessage(),
            'e' => $e,
        ];

        $_SESSION['old_data'] = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'umid' => $umid,
            'project_title' => $projectTitle,
            'timeslot' => $timeslot
        ];

        // redirect back to index page with session data
        header('Location: /');
    }

    $stmt->close();
    $conn->close();
}