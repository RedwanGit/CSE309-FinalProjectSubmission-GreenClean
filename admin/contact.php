<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isAdminLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Handle message deletion
if (isset($_POST['delete']) && isset($_POST['message_id'])) {
    $messageId = filter_var($_POST['message_id'], FILTER_VALIDATE_INT);
    if ($messageId) {
        try {
            $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
            $stmt->execute([$messageId]);
            $_SESSION['success_message'] = "Contact message deleted successfully.";
        } catch(PDOException $e) {
            error_log("Contact deletion error: " . $e->getMessage());
            $_SESSION['error_message'] = "Error deleting message.";
        }
        header("Location: contact.php");
        exit();
    }
}

// Fetch contact messages with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

try {
    // Get total count of messages
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM contacts");
    $totalMessages = $stmt->fetch()['total'];
    $totalPages = ceil($totalMessages / $perPage);
    
    // Get messages for current page with direct SQL query
    $messages = $pdo->query("SELECT * FROM contacts ORDER BY submission_date DESC")
        ->fetchAll(PDO::FETCH_ASSOC);
    
    // Add debug information
    if (empty($messages)) {
        error_log("Debug: No messages found in contacts table");
    } else {
        error_log("Debug: Found " . count($messages) . " messages");
    }
    
} catch(PDOException $e) {
    error_log("Contact page error: " . $e->getMessage());
    $messages = [];
    $totalPages = 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Messages - GreenClean Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-contact.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar Navigation -->
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h2>GreenClean Admin</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li class="active"><a href="contact.php">Contact Messages</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="admin-content">
            <div class="content-header">
                <h1>Contact Messages</h1>
                <p>Manage customer inquiries and messages</p>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message">
                    <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="error-message">
                    <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Debug Information -->
            <?php if (empty($messages)): ?>
                <div class="debug-info">
                    <p>Debug Info:</p>
                    <pre>
                    <?php
                    echo "Total Messages: " . $totalMessages . "\n";
                    echo "Current Page: " . $page . "\n";
                    echo "Messages Per Page: " . $perPage . "\n";
                    echo "Offset: " . $offset . "\n";
                    
                    // Test database connection
                    try {
                        $testQuery = $pdo->query("SELECT 1 FROM contacts LIMIT 1");
                        echo "Database connection test: Success\n";
                    } catch(PDOException $e) {
                        echo "Database connection test: Failed - " . $e->getMessage() . "\n";
                    }
                    ?>
                    </pre>
                </div>
            <?php endif; ?>

            <div class="contact-messages">
                <?php if (empty($messages)): ?>
                    <p class="no-messages">No contact messages found.</p>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="message-card">
                            <div class="message-header">
                                <div class="message-info">
                                    <h3><?php echo htmlspecialchars($message['name']); ?></h3>
                                    <p class="message-email"><?php echo htmlspecialchars($message['email']); ?></p>
                                    <p class="message-date"><?php echo date('F j, Y g:i a', strtotime($message['submission_date'])); ?></p>
                                </div>
                                <form method="POST" class="delete-form">
                                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                    <button type="button" class="delete-btn" onclick="showDeleteConfirm(<?php echo $message['id']; ?>, '<?php echo htmlspecialchars($message['name'], ENT_QUOTES); ?>')">Delete</button>
                                </form>
                            </div>
                            <div class="message-content">
                                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>" class="<?php echo $page === $i ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Custom Delete Confirmation Dialog -->
    <div class="confirm-dialog-overlay" id="deleteConfirmDialog">
        <div class="confirm-dialog">
            <h3>Delete Message</h3>
            <p>Are you sure you want to delete the message from <span id="messageAuthor"></span>? This action cannot be undone.</p>
            <div class="confirm-dialog-buttons">
                <button class="confirm-dialog-cancel" onclick="hideDeleteConfirm()">Cancel</button>
                <button class="confirm-dialog-confirm" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>

    <script src="../js/admin-contact.js" defer></script>
</body>
</html>