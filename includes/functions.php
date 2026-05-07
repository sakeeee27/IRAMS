<?php
// ── REUSABLE FUNCTIONS WITH PREPARED STATEMENTS ──

/**
 * Get user by RFID — prepared statement
 */
function get_user_by_rfid($conn, $rfid){
    $stmt = $conn->prepare("
        SELECT users.*, departments.name AS department, departments.logo
        FROM users
        LEFT JOIN departments ON users.department_id = departments.id
        WHERE users.rfid_uid = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $rfid);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

/**
 * Get last attendance record for a user
 */
function get_last_attendance($conn, $user_id){
    $stmt = $conn->prepare("
        SELECT * FROM attendance
        WHERE user_id = ?
        ORDER BY time DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

/**
 * Insert attendance record
 */
function insert_attendance($conn, $user_id, $status){
    $stmt = $conn->prepare("INSERT INTO attendance (user_id, status) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $status);
    return $stmt->execute();
}

/**
 * Get user by ID
 */
function get_user_by_id($conn, $id){
    $stmt = $conn->prepare("
        SELECT users.*, departments.name AS dept_name
        FROM users
        LEFT JOIN departments ON users.department_id = departments.id
        WHERE users.id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

/**
 * Check if RFID already exists (exclude a user ID for edit)
 */
function rfid_exists($conn, $rfid, $exclude_id = 0){
    $stmt = $conn->prepare("SELECT id FROM users WHERE rfid_uid = ? AND id != ?");
    $stmt->bind_param("si", $rfid, $exclude_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/**
 * Insert new employee
 */
function insert_employee($conn, $data){
    $stmt = $conn->prepare("
        INSERT INTO users
        (rfid_uid, employee_id, biometric_id, first_name, middle_name, surname, name, position, department_id, photo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "ssssssssss",
        $data['rfid_uid'],
        $data['employee_id'],
        $data['biometric_id'],
        $data['first_name'],
        $data['middle_name'],
        $data['surname'],
        $data['name'],
        $data['position'],
        $data['department_id'],
        $data['photo']
    );
    return $stmt->execute();
}

/**
 * Update employee (with photo)
 */
function update_employee($conn, $data){
    if(!empty($data['photo'])){
        $stmt = $conn->prepare("
            UPDATE users SET
            rfid_uid=?, employee_id=?, biometric_id=?,
            first_name=?, middle_name=?, surname=?, name=?,
            position=?, department_id=?, photo=?
            WHERE id=?
        ");
        $stmt->bind_param(
            "ssssssssssi",
            $data['rfid_uid'], $data['employee_id'], $data['biometric_id'],
            $data['first_name'], $data['middle_name'], $data['surname'], $data['name'],
            $data['position'], $data['department_id'], $data['photo'], $data['id']
        );
    } else {
        $stmt = $conn->prepare("
            UPDATE users SET
            rfid_uid=?, employee_id=?, biometric_id=?,
            first_name=?, middle_name=?, surname=?, name=?,
            position=?, department_id=?
            WHERE id=?
        ");
        $stmt->bind_param(
            "sssssssssi",
            $data['rfid_uid'], $data['employee_id'], $data['biometric_id'],
            $data['first_name'], $data['middle_name'], $data['surname'], $data['name'],
            $data['position'], $data['department_id'], $data['id']
        );
    }
    return $stmt->execute();
}

/**
 * Delete employee and their attendance records
 */
function delete_employee($conn, $id){
    // Delete attendance first (foreign key constraint)
    $stmt = $conn->prepare("DELETE FROM attendance WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Delete employee
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

/**
 * Log admin activity
 */
function log_activity($conn, $action, $emp_name, $admin_name){
    $stmt = $conn->prepare("
        INSERT INTO activity_log (action, emp_name, admin_name)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("sss", $action, $emp_name, $admin_name);
    return $stmt->execute();
}

/**
 * Get admin by username
 */
function get_admin_by_username($conn, $username){
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

/**
 * Check if admin username exists
 */
function admin_username_exists($conn, $username){
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/**
 * Create new admin account
 */
function create_admin($conn, $username, $password, $full_name){
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt   = $conn->prepare("INSERT INTO admin_users (username, password, full_name) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed, $full_name);
    return $stmt->execute();
}

/**
 * Build full name from parts
 */
function build_full_name($first, $middle, $surname){
    $parts = array_filter([trim($first), trim($middle), trim($surname)]);
    return implode(' ', $parts);
}

/**
 * Null-or-value helper — returns null if empty string
 */
function null_or($value){
    $v = trim($value ?? '');
    return $v === '' ? null : $v;
}

/**
 * Handle file upload — returns relative path or null
 */
function handle_photo_upload($file_input, $upload_dir = 'uploads/'){
    if(empty($file_input['name'])) return null;
    $filename = time() . '_' . basename($file_input['name']);
    $dest     = __DIR__ . '/../' . $upload_dir . $filename;
    if(move_uploaded_file($file_input['tmp_name'], $dest)){
        return $upload_dir . $filename;
    }
    return null;
}

/**
 * Delete a photo file safely
 */
function delete_photo($photo_path){
    if($photo_path && $photo_path !== 'default.png'){
        $full = __DIR__ . '/../' . $photo_path;
        if(file_exists($full)) unlink($full);
    }
}
?>