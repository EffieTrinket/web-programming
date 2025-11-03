<?php

require_once 'users.php';

class Account extends Users{

    public $user_id;
    public $email;
    public $password;

    function login(){
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1;";
        $query = $this->connect()->prepare($sql);
        $query->bindParam(':email', $this->email);
    
        if ($query->execute()) {
            $accountData = $query->fetch();
    
            if ($accountData && password_verify($this->password, $accountData['password'])) {
                $this->user_id = $accountData['user_id'];
                return true;
            }
        }
    
        return false;
    }

}

?>