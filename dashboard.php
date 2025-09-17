<?php
session_start();
require_once 'db_actions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add_student') {
        $name = trim($_POST['name']);
        $age = intval($_POST['age']);
        $grade = trim($_POST['grade']);

        if (empty($name) || empty($age) || empty($grade)) {
            $message = 'All fields are required.';
        } else {
            addStudent($name, $age, $grade, $_SESSION['username']);
            $message = 'Student added successfully.';
        }
    } elseif ($action === 'delete_student') {
        $id = intval($_POST['id']);
        deleteStudent($id, $_SESSION['username']);
        $message = 'Student deleted successfully.';
    } elseif ($action === 'update_student') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $age = intval($_POST['age']);
        $grade = trim($_POST['grade']);

        if (empty($name) || empty($age) || empty($grade)) {
            $message = 'All fields are required.';
        } else {
            updateStudent($id, $name, $age, $grade, $_SESSION['username']);
            $message = 'Student updated successfully.';
        }
    }
}
$students = getStudents();
$logs = getLastLogs($_SESSION['username'], 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <a href="?logout=1" class="btn btn-danger">Logout</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Add New Student</div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_student">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="age" class="form-label">Age</label>
                                <input type="number" name="age" id="age" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="grade" class="form-label">Grade</label>
                                <input type="text" name="grade" id="grade" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Student</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Recent Activity</div>
                    <div class="card-body">
                        <?php if (empty($logs)): ?>
                            <p>No recent activity.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($logs as $log): ?>
                                    <li class="list-group-item"><?php echo htmlspecialchars($log); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                Students List
                <a href="export.php" class="btn btn-info btn-sm">Export to JSON</a>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <p>No students found.</p>
                <?php else: ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo $student['id']; ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo $student['age']; ?></td>
                                    <td><?php echo htmlspecialchars($student['grade']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editStudent(<?php echo $student['id']; ?>, '<?php echo addslashes($student['name']); ?>', <?php echo $student['age']; ?>, '<?php echo addslashes($student['grade']); ?>')">Edit</button>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_student">
                                            <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" id="editForm">
                            <input type="hidden" name="action" value="update_student">
                            <input type="hidden" name="id" id="editId">
                            <div class="mb-3">
                                <label for="editName" class="form-label">Name</label>
                                <input type="text" name="name" id="editName" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="editAge" class="form-label">Age</label>
                                <input type="number" name="age" id="editAge" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="editGrade" class="form-label">Grade</label>
                                <input type="text" name="grade" id="editGrade" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Student</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editStudent(id, name, age, grade) {
            document.getElementById('editId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editAge').value = age;
            document.getElementById('editGrade').value = grade;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        // Handle logout
        if (window.location.search.includes('logout=1')) {
            fetch('dashboard.php', { method: 'POST', body: new FormData() }).then(() => {
                window.location.href = 'index.php';
            });
        }
    </script>

    <?php
    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: index.php");
        exit;
    }
    ?>
</body>
</html>
