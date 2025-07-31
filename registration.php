<?php

    require 'vendor/autoload.php';

    use App\Configurations;
    use App\Admins\Admins;

    Configurations::getHeader();
    Configurations::redirectOperators(); 
    
    $roles = Admins::getRolesAdmins(); 
    
    ?>      <div class="registration-block">
                <h3 class="text-center">Регистрация</h3>
                <form class="m-t-50 m-auto" name="registration" method="post" action="/" onsubmit="registrationFunc(event, this)">
                    <div class="login m-b-50">
                        <input type="text" name="login" placeholder="Логин">
                    </div>
                    <div class="password m-b-50 pos-rel" onclick="showHidePassword(event, this)">
                        <input type="password" name="password" placeholder="Пароль">
                        <i class="show-hide fas fa-eye-slash pos-abs"></i>
                    </div>
                    <div class="phone m-b-40">
                        <input type="tel" name="phone" placeholder="Телефон">
                    </div>
                    <div class="admin-roles m-b-50">

                        <?php foreach ($roles as $key => $role) : ?>

                            <div class="radio-<?= $role['role_name']; ?> m-b-20">
                                <input id="<?= $role['role_name']; ?>" type="radio" name="roles-operators" value="<?= $role['role_name']; ?>">
                                <label for="<?= $role['role_name']; ?>"><?= $role['role_description']; ?></label>
                            </div>

                        <?php endforeach; ?>

                    </div>
                    <div class="submit m-b-50">
                        <button>Добавить</button>
                    </div>
                </form>
            </div>
            <script src="/assets/js/registration.js"></script>
        </body>
    </html>