<?php
$username = $_GET['user'] ?? '';
if (!$username) {
    echo "No user specified. <a href='register.html'>Register</a>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Queue</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <!-- Create Queue Form -->
<div class="mb-8">
    <h2 class="text-2xl font-semibold text-gray-700 mb-4">Create New Queue</h2>
    <form action="queue_action.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
        <input type="text" name="new_queue" placeholder="Queue name" required
            class="border border-gray-300 p-3 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="text" name="location" placeholder="Location (e.g., Room 101)" required
            class="border border-gray-300 p-3 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="text" name="timings" placeholder="Timings (e.g., 10AM - 1PM)" required
            class="border border-gray-300 p-3 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="text" name="description" placeholder="Description (optional)"
            class="border border-gray-300 p-3 rounded-lg col-span-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">

        <button type="submit" name="action" value="create"
            class="bg-blue-500 text-white p-3 rounded-lg shadow hover:bg-blue-600 transition duration-300 col-span-2">
            Create Queue
        </button>
    </form>
</div>

</body>
</html>
