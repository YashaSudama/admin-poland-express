<?php

namespace App;

require '../vendor/autoload.php';

use App\Configurations;

header('Content-Type: application/json');

$HTTPMethod = $_SERVER['REQUEST_METHOD'];

$allowedClasses = [
    'Passengers' => \App\Passengers\Passengers::class,
    'Routes' => \App\Routes\Routes::class,
    'Registration' => \App\Registration\Registration::class,
    'Autorisation' => \App\Autorisation\Autorisation::class,
    'Mailing' => \App\Mailing\Mailing::class, 
]; 

$classKey = $_GET['class'];
$classMethod = $_GET['method']; 

if (isset($allowedClasses[$classKey]) && method_exists($allowedClasses[$classKey], $classMethod)) {

    if ($HTTPMethod === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $allowedClasses[$classKey]::$classMethod($data);
        
    } else {
        $allowedClasses[$classKey]::$classMethod();
    }


} else {
    echo json_encode(['error' => Configurations::$messages['Api'][0]]);
}
