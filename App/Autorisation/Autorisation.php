<?php

namespace App\Autorisation;

use App\Configurations;
use App\DataBase\DataBase;

class Autorisation
{
    private static $prefixDB;
    private static $tableAdmins;
    private static $tableRolesAdmins;

    public function __construct() {
        
    }

    public static function autorisation($data) {
        Configurations::connectDotENV();
        DataBase::connectDB();
        session_start();

        $login =    isset($data['login'])    ? trim($data['login'])    : null;
        $password = isset($data['password']) ? trim($data['password']) : null;
        $emptyStr = 'Поле <b>%s</b> не заполнено!';
        $noValidate = [];

        if (empty($login))    $noValidate['empty']['login'] = sprintf($emptyStr, 'Логин');
        if (empty($password)) $noValidate['empty']['password'] = sprintf($emptyStr, 'Пароль');

        if (!empty($noValidate)) {
            echo json_encode(['novalidate' => $noValidate]);
            return;
        }

        if ($login && $password) {
            self::$prefixDB = $_ENV['DB_PREFIX'];
            self::$tableAdmins = $_ENV['DB_TABLE_ADMINS'];
            self::$tableRolesAdmins = $_ENV['DB_TABLE_ROLES_ADMINS'];

            $checkLoginSql = "SELECT 
                                a.*, 
                                ra.role_name
                              
                              FROM 
                                `" . self::$prefixDB . self::$tableAdmins . "` AS a

                              JOIN 
                                `" . self::$prefixDB . self::$tableRolesAdmins . "` AS ra ON a.role_id = ra.id
                              
                              WHERE login = ?";

            $checkLogin = DataBase::$conn->prepare($checkLoginSql);
            $checkLogin->bind_param("s", $login);
            $checkLogin->execute();
            $result = $checkLogin->get_result();

            if ($result->num_rows) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_login'] = $user['login'];
                    $_SESSION['user_role'] = $user['role_name'];
                    
                    echo json_encode(['result' => true]);
                } else {
                    echo json_encode(['error' => Configurations::$messages['Admins'][3]]);
                }
                
            } else {
                echo json_encode(['error' => Configurations::$messages['Admins'][2]]);
            }
                    
            $checkLogin->close();

        } else {
            echo json_encode(['error' => Configurations::$messages['Repeating'][1]]);
        }

    }

}