<?php

require_once 'config.php';
$user_id = 1; // Default user for now

// Handle actions (Create, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add note
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        if ($title && $content) {
            $stmt = $conn->prepare("INSERT INTO notes (title, content, status, date_created, user_id) VALUES (?, ?, 'normal', NOW(), ?)");
            $stmt->execute([$title, $content, $user_id]);
        }
    }
    // Set as favorite
    if (isset($_POST['action']) && $_POST['action'] === 'favorite') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            $stmt = $conn->prepare("UPDATE notes SET status = 'Favorite' WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
        }
    }
    // Archive
    if (isset($_POST['action']) && $_POST['action'] === 'archive') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            $stmt = $conn->prepare("UPDATE notes SET status = 'Archived' WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
        }
    }
    // Delete
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
        }
    }
    // Redirect to prevent form resubmission
    header("Location: index.php" . (isset($_GET['filter']) ? "?filter=" . $_GET['filter'] : ""));
    exit;
}

// Get current filter
$filter = $_GET['filter'] ?? 'all';
$section_title = "All Notes";
$title_color = "#222";

if ($filter === 'favorite') {
    $section_title = "★ Favorites";
    $title_color = "#06b399";
} elseif ($filter === 'archived') {
    $section_title = "🗄️ Archives";
    $title_color = "#ff9800";
}

// Fetch notes based on filter
$sql = "SELECT * FROM notes WHERE user_id = ?";
if ($filter === 'favorite') {
    $sql .= " AND status = 'Favorite'";
} elseif ($filter === 'archived') {
    $sql .= " AND status = 'Archived'";
}
$sql .= " ORDER BY date_created DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoteIt_Admin</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="admin-body">
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">Note<span>It</span><span class="exclamation">!</span></div>
            <div class="nav-admin">
                <a href="index.php" class="nav-item <?php echo $filter === 'all' ? 'active' : ''; ?>">
                    <span class="solar--notes-broken"></span>
                    All Notes
                </a>
                <a href="index.php?filter=favorite" class="nav-item <?php echo $filter === 'favorite' ? 'active' : ''; ?>">
                    <span class="material-symbols--favorite-outline"></span>
                    Favorites
                </a>
                <a href="index.php?filter=archived" class="nav-item <?php echo $filter === 'archived' ? 'active' : ''; ?>">
                    <span class="vaadin--archives"></span>
                    Archives
                </a>
                <a href="login.html" class="nav-item">
                    <span class="hugeicons--logout-04"></span>
                    Logout
                </a>
            </div>
            <div class="user-info">
                <div class="user-text">
                    <p>Hi Jhonard!<br><span>Welcome back.</span></p>
                </div>
            </div>
            <div class="user-avatar"></div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="headerA">
                <div class="title" id="sectionTitle" style="color: <?php echo $title_color; ?>"><?php echo $section_title; ?></div>
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Search">
                    <!-- Add Note Form -->
                    <div class="add-note-form">
                        <form method="POST" action="index.php<?php echo $filter !== 'all' ? '?filter=' . $filter : ''; ?>">
                            <input type="hidden" name="action" value="add">
                            <input type="text" name="title" placeholder="Title" required>
                            <textarea name="content" placeholder="Content" required></textarea>
                            <button type="submit" class="add-note-btn">Add Note</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="notes-grid">
                <?php foreach ($notes as $note): ?>
                <div class="note-card">
                    <div class="note-title">
                        <?php echo htmlspecialchars($note['title']); ?>
                        <div class="note-actions">
                            <form method="POST" style="display:inline" action="index.php<?php echo $filter !== 'all' ? '?filter=' . $filter : ''; ?>">
                                <input type="hidden" name="action" value="favorite">
                                <input type="hidden" name="id" value="<?php echo $note['id']; ?>">
                                <button type="submit" class="action-btn favorite-btn" title="Add to Favorites">★</button>
                            </form>
                            
                            <form method="POST" style="display:inline" action="index.php<?php echo $filter !== 'all' ? '?filter=' . $filter : ''; ?>">
                                <input type="hidden" name="action" value="archive">
                                <input type="hidden" name="id" value="<?php echo $note['id']; ?>">
                                <button type="submit" class="action-btn archive-btn" title="Archive">🗄️</button>
                            </form>
                            
                            <form method="POST" style="display:inline" action="index.php<?php echo $filter !== 'all' ? '?filter=' . $filter : ''; ?>" onsubmit="return confirm('Are you sure you want to delete this note?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $note['id']; ?>">
                                <button type="submit" class="action-btn delete-btn" title="Delete">🗑️</button>
                            </form>
                        </div>
                    </div>
                    <div class="note-content">
                        <p><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                    </div>
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <span class="note-indicator"></span>
                        <div class="note-date"><?php echo date('M d, Y', strtotime($note['date_created'])); ?></div>
                    </div>
                    <div class="note-status">
                        <?php if ($note['status'] === 'Favorite'): ?>
                            <span class="status-badge favorite">★ Favorite</span>
                        <?php elseif ($note['status'] === 'Archived'): ?>
                            <span class="status-badge archived">🗄️ Archived</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($notes)): ?>
                <div class="no-notes">
                    <p>No notes found. Create your first note!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .note-actions {
            position: absolute;
            right: 10px;
            top: 10px;
        }
        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            margin-left: 5px;
            font-size: 16px;
        }
        .favorite-btn:hover { color: #06b399; }
        .archive-btn:hover { color: #ff9800; }
        .delete-btn:hover { color: #f44336; }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-top: 5px;
        }
        .status-badge.favorite { background: rgba(6, 179, 153, 0.1); color: #06b399; }
        .status-badge.archived { background: rgba(255, 152, 0, 0.1); color: #ff9800; }
        .add-note-form {
            margin: 20px 0;
        }
        .add-note-form input, .add-note-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .add-note-form button {
            padding: 8px 16px;
            background: #06b399;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</body>

</html>