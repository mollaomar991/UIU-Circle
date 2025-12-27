<?php
$pageTitle = 'Alumni Management';
require_once __DIR__ . '/../includes/admin_header.php';

$db = getDB();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_user'])) {
        $userId = (int)$_POST['user_id'];
        $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            redirectWithMessage(SITE_URL . '/admin/users.php', 'User approved successfully!', 'success');
        }
    } elseif (isset($_POST['block_user'])) {
        $userId = (int)$_POST['user_id'];
        $stmt = $db->prepare("UPDATE users SET status = 'blocked' WHERE id = ?");
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            redirectWithMessage(SITE_URL . '/admin/users.php', 'User blocked successfully!', 'success');
        }
    } elseif (isset($_POST['delete_user'])) {
        $userId = (int)$_POST['user_id'];
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            redirectWithMessage(SITE_URL . '/admin/users.php', 'User deleted successfully!', 'success');
        }
    }
}

// Get filters
$statusFilter = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';

$conditions = ["1=1"];
$params = [];
$types = '';

if (!empty($statusFilter)) {
    $conditions[] = "status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if (!empty($searchQuery)) {
    $conditions[] = "(name LIKE ? OR email LIKE ? OR batch LIKE ?)";
    $searchParam = "%$searchQuery%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

$whereClause = implode(' AND ', $conditions);
$query = "SELECT * FROM users WHERE $whereClause ORDER BY created_at DESC";

$stmt = $db->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fas fa-users me-2 text-primary"></i>Alumni Management
        </h4>
    </div>
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" 
                           value="<?php echo htmlspecialchars($searchQuery); ?>" 
                           placeholder="Search by name, email, batch...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="blocked" <?php echo $statusFilter === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="<?php echo SITE_URL; ?>/admin/users.php" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Batch</th>
                            <th>Department</th>
                            <th>ID Card</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users->num_rows > 0): ?>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo SITE_URL . '/uploads/' . $user['profile_image']; ?>" 
                                                 class="rounded-circle me-2" 
                                                 style="width: 30px; height: 30px; object-fit: cover;"
                                                 alt="<?php echo htmlspecialchars($user['name']); ?>">
                                            <?php echo htmlspecialchars($user['name']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['batch']); ?></td>
                                    <td><?php echo htmlspecialchars($user['department']); ?></td>
                                    <td>
                                        <?php if (!empty($user['id_card_image'])): ?>
                                            <a href="<?php echo SITE_URL . '/uploads/' . $user['id_card_image']; ?>" 
                                               target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-id-card me-1"></i>View ID
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Not uploaded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $user['status'] === 'active' ? 'success' : 
                                                ($user['status'] === 'pending' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($user['status'] === 'pending'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" name="approve_user" class="btn btn-success btn-sm" 
                                                            onclick="return confirm('Approve this user?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($user['status'] !== 'blocked'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" name="block_user" class="btn btn-warning btn-sm" 
                                                            onclick="return confirm('Block this user?')">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Delete this user permanently?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                        No users found
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
