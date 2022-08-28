<?php

namespace App\Models;
use App\Database;
use Valitron\Validator;

class User{

    private $db;

    public function __construct()
    {
        $this->db = new Database();
        return $this;
    }

    public function findBy($username) {
        $query = $this->db->queryExec('SELECT * FROM user WHERE username = ?', [$username]);
        return $query->fetch();
    }

    public function create(array $newUser = [])
    {
        $v = new Validator($newUser);
        $v->rules([
            'required' => [
                ['username'],
                ['password'],
                ['confirm_password']
            ],
            'lengthBetween' => [
                ['username', 5, 50],
                ['password', 8, 20]
            ],
            'equals' => [
                ['confirm_password', 'password']
            ]
        ]);

        if ($v->validate()) {
            $hashed_password = hash('sha256', $newUser['password']);
            $this->db->queryExec('INSERT INTO user (username, password, avatar) VALUES (?, ?, ?)', [$newUser['username'], $hashed_password, '/img/avatar.jpg']);
            return ['error' => null];
        } else {
            return ['error' => $v->errors()];
        }

    }

    public function updatePwd($new_password)
    {
        $db = new Database();
        $db = $db->getConnection();

        $newPassword = $db->prepare("UPDATE user SET password = :password WHERE user_id = :user_id");
        $v = new Validator($new_password);
        $v->rules([
            'required' => [
                ['old_password'],
                ['new_password'],
                ['confirm_password']
            ],
            'lengthBetween' => [
                ['old_password',8,20],
                ['new_password',8,20]
            ],
            'equals' => [
                ['confirm_password', 'new_password']
            ]
        ]);
        if ($v->validate() AND hash_equals(hash('sha256', $new_password['old_password']), $_SESSION['user']['password'])) {

            $dataPassword = [
                ':password' => hash('sha256', $new_password['new_password']),
                ':user_id' => $_SESSION['user']['user_id']
            ];

            $_SESSION['user']['password'] = $new_password['new_password'];

            $newPassword->execute($dataPassword);
            $validPass = "Password Changed";

            return [
                'error' => null,
                'validPass' => $validPass
            ];
        } elseif(!hash_equals(hash('sha256', $new_password['old_password']), $_SESSION['user']['password'])) {
            $errorPass = "Wrong password";
            return [
                'errorPass' => $errorPass
            ];
        }else{
            return ['error' => $v->errors()];
        }
    }

    public function updateAvatar($avatar)
    {
        $db = new Database();
        $db = $db->getConnection();

        $newAvatar = $db->prepare("UPDATE user SET avatar = :avatar WHERE user_id = :user_id");
        $dataAvatar = [
            ':avatar' => $avatar,
            ':user_id' => $_SESSION['user']['user_id']
        ];

        $newAvatar->execute($dataAvatar);
        $_SESSION['user']['avatar'] = $avatar;
    }

    public function updateUsername(array $username)
    {
        $db = new Database();
        $db = $db->getConnection();

        $newUsername = $db->prepare("UPDATE user SET username = :username WHERE user_id = :user_id");
        $v = new Validator($username);
        $v->rules([
            'required' => [
                ['username']
            ],
            'lengthBetween' => [
                ['username',5,50]
            ]
        ]);
        if ($v->validate()) {

            $dataUsername = [
                ':username' => $username['username'],
                ':user_id' => $_SESSION['user']['user_id']
            ];

            $newUsername->execute($dataUsername);
            $_SESSION['user']['username'] = $username['username'];

            return ['error' => null];
        } else {
            return ['error' => $v->errors()];
        }
    }

    public function delete()
    {
        $db = new Database();
        $db->queryExec("DELETE FROM user WHERE user_id = :user_id", [':user_id' => $_SESSION['user']['user_id']]);
    }

}