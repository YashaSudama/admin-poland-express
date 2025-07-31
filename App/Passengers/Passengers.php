<?php
namespace App\Passengers;

use App\Configurations;
use App\DataBase\DataBase;

class Passengers
{
    private static $prefixDB;
    private static $tablePassengers;
    private static $tableRoutes;
    private static $tableAdmins;
    private static $tableRolesAdmins;

    public function __construct() { 
        
    }

    public static function getPassengersMonth() {
        Configurations::connectDotENV();
        DataBase::connectDB();

        $year = isset($_GET['year']) ? (int)$_GET['year'] : null;
        $month = isset($_GET['month']) ? (int)$_GET['month'] + 1 : null;

        if ($year && $month ) {
            self::$prefixDB = $_ENV['DB_PREFIX'];
            self::$tablePassengers = $_ENV['DB_TABLE_PASSENGERS'];
            self::$tableAdmins = $_ENV['DB_TABLE_ADMINS'];
            self::$tableRolesAdmins = $_ENV['DB_TABLE_ROLES_ADMINS'];

            $sql = "
                SELECT 
                    p.date, 
                    p.number_seats, 
                    p.from_poland,
                    p.referal_id,
                    ra.role_name AS admin_role_name

                FROM `" . self::$prefixDB . self::$tablePassengers . "` AS p

                JOIN 
                    `" . self::$prefixDB . self::$tableAdmins . "` AS a ON p.admin_id = a.id 

                JOIN 
                    `" . self::$prefixDB . self::$tableRolesAdmins . "` AS ra ON a.role_id = ra.id

                WHERE 
                    MONTH(p.date) = ?                   AND 
                    YEAR(p.date) = ?                    AND 
                    p.passenger_or_parcel = 'passenger' AND
                    p.cancellation_booking = 0          AND

                    (p.status_referal IS NULL           OR 
                     p.status_referal = ''              OR 
                     p.status_referal = 'received')
            ";

            $select = DataBase::$conn->prepare($sql);
            $select->bind_param("ii", $month, $year);
            $select->execute();

            $result = $select->get_result();

            if ($result) {
                $rows = $result->fetch_all(MYSQLI_ASSOC);
                $select->close();
                
                echo json_encode(['result' => $rows]);

            } else {
                echo json_encode(['error' => Configurations::$messages['Passengers'][0]]);
            }
            
            DataBase::$conn->close();

        } else {
            echo json_encode(['error' => Configurations::$messages['Repeating'][0]]);
        }
        
    }

    public static function getPassengersDay() {
        Configurations::connectDotENV();
        DataBase::connectDB();

        $year = isset($_GET['year']) ? (int)$_GET['year'] : null;
        $month = isset($_GET['month']) ? (int)$_GET['month'] + 1 : null;
        $day = isset($_GET['day']) ? (int)$_GET['day'] : null;
        
        if ($year && $month && $day ) {
            self::$prefixDB = $_ENV['DB_PREFIX'];
            self::$tablePassengers = $_ENV['DB_TABLE_PASSENGERS'];
            self::$tableAdmins = $_ENV['DB_TABLE_ADMINS'];
            self::$tableRolesAdmins = $_ENV['DB_TABLE_ROLES_ADMINS'];

            $sql = "
                SELECT 
                    p.date, 
                    p.referal_id,
                    p.status_referal, 
                    p.number_seats, 
                    p.from_poland, 
                    p.passenger_or_parcel,
                    ra.role_name AS admin_role_name

                FROM `" . self::$prefixDB . self::$tablePassengers . "` AS p
                
                JOIN 
                    `" . self::$prefixDB . self::$tableAdmins . "` AS a ON p.admin_id = a.id 

                JOIN 
                    `" . self::$prefixDB . self::$tableRolesAdmins . "` AS ra ON a.role_id = ra.id

                WHERE 
                    MONTH(`date`) = ? AND 
                    YEAR(`date`) = ?  AND 
                    DAY(`date`) = ?   AND
                    cancellation_booking = 0 
            ";

            $select = DataBase::$conn->prepare($sql);
            $select->bind_param("iii", $month, $year, $day);
            $select->execute();

            $result = $select->get_result();

            if ($result) {
                $rows = $result->fetch_all(MYSQLI_ASSOC);
                $select->close();
                DataBase::$conn->close();

                return $rows;

            } else {
                DataBase::$conn->close();
                Configurations::messageOutput(Configurations::$messages['Passengers'][0], 'error');
            }

        } else {
            DataBase::$conn->close();
            Configurations::messageOutput(Configurations::$messages['Repeating'][0], 'error');
        }

    }

    public static function getPassengersDayMore() {
        Configurations::connectDotENV();
        DataBase::connectDB();

        $year = isset($_GET['year']) ? (int)$_GET['year'] : null;
        $month = isset($_GET['month']) ? (int)$_GET['month'] + 1 : null;
        $day = isset($_GET['day']) ? (int)$_GET['day'] : null;
        
        if ($year && $month && $day && isset($_GET['from-poland'])) {
            self::$prefixDB = $_ENV['DB_PREFIX'];
            self::$tablePassengers = $_ENV['DB_TABLE_PASSENGERS'];
            self::$tableRoutes = $_ENV['DB_TABLE_ROUTES'];
            self::$tableAdmins = $_ENV['DB_TABLE_ADMINS'];
            self::$tableRolesAdmins = $_ENV['DB_TABLE_ROLES_ADMINS'];
            $routeDirection = (int)$_GET['from-poland'];

            $sql = "
                SELECT 
                    p.*, 
                    r.route_name, 

                    -- Основной админ
                    a.login AS admin_login,
                    ra.role_name AS admin_role_name,

                    -- Реферал
                    ref1.login AS referal_login,

                    -- Реферал отдать
                    ref2.login AS referal_login_given

                FROM 
                    `" . self::$prefixDB . self::$tablePassengers . "` AS p

                -- Маршрут
                JOIN 
                    `" . self::$prefixDB . self::$tableRoutes . "` AS r ON p.route_id = r.id 

                -- Основной админ
                JOIN 
                    `" . self::$prefixDB . self::$tableAdmins . "` AS a ON p.admin_id = a.id 

                JOIN 
                    `" . self::$prefixDB . self::$tableRolesAdmins . "` AS ra ON a.role_id = ra.id

                -- Реферал (если есть)
                LEFT JOIN 
                    `" . self::$prefixDB . self::$tableAdmins . "` AS ref1 ON p.referal_id = ref1.id

                -- Реферал отдать (если есть)
                LEFT JOIN 
                    `" . self::$prefixDB . self::$tableAdmins . "` AS ref2 ON p.referal_id_given = ref2.id

                WHERE 
                    MONTH(p.date) = ? AND 
                    YEAR(p.date) = ? AND 
                    DAY(p.date) = ? AND 
                    p.from_poland = ?
            ";

            $select = DataBase::$conn->prepare($sql);
            $select->bind_param("iiii", $month, $year, $day, $routeDirection); 
            $select->execute();

            $result = $select->get_result();

            if ($result) {
                $rows = $result->fetch_all(MYSQLI_ASSOC);
                $select->close();
                DataBase::$conn->close();

                return $rows;

            } else {
                DataBase::$conn->close();
                Configurations::messageOutput(Configurations::$messages['Passengers'][0], 'error');
            }

        } else {
            Configurations::messageOutput(Configurations::$messages['Repeating'][0], 'error');
        }

    }
    public static function addPassenger($data) {
        Configurations::connectDotENV();
        DataBase::connectDB();

        $adminId           = isset($data['admin-id']) ? (int)$data['admin-id'] : null;
        $roleOperator      = isset($data['role-admin-id']) ? $data['role-admin-id'] : null;
        $year              = isset($data['year']) ? (int)$data['year'] : null;
        $month             = isset($data['month']) ? sprintf('%02d', (int)$data['month'] + 1) : null;
        $day               = isset($data['day']) ? sprintf('%02d', (int)$data['day']) : null;
        $routeId           = isset($data['route-id']) ? (int)$data['route-id'] : null;
        $searchRoute       = isset($data['search-route']) ? trim($data['search-route']) : null;
        $priceRoute        = isset($data['price-route']) ? (int)$data['price-route'] : null;
        $name              = isset($data['name']) ? trim($data['name']) : null;
        $numberOfSeats     = isset($data['number-of-seats']) ? (int)$data['number-of-seats'] : null;
        $passengerOrParcel = isset($data['passenger-or-parcel']) ? $data['passenger-or-parcel'] : null;
        $phones            = isset($data['phone']) ? $data['phone'] : null;
        $prepayment        = isset($data['prepayment']) && $data['prepayment'] !== '' ? (float)$data['prepayment'] : 0;
        $prepaymentComment = isset($data['prepayment-comment']) ? ($data['prepayment-comment']) : null;
        $comment           = isset($data['comment']) ? trim($data['comment']) : null;
        $given             = isset($data['given']) ? (int)$data['given'] : null;
        $received          = isset($data['received']) ? (int)$data['received'] : null;

        $nameRegex = '/^(?:(?! {3})[\/а-яА-ЯЇїІіЄєҐґЁёa-zA-Z0-9\s\-_|.:;]+)$/u';
        $phoneRegex = '/^[\d+\-\s\(\)]+$/';
        $emptyStr = 'Поле <b>%s</b> не заполнено!';
        $regexStr = 'В поле <b>%s</b> применяются недопустимые символы!';
        $lengthStr = 'Поле <b>%s</b> должно содержать минимум <b>%d</b> символов!';
        $countPhones = 0;
        $noValidate = [];

        if (empty($searchRoute) || empty($routeId)) $noValidate['empty']['search-route'] = sprintf($emptyStr, 'Маршрут');
        if (empty($priceRoute)) $noValidate['empty']['price-route'] = sprintf($emptyStr, 'Цена маршрута');
        if (empty($name)) $noValidate['empty']['name'] = sprintf($emptyStr, 'Имя');

        if (!isset($data['without-phone'])) {

            foreach ($phones as $phone) {

                if (empty($phone)) {
                    $noValidate['empty']['phone[]'][$countPhones] = sprintf($emptyStr, 'Телефон');
                } else {

                    if (!preg_match($phoneRegex, $phone)) {
                        $noValidate['regex']['phone[]'][$countPhones] = sprintf($regexStr, 'Телефон');
                    }
                    
                    if (!empty($phone) && strlen($phone) < 10) {
                        $noValidate['length']['phone[]'][$countPhones] = sprintf($lengthStr, 'Телефон', 10);
                    }

                }

                $countPhones++;
                
            }

        }

        if (!empty($name) && !preg_match($nameRegex, $name)) {
            $noValidate['regex']['name'] = sprintf($regexStr, 'Имя');
        }

        if (!empty($comment) && !preg_match($nameRegex, $comment)) {
            $noValidate['regex']['comment'] = sprintf($regexStr, 'Комментарий');
        }

        if (!empty($prepaymentComment) && !preg_match($nameRegex, $prepaymentComment)) {
            $noValidate['regex']['prepayment-comment'] = sprintf($regexStr, 'Комментарий к предоплате');
        }

        if (!empty($given) && !empty($received)) {
            $noValidate['empty']['given'] = '';
            $noValidate['empty']['received'] = 'Поля "Принять" и "Отдать" не могут быть одновременно выбраны!';
        }

        if ($roleOperator === 'external-operator' && empty($received)) {
            $noValidate['empty']['received'] = sprintf($emptyStr, 'Принять');
        }

        if (!empty($noValidate)) {
            echo json_encode(['novalidate' => $noValidate]);
            return;
        }

        if ($year && $month && $day && isset($data['from-poland']) && $routeId && $name && ($phones || $phones === null)) {
            self::$prefixDB = $_ENV['DB_PREFIX'];
            self::$tablePassengers = $_ENV['DB_TABLE_PASSENGERS'];
            $fromPoland = (int)$data['from-poland'];
            $date = $year . '-' . $month . '-' . $day;
            $referalId = null;
            $status_referal = null;
            $phones = $data['without-phone'] === 'on' ? null : implode(',', $data['phone']);

            if ($roleOperator !== 'external-operator') {

                if (!empty($given)) {
                    $referalId = $given;
                    $status_referal = 'given';
                }

                if (!empty($received)) {
                    $referalId = $received;
                    $status_referal = 'received';
                }

            } else {
                $status_referal = 'received';
            }

            $sql = "INSERT INTO `" . self::$prefixDB . self::$tablePassengers . "` 

                    (`route_id`, 
                     `price_route`, 
                     `admin_id`, 
                     `referal_id`, 
                     `status_referal`, 
                     `date`,
                     `number_seats`,
                     `from_poland`,
                     `name`,
                     `phones`,
                     `passenger_or_parcel`,
                     `comment`,
                     `prepayment`,
                     `prepayment_comment`
                     ) 

                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $insert = DataBase::$conn->prepare($sql);
            $insert->bind_param("iiiissiissssis", $routeId,
                                                       $priceRoute, 
                                                              $adminId, 
                                                              $referalId,
                                                              $status_referal,
                                                              $date,
                                                              $numberOfSeats,
                                                              $fromPoland,
                                                              $name, 
                                                              $phones,
                                                              $passengerOrParcel,
                                                              $comment,
                                                              $prepayment,
                                                              $prepaymentComment
                                );

            $success = $insert->execute();

            if ($success && $insert->affected_rows > 0) {
                echo json_encode(['result' => true]);
            } else {
                echo json_encode(['error' => Configurations::$messages['Passengers'][2]]);
            }

            $insert->close();
            
        } else {
            echo json_encode(['error' => Configurations::$messages['Repeating'][1]]);
        }

        DataBase::$conn->close();

    }

    public static function selectPassenger($id) {
        Configurations::connectDotENV();
        DataBase::connectDB();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

        if ($id) {
            self::$prefixDB = $_ENV['DB_PREFIX'];
            self::$tablePassengers = $_ENV['DB_TABLE_PASSENGERS'];
            self::$tableRoutes = $_ENV['DB_TABLE_ROUTES'];
            self::$tableAdmins = $_ENV['DB_TABLE_ADMINS'];
            self::$tableRolesAdmins = $_ENV['DB_TABLE_ROLES_ADMINS'];

            $sql = "
                SELECT 
                    p.*, 
                    r.route_name, 

                    -- Основной админ
                    a.login AS admin_login,
                    ra.role_name AS admin_role_name,

                    -- Реферал
                    ref1.login AS referal_login,

                    -- Реферал отдать
                    ref2.login AS referal_login_given

                FROM 
                    `" . self::$prefixDB . self::$tablePassengers . "` AS p

                -- Маршрут
                JOIN 
                    `" . self::$prefixDB . self::$tableRoutes . "` AS r ON p.route_id = r.id 

                -- Основной админ
                JOIN 
                    `" . self::$prefixDB . self::$tableAdmins . "` AS a ON p.admin_id = a.id 

                JOIN 
                    `" . self::$prefixDB . self::$tableRolesAdmins . "` AS ra ON a.role_id = ra.id

                -- Реферал (если есть)
                LEFT JOIN 
                    `" . self::$prefixDB . self::$tableAdmins . "` AS ref1 ON p.referal_id = ref1.id

                -- Реферал отдать (если есть)
                LEFT JOIN 
                    `" . self::$prefixDB . self::$tableAdmins . "` AS ref2 ON p.referal_id_given = ref2.id

                WHERE 
                    p.id = ?
            ";

            $select = DataBase::$conn->prepare($sql);
            $select->bind_param("i", $id);
            $select->execute();

            $result = $select->get_result();

            if ($result) {
                $row = $result->fetch_assoc();
                $select->close();

                DataBase::$conn->close();
                
                return $row;

            } else {
                DataBase::$conn->close();
                Configurations::messageOutput(Configurations::$messages['Passengers'][3], 'error');
            }

        } else {
            DataBase::$conn->close();
            Configurations::messageOutput(Configurations::$messages['Repeating'][0], 'error');
        }

    }

    public static function editPassenger($data) {
        Configurations::connectDotENV();
        DataBase::connectDB();

        $id                = isset($data['id'])                  ? (int)$data['id'] : null;
        $routeId           = isset($data['route-id'])            ? (int)$data['route-id'] : null;
        $searchRoute       = isset($data['search-route'])        ? trim($data['search-route']) : null;
        $passengerOrParcel = isset($data['passenger-or-parcel']) ? $data['passenger-or-parcel'] : null;
        $numberOfSeats     = isset($data['number-of-seats'])     ? (int)$data['number-of-seats'] : null;
        $priceRoute        = isset($data['price-route'])         ? (int)$data['price-route'] : null;
        $phones            = isset($data['phone'])               ? $data['phone'] : null;
        $name              = isset($data['name'])                ? trim($data['name']) : null;
        $comment           = isset($data['comment'])             ? trim($data['comment']) : null;
        $prepayment        = isset($data['prepayment']) && $data['prepayment'] !== '' ? $data['prepayment'] : 0;
        $prepaymentComment = isset($data['prepayment-comment'])  ? ($data['prepayment-comment']) : null;
        $given             = isset($data['given'])               ? (int)$data['given'] : null;
        $received          = isset($data['received'])            ? (int)$data['received'] : null;
        $date              = isset($data['date'])                ? $data['date'] : null;
        $statusReferal     = isset($data['status-referal'])      ? $data['status-referal'] : null;
        $referalIdGiven    = null;

        $nameRegex = '/^(?:(?! {3})[\/а-яА-ЯЇїІіЄєҐґЁёa-zA-Z0-9\s\-_|.:;]+)$/u';
        $phoneRegex = '/^[\d+\-\s\(\)]+$/';
        $emptyStr = 'Поле <b>%s</b> не заполнено!';
        $regexStr = 'В поле <b>%s</b> применяются недопустимые символы!';
        $lengthStr = 'Поле <b>%s</b> должно содержать минимум <b>%d</b> символов!';
        $countPhones = 0;
        $noValidate = [];

        if (empty($searchRoute) || empty($routeId)) $noValidate['empty']['search-route'] = sprintf($emptyStr, 'Маршрут');
        if (empty($priceRoute)) $noValidate['empty']['price-route'] = sprintf($emptyStr, 'Цена маршрута');
        if (empty($name)) $noValidate['empty']['name'] = sprintf($emptyStr, 'Имя');

         if (!isset($data['without-phone'])) {

            foreach ($phones as $phone) {

                if (empty($phone)) {
                    $noValidate['empty']['phone[]'][$countPhones] = sprintf($emptyStr, 'Телефон');
                } else {

                    if (!preg_match($phoneRegex, $phone)) {
                        $noValidate['regex']['phone[]'][$countPhones] = sprintf($regexStr, 'Телефон');
                    }
                    
                    if (!empty($phone) && strlen($phone) < 10) {
                        $noValidate['length']['phone[]'][$countPhones] = sprintf($lengthStr, 'Телефон', 10);
                    }

                }

                $countPhones++;
                
            }

        }

        if (!empty($name) && !preg_match($nameRegex, $name)) {
            $noValidate['regex']['name'] = sprintf($regexStr, 'Имя');
        }

        if (!empty($comment) && !preg_match($nameRegex, $comment)) {
            $noValidate['regex']['comment'] = sprintf($regexStr, 'Комментарий');
        }

        if (!empty($prepaymentComment) && !preg_match($nameRegex, $prepaymentComment)) {
            $noValidate['regex']['prepayment-comment'] = sprintf($regexStr, 'Комментарий к предоплате');
        }

        if (!empty($given) && !empty($received)) {

            if ($received === (int) $data['referal-id']) {
                $referalIdGiven = $given;
                $statusReferal = 'received-given';
            } else {
                $noValidate['empty']['given'] = '';
                $noValidate['empty']['received'] = 'В поле <b>"Принять"</b> указан не тот оператор, который был до редактирования брони!';
            }
            
        }

        if (!empty($noValidate)) {
            echo json_encode(['novalidate' => $noValidate]);
            return;
        }

        if ($id && $date && $routeId && $name && ($phones || $phones === null)) {
            self::$prefixDB = $_ENV['DB_PREFIX'];
            self::$tablePassengers = $_ENV['DB_TABLE_PASSENGERS'];
            $phones = $data['without-phone'] === 'on' ? null : implode(',', $data['phone']);

            $sql = "UPDATE `" . self::$prefixDB . self::$tablePassengers . "` 
                    SET 
                        `route_id` = ?, 
                        `price_route` = ?,
                        `referal_id_given` = ?,
                        `status_referal` = ?,
                        `date` = ?, 
                        `number_seats` = ?, 
                        `name` = ?, 
                        `phones` = ?, 
                        `passenger_or_parcel` = ?, 
                        `comment` = ?, 
                        `prepayment` = ?, 
                        `prepayment_comment` = ?

                    WHERE `id` = ?";

            $update = DataBase::$conn->prepare($sql);
            $update->bind_param("iiisssssssisi", 
                $routeId,
                $priceRoute,
                $referalIdGiven, 
                $statusReferal,
                $date,
                $numberOfSeats,
                $name, 
                $phones,
                $passengerOrParcel,
                $comment,
                $prepayment,
                $prepaymentComment,
                $id
            );

            $success = $update->execute();

            if ($success) {
                
                if ($update->affected_rows > 0) {
                    echo json_encode(['result' => true]);
                } else {
                    echo json_encode(['info' => Configurations::$messages['Passengers'][6]]);
                }
                
            } else {
                echo json_encode(['error' => Configurations::$messages['Passengers'][5]]);
            }

            $update->close();

        } else {
            echo json_encode(['error' => Configurations::$messages['Repeating'][1]]);
        }

        DataBase::$conn->close();

    }

    public static function editStatusBooking($data) {
        Configurations::connectDotENV();
        DataBase::connectDB();

        $id = isset($data['id']) ? (int)$data['id'] : null;

        if ($id && isset($data['status-booking'])) {
            self::$prefixDB = $_ENV['DB_PREFIX'];
            self::$tablePassengers = $_ENV['DB_TABLE_PASSENGERS'];
            $statusBooking = $data['status-booking'];

            $sql = "UPDATE `" . self::$prefixDB . self::$tablePassengers . "` 

                    SET 
                        `cancellation_booking` = ?
                        
                    WHERE `id` = ?";

            $update = DataBase::$conn->prepare($sql);
            $update->bind_param("ii", $statusBooking, $id);
            $success = $update->execute();

            if ($success && $update->affected_rows > 0) {
                echo json_encode(['result' => true, 'status' => $statusBooking]);
            } else {
                echo json_encode(['error' => Configurations::$messages['Passengers'][4]]);
            }

            $update->close();

        } else {
            echo json_encode(['error' => Configurations::$messages['Repeating'][1]]);
        }

        DataBase::$conn->close();
        
    }

}
