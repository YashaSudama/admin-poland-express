<?php
namespace App\DataBase;

use App\Configurations;

class DataBase
{
    public static $conn;
    private static $nameDB;
    private $prefixDB;
    private $tableRoutes;
    private $tablePassengers;
    private $tableRolesAdmins;
    private $tableAdmins;

    public function __construct() {
        Configurations::connectDotENV();
        $this->prefixDB = $_ENV['DB_PREFIX'];
        self::connectDB();
        $this->createTableRoleAdmins();
        $this->createTableAdmins();
        $this->createTableRoutes();
        $this->createTablePassengers();
        $this->insertTableRoleAdmins();
        self::$conn->close();
    }

    public static function connectDB() {
        $hostDB = $_ENV['DB_HOST'];
        $userDB = $_ENV['DB_USER'];
        $passDB = $_ENV['DB_PASS'];
        $portDB = $_ENV['DB_PORT'];
        self::$nameDB = $_ENV['DB_NAME'];
        
        self::$conn = new \mysqli($hostDB, $userDB, $passDB, self::$nameDB, $portDB);

        if (self::$conn->connect_error) {
            return Configurations::messageOutput('Не удалось соединится с Базой данных!' . self::$conn->connect_error, 'error');
            exit();
        }
        
    }

    private function tableExists(string $tableName): bool {
        $result = self::$conn->query("SHOW TABLES LIKE '$tableName'");

        return $result->num_rows;

    }

    private function createTableRoutes() {
        $this->tableRoutes = $this->prefixDB . $_ENV['DB_TABLE_ROUTES'];
        self::$conn->select_db(self::$nameDB);

        if (!$this->tableExists($this->tableRoutes)) {

            $result = self::$conn->query("
                CREATE TABLE IF NOT EXISTS `$this->tableRoutes` (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    route_name VARCHAR(255) UNIQUE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            
            return $this->messageCreateTable($this->tableRoutes, $result);

        } else {
            return;
        }

    }

    private function createTablePassengers() {
        $this->tablePassengers = $this->prefixDB . $_ENV['DB_TABLE_PASSENGERS'];
        self::$conn->select_db(self::$nameDB);
        
        if (!$this->tableExists($this->tablePassengers)) {

            $result = self::$conn->query("
                CREATE TABLE IF NOT EXISTS `$this->tablePassengers` (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    route_id INT UNSIGNED NOT NULL,
                    price_route BIGINT UNSIGNED DEFAULT 0,
                    admin_id INT UNSIGNED NOT NULL,
                    referal_id INT UNSIGNED DEFAULT NULL,
                    referal_id_given INT UNSIGNED DEFAULT NULL,
                    status_referal ENUM('received', 'given', 'received-given') DEFAULT NULL,
                    cancellation_booking BOOLEAN NOT NULL DEFAULT FALSE,
                    date DATE NOT NULL,
                    number_seats TINYINT UNSIGNED DEFAULT 0,
                    from_poland BOOLEAN NOT NULL DEFAULT FALSE,
                    name VARCHAR(100) NOT NULL,
                    phones VARCHAR(255) DEFAULT NULL,
                    passenger_or_parcel ENUM('passenger', 'parcel') NOT NULL,
                    comment TEXT,
                    prepayment BIGINT UNSIGNED DEFAULT 0,
                    prepayment_comment TEXT,

                    INDEX idx_date (date),
                    FOREIGN KEY (route_id)         REFERENCES `" . $this->tableRoutes . "`(id) ON DELETE CASCADE,
                    FOREIGN KEY (admin_id)         REFERENCES `" . $this->tableAdmins . "`(id) ON DELETE CASCADE,
                    FOREIGN KEY (referal_id)       REFERENCES `" . $this->tableAdmins . "`(id) ON DELETE SET NULL,
                    FOREIGN KEY (referal_id_given) REFERENCES `" . $this->tableAdmins . "`(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            
            return $this->messageCreateTable($this->tablePassengers, $result);

        } else {
            return;
        }

    }
    private function createTableRoleAdmins() {
        $this->tableRolesAdmins = $this->prefixDB . $_ENV['DB_TABLE_ROLES_ADMINS'];
        self::$conn->select_db(self::$nameDB);
        
        if (!$this->tableExists($this->tableRolesAdmins)) {

            $result = self::$conn->query("
                CREATE TABLE IF NOT EXISTS `$this->tableRolesAdmins` (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    role_name VARCHAR(100) NOT NULL UNIQUE,
                    role_description TEXT DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;        
            ");

            return $this->messageCreateTable($this->tableRolesAdmins, $result);

        } else {
            return;
        }

    }

    private function createTableAdmins() {
        $this->tableAdmins = $this->prefixDB . $_ENV['DB_TABLE_ADMINS'];

        if (!$this->tableExists($this->tableAdmins)) {

            $result = self::$conn->query("
                CREATE TABLE IF NOT EXISTS `$this->tableAdmins` (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    login VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) DEFAULT NULL,
                    role_id INT UNSIGNED NOT NULL,
                    phone VARCHAR(30) DEFAULT NULL,

                    INDEX idx_role_id (role_id),
                    FOREIGN KEY (role_id) REFERENCES `$this->tableRolesAdmins`(id) ON DELETE RESTRICT ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
      
            ");

            return $this->messageCreateTable($this->tableAdmins, $result);

        } else {
            return;
        }

    }
    private function insertTableRoleAdmins() {

        if ($this->tableExists($this->tableRolesAdmins)) {
            $result = self::$conn->query("SELECT id, role_name FROM `$this->tableRolesAdmins` ORDER BY id");

            if (!$result->num_rows) {

                $result = self::$conn->query("
                    INSERT INTO `" . $this->tableRolesAdmins . "` (`role_name`, `role_description`)
                    VALUES 
                    ('super-admin', 'Супер администратор'),
                    ('admin', 'Администратор'),
                    ('external-operator', 'Внешний оператор'),
                    ('internal-operator', 'Внутренний оператор');
                ");

                $type = $result ? 'success' : 'error';
                $message = $result ? 'Роли администраторов успешно созданы!' : 'Роли администраторов создать не удалось!';
                
                return Configurations::messageOutput($message, $type);

            } else {
                return;
            }

        } else {
            return;
        }

    }
    private function messageCreateTable($tableName, $result) {
        $type = $result ? 'success' : 'error';
        $message = sprintf(
            'Таблица <b>%s</b> %s%s',
            $tableName,
            $result ? 'успешно создана!' : 'не создана.',
            $result ? '' : ' Ошибка: ' . self::$conn->error
        );

        return Configurations::messageOutput($message, $type);

    }

}