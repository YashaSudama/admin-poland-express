<?php
namespace App;

use Pdp\Rules;
use Dotenv\Dotenv;

class Configurations
{   
    private const ERROR = 'Ошибка! ';
    
    private static $url;

    public static $userLogin;
    
    public static $userRole;

    public static $userId;

    public static $messages = [
        'Repeating' => [
            self::ERROR . 'Переданы некорректные гет-параметры!',
            self::ERROR . 'Переданы некорректные пост-параметры!',
        ],
        'Api' => [
            self::ERROR . 'Недопустимый класс или метод!',
            self::ERROR . 'Метод не поддерживается!',
        ],
        'Configurations' => [
            self::ERROR . 'Не удалось получить список маршрутов!',
            self::ERROR . 'Некорректные параметры даты',
        ],
        'Passengers' => [
            self::ERROR . 'Не удалось получить список броней!',
            self::ERROR . 'Не удалось подтвердить бронь!',
            self::ERROR . 'Не удалось добавить бронь!',
            self::ERROR . 'Не удалось получить информацию о брони!',
            self::ERROR . 'Не удалось обновить статус брони!',
            self::ERROR . 'Не удалось обновить бронь!',
            'Не було внесено никаних изменений!',
        ],
        'Routes' => [
            self::ERROR . 'Не удалось получить маршруты!',
            self::ERROR . 'Не удалось добавить маршрут!',
        ],
        'Admins' => [
            self::ERROR . 'Не удалось получить операторов!',
            'Такой логин уже существует!',
            self::ERROR . 'С таким логином оператора не найдено!',
            self::ERROR . 'Неверный пароль!',
            self::ERROR . 'Не удалось получить роли администраторов и операторов!',
        ],
        'Registration' => [
            self::ERROR . 'Не удалось зарегистрировать оператора!',
        ],
        'RolesAdmins' => [
            self::ERROR . 'Не удалось получить роль оператора!',
        ],
        'Mailing' => [
            self::ERROR . 'Не удалось получить списки телефонов!',
        ]

    ];

    public function __construct() {
        
    }

    public static function connectDotENV(){
        $dotEnv = Dotenv::createImmutable(dirname(__DIR__));
        $dotEnv->load();
    }

    public static function getListRoutes() {

        $rules = Rules::fromPath(
            'https://publicsuffix.org/list/public_suffix_list.dat'
        );
        
        $json = file_get_contents('https://' . $rules->resolve($_SERVER['HTTP_HOST'])->registrableDomain()->toString() . '/tab.json');

        if (!$json) {
            return self::messageOutput(self::$messages['Configurations'][0], 'error');
        }

        return json_decode($json, true);

    }

    public static function messageOutput($message, $type) { ?>
    
        <script class="script-message-output">
            messageOutput(<?= json_encode($message); ?>, <?= json_encode($type); ?>);
        </script>

    <?php }

    private static function getMenu() {

        return [
            'login' => [
                'name' => self::$userLogin,
                'icon' => self::$userLogin,
                'link' => self::$url,
            ],
            'users' => [
                'name' => 'Пользователи',
                'icon' => '<i class="fas fa-users"></i>',
                'link' => '/users.php',
            ],
            'mailing' => [
                'name' => 'Рассылка',
                'icon' => '<i class="fas fa-envelope"></i>',
                'link' => '/mailing.php',
            ],
            'logout' => [
                'name' => 'Выйти',
                'icon' => '<i class="fas fa-sign-out-alt"></i>',
                'link' => '/logout.php',
            ],
        ];

    }

    public static function getHeader() { 
        session_start();
        self::$url = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $hideHeader = '';

        if (empty($_SESSION)) {

            if ($_SERVER['REQUEST_URI'] !== '/autorisation.php') header('Location: /autorisation.php');

        } else {
            self::$userId = $_SESSION['user_id'];
            self::$userLogin = $_SESSION['user_login'];
            self::$userRole = $_SESSION['user_role'];
        }

        if ($_SERVER['REQUEST_URI'] === '/autorisation.php') $hideHeader = 'd-none';

        self::connectDotENV(); ?>
        
        <!DOCTYPE html>
            <html lang="ru">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link rel="stylesheet" href="/assets/css/style.css">
                    <link rel="stylesheet" href="/assets/css/font-awesome.css">
                    <script src="/assets/js/message.js"></script>
                    <title><?= $_ENV['SITE_TITLE']; ?></title>
                </head>
                <body>
                    <div class="container m-auto">
                        <header class="d-flex <?= $hideHeader; ?>">
                            <div class="logo">
                                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/assets/img/logo/poland-express-logo-mobile.webp') &&
                                          file_exists($_SERVER['DOCUMENT_ROOT'] . '/assets/img/logo/poland-express-logo-desctop.webp')): ?>
                                    <a class="logo-mobile" href="/">
                                        <img src="/assets/img/logo/poland-express-logo-mobile.webp" alt="<?= $_ENV['SITE_TITLE']; ?>">
                                    </a>
                                    <a class="logo-desctop" href="/">
                                        <img src="/assets/img/logo/poland-express-logo-desctop.webp" alt="<?= $_ENV['SITE_TITLE']; ?>">
                                    </a>
                                <?php else: ?>
                                    <span><?= $_ENV['SITE_TITLE']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="menu">
                                <nav>
                                    <ul class="d-flex">

                                        <?php $menu = self::getMenu();
                                        
                                        foreach ($menu as $key => $value) : 
                                            
                                            if (($key === 'users' || $key === 'mailing') && self::$userRole !== 'super-admin') continue; ?>

                                            <li>
                                                <a href="<?= $value['link'];?>" title="<?= $value['name']; ?>">
                                                    <span class="d-block text-center l-height-1-2 <?= $key;?>">
                                                        <?= $value['icon']; ?>
                                                    </span>
                                                </a>
                                            </li>

                                        <?php endforeach; ?>
                                        
                                    </ul>
                                </nav>
                            </div>
                        </header>
    <?php }

    public static function redirectOperators() {
        if (self::$userRole !== 'super-admin') header('Location: index.php');
    }

    public static function getDate() {
        $day   = isset($_GET['day'])   ? (int)$_GET['day']   : null;
        $month = isset($_GET['month']) ? (int)$_GET['month'] : null;
        $year  = isset($_GET['year'])  ? (int)$_GET['year']  : null;

        if ($day && $month && $year) {
            $monthLocal = $month + 1;
            $timezone = new \DateTimeZone('Europe/Kiev');
            $date = new \DateTime("$year-$monthLocal-$day", $timezone);

            $formatter = new \IntlDateFormatter(
                'ru_RU',                  // или 'uk_UA' если нужен украинский
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::NONE,
                $timezone, 
                null,
                'd MMMM y'
            );

            return [ 
                    'result' => [ 
                        'dateFormat' => $formatter->format($date),
                        'month'      => $month,
                        'year'       => $year,
                        'day'        => $day
                    ]
                ];

        } else {
            return [ 'error' => self::$messages['Repeating'][0]];
        }

    }
    public static function addPassengerOrUserHTML() { ?>

        <div class="add-passenger-link-block pos-fixed" onclick="addPassengerOrUserLink(event, this)">
            <div class="add-passenger-link">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="check-direction pos-fixed d-none">
                <i class="close fas fa-times pos-abs"></i>
                <div class="pos-abs text-center">
                    <h3 class="to-poland">В Польшу</h3>
                    <h3 class="from-poland">Из Польши</h3>
                </div>
            </div>
        </div>

    <?php }

}