<?php 

    require 'vendor/autoload.php';

    use App\Configurations;
    use App\Admins\Admins;

    $operators = Admins::getAdmins();

    Configurations::getHeader(); 
    Configurations::redirectOperators();
    
?>              
            <div class="users-block m-auto">
                <h3 class="text-center">Операторы</h3>

                    <?php foreach ($operators as $operator) : 
                        $phone = !empty($operator['phone']) ? $operator['phone'] : '--------'; ?>

                        <table class="m-b-50 m-t-50">
                            <tbody>
                                <tr><th>Логин</th></tr>
                                <tr><td><?= $operator['login']; ?></td></tr>
                                <tr><th>Телефон</th></tr>
                                <tr><td><?= $phone; ?></td></tr>
                                <tr><th>Роль</th></tr>
                                <tr><td><?= $operator['role_description']; ?></td></tr>
                            </tbody>
                        </table>
                            
                    <?php endforeach; ?>
            </div>
            <?php Configurations::addPassengerOrUserHTML(); ?>
            <script src="/assets/js/add-passenger-or-user-link.js"></script>
        </body>
    </html>