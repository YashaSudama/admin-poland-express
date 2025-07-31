<?php

namespace App;

use App\DataBase\DataBase;

class LaunchingApplication
{

    public function __construct() {
        new DataBase();
    }

}