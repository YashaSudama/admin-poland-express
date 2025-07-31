<?php

namespace App\Registration;

use App\Configurations;
use App\DataBase\DataBase;

class Registration
{
    private static $prefixDB;
    private static $tableAdmins;
    private static $tableRolesAdmins;

    public function __construct() {
        
    }

    public static function registration($data) {
        Configurations::connectDotENV();
        DataBase::connectDB();

        $login =    isset($data['login'])    ? trim($data['login'])    : null; 
        $statusPassword = isset($data['operator-password']) ? (int) $data['operator-password'] : null;
        $emptyStr = 'Поле <b>%s</b> не заполнено!';
        $regexStr = 'В поле <b>%s</b> применяются недопустимые символы!';
        $noValidate = [];

        if (empty($login)) $noValidate['empty']['login'] = sprintf($emptyStr, 'Логин');

        if ($statusPassword) {
            $password = isset($data['password']) ? trim($data['password']) : null;
            $phone =    isset($data['phone'])    ? trim($data['phone'])    : null;
            $roleOperator = isset($data['roles-operators']) ? $data['roles-operators'] : null;
            $loginRegex = '/^(?:(?! {3})[\/а-яА-ЯЇїІіЄєҐґЁёa-zA-Z0-9\s\-_|]+)$/u';
            $phoneRegex = '/^[\d+\-\s\(\)]+$/';
            $lengthStr = 'Поле <b>%s</b> должно содержать минимум <b>%d</b> символов!';

            if (empty($password)) $noValidate['empty']['password'] = sprintf($emptyStr, 'Пароль');
            if (empty($phone))    $noValidate['empty']['phone'] = sprintf($emptyStr, 'Телефон');
            if (!$roleOperator)   $noValidate['empty']['admin-roles'] = 'Не выбрана роль оператора!'; 

            if (!empty($login) && !preg_match($loginRegex, $login)) $noValidate['regex']['login'] = sprintf($regexStr, 'Логин');
            if (!empty($phone) && !preg_match($phoneRegex, $phone)) $noValidate['regex']['phone'] = sprintf($regexStr, 'Телефон');
            if (!empty($password) && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]+$/', $password)) {
                $noValidate['regex']['password'] = 'Пароль должен содержать только латинские буквы и цифры, включая хотя бы одну строчную (a-z), одну заглавную (A-Z) и одну цифру (0-9)!';
            }

            if (!empty($login) && mb_strlen($login) < 6)     $noValidate['length']['login'] = sprintf($lengthStr, 'Логин', 6);
            if (!empty($password) && strlen($password) < 12) $noValidate['length']['password'] = sprintf($lengthStr, 'Пароль', 12);
            if (!empty($phone) && strlen($phone) < 10)       $noValidate['length']['phone'] = sprintf($lengthStr, 'Телефон', 10);

        } else {
            $loginRegex = "/['\";<>\/=()\\\\`]/";
            $roleOperator = 'external-operator';
            $phone = null;

            if (!empty($login) && preg_match($loginRegex, $login)) $noValidate['regex']['login'] = sprintf($regexStr, 'Логин');
            
        }

        if (!empty($noValidate)) {
            echo json_encode(['novalidate' => $noValidate]);
            return;
        }

        if ($login && ($password || $password === null) && ($phone || $phone === null) && $roleOperator) {
            self::$prefixDB = $_ENV['DB_PREFIX'];
            self::$tableAdmins = $_ENV['DB_TABLE_ADMINS'];
            self::$tableRolesAdmins = $_ENV['DB_TABLE_ROLES_ADMINS'];

            $checkSql = "SELECT id FROM `" . self::$prefixDB . self::$tableAdmins . "` WHERE login = ?";
            $checkLogin = DataBase::$conn->prepare($checkSql);
            $checkLogin->bind_param("s", $login);
            $checkLogin->execute();
            $checkLogin->store_result();

            if ($checkLogin->num_rows > 0) {
                $checkLogin->close();
                echo json_encode(['info' => Configurations::$messages['Admins'][1]]);
                return;
            }

            $checkLogin->close();

            $sql = "SELECT id FROM `" . self::$prefixDB . self::$tableRolesAdmins . "` WHERE role_name = ?";

            $select = DataBase::$conn->prepare($sql);
            $select->bind_param("s", $roleOperator);
            $select->execute();
            $select->store_result();

            if ($select->num_rows > 0) {
                $select->bind_result($roleId);
                $select->fetch();
                $select->close();
                
                $password = $statusPassword ? password_hash($password, PASSWORD_DEFAULT) : null;

                $sql = "INSERT INTO `" . self::$prefixDB . self::$tableAdmins . "` 
                        (`login`, `password`, `phone`, `role_id`) 
                        VALUES (?, ?, ?, ?)";

                $insert = DataBase::$conn->prepare($sql);
                $insert->bind_param("sssi", $login, $password, $phone, $roleId);
                $success = $insert->execute();

                if ($success && $insert->affected_rows > 0) {
                    echo json_encode(['result' => true, 'id' => $insert->insert_id, 'login' => $login]);
                } else {
                    echo json_encode(['error' => Configurations::$messages['Registration'][0]]);
                }

                $insert->close();
            
                DataBase::$conn->close();

            } else {
                $select->close();
                echo json_encode(['error' => Configurations::$messages['RolesAdmins'][0]]);
            }

        } else {
            echo json_encode(['error' => Configurations::$messages['Repeating'][1]]);
        }

    }

}