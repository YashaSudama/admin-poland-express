<?php

    require 'vendor/autoload.php';   

    use App\Configurations;
    use App\Admins\Admins;
    use App\Passengers\Passengers;

    Configurations::getHeader(); 
    $booking = Passengers::selectPassenger($_GET['id']);

    $idOperator = Configurations::$userId;
    $roleOperator = Configurations::$userRole;
    $loginOperator = Configurations::$userLogin;
    $date = Configurations::getDate();
    $linkBack = '/'; 
    $routeDirection = (int) $booking['from_poland'] ? 'Из Польши' : 'В Польшу'; 

    if ($date['result']) {
        $result = $date['result'];
        $date = $result['dateFormat'];
        $linkBack = '/day-of-travel-more.php/?day=' . $result['day'] . '&month=' . $result['month'] . '&year=' . $result['year'] . '&from-poland=' . (int) $booking['from_poland'];
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
                <div class="edit-passenger-block m-t-40">
                    <div class="top-block d-flex m-b-20">
                        <div class="back">
                            <a href="<?= $linkBack;?>" title="back">
                                <i class="fas fa-long-arrow-alt-left"></i> 
                            </a>
                        </div> 
                        <div class="date m-auto">
                            <h3 class="text-center">Редактировать за<br><?= $date . ' / ' . $routeDirection; ?></h3>
                        </div>
                    </div>
                    <div class="status-booking-block m-auto m-b-40">

                        <?php if ($booking['cancellation_booking']) : ?>

                            <div class="canceled-booking">
                                <span>Бронь отменена!</span>
                                <button class="restore-booking" 
                                        name="restore-booking"
                                        onclick="editStatusBooking(event, this)"
                                        type="button"
                                        data-id="<?= htmlspecialchars($booking['id']); ?>">
                                    Восстановить
                                </button>
                            </div>

                        <?php else : ?>

                            <div class="booking">

                            <?php if ($booking['admin_role_name'] === 'external-operator') : ?>
                                
                                <span class="received">Приняли от:</span>
                                <span><?= htmlspecialchars($booking['admin_login']); ?></span>

                            <?php else : ?>

                                <?php if ($booking['referal_id']) : ?>

                                    <?php if ($booking['status_referal'] === 'received') : ?>

                                        <span class="received">Приняли от:</span>
                                        <span><?= htmlspecialchars($booking['referal_login']); ?></span>

                                    <?php elseif ($booking['status_referal'] === 'given') : ?>

                                        <span class="given">Отдали:</span>
                                        <span><?= htmlspecialchars($booking['referal_login']); ?></span>

                                    <?php elseif ($booking['status_referal'] === 'received-given') : ?>

                                        <div class="first-received m-b-10">
                                            <span class="">Приняли от:</span>
                                            <span><?= htmlspecialchars($booking['referal_login']); ?></span>
                                        </div>
                                        <div class="then-given">
                                            <span class="">Отдали:</span>
                                            <span><?= htmlspecialchars($booking['referal_login_given']); ?></span>
                                        </div>

                                    <?php endif; ?>

                                <?php else : ?>

                                    <?php if ($booking['admin_role_name'] === 'external-operator') : ?>
                                
                                        <span class="received">Приняли от:</span>

                                    <?php endif; ?>
                                
                                      <span class="our-booking"><?= htmlspecialchars($booking['admin_login']); ?></span>

                                <?php endif; ?>

                            <?php endif; ?>

                            </div>

                        <?php endif; ?>

                    </div>
                    
                    <div class="content">
                        <form class="m-auto"
                              name="edit-passenger" 
                              method="post"
                              action="/"
                              data-id="<?= htmlspecialchars($booking['id']); ?>"
                              onsubmit="addOrEditPassengerFunc(event, this)">
                            <div class="form-block-1 search-route-block pos-rel m-b-50">
                                <input id="route-id" type="hidden" name="route-id" value="<?= htmlspecialchars($booking['route_id']); ?>">
                                <input id="search-route" 
                                       name="search-route" 
                                       type="search" 
                                       placeholder="Маршрут"
                                       value="<?= htmlspecialchars($booking['route_name']); ?>" 
                                       oninput="searchRoute(event, this)">
                                <div id="output-search-routes" class="pos-abs"></div>
                            </div>
                            <div class="form-block-2 d-flex m-b-50">
                                <div class="passenger-or-parcel">
                                    <div class="radio-passenger m-b-20">
                                        <input id="passenger" 
                                               type="radio" 
                                               name="passenger-or-parcel" 
                                               value="passenger" 
                                               <?php if ($booking['passenger_or_parcel'] === 'passenger') echo 'checked'; ?>>
                                        <label for="passenger">Пассажир</label>
                                    </div>
                                    <div class="radio-parcel">
                                        <input id="parcel" 
                                               type="radio" 
                                               name="passenger-or-parcel" 
                                               value="parcel" 
                                               onclick="checkParcel(event, this)"
                                               <?php if ($booking['passenger_or_parcel'] === 'parcel') echo 'checked'; ?>>
                                        <label for="parcel">Посылка</label>
                                    </div>
                                </div>
                                <div class="number-of-seats d-flex" 
                                     onclick="numberOfSeats(event, this)" 
                                     oninput="numberOfSeats(event, this)">
                                    <span class="minus action-minus"><i class="fas fa-minus action-minus"></i></span>
                                    <input id="number" 
                                           type="number" 
                                           name="number-of-seats" 
                                           value="<?= $booking['number_seats']; ?>">
                                    <span class="plus action-plus"><i class="fas fa-plus action-plus"></i></span>
                                </div>
                            </div>
                            <div class="form-block-3 m-b-50">
                                <div class="price-container m-b-50 d-flex">
                                    <input id="price-route" 
                                           type="number" 
                                           name="price-route" 
                                           placeholder="Цена маршрута" 
                                           oninput="amount(event, this)"
                                           value="<?= htmlspecialchars($booking['price_route']); ?>">
                                    <span class="amount-route">Сумма <?= $booking['number_seats'] * $booking['price_route']; ?> грн</span>
                                </div>
                                <div class="phone-container">
                                    <div class="m-b-30">
                                        <input type="checkbox" 
                                               id="without-phone"
                                               name="without-phone" 
                                               onclick="withoutPhone(event, this)"
                                               <?php if (!$booking['phones']) echo 'checked'; ?>>
                                        <label for="without-phone">Без телефона</label>
                                    </div>

                                    <?php foreach (explode(',', $booking['phones']) as $phone) : ?>

                                        <div class="phone d-flex m-b-30 <?php if (!$booking['phones']) echo 'd-none'; ?>">
                                            <input type="tel" 
                                                   name="phone[]" 
                                                   placeholder="Телефон"
                                                   value="<?= htmlspecialchars($phone); ?>">
                                            <i class="fas fa-plus" onclick="addPhone(event, this)"></i>
                                        </div>

                                    <?php endforeach; ?>

                                </div>
                                <div class="name m-t-50 m-b-50">
                                    <input type="text" name="name" placeholder="Имя" value="<?= htmlspecialchars($booking['name']); ?>">
                                </div>
                                <div class="comment">
                                    <textarea name="comment" placeholder="Комментарий"><?= $booking['comment']; ?></textarea>
                                </div>
                            </div>
                            <div class="form-block-4 m-b-50">
                                <div class="prepayment m-b-50">
                                    <input class="m-b-50" 
                                           type="number" 
                                           name="prepayment" 
                                           placeholder="Предоплата"
                                           value="<?= htmlspecialchars($booking['prepayment']); ?>">
                                    <textarea name="prepayment-comment" placeholder="Комментарий к предоплате"><?= $booking['prepayment_comment']; ?></textarea>
                                </div>

                                <div class="select-wrapper pos-rel received m-b-50">
                                    <select id="received" name="received">
                                        <option value="">Принять</option>
                                        
                                    <?php foreach ($externalOperators as $operator): ?>

                                        <option value="<?= htmlspecialchars($operator['id']); ?>"
                                        
                                        <?php if (($booking['status_referal'] === 'received' ||
                                                   $booking['status_referal'] === 'received-given') &&
                                                  ($operator['id'] === $booking['admin_id']  ||
                                                   $operator['id'] === $booking['referal_id']))
                                                echo 'selected'; ?>>

                                            <?= htmlspecialchars($operator['login']); ?> 
                                        </option>

                                    <?php endforeach; ?>

                                    </select>
                                </div>

                                <div class="select-wrapper pos-rel given">
                                    <select id="given" name="given">
                                        <option value="">Отдать</option>

                                        <?php foreach ($externalOperators as $operator): ?>

                                            <option value="<?= htmlspecialchars($operator['id']); ?>"
                                            
                                                <?php if (($booking['status_referal'] === 'given' &&
                                                          $operator['id'] === $booking['referal_id']) ||
                                                          ($booking['status_referal'] === 'received-given' &&
                                                          $operator['id'] === $booking['referal_id_given'])) 
                                                        echo 'selected'; ?>>

                                                    <?= htmlspecialchars($operator['login']); ?>
                                                </option>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>
                                </div>
                            </div>
                            <div class="form-block-5 m-b-50">
                                <div class="date-container m-b-50">
                                    <input type="date" name="date" value="<?= htmlspecialchars($booking['date']); ?>">
                                </div>

                                <?php if (!$booking['cancellation_booking']) : ?>

                                    <div class="button-container d-flex">
                                        <button type="submit">Редактировать</button>
                                        <button class="canceled-booking" 
                                                name="canceled-booking" 
                                                onclick="editStatusBooking(event, this)" 
                                                type="button"
                                                data-id="<?= htmlspecialchars($booking['id']); ?>">
                                            Отменить бронь
                                        </button>
                                    </div>

                                <?php endif; ?>

                            </div>
                            <input hidden name="role-admin-id" value="<?= $booking['admin_role_name']; ?>">
                            <input hidden name="referal-id" value="<?= $booking['referal_id']; ?>">
                            <input hidden name="status-referal" value="<?= $booking['status_referal']; ?>">
                        </form>
                    </div>
                </div>
            </div>
            <script src="/assets/js/add-edit-passenger.js"></script>
        </body>
    </html>