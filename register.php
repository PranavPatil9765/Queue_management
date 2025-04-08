<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);

    if (!file_exists('data/users.json')) {
        file_put_contents('data/users.json', json_encode([]));
    }

    $users = json_decode(file_get_contents('data/users.json'), true);
    if (!is_array($users)) $users = [];

    if (!in_array($username, $users)) {
        $users[] = $username;
        file_put_contents('data/users.json', json_encode($users));
    }

    // Redirect to queue list after registration
    header("Location: queues.php?user=" . urlencode($username));
    exit();
}
?>
