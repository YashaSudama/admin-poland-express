<?php
    require 'vendor/autoload.php';

    use App\Configurations;
    use App\Passengers\Passengers;

    Configurations::getHeader(); 
    $operatorId = (int) Configurations::$userId;
    $roleOperator = Configurations::$userRole;
    $loginOperator = Configurations::$userLogin; 
    $date = Configurations::getDate();
    $linkBack = '/'; 
    $dataDayTravelMore = Passengers::getPassengersDayMore();
    $routeDirection = (int)$_GET['from-poland'] ? 'Из Польши' : 'В Польшу';

    if ($date['result']) {
        $result = $date['result'];
        $date = $result['dateFormat'];
        $linkBack = '/day-of-travel.php/?month=' . $result['month'] . '&year=' . $result['year'] . '&day=' . $result['day'];
    } else {
        Configurations::messageOutput($date['error'], 'error');
        $date = '';
    }
    
?>
                <div class="day-of-travel-more-block m-t-40 m-b-50">
                    <div class="top-block d-flex m-b-20">
                        <div class="back">
                            <a href="<?= $linkBack;?>" title="back">
                                <i class="fas fa-long-arrow-alt-left"></i>
                            </a>
                        </div> 
                        <div class="date m-auto"><h3><?= $date . ' / ' . $routeDirection; ?></h3></div> 
                    </div>
                    
                    <div class="content">

                        <?php if (count($dataDayTravelMore)) : ?>

                            <?php foreach ($dataDayTravelMore as $value) : ?>
                            
                                <div class="passenger pos-rel m-b-30<?php if ($value['cancellation_booking']) echo ' canceled'; ?>" 
                                     data-id="<?= $value['id']; ?>">
                                    
                                    <?php if ($value['cancellation_booking']) : ?>
                                        <div class="canceled-booking m-b-10"><span>Отменена</span></div>
                                    <?php endif; ?>

                                    <div class="day-of-travel-more-block-1 d-flex">
                                        <div class="number-seats d-flex">

                                            <?php if ($value['passenger_or_parcel'] === 'passenger') : ?>

                                                <i class="fas fa-user-alt"></i>
                                                <span class="bold"><?= $value['number_seats'];?></span>
                                            
                                            <?php else : ?>

                                                <i class="fas fa-cube"></i>

                                            <?php endif; ?>

                                        </div>
                                        <div class="route-phone-name">
                                            <div class="route">
                                                <h4><?= $value['route_name'];?></h4>
                                            </div>
                                            <div class="phones-name m-t-10">
                                                
                                                <?php if ($value['phones']) : ?>

                                                    <?php foreach (explode(',', $value['phones']) as $phone) : 
                                                        $phone = (int) $value['admin_id'] === $operatorId   ||
                                                                 (int) $value['referal_id'] === $operatorId ||
                                                                    $roleOperator === 'super-admin' ?       
                                                                                        $phone :
                                                                                        substr($phone, 0, -4) . "****"; ?>

                                                        <div class="m-b-10">
                                                            <i class="fas fa-phone-alt with-phone"></i>
                                                            <span class="phone"><?= $phone; ?></span>
                                                        </div>
                                                        
                                                    <?php endforeach; ?>

                                                <?php else : ?>

                                                    <div class="m-b-10">
                                                        <i class="fas fa-phone-slash without-phone"></i>
                                                        <span class="phone">Нет телефона</span>
                                                    </div>
                                                    
                                                <?php endif; ?>
                                                    
                                                <span class="name bold"><?= $value['name']; ?></span>

                                            </div>
                                        </div>
                                        <div class="route-price">
                                            <span class="d-block m-b-10"><b><?= $value['price_route']; ?></b> грн</span>
                                            <span class="d-block">
                                                <span class="amount-word">Сумма:</span><br>
                                                <b class="amount"><?= $value['price_route'] * $value['number_seats']; ?></b> грн
                                            </span>
                                        </div>
                                    </div>
                                    <div class="day-of-travel-more-block-2 m-t-20">

                                    <?php if ($value['comment']) : ?>

                                        <div class="comment m-b-20">
                                            <span><?= $value['comment']; ?></span>
                                        </div>

                                    <?php endif; ?>

                                    <?php if ($value['admin_role_name'] === 'external-operator' || isset($value['referal_id'])) : ?>

                                        <div class="referal d-flex" data-id="<?= $value['id']; ?>">

                                            <?php if ($value['status_referal'] !== 'received-given') : ?>
                                    
                                                <?php 
                                                    $classOperator = $value['status_referal'] === 'received' ? 'received' : 'given';
                                                    $textOperator =  $value['status_referal'] === 'received' ? 'Приняли от:' : 'Отдали:';
                                                    $login =         $value['admin_role_name'] === 'external-operator' ? $value['admin_login'] : $value['referal_login']; 
                                                ?>

                                                <div class="operator <?= $classOperator; ?>">
                                                    <div class="info-operator info-<?= $classOperator; ?> d-flex">
                                                        <i class="fas fa-location-arrow pos-rel"></i>
                                                        <div>
                                                            <span class="d-block"><?= $textOperator; ?></span>
                                                            <span class="d-block m-t-5"><?= $login; ?></span>
                                                        </div>
                                                    </div>
                                                </div>

                                            <?php else : ?>

                                                <div>
                                                    <div class="operator received m-b-10">
                                                        <div class="info-operator info-received d-flex">
                                                            <i class="fas fa-location-arrow pos-rel"></i>
                                                            <div>
                                                                <span class="d-block">Приняли от:</span>
                                                                <span class="d-block m-t-5"><?= $value['referal_login']; ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="operator given">
                                                        <div class="info-operator info-given d-flex">
                                                            <i class="fas fa-location-arrow pos-rel"></i>
                                                            <div>
                                                                <span class="d-block">Отдали:</span>
                                                                <span class="d-block m-t-5"><?= $value['referal_login_given']; ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            <?php endif; ?>

                                    <?php endif; ?>

                                            <div class="prepayment">
                                                <div class="d-flex">
                                                    <i class="fas fa-money-check-alt pos-rel"></i>
                                                    <div>
                                                        <span class="d-block m-b-5">Предоплата:</span>
                                                        <span class="d-block"><?= $value['prepayment']; ?> грн</span>
                                                    </div>
                                                </div>
                                                <?php if ($value['prepayment'] && $value['prepayment_comment']) : ?>

                                                    <div class="prepayment-comment m-t-5">
                                                        <span><?= $value['prepayment_comment']; ?></span>
                                                    </div>

                                                <?php endif; ?>

                                                
                                            </div>

                                        <?php if ($value['admin_role_name'] === 'external-operator' || isset($value['referal_id'])) : ?>

                                        </div>

                                        <?php endif; ?>

                                    </div>

                                    <?php if ((int) $value['admin_id'] === $operatorId   ||
                                              (int) $value['referal_id'] === $operatorId ||
                                              $roleOperator === 'super-admin') : ?>

                                        <div class="edit-passenger pos-abs">
                                            <a href="/edit-passenger.php?id=<?= $value['id']; ?>&month=<?= $result['month']; ?>&year=<?= $result['year']; ?>&day=<?= $result['day']; ?>" 
                                               title="Редактировать бронь">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                        </div>

                                    <?php endif; ?>

                                </div>

                            <?php endforeach; ?>

                        <?php else : ?>
                             <div class="passenger text-center">
                                <h4>Нет заказов</h4>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
            <?php Configurations::addPassengerOrUserHTML(); ?>
            <script src="/assets/js/add-passenger-or-user-link.js"></script>
        </body>
    </html>