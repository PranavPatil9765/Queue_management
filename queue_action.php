<?php
include 'db.php'; // Ensure this includes the database connection

$username = $_POST['username'] ?? '';
$action = $_POST['action'] ?? '';
$queueName = $_POST['queue'] ?? ''; // Use the 'queue' parameter here for actions like join/leave/next
$newQueue = $_POST['new_queue'] ?? ''; // New queue creation
$location = $_POST['location'] ?? '';
$timings = $_POST['timings'] ?? '';
$description = $_POST['description'] ?? '';

// Ensure the action is properly set
if ($action === 'delete') {
    // Ensure the queue belongs to the current user (owner)
    $queueName = $_POST['queue'] ?? '';

    // Fetch queue details
    $stmt = $conn->prepare("SELECT owner FROM queues WHERE name = ?");
    $stmt->bind_param("s", $queueName);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($owner);
    $stmt->fetch();

    if ($owner === $username) {
        // Delete the queue from the database
        $deleteStmt = $conn->prepare("DELETE FROM queues WHERE name = ?");
        $deleteStmt->bind_param("s", $queueName);
        $deleteStmt->execute();
        $deleteStmt->close();

        // Redirect to the queue management page after successful deletion
        header("Location: queues.php?user=" . urlencode($username));
        exit();
    } else {
        echo "You are not the owner of this queue. <a href='queues.php?user=" . urlencode($username) . "'>Go back</a>";
        exit();
    }
}

if ($action === 'create' && $newQueue) {
    // Check if queue already exists in DB
    $stmt = $conn->prepare("SELECT * FROM queues WHERE name = ?");
    $stmt->bind_param("s", $newQueue);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Insert new queue into the database
        $stmt = $conn->prepare("INSERT INTO queues (name, owner, location, timings, description, members) VALUES (?, ?, ?, ?, ?, ?)");
        $members = ''; // No members at the start
        $stmt->bind_param("ssssss", $newQueue, $username, $location, $timings, $description, $members);
        $stmt->execute();
        $stmt->close();

        header("Location: queues.php?user=" . urlencode($username));
        exit();
    } else {
        echo "Queue with this name already exists. <a href='queues.php?user=" . urlencode($username) . "'>Go back</a>";
        exit();
    }
} elseif ($action === 'join') {
    // Add user to queue
    $stmt = $conn->prepare("SELECT members FROM queues WHERE name = ?");
    $stmt->bind_param("s", $queueName);
    $stmt->execute();
    $result = $stmt->get_result();
    $queueData = $result->fetch_assoc();

    if ($queueData) {
        $members = $queueData['members'];
        if (!empty($members)) {
            $membersArray = explode(',', $members);
        } else {
            $membersArray = [];
        }

        // Prevent joining if already a member
        if (in_array($username, $membersArray)) {
            echo "You are already in the queue. <a href='queues.php?user=" . urlencode($username) . "'>Go back</a>";
            exit();
        }

        // Add user to the queue's members
        $membersArray[] = $username;
        $updatedMembers = implode(',', $membersArray);

        // Update the queue in the DB with the new member list
        $stmt = $conn->prepare("UPDATE queues SET members = ? WHERE name = ?");
        $stmt->bind_param("ss", $updatedMembers, $queueName);
        $stmt->execute();
        $stmt->close();

        header("Location: queues.php?user=" . urlencode($username));
        exit();
    } else {
        echo "Queue does not exist. <a href='queues.php?user=" . urlencode($username) . "'>Go back</a>";
        exit();
    }
} elseif ($action === 'leave') {
    // Remove user from queue
    $stmt = $conn->prepare("SELECT members FROM queues WHERE name = ?");
    $stmt->bind_param("s", $queueName);
    $stmt->execute();
    $result = $stmt->get_result();
    $queueData = $result->fetch_assoc();

    if ($queueData) {
        $members = $queueData['members'];
        if (!empty($members)) {
            $membersArray = explode(',', $members);
            $index = array_search($username, $membersArray);

            if ($index !== false) {
                // Remove the user from the queue
                unset($membersArray[$index]);
                $updatedMembers = implode(',', $membersArray);

                // Update the queue in the DB with the new member list
                $stmt = $conn->prepare("UPDATE queues SET members = ? WHERE name = ?");
                $stmt->bind_param("ss", $updatedMembers, $queueName);
                $stmt->execute();
                $stmt->close();

                header("Location: queues.php?user=" . urlencode($username));
                exit();
            } else {
                echo "You are not in the queue. <a href='queues.php?user=" . urlencode($username) . "'>Go back</a>";
                exit();
            }
        } else {
            echo "Queue is empty. <a href='queues.php?user=" . urlencode($username) . "'>Go back</a>";
            exit();
        }
    } else {
        echo "Queue does not exist. <a href='queues.php?user=" . urlencode($username) . "'>Go back</a>";
        exit();
    }
} elseif ($action === 'next') {
    // Remove the first member from the queue (if the user is the owner)
    $stmt = $conn->prepare("SELECT members FROM queues WHERE name = ? AND owner = ?");
    $stmt->bind_param("ss", $queueName, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $queueData = $result->fetch_assoc();

    if ($queueData) {
        $members = $queueData['members'];
        if (!empty($members)) {
            $membersArray = explode(',', $members);
            array_shift($membersArray); // Remove the first member

            $updatedMembers = implode(',', $membersArray);

            // Update the queue in the DB with the new member list
            $stmt = $conn->prepare("UPDATE queues SET members = ? WHERE name = ?");
            $stmt->bind_param("ss", $updatedMembers, $queueName);
            $stmt->execute();
            $stmt->close();

            header("Location: queues.php?user=" . urlencode($username));
            exit();
        } else {
            echo "No members in the queue. <a href='queues.php?user=" . urlencode($username) . "'>Go back</a>";
            exit();
        }
    } else {
        echo "Queue does not exist or you are not the owner. <a href='queues.php?user=" . urlencode($username) . "'>Go back</a>";
        exit();
    }
} else {
    echo "Invalid action. <a href='queues.php?user=" . urlencode($username) . "'>Go back</a>";
    exit();
}
?>
