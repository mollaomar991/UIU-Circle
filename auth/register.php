<?php
$pageTitle = 'Register';
require_once __DIR__ . '/../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/user/dashboard.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitizeInput($_POST['role'] ?? 'student'); // Default to student
    $department = sanitizeInput($_POST['department'] ?? '');
    $batch = sanitizeInput($_POST['batch'] ?? '');
    
    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($department)) {
        $errors[] = "Department is required";
    }
    
    // Batch is required only for Alumni
    if ($role === 'alumni' && empty($batch)) {
        $errors[] = "Batch is required for Alumni";
    }
    
    // File Upload Validation
    if (!isset($_FILES['id_card']) || $_FILES['id_card']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "ID Card image is required";
    }
    
    // Check if email exists
    if (empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email already registered";
        }
    }
    
    // Process Registration
    if (empty($errors)) {
        // Upload ID Card
        $uploadResult = uploadFile($_FILES['id_card'], ['jpg', 'jpeg', 'png'], 5 * 1024 * 1024); // 5MB max
        
        if ($uploadResult['success']) {
            $idCardImage = $uploadResult['filename'];
            $hashedPassword = hashPassword($password);
            
            // If student, batch might be empty or we can just leave it NULL.
            // If alumni, batch is set.
            
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role, department, batch, id_card_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $name, $email, $hashedPassword, $role, $department, $batch, $idCardImage);
            
            if ($stmt->execute()) {
                $success = true;
                redirectWithMessage(SITE_URL . '/auth/login.php', 'Registration successful! Please wait for admin approval.', 'success');
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        } else {
            $errors[] = "ID Card Upload Failed: " . $uploadResult['message'];
        }
    }
}

// Prepare batches data for JavaScript
$db = getDB();
$batchesResult = $db->query("SELECT DISTINCT batch_name, department FROM batches ORDER BY department, batch_name");
$batchesData = [];
$batchesResult->data_seek(0);
while ($row = $batchesResult->fetch_assoc()) {
    $batchesData[] = $row;
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold">Join UIU Alumni Connect</h3>
                        <p class="text-muted">Register as a Student or Alumni</p>
                    </div>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <!-- Role Selection -->
                        <div class="mb-4 text-center">
                            <label class="form-label d-block fw-bold mb-3">I am a:</label>
                            <div class="btn-group w-100" role="group" aria-label="Role selection">
                                <input type="radio" class="btn-check" name="role" id="role_student" value="student" 
                                    <?php echo (!isset($_POST['role']) || $_POST['role'] === 'student') ? 'checked' : ''; ?> 
                                    onchange="toggleFields()">
                                <label class="btn btn-outline-primary" for="role_student">
                                    <i class="fas fa-user-graduate me-2"></i>Current Student
                                </label>

                                <input type="radio" class="btn-check" name="role" id="role_alumni" value="alumni" 
                                    <?php echo (isset($_POST['role']) && $_POST['role'] === 'alumni') ? 'checked' : ''; ?> 
                                    onchange="toggleFields()">
                                <label class="btn btn-outline-primary" for="role_alumni">
                                    <i class="fas fa-graduation-cap me-2"></i>Alumni
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department" required onchange="filterBatches()">
                                    <option value="">Select Department</option>
                                    <?php
                                    $departments = [];
                                    foreach ($batchesData as $row) {
                                        if (!in_array($row['department'], $departments)) {
                                            $departments[] = $row['department'];
                                            $selected = (isset($_POST['department']) && $_POST['department'] === $row['department']) ? 'selected' : '';
                                            echo "<option value=\"" . htmlspecialchars($row['department']) . "\" $selected>" 
                                                 . htmlspecialchars($row['department']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6" id="batch_container" style="display: none;">
                                <label for="batch" class="form-label">Batch</label>
                                <select class="form-select" id="batch" name="batch">
                                    <option value="">Select Batch</option>
                                    <!-- Options will be populated by JS -->
                                </select>
                            </div>
                        </div>

                         <div class="mb-4">
                            <label for="id_card" class="form-label" id="id_card_label">Upload Student ID Card</label>
                            <input type="file" class="form-control" id="id_card" name="id_card" accept="image/*" required>
                            <div class="form-text">Please upload a clear image of your ID card for verification.</div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="text-muted">Min 6 chars</small>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0">Already have an account? 
                                <a href="<?php echo SITE_URL; ?>/auth/login.php" class="text-primary fw-bold">Login</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Pass PHP data to JS
const batchesData = <?php echo json_encode($batchesData); ?>;

function toggleFields() {
    const roleAlumni = document.getElementById('role_alumni');
    const batchContainer = document.getElementById('batch_container');
    const idCardLabel = document.getElementById('id_card_label');
    const batchSelect = document.getElementById('batch');

    if (roleAlumni.checked) {
        batchContainer.style.display = 'block';
        batchSelect.required = true;
        idCardLabel.textContent = 'Upload Alumni Card / ID';
    } else {
        batchContainer.style.display = 'none';
        batchSelect.required = false;
        idCardLabel.textContent = 'Upload Student ID Card';
    }
}

function filterBatches() {
    const departmentSelect = document.getElementById('department');
    const batchSelect = document.getElementById('batch');
    const selectedDept = departmentSelect.value;
    const currentBatch = "<?php echo htmlspecialchars($_POST['batch'] ?? ''); ?>";

    // Clear current options
    batchSelect.innerHTML = '<option value="">Select Batch</option>';

    if (selectedDept) {
        const filteredBatches = batchesData.filter(item => item.department === selectedDept);
        
        filteredBatches.forEach(item => {
            const option = document.createElement('option');
            option.value = item.batch_name;
            option.textContent = item.batch_name;
            if (item.batch_name === currentBatch) {
                option.selected = true;
            }
            batchSelect.appendChild(option);
        });
    }
}

// Run on page load
document.addEventListener('DOMContentLoaded', () => {
    toggleFields();
    filterBatches();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
