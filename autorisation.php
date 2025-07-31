<?php

    require 'vendor/autoload.php';

    use App\Configurations;

    Configurations::getHeader(); 
    
?>

            <div class="autorisation-block pos-abs">
                <h3 class="text-center">Авторизация</h3>
                <form class="m-t-50 m-auto" name="autorisation" method="post" action="/" onsubmit="autorisationFunc(event, this)">
                    <div class="login m-b-50">
                        <input type="text" name="login" placeholder="Логин">
                    </div>
                    <div class="password m-b-50 pos-rel" onclick="showHidePassword(event, this)">
                        <input type="password" name="password" placeholder="Пароль">
                        <i class="show-hide fas fa-eye-slash pos-abs"></i>
                    </div>
                    <div class="submit m-b-50">
                        <button>Авторизация</button>
                    </div>
                </form>
            </div>
            <script src="/assets/js/autorisation.js"></script>
        </body>
    </html>