<?php
namespace App\Routes;

use App\Configurations;
use App\DataBase\DataBase;

class Routes
{
    private static $prefixDB;
    private static $tableRoutes;

    public function __construct() {
        
    }

    public static function searchRoute() {
        Configurations::connectDotENV();
        DataBase::connectDB();

        $string = isset($_GET['string']) ? $_GET['string'] : null; 

        if ($string) {
            self::$prefixDB = $_ENV['DB_PREFIX'];
            self::$tableRoutes = $_ENV['DB_TABLE_ROUTES'];

            $sql = "SELECT id, route_name

                    FROM `" . self::$prefixDB . self::$tableRoutes . "` 

                    WHERE route_name LIKE CONCAT('%', ?, '%')";

            $select = DataBase::$conn->prepare($sql);
            $select->bind_param("s", $string);
            $select->execute();

            $result = $select->get_result();

            if ($result) {
                $rows = $result->fetch_all(MYSQLI_ASSOC);
                $select->close();
                
                echo json_encode(['result' => $rows]);

            } else {
                echo json_encode(['error' => Configurations::$messages['Routes'][0]]);
            }
            
            DataBase::$conn->close();

        } else {
            echo json_encode(['error' => Configurations::$messages['Repeating'][0]]);
        }
    }

    public static function addRoute($data) {
        Configurations::connectDotENV();
        DataBase::connectDB();

        $routeName = isset($data['route-name']) ? trim($data['route-name']) : null;
        $routeRegex = '/^([А-ЯЁІЇЄҐ][а-яёіїєґ\']+(?:[ \/-][А-ЯЁІЇЄҐ]?[а-яёіїєґ\']+){0,2}(?:\s\([^)]+\))?)(?:\s[-–—]\s[А-ЯЁІЇЄҐ][а-яёіїєґ\']+(?:[ \/-][А-ЯЁІЇЄҐ]?[а-яёіїєґ\']+){0,2}(?:\s\([^)]+\))?){1,}$/ux';
        $noValidate = [];

        if (!empty($routeName) && !preg_match($routeRegex, $routeName)) {
            $noValidate['regex']['search-route'] = 'Используется или неверный формат маршрута или недопустимые символы!';
        }

        if (!empty($noValidate)) {
            echo json_encode(['novalidate' => $noValidate]);
            return;
        }

        if ($routeName) {
            self::$prefixDB = $_ENV['DB_PREFIX'];
            self::$tableRoutes = $_ENV['DB_TABLE_ROUTES'];

            $sql = "INSERT INTO `" . self::$prefixDB . self::$tableRoutes . "` (route_name) 
                    VALUES (?)";

            $insert = DataBase::$conn->prepare($sql);
            $insert->bind_param("s", $routeName);
            $success = $insert->execute();

            if ($success && $insert->affected_rows > 0) {
                echo json_encode(['result' => true, 'id' => $insert->insert_id]);
            } else {
                echo json_encode(['error' => Configurations::$messages['Routes'][1]]);
            }

            $insert->close();
            DataBase::$conn->close();

        } else {
            echo json_encode(['error' => Configurations::$messages['Repeating'][1]]);
        }
    } 

}