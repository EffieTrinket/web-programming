<?php
require_once 'database.php';

class Users extends Database {
    public $user_id;
    public $fname;
    public $mname;
    public $lname;
    public $email;
    public $password;
    public $role;
    public $department_id;

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
        $sql = "INSERT INTO users (lname, fname, mname, email, password, role, department_id) VALUES 
        (:lname, :fname, :mname, :email, :password, :role, :department_id);";

        $query = $this->conn->prepare($sql);
        $query->bindParam(':lname', $this->lname);
        $query->bindParam(':fname', $this->fname);
        $query->bindParam(':mname', $this->mname);
        $query->bindParam(':email', $this->email);
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
        $query->bindParam(':password', $hashedPassword);
        $query->bindParam(':role', $this->role);
        $query->bindParam(':department_id', $this->department_id);
        
        return $query->execute();
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
    $sql = "INSERT INTO complaints (user_id, department_id, category, description, attachment, date_filed)
            VALUES (:user_id, :department_id, :category, :description, :attachment, NOW())";

    $stmt = $this->conn->prepare($sql);

    return $stmt->execute([
        ':user_id' => $complaint['user_id'],
        ':department_id' => $complaint['department_id'],
        ':category' => $complaint['category'],
        ':description' => $complaint['description'],
        ':attachment' => $complaint['attachment']
    ]);
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

    function getComplaintById($complaint_id) {
    $sql = "SELECT c.*, d.department AS department
            FROM complaints c
            LEFT JOIN departments d ON c.department_id = d.department_id
            WHERE c.complaint_id = :complaint_id
            LIMIT 1";
    $stmt = $this->conn->prepare($sql);
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

    public function filterComplaints($user_id, $category = '', $department_id = '') {
    $sql = "SELECT c.*, d.department
            FROM complaints c
            LEFT JOIN departments d ON c.department_id = d.department_id
            WHERE 1";

    $params = [];

    if (!empty($category)) {
        $sql .= " AND c.category = :category";
        $params[':category'] = $category;
    }

    if (!empty($department_id)) {
        $sql .= " AND c.department_id = :department_id";
        $params[':department_id'] = $department_id;
    }

    $stmt = $this->conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


}

?>
