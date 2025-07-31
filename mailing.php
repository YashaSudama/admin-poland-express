<?php

    require 'vendor/autoload.php';

    use App\Configurations;

    Configurations::getHeader();
    Configurations::redirectOperators();
    
?>

            <div class="mailing-block">
                <h3 class="text-center">Рассылка</h3>
                <form class="m-t-50 m-auto" name="mailing" method="post" action="/" onsubmit="mailingFunc(event, this)">
                    <div class="text m-b-50">
                        <textarea name="text-mailing" placeholder="Текст рассылки"></textarea>
                    </div>
                    <div class="submit m-b-50">
                        <button>Рассылка</button>
                    </div>
                </form>
            </div>
            <script src="/assets/js/mailing.js"></script>
        </body>
    </html>