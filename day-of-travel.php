<?php
    require 'vendor/autoload.php';

    use App\Configurations;
    use App\Passengers\Passengers;

    Configurations::getHeader(); 
    $date = Configurations::getDate();
    $linkBack = $linkForwards = '/';
    $dataDayTravel = Passengers::getPassengersDay(); 

    if ($date['result']) {
        $result = $date['result'];
        $date = $result['dateFormat'];
        $linkBack = '/?month=' . $result['month'] . '&year=' . $result['year'];
        $linkForwards = '/day-of-travel-more.php' . $linkBack . '&day=' . $result['day'] . '&from-poland=';
    } else {
        Configurations::messageOutput($date['error'], 'error');
        $date = '';
    }

    $sumPassengersToPoland = 0;
    $sumParcelsToPoland = 0;
    $sumReferalToPolandReceived = 0;
    $sumReferalToPolandGiven = 0;

    $sumPassengersFromPoland = 0;
    $sumParcelsFromPoland = 0;
    $sumReferalFromPolandReceived = 0;
    $sumReferalFromPolandGiven = 0;

    foreach ($dataDayTravel as $key => $value) {
        
        if (!$value['from_poland']) { // В Польшу

            if ($value['passenger_or_parcel'] === 'parcel') $sumParcelsToPoland += 1;
            
            if ($value['passenger_or_parcel'] === 'passenger' && 
                ($value['status_referal'] === 'received' || !$value['status_referal'])) {
                $sumPassengersToPoland += $value['number_seats'];
            }

            if ($value['admin_role_name'] === 'external-operator') {
                $sumReferalToPolandReceived += $value['number_seats'];
            } else {

                if ($value['referal_id']) {

                    if ($value['status_referal'] === 'received') {
                        $sumReferalToPolandReceived += $value['number_seats'];
                    } else {
                        $sumReferalToPolandGiven += $value['number_seats'];
                    }
                    
                }

            }

        } else {

            if ($value['passenger_or_parcel'] === 'parcel') $sumParcelsFromPoland += 1;

            if ($value['passenger_or_parcel'] === 'passenger' && 
                ($value['status_referal'] === 'received' || !$value['status_referal'])) {
                $sumPassengersFromPoland += $value['number_seats'];
            }

            if ($value['admin_role_name'] === 'external-operator') {
                $sumReferalFromPolandReceived += $value['number_seats'];
            } else {

                if ($value['referal_id']) {

                    if ($value['status_referal'] === 'received') {
                        $sumReferalFromPolandReceived += $value['number_seats'];
                    } else {
                        $sumReferalFromPolandGiven += $value['number_seats'];
                    }
                    
                }

            }

        }

    }

?>

            <div class="day-of-travel-block m-t-40 m-b-50">
                <div class="top-block d-flex m-b-20">
                    <div class="back">
                        <a href="<?= $linkBack;?>" title="back">
                            <i class="fas fa-long-arrow-alt-left"></i>
                        </a>
                    </div>
                    <div class="date m-auto"><h3><?= $date; ?></h3></div>
                </div>
                
                <div class="content">
                    <div class="to-poland-day-of-travel">
                        <h3><a href="<?= $linkForwards . '0'; ?>" class="d-block">В Польшу</a></h3>
                        <div class="info-day-of-travel d-flex">

                            <?php if (!$sumPassengersToPoland      && 
                                      !$sumParcelsToPoland         &&
                                      !$sumReferalToPolandReceived &&
                                      !$sumReferalToPolandGiven) : ?>

                                <div class="text-center"><span>Нет заказов</span></div>

                            <?php else : ?>

                                <div class="passengers-parcels d-flex">
                                    <div class="passengers d-flex">
                                        <i class="fas fa-user-alt"></i>
                                        <span class="bold"><?= $sumPassengersToPoland;?></span>
                                    </div>
                                    <div class="parcels d-flex">
                                        <i class="fas fa-cube"></i>
                                        <span class="bold"><?= $sumParcelsToPoland;?></span>
                                    </div>
                                </div>
                                <div class="referals d-flex">
                                    <div class="received"> 
                                        <i class="fas fa-location-arrow pos-rel"></i>
                                        <span class="bold"><?= $sumReferalToPolandReceived; ?></span> 
                                    </div>
                                    <div class="given">
                                        <i class="fas fa-location-arrow pos-rel"></i>
                                        <span class="bold"><?= $sumReferalToPolandGiven;?></span>
                                    </div>
                                </div>

                            <?php endif; ?>

                        </div>
                    </div>
                    <div class="from-poland-day-of-travel">
                        <h3><a href="<?= $linkForwards . '1'; ?>" class="d-block">Из Польши</a></h3>
                        <div class="info-day-of-travel d-flex">

                            <?php if (!$sumPassengersFromPoland      && 
                                      !$sumParcelsFromPoland         &&
                                      !$sumReferalFromPolandReceived &&
                                      !$sumReferalFromPolandGiven) : ?>

                                <div class="text-center"><span>Нет заказов</span></div>

                            <?php else : ?>

                                <div class="passengers-parcels d-flex">
                                    <div class="passengers d-flex">
                                        <i class="fas fa-user-alt"></i>
                                        <span class="bold"><?= $sumPassengersFromPoland;?></span>
                                    </div>
                                    <div class="parcels d-flex">
                                        <i class="fas fa-cube"></i>
                                        <span class="bold"><?= $sumParcelsFromPoland;?></span>
                                    </div>
                                </div>
                                <div class="referals d-flex">
                                    <div class="received">
                                        <i class="fas fa-location-arrow pos-rel"></i>
                                        <span class="bold"><?= $sumReferalFromPolandReceived;?></span>
                                    </div>
                                    <div class="given">
                                        <i class="fas fa-location-arrow pos-rel"></i>
                                        <span class="bold"><?= $sumReferalFromPolandGiven;?></span>
                                    </div>
                                </div>

                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php Configurations::addPassengerOrUserHTML(); ?>
        <script src="/assets/js/add-passenger-or-user-link.js"></script>
    </body>
</html>

