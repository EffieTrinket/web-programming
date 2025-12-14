<?php
require_once 'database.php';

class Users extends Database {
    public $conn;
    public $user_id;
    public $fname;
    public $mname;
    public $lname;
    public $email;
    public $password;
    public $role;
    public $department_id;
    public $course_id;

    function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }  

    // function addUser(){
    //      $sql = "INSERT INTO staff (lname, fname, mname, email, password, role, department) VALUES 
    //     (:lname, :fname, :mname, :email, :password, :role, :department);";

    //     $query = $this->conn->prepare($sql); 
    //     $query->bindParam(':lname', $this->lname);
    //     $query->bindParam(':fname', $this->fname);
    //     $query->bindParam(':mname', $this->mname);
    //     $query->bindParam(':email', $this->email);
    //     $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
    //     $query->bindParam(':password', $hashedPassword);
    //     $query->bindParam(':role', $this->role);
    //     $query->bindParam(':department', $this->department);
        
    //     return $query->execute();
    // }

    function getUsersByEmail(){
        $sql = "SELECT * FROM users WHERE email = :email;";
        $query = $this->conn->prepare($sql);
        $query->bindParam(':email', $this->email);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }


    function registerUser(){
    $sql = "INSERT INTO users (lname, fname, mname, email, password, role, department_id, course_id, verification_token, is_verified) 
            VALUES (:lname, :fname, :mname, :email, :password, :role, :department_id, :course_id, :verification_token, :is_verified);";

    $query = $this->conn->prepare($sql);
    $query->bindParam(':lname', $this->lname);
    $query->bindParam(':fname', $this->fname);
    $query->bindParam(':mname', $this->mname);
    $query->bindParam(':email', $this->email);
    $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
    $query->bindParam(':password', $hashedPassword);
    $query->bindParam(':role', $this->role);
    $query->bindParam(':department_id', $this->department_id);
    $query->bindParam(':course_id', $this->course_id);
    $query->bindParam(':verification_token', $this->verification_token);
    $query->bindParam(':is_verified', $this->is_verified);

    return $query->execute();
}


    public function verifyEmail($token) {
    $sql = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$token]);
    return $stmt->rowCount() > 0;
}

    function getUserById($user_id){
        $sql = "SELECT * FROM users WHERE user_id = :user_id LIMIT 1";
        $query = $this->conn->prepare($sql);
        $query->bindParam(':user_id', $user_id);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    function getComplaintsByUserId($user_id){
    $sql = "SELECT c.complaint_id, c.category, c.description, c.attachment, c.status, c.date_filed,
                   d.department AS department
            FROM complaints c
            JOIN departments d ON c.department_id = d.department_id
            WHERE c.user_id = :user_id
            ORDER BY c.date_filed DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


   public function fileComplaint($complaint) {
    $sql = "INSERT INTO complaints (user_id, department_id, course_id, category, description, attachment, date_filed)
            VALUES (:user_id, :department_id, :course_id, :category, :description, :attachment, NOW())";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute([
        ':user_id' => $complaint['user_id'],
        ':department_id' => $complaint['department_id'],
        ':course_id' => $complaint['course_id'],
        ':category' => $complaint['category'],
        ':description' => $complaint['description'],
        ':attachment' => $complaint['attachment']
    ]);

    return $this->conn->lastInsertId();  
}



    function getAllComplaints() {
    $sql = "SELECT c.complaint_id, c.user_id, c.category, c.description, c.attachment, 
                   c.status, c.date_filed, d.department AS department
            FROM complaints c
            JOIN departments d ON c.department_id = d.department_id
            ORDER BY c.date_filed DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    function updateComplaintStatus($complaint_id, $status, $remarks) {
    $sql = "UPDATE complaints 
            SET status = :status, resolution_remarks = :remarks 
            WHERE complaint_id = :complaint_id";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':remarks', $remarks);
    $stmt->bindParam(':complaint_id', $complaint_id, PDO::PARAM_INT);
    return $stmt->execute();
    }

    public function getComplaintById($complaint_id)
{
    $query = "
        SELECT 
            c.complaint_id,
            c.user_id,
            c.category,
            c.description,
            c.status,
            c.date_filed,
            c.resolution_remarks,
            c.attachment,
            d.department AS sender_department,
            co.course_name AS sender_course,
            u.fname,
            u.lname,
            u.email
        FROM complaints c
        LEFT JOIN departments d ON c.department_id = d.department_id
        LEFT JOIN courses co ON c.course_id = co.course_id
        LEFT JOIN users u ON c.user_id = u.user_id
        WHERE c.complaint_id = :complaint_id
    ";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':complaint_id', $complaint_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



    function checkEmailExists($email) {
    $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    return $stmt->rowCount() > 0;
    }

   public function filterComplaints($category = '', $department_id = 0, $complaint_id = '') {
    $query = "SELECT c.complaint_id, d.department, c.category, c.status, c.date_filed 
              FROM complaints c
              LEFT JOIN departments d ON c.department_id = d.department_id
              WHERE 1";

    $params = [];

    if (!empty($category)) {
        $query .= " AND c.category = :category";
        $params[':category'] = $category;
    }

    if ($department_id > 0) {
        $query .= " AND c.department_id = :department_id";
        $params[':department_id'] = $department_id;
    }

    if (!empty($complaint_id)) {
        $query .= " AND c.complaint_id = :complaint_id";
        $params[':complaint_id'] = $complaint_id;
    }

    $query .= " ORDER BY c.date_filed DESC";

    $stmt = $this->conn->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


     public function filterStudentComplaints($user_id, $category = '', $department_id = 0, $complaint_id = '') {
    $sql = "SELECT c.*, d.department AS department
            FROM complaints c
            LEFT JOIN departments d ON c.department_id = d.department_id
            WHERE c.user_id = :user_id";

    $params = [':user_id' => $user_id];

    if (!empty($category)) {
        $sql .= " AND c.category = :category";
        $params[':category'] = $category;
    }

    if ($department_id > 0) {
        $sql .= " AND c.department_id = :department_id";
        $params[':department_id'] = $department_id;
    }

    if (!empty($complaint_id)) {
        $sql .= " AND c.complaint_id = :complaint_id"; 
        $params[':complaint_id'] = $complaint_id;
    }

    $sql .= " ORDER BY c.date_filed DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    function deleteComplaint($complaint_id) {
    $sql = "SELECT attachment FROM complaints WHERE complaint_id = :complaint_id";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':complaint_id' => $complaint_id]);
    $complaint = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($complaint) {
        // Delete the file if it exists
        if (!empty($complaint['attachment'])) {
            $filePath = __DIR__ . '/../uploads/' . $complaint['attachment'];
            if (file_exists($filePath)) {
                unlink($filePath); // Delete the file
            }
        }

        // Now delete the complaint from DB
        $sqlDelete = "DELETE FROM complaints WHERE complaint_id = :complaint_id";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        return $stmtDelete->execute([':complaint_id' => $complaint_id]);
    }

    return false;
    }

    // Get all users
    function getAllUsers($search = '', $department_id = 0) {
    $sql = "SELECT u.*, d.department, c.course_name
            FROM users u
            LEFT JOIN departments d ON u.department_id = d.department_id
            LEFT JOIN courses c ON u.course_id = c.course_id
            WHERE 1=1";

    $params = [];

    if(!empty($search)) {
        $sql .= " AND (u.fname LIKE ? OR u.mname LIKE ? OR u.lname LIKE ? OR u.email LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    }

    if($department_id > 0) {
        $sql .= " AND u.department_id = ?";
        $params[] = $department_id;
    }

    $stmt = $this->conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



// Delete user
    function deleteUser($user_id) {
    $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id = :user_id");
    return $stmt->execute([':user_id' => $user_id]);
    }

    function updateUser($user_id, $data) {
    $conn = $this->connect(); 

    $sql = "UPDATE users SET 
                fname = :fname, 
                mname = :mname, 
                lname = :lname, 
                email = :email, 
                role = :role, 
                department_id = :department_id, 
                course_id = :course_id
            WHERE user_id = :user_id";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':fname', $data['fname'], PDO::PARAM_STR);
    $stmt->bindValue(':mname', $data['mname'], PDO::PARAM_STR);
    $stmt->bindValue(':lname', $data['lname'], PDO::PARAM_STR);
    $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
    $stmt->bindValue(':role', $data['role'], PDO::PARAM_STR);

   
    if (!empty($data['department_id'])) {
        $stmt->bindValue(':department_id', $data['department_id'], PDO::PARAM_INT);
    } else {
        $stmt->bindValue(':department_id', null, PDO::PARAM_NULL);
    }

    if (!empty($data['course_id'])) {
        $stmt->bindValue(':course_id', $data['course_id'], PDO::PARAM_INT);
    } else {
        $stmt->bindValue(':course_id', null, PDO::PARAM_NULL);
    }

    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    return $stmt->execute(); 
    }   

    public function getDepartments() {
    $stmt = $this->conn->query("SELECT * FROM departments ORDER BY department ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// KPI: Total complaints in the past month
public function getTotalComplaintsPastMonth() {
    $sql = $this->conn->prepare("
        SELECT COUNT(*) AS total
        FROM complaints
        WHERE date_filed >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    ");
    $sql->execute();
    return $sql->fetch(PDO::FETCH_ASSOC)['total'];
}

// KPI: Complaints per department
public function getComplaintsPerDepartment() {
    $sql = $this->conn->prepare("
        SELECT d.department AS department_name, COUNT(c.complaint_id) AS total
        FROM complaints c
        JOIN departments d ON c.department_id = d.department_id
        GROUP BY c.department_id
        ORDER BY total DESC
    ");
    $sql->execute();
    return $sql->fetchAll(PDO::FETCH_ASSOC);
}

// KPI: Complaints per category (ENUM)
public function getComplaintsPerCategory() {
    $sql = $this->conn->prepare("
        SELECT category, COUNT(*) AS total
        FROM complaints
        GROUP BY category
        ORDER BY category ASC
    ");
    $sql->execute();
    return $sql->fetchAll(PDO::FETCH_ASSOC);
}

public function getComplaintHistory($complaint_id) {
    $sql = $this->conn->prepare("
        SELECT ch.*, CONCAT(u.fname, ' ', u.lname) AS updated_by_name
        FROM complaint_history ch
        JOIN users u ON ch.updated_by = u.user_id
        WHERE ch.complaint_id = :complaint_id
        ORDER BY ch.updated_at ASC
    ");
    $sql->execute([':complaint_id' => $complaint_id]);
    return $sql->fetchAll(PDO::FETCH_ASSOC);
}




}

?>
