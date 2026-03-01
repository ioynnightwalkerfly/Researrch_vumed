<?php
// api/set_role.php
ob_start();
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized');
    }

    $role = $_POST['role'] ?? '';
    $allowedRoles = $_SESSION['user_roles'] ?? [];

    if (empty($role)) {
        throw new Exception('No role specified');
    }

    // Role Validation
    $validRoles = ['researcher', 'coordinator', 'admin', 'officer', 'secretary'];
    if (!in_array($role, $validRoles)) {
        throw new Exception('Invalid Role Type');
    }

    // Check permission (researcher is default for everyone)
    if ($role !== 'researcher') {
        if (empty($allowedRoles[$role])) {
            throw new Exception("Access Denied ($role)");
        }
    }

    // Set Role
    $_SESSION['role'] = $role;

    // Determine Redirect
    $redirectMap = [
        'researcher'  => 'dashboard.php',
        'coordinator' => 'dashboard.php',
        'admin'       => 'admin/admin_dashboard.php',
        'officer'     => 'officer/dashboard.php',
        'secretary'   => 'secretary/dashboard.php',
    ];
    $redirect = $redirectMap[$role] ?? 'dashboard.php';

    ob_clean();
    echo json_encode(['success' => true, 'redirect' => $redirect]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit();
?>
