<?php
// MUHAMMAD AFIQ IRSYAD | 2024429922
include 'db_connect.php'; 

$search_query = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$category_filter = $_GET['category_filter'] ?? '';

$sql = "SELECT a.*, c.title AS category_title FROM Applications a LEFT JOIN Categories c ON a.category_id = c.id WHERE 1=1";
$params = [];
$types = "";

if (!empty($search_query)) {
    $sql .= " AND (a.title LIKE ? OR a.author LIKE ? OR a.review LIKE ?)";
    $params[] = "%" . $search_query . "%";
    $params[] = "%" . $search_query . "%";
    $params[] = "%" . $search_query . "%";
    $types .= "sss";
}

if (!empty($status_filter) && ($status_filter === 'active' || $status_filter === 'inactive')) {
    $sql .= " AND a.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($category_filter)) {
    $sql .= " AND a.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}

$sql .= " ORDER BY a.created DESC";


$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {

        call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $params));
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $applications = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $applications = [];
    echo "Error preparing statement: " . $conn->error;
}

$categories = [];
$cat_result = $conn->query("SELECT id, title FROM Categories ORDER BY title ASC");
if ($cat_result && $cat_result->num_rows > 0) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$conn->close();


function formatDateTime($datetime) {
    return date("F j, Y, g:i a", strtotime($datetime));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MobRevApp - Homepage</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <nav class="bg-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-white text-2xl font-bold"> Review Mobile App </a>
            <div class="space-x-4">
                <a href="index.php" class="text-white hover:text-blue-200"> Applications </a>
                <a href="categories.php" class="text-white hover:text-blue-200"> Categories </a>
                <a href="comments.php" class="text-white hover:text-blue-200"> Comments </a>
                <a href="create_application.php" class="bg-green-700 hover:bg-green-800 text-white font-bold py-2 px-4 rounded">Add New Review</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-4">
    <div style="display: flex; justify-content: center; align-items: center; height: 300px;">
  <img src="https://cdn3.iconfinder.com/data/icons/yellow-commerce/100/____-4-512.png" width="320" height="320">
</div>
        <br><h1 class="text-4xl font-bold mb-8 text-center text-gray-800">Review Mobile App</h1>

        <form action="index.php" method="GET" class="mb-8 p-6 bg-white shadow-md rounded-lg flex flex-wrap gap-4 items-end">
            <div class="flex-grow">
                <label for="search" class="block text-gray-700 text-sm font-bold mb-2">Search:</label>
                <input type="text" name="search" id="search" placeholder="Search by title, author, or review" value="<?php echo htmlspecialchars($search_query); ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="w-full sm:w-auto">
                <label for="status_filter" class="block text-gray-700 text-sm font-bold mb-2">Status :</label>
                <select name="status_filter" id="status_filter" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value=""> All Statuses </option>
                    <option value="active" <?php echo ($status_filter == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($status_filter == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="w-full sm:w-auto">
                <label for="category_filter" class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
                <select name="category_filter" id="category_filter" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value=""> All Categories </option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo ($category_filter == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="w-full sm:w-auto">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Apply Filter
                </button>
            </div>
            <div class="w-full sm:w-auto">
                <a href="index.php" class="bg-purple-700 hover:bg-purple-800 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full text-center">
                    Clear Filter
                </a>
            </div>
        </form>

        <?php if (empty($applications)): ?>
            <p class="text-center text-gray-600 text-xl mt-10">No application reviews found.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($applications as $app): ?>
                    <div class="bg-white shadow-md rounded-lg overflow-hidden flex flex-col h-full">
                        <?php if (!empty($app['image_dir']) && file_exists($app['image_dir'])): ?>
                            <img src="<?php echo htmlspecialchars($app['image_dir']); ?>" alt="<?php echo htmlspecialchars($app['title']); ?>" class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500">No Image Available</div>
                        <?php endif; ?>

                        <div class="p-6 flex flex-col flex-grow">
                            <h2 class="text-xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($app['title']); ?></h2>
                            <p class="text-gray-600 text-sm mb-1">By : <span class="font-medium"><?php echo htmlspecialchars($app['author']); ?></span></p>
                            <p class="text-gray-600 text-sm mb-2">Category : <span class="font-medium"><?php echo htmlspecialchars($app['category_title']); ?></span></p>

                            <p class="text-sm font-bold mb-2"> Status :
                                <?php if ($app['status'] == 'active'): ?>
                                    <span class="text-green-600">Active</span>
                                <?php else: ?>
                                    <span class="text-red-600">Inactive</span>
                                <?php endif; ?>
                            </p>

                            <p class="text-gray-700 text-base mb-4 flex-grow"><?php echo nl2br(htmlspecialchars(substr($app['review'], 0, 150))) . (strlen($app['review']) > 150 ? '...' : ''); ?></p>

                            <div class="text-xs text-gray-500 mt-auto">
                                <p>Created : <?php echo formatDateTime($app['created']); ?></p>
                                <p>Modified : <?php echo formatDateTime($app['modified']); ?></p>
                            </div>

                            <div class="mt-4 flex space-x-2">
                                <a href="view_application.php?id=<?php echo htmlspecialchars($app['id']); ?>" class="bg-red-500 hover:bg-red-600 text-white text-sm font-bold py-2 px-3 rounded">View Details</a>
                                <a href="edit_application.php?id=<?php echo htmlspecialchars($app['id']); ?>" class="bg-green-500 hover:bg-green-600 text-white text-sm font-bold py-2 px-3 rounded">Edit</a>
                                <a href="delete_application.php?id=<?php echo htmlspecialchars($app['id']); ?>" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-bold py-2 px-3 rounded" onclick="return confirm('Are you sure you want to delete this review?');">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>