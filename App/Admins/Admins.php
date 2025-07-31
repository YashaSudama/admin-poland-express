<?php
namespace App\Admins;

use App\Configurations;
use App\DataBase\DataBase;

class Admins
{
    private static $prefixDB;
    private static $tableAdmins;
    private static $tableRolesAdmins;

    public function __construct() {
        
    }

    public static function getAdmins() {
        Configurations::connectDotENV();
        DataBase::connectDB();

        self::$prefixDB = $_ENV['DB_PREFIX'];
        self::$tableAdmins = $_ENV['DB_TABLE_ADMINS'];
        self::$tableRolesAdmins = $_ENV['DB_TABLE_ROLES_ADMINS'];

        $sql = "
            SELECT 
                a.*, 
                r.role_name,
                r.role_description

            FROM " . self::$prefixDB . self::$tableAdmins . " AS a

            JOIN " . self::$prefixDB . self::$tableRolesAdmins . " AS r ON a.role_id = r.id

        ";

        $select = DataBase::$conn->prepare($sql);
        $select->execute();

        $result = $select->get_result();

        if ($result) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $select->close();

            DataBase::$conn->close();
            
            return $rows;

        } else {
            DataBase::$conn->close();

            return Configurations::messageOutput(Configurations::$messages['Admins'][0], 'error');
        }

    }

    public static function getRolesAdmins() { 
        Configurations::connectDotENV();
        DataBase::connectDB();

        self::$prefixDB = $_ENV['DB_PREFIX'];
        self::$tableRolesAdmins = $_ENV['DB_TABLE_ROLES_ADMINS'];

        $sql = "SELECT *FROM " . self::$prefixDB . self::$tableRolesAdmins . "";

        $select = DataBase::$conn->prepare($sql);
        $select->execute();

        $result = $select->get_result();

        if ($result) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $select->close();

            DataBase::$conn->close();
            
            return $rows;

        } else {
            DataBase::$conn->close();

            return Configurations::messageOutput(Configurations::$messages['Admins'][4], 'error');
        }

    }

}
