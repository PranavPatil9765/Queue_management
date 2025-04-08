<?php
$username = $_POST['username'] ?? '';
$action = $_POST['action'] ?? '';
$queueName = $_POST['queue'] ?? '';
$newQueue = $_POST['new_queue'] ?? '';

$jsonFile = 'data/queues.json';
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode([]));
}
$queues = json_decode(file_get_contents($jsonFile), true);

// Connect to MySQL
$mysqli = new mysqli("localhost", "root", "", "queue_db");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($action === 'create') {
    if (!isset($queues[$newQueue])) {
        // JSON
        $queues[$newQueue] = ['owner' => $username, 'members' => []];

        // SQL
        $stmt = $mysqli->prepare("INSERT INTO queues (name, owner) VALUES (?, ?)");
        $stmt->bind_param("ss", $newQueue, $username);
        $stmt->execute();
        $stmt->close();
    }

} elseif ($action === 'join') {
    if (!in_array($username, $queues[$queueName]['members'])) {
        // JSON
        $queues[$queueName]['members'][] = $username;

        // SQL
        $stmt = $mysqli->prepare("INSERT INTO queue_members (queue_name, member_name) VALUES (?, ?)");
        $stmt->bind_param("ss", $queueName, $username);
        $stmt->execute();
        $stmt->close();
    }

} elseif ($action === 'leave') {
    if (($key = array_search($username, $queues[$queueName]['members'])) !== false) {
        // JSON
        unset($queues[$queueName]['members'][$key]);
        $queues[$queueName]['members'] = array_values($queues[$queueName]['members']);

        // SQL
        $stmt = $mysqli->prepare("DELETE FROM queue_members WHERE queue_name = ? AND member_name = ?");
        $stmt->bind_param("ss", $queueName, $username);
        $stmt->execute();
        $stmt->close();
    }

} elseif ($action === 'next') {
    if ($queues[$queueName]['owner'] === $username && count($queues[$queueName]['members']) > 0) {
        $removed = array_shift($queues[$queueName]['members']);

        // SQL
        $stmt = $mysqli->prepare("DELETE FROM queue_members WHERE queue_name = ? AND member_name = ? LIMIT 1");
        $stmt->bind_param("ss", $queueName, $removed);
        $stmt->execute();
        $stmt->close();
    }
}

// Save JSON
file_put_contents($jsonFile, json_encode($queues));

// Redirect back
header("Location: queues.php?user=" . urlencode($username));
exit();
