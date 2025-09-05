<?php
namespace App\Mailing;

use App\Configurations;
use App\DataBase\DataBase;

class Mailing
{
    private static $prefixDB;
    private static $tablePassengers;

    public function __construct() { 
        
    }

    public static function mailing($data) {
        Configurations::connectDotENV();
        DataBase::connectDB();

        $textMailing = isset($data['text-mailing']) ? $data['text-mailing'] : null;
        $nameRegex = '/^(?!.* {3})[а-яА-ЯёЁїЇіІєЄґҐa-zA-Z0-9\/_\-|.,!?()\[\]\s]+$/u';
        $emptyStr = 'Поле <b>%s</b> не заполнено!';
        $regexStr = 'В поле <b>%s</b> применяются недопустимые символы!';

        if (empty($textMailing)) $noValidate['empty']['text-mailing'] = sprintf($emptyStr, 'Текст рассылки');

        if (!empty($textMailing) && !preg_match($nameRegex, $textMailing)) {
            $noValidate['regex']['text-mailing'] = sprintf($regexStr, 'Текст рассылки');
        }

        if (!empty($noValidate)) {
            echo json_encode(['novalidate' => $noValidate]);
            return;
        }

        if ($textMailing) {
            self::$prefixDB = $_ENV['DB_PREFIX'];
            self::$tablePassengers = $_ENV['DB_TABLE_PASSENGERS'];

            $sql = "
                SELECT 
                    p.phones,
                    p.name

                FROM `" . self::$prefixDB . self::$tablePassengers . "` AS p 
                WHERE p.phones IS NOT NULL

            ";

            $select = DataBase::$conn->prepare($sql);
            $select->execute();
            $result = $select->get_result();

            if ($result) {
                $rows = $result->fetch_all(MYSQLI_ASSOC);
                $phones = [];

                foreach ($rows as $row) {

                    if (!isset($row['phones'])) {
                        continue;
                    }

                    $rawPhones = $row['phones'];
                    $splitPhones = preg_split('/[,]+/', $rawPhones);

                    foreach ($splitPhones as $phone) {
                        $cleanPhone = preg_replace('/\D+/', '', $phone);
                        
                        if (preg_match('/^0\d{9}$/', $cleanPhone)) {
                            $cleanPhone = '38' . $cleanPhone;
                        }
                        
                        $phones[] = $cleanPhone;

                    }

                }
                
                $phones = array_values(array_unique($phones));

                $token = $_ENV['TURBO_SMS_TOKEN'];
                $method = 'send.json';
                $postField = [
                    'recipients' => $phones,
                    // 'recipients' => ['380503684399'],
                    // 'recipients' => ['380989198541','380502401365'],
                    'sms' => [
                        'sender' => $_ENV['TURBO_SMS_SENDER'],
                        'text' => $textMailing
                    ],
                ];

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $_ENV['TURBO_SMS_URL'] . $method,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($postField),
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer ' . $token,
                        'Content-Type: application/json'
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);

                if ($err) {
                    echo json_encode(['error' => $err]);
                }

                $responseData = json_decode($response, true);
                echo json_encode(['result' => $responseData]);
                
            } else {
                echo json_encode(['error' => Configurations::$messages['Mailing'][0]]);
            }
            
            $select->close();
            
        } else {
            echo json_encode(['error' => Configurations::$messages['Repeating'][0]]);
        }
        
        DataBase::$conn->close();
        
    }

}