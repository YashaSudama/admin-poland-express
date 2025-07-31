<?php

    require 'vendor/autoload.php';   

    use App\Configurations;
    use App\Admins\Admins;

    Configurations::getHeader(); 
    $idOperator = Configurations::$userId;
    $roleOperator = Configurations::$userRole;
    $loginOperator = Configurations::$userLogin;
    $date = Configurations::getDate();
    $linkBack = '/'; 
    $routeDirection = (int)$_GET['from-poland'] ? 'Из Польши' : 'В Польшу';  

    if ($date['result']) {
        $result = $date['result'];
        $date = $result['dateFormat'];
        $linkBack = '/day-of-travel-more.php/?day=' . $_GET['day'] . '&month=' . $result['month'] . '&year=' . $result['year'] . '&from-poland=' . $_GET['from-poland'];
    } else {
        Configurations::messageOutput($date['error'], 'error');
        $date = '';
    } 

    $operators = Admins::getAdmins();
    $externalOperators = [];
    $internalOperators = [];

    foreach ($operators as $operator) {

        if ($operator['role_name'] === 'external-operator') {
            $externalOperators[] = $operator;
        } else {
            $internalOperators[] = $operator; 
        }

    }

?>              
                <div class="add-passenger-block m-t-40">
                    <div class="top-block d-flex m-b-20">
                        <div class="back">
                            <a href="<?= $linkBack;?>" title="back">
                                <i class="fas fa-long-arrow-alt-left"></i>
                            </a>
                        </div> 
                        <div class="date m-auto">
                            <h3 class="text-center">Добавить на<br><?= $date . ' / ' . $routeDirection; ?></h3>
                        </div>
                    </div>
                    
                    <div class="content">
                        <form class="m-auto"
                              name="add-passenger" 
                              method="post" 
                              action="/"
                              onsubmit="addOrEditPassengerFunc(event, this)">
                            <div class="form-block-1 search-route-block pos-rel m-b-50">
                                <input id="route-id" type="hidden" name="route-id">
                                <input id="search-route" 
                                        name="search-route" 
                                        type="search" 
                                        placeholder="Маршрут" 
                                        oninput="searchRoute(event, this)">
                                <div id="output-search-routes" class="pos-abs"></div>
                            </div>
                            <div class="form-block-2 d-flex m-b-50">
                                <div class="passenger-or-parcel">
                                    <div class="radio-passenger m-b-20">
                                        <input id="passenger" type="radio" name="passenger-or-parcel" value="passenger" checked>
                                        <label for="passenger">Пассажир</label>
                                    </div>
                                    <div class="radio-parcel">
                                        <input id="parcel" type="radio" name="passenger-or-parcel" value="parcel" onclick="checkParcel(event, this)">
                                        <label for="parcel">Посылка</label>
                                    </div>
                                </div>
                                <div class="number-of-seats d-flex" 
                                     onclick="numberOfSeats(event, this)" 
                                     oninput="numberOfSeats(event, this)">
                                    <span class="minus action-minus"><i class="fas fa-minus action-minus"></i></span>
                                    <input id="number" type="number" name="number-of-seats" value="1">
                                    <span class="plus action-plus"><i class="fas fa-plus action-plus"></i></span>
                                </div>
                            </div>
                            <div class="form-block-3 m-b-50">
                                <div class="price-container m-b-50 d-flex">
                                    <input id="price-route" type="number" name="price-route" placeholder="Цена маршрута" oninput="amount(event, this)">
                                    <span class="amount-route">Сумма 0.00 грн</span>
                                </div>
                                <div class="phone-container">
                                    <div class="m-b-30">
                                        <input type="checkbox" 
                                               id="without-phone"
                                               name="without-phone" 
                                               onclick="withoutPhone(event, this)">
                                        <label for="without-phone">Без телефона</label>
                                    </div>
                                    <div class="phone d-flex m-b-30">
                                        <input type="tel" 
                                               name="phone[]" 
                                               placeholder="Телефон">
                                        <i class="fas fa-plus" onclick="addPhone(event, this)"></i>
                                    </div>
                                </div>
                                <div class="name m-t-50 m-b-50">
                                    <input type="text" name="name" placeholder="Имя">
                                </div>
                                <div class="comment">
                                    <textarea name="comment" placeholder="Комментарий"></textarea>
                                </div>
                            </div>
                            <div class="form-block-4 m-b-50">
                                <div class="prepayment m-b-50">
                                    <input class="m-b-50" type="number" name="prepayment" placeholder="Предоплата">
                                    <textarea name="prepayment-comment" placeholder="Комментарий к предоплате"><?php if ($roleOperator === 'external-operator') echo 'У ' . $loginOperator; ?></textarea>
                                </div>

                                <div class="select-wrapper pos-rel received m-b-50">
                                    <select id="received" name="received">
                                        <option value="">Принять</option>
                                        
                                    <?php foreach ($externalOperators as $operator): ?>

                                        <option value="<?= htmlspecialchars($operator['id']) ?>">
                                            <?= htmlspecialchars($operator['login']) ?> 
                                        </option>

                                    <?php endforeach; ?>

                                    </select>
                                </div>

                                <?php if ($roleOperator !== 'external-operator') : ?>

                                    <div class="select-wrapper pos-rel given">
                                        <select id="given" name="given">
                                            <option value="">Отдать</option>

                                            <?php foreach ($externalOperators as $operator): ?>

                                                <option value="<?= htmlspecialchars($operator['id']) ?>">
                                                    <?= htmlspecialchars($operator['login']) ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>
                                    </div>

                                <?php endif; ?>

                            </div>
                            <div class="form-block-5 m-b-50">
                                <button type="submit">Добавить</button>

                                <?php if ($roleOperator === 'super-admin' || $roleOperator === 'admin') : ?>

                                    <div class="add-operator-without-password-block" onclick="addOperatorWithoutPassword(event, this)">
                                        <div class="add-operator-without-password">
                                            <i class="fas fa-plus"></i>
                                        </div>
                                        <div class="add-operator-without-password-form pos-fixed d-none">
                                            <i class="close fas fa-times pos-abs"></i>
                                            <div class="pos-abs">
                                                <input type="text" name="login" placeholder="Логин">
                                                <button class="add-operator m-t-30" type="button">Добавить</button> 
                                            </div>
                                        </div>
                                    </div>

                                <?php endif; ?>
                            </div>
                            <input hidden name="admin-id" value="<?= $idOperator; ?>">
                            <input hidden name="role-admin-id" value="<?= $roleOperator; ?>">
                        </form>
                    </div>
                </div>
            </div>
            <script src="/assets/js/add-edit-passenger.js"></script>
        </body>
    </html>