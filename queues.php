<?php
include 'db.php'; // Include your database connection file

$username = $_GET['user'] ?? '';
if (!$username) {
    echo "No user specified. <a href='register.html'>Register</a>";
    exit();
}

// Fetch queues from the database
$sql = "SELECT * FROM queues";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Store the queues in an associative array
    $queues = [];
    while ($row = $result->fetch_assoc()) {
        $queues[] = $row;
    }
} else {
    $queues = []; // No queues in the database
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Queue Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Welcome, <?= htmlspecialchars($username) ?>!</h1>

        <!-- Create Queue Button -->
        <div class="mb-8">
            <a href="create_queue.php?user=<?= urlencode($username) ?>"
                class="bg-blue-500 text-white p-3 rounded-lg shadow hover:bg-blue-600 transition duration-300">
                Create New Queue
            </a>
        </div>

        <!-- Search Box -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Search Queues:</h2>
            <input type="text" id="queueSearch" placeholder="Type to search..."
                class="border border-gray-300 p-3 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Queue List -->
        <div>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Available Queues:</h2>
            <div id="queueList" class="space-y-6">
                <?php foreach ($queues as $data): ?>
                    <div class="bg-gray-100 border border-gray-300 p-6 rounded-lg shadow">
                        <p class="font-bold text-lg text-gray-800">
                            <?= htmlspecialchars($data['name']) ?>
                            <span class="font-normal text-gray-600">(Owner: <?= htmlspecialchars($data['owner']) ?>)</span>
                        </p>
                        <p class="text-gray-700 mb-4">Location: <?= htmlspecialchars($data['location']) ?></p>
                        <p class="text-gray-700 mb-4">Timings: <?= htmlspecialchars($data['timings']) ?></p>
                        <p class="text-gray-700 mb-4">Description: <?= htmlspecialchars($data['description']) ?></p>
                        <p class="text-gray-700 mb-4">Total in queue: <?= !empty($data['members']) ? count(explode(',', $data['members'])) : 0 ?></p>

                        <ul class="list-disc pl-5 text-gray-700 mb-4">
                            <?php
                            // Split the members and limit to 5 for display
                            $members = explode(',', $data['members']);
                            foreach (array_slice($members, 0, 5) as $i => $person): ?>
                                <li>#<?= $i + 1 ?> - <?= htmlspecialchars($person) ?></li>
                            <?php endforeach; ?>
                            <?php if (count($members) > 5): ?>
                                <li>...and <?= count($members) - 5 ?> more</li>
                            <?php endif; ?>
                        </ul>

                        <!-- Join / Leave / Next Buttons -->
                        <form action="queue_action.php" method="POST" class="mt-4 space-x-4 inline">
                            <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
                            <input type="hidden" name="queue" value="<?= htmlspecialchars($data['name']) ?>">

                            <button type="submit" name="action" value="join"
                                class="bg-green-500 text-white p-3 rounded-lg shadow hover:bg-green-600 transition duration-300">
                                Join
                            </button>
                            <button type="submit" name="action" value="leave"
                                class="bg-red-500 text-white p-3 rounded-lg shadow hover:bg-red-600 transition duration-300">
                                Leave
                            </button>

                            <?php if ($data['owner'] === $username): ?>
                                <button type="submit" name="action" value="next"
                                    class="bg-yellow-500 text-white p-3 rounded-lg shadow hover:bg-yellow-600 transition duration-300">
                                    Next (Remove First)
                                </button>
                                <?php if ($data['owner'] === $username): ?>
    <form action="queue_action.php" method="POST" class="mt-4 inline">
        <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
        <input type="hidden" name="queue" value="<?= htmlspecialchars($data['name']) ?>">
        <button type="submit" name="action" value="delete"
            class="bg-red-500 text-white p-3 rounded-lg shadow hover:bg-red-600 transition duration-300">
            Delete Queue
        </button>
    </form>
<?php endif; ?>

                            <?php endif; ?>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        // Simple client-side search
        document.getElementById('queueSearch').addEventListener('input', function () {
            const filter = this.value.toLowerCase();
            document.querySelectorAll('#queueList > div').forEach(div => {
                const text = div.textContent.toLowerCase();
                div.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
