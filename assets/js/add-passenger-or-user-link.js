"use strict";

function addPassengerOrUserLink(event, elem) {
    let location = window.location,
        checkDirection = elem.querySelector('.check-direction'), 
        params = new URLSearchParams(window.location.search),
        day = params.get('day'),
        month = params.get('month'),
        year = params.get('year'),
        fromPoland = params.get('from-poland'),
        getRequestPart = '/add-passenger.php/?day=' + encodeURIComponent(day) +
                                            '&month=' + encodeURIComponent(month) +
                                            '&year=' + encodeURIComponent(year) + 
                                            '&from-poland=',
        target = event.target;
    
    if (location.pathname.includes('day-of-travel.php')) {

        requestAnimationFrame(() => {

            if (checkDirection.classList.contains('d-none')) {
                checkDirection.classList.remove('d-none')

                setTimeout(() => {
                    checkDirection.style.zIndex = '10';
                    checkDirection.style.opacity = '1';
                }, 500);
            }

        });

    } else if (location.pathname.includes('users.php')) {
        location.href = '/registration.php';
     } else {
        location.href = getRequestPart + encodeURIComponent(fromPoland);
    }

    if (target.classList.contains('close')) {
        
        if (!checkDirection.classList.contains('d-none')) {
            checkDirection.style.opacity = '0';
            
            setTimeout(() => {
                checkDirection.classList.add('d-none')
                checkDirection.style.zIndex = '-1';
            }, 500);

        }

    } else if (target.classList.contains('to-poland')) {
        location.href = getRequestPart + encodeURIComponent(0);
    } else if (target.classList.contains('from-poland')) {
        location.href = getRequestPart + encodeURIComponent(1);
    }

}
