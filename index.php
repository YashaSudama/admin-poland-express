<?php
require 'vendor/autoload.php';

use App\LaunchingApplication;
use App\Configurations;

Configurations::getHeader(); ?>

            <div id="calendar" class="m-t-40 m-auto">
                <div class="d-flex calendar-top">
                    <div class="prev"><i class="fas fa-angle-double-left"></i></div> 
                    <div class="title"></div>
                    <div class="next"><i class="fas fa-angle-double-right"></i></div>
                </div>
                <div class="d-flex calendar-middle m-t-20"></div>
                <ul class="d-flex calendar-content m-t-20"></ul>
            </div>
        </div>
        <script src="/assets/js/calendar.js"></script>
    </body>
</html>

<?php new LaunchingApplication();