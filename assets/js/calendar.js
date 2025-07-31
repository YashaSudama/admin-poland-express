"use strict"

window.addEventListener('load', () => {
    calendarInit();
});

function calendarInit() {
    const calendar = document.getElementById('calendar'),
          calendarTitle = calendar.querySelector('.title'),
          calendarMiddle = calendar.querySelector('.calendar-middle'),
          calendarContent = calendar.querySelector('.calendar-content'),
          params = new URLSearchParams(window.location.search);

    let nowDate = new Date(), 
        nowYear = params.get('year') || nowDate.getFullYear(),
        nowMonth = params.get('month') || nowDate.getMonth(),
        monthName = [ 'Январь', 
                      'Февраль',
                      'Март',
                      'Апрель',
                      'Май',
                      'Июнь',
                      'Июль',
                      'Август',
                      'Сентябрь',
                      'Октябрь',
                      'Ноябрь',
                      'Декабрь' ],
        dayName = [ 'Пн',
                    'Вт',
                    'Ср',
                    'Чт',
                    'Пт',
                    'Сб',
                    'Вс' ],
        timeout = 100;

    for (let day of dayName) {
        calendarMiddle.innerHTML += '<span class="day-week">' + day + '</span>';
    }

    nowMonth = typeof nowMonth === 'string' ? Number(nowMonth) :nowMonth;
    nowYear = typeof nowYear === 'string' ? Number(nowYear) : nowYear;
                         
    printMonth(nowMonth, nowYear);

    calendar.onclick = function(event) {
        let target = event.target;

        if (target.classList.contains('prev') || target.closest('.prev')) {
            nowYear = nowMonth === 0 ? nowYear - 1 : nowYear;
            nowMonth = nowMonth === 0 ? 11 : nowMonth - 1;
            calendarTitle.style.opacity = 0;
            calendarContent.style.opacity = 0;

            setTimeout(() => {
                calendarTitle.innerHTML = '';
                calendarContent.innerHTML = '';

                printMonth(nowMonth, nowYear);

            }, timeout * 2);
            
        } else if (target.classList.contains('next') || target.closest('.next')) {
            nowYear = nowMonth === 11 ? nowYear + 1 : nowYear;
            nowMonth = nowMonth === 11 ? 0 : nowMonth + 1;
            calendarTitle.style.opacity = 0;
            calendarContent.style.opacity = 0;

            setTimeout(() => {
                calendarTitle.innerHTML = '';
                calendarContent.innerHTML = '';

                printMonth(nowMonth, nowYear);

            }, timeout * 2);
            
        } else if (target.classList.contains('current-month') || target.closest('.current-month')) {
            let monthNumber = target.closest('.current-month').dataset.monthnumber || target.dataset.monthnumber;
            
            window.location.href = 'day-of-travel.php?' +
                                    'day=' + encodeURIComponent(monthNumber) +
                                    '&month=' + encodeURIComponent(nowMonth) + 
                                    '&year=' + encodeURIComponent(nowYear);

        }

    }

    function printMonth(month, year) {
        let firstDayMonth = new Date( year, month, 1 ).getDay() === 0 ? 7 : new Date( year, month, 1 ).getDay(),
            daysMonth = getMonthDays( month, nowYear ),
            daysPrevMonth = month === 0 ? getMonthDays( 11, year - 1 ) : getMonthDays( month - 1, year ),
            autoCompleteBefore = (daysPrevMonth - firstDayMonth) + 2,
            autoCompleteAfter = (firstDayMonth - 1) + daysMonth,
            numberСells = autoCompleteAfter > 35 ? 42 : autoCompleteAfter > 28 ? 35 : 28,
            localYear = nowDate.getFullYear(),
            localMonth = nowDate.getMonth(),
            nowDateNumber = month === localMonth && year === localYear ? nowDate.getDate() : 0;
        
        calendarTitle.innerHTML = '<h3><span class="month">' + monthName[month] + '</span> ' + 
                                  '<span class="year">' + year + '</span></h3>';

        for (let i = autoCompleteBefore; i <= daysPrevMonth; i++) {
            calendarContent.innerHTML += '<li class="day-month-block prev-month text-center"><span>' +  + i + '</span></li>';
        }

        for (let i = 1; i <= daysMonth; i++) {
            let classes = 'day-month-blok current-month text-center';
            
            classes += i === nowDateNumber ? ' now-date' : '';

            calendarContent.innerHTML += '<li class="' + classes + '" data-monthnumber="' + i + '">' +
                                            '<span class="day-month bold">' + i + '</span>' +
                                         '</li>';
        }

        for (let i = 1; i <= (numberСells - autoCompleteAfter); i++) {
            calendarContent.innerHTML += '<li class="day-month-block next-month text-center"><span>' + i + '</span></li>';
        }

        if (calendarContent.style.opacity === '0' && calendarTitle.style.opacity === '0') {

            setTimeout(() => {
                calendarTitle.style.opacity = 1;
                calendarContent.style.opacity = 1;
            }, timeout * 2);

        }

        fetch('/App/Api.php?class=Passengers&method=getPassengersMonth&month=' + month + '&year=' + year, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {

            if (data.result) {
                let passengers = data.result,
                    currentMonth = calendarContent.querySelectorAll('.current-month');
                
                for (let day of currentMonth) {
                    let dayNumber = day.querySelector('.day-month').innerHTML,
                        date = new Date(year, month, dayNumber),
                        dateFormat = date.getFullYear() + '-' + 
                                     String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                                     String(date.getDate()).padStart(2, '0'),
                        toPoland = passengers.filter(item => 
                                                     item.date === dateFormat &&
                                                     !item.from_poland
                                                    ),
                        fromPoland = passengers.filter(item => 
                                                       item.date === dateFormat &&
                                                       item.from_poland
                                                     ),
                        result = 0,
                        line = '';

                    if (toPoland.length > 0) {
                        result = toPoland.reduce((sum, item) => sum + Number(item.number_seats), 0);
                        day.innerHTML += '<span class="to-poland"><i>' + result + '/</i></span>'; 
                    }

                    if (fromPoland.length > 0) {

                        if (toPoland.length === 0) line = '/';
                            
                        result = fromPoland.reduce((sum, item) => sum + Number(item.number_seats), 0);
                        day.innerHTML += '<span class="from-poland"><i>' + line + result + '</i></span>';                    
                    }

                }

            } else if (data.error) {
                messageOutput(data.error, 'error');
            } else {
                messageOutput('Не удалось получить пассижиров!', 'error');
            }

        });

    }

}

function getMonthDays( month, year ) {
    let monthDays;

    if ( month === 0 || 
         month === 2 || 
         month === 4 ||
         month === 6 || 
         month === 7 || 
         month === 9 || 
         month === 11 ) {
   
       monthDays = 31;
   
   } else if ( month === 3 || 
               month === 5 || 
               month === 8 || 
               month === 10 ) {
   
      monthDays = 30;
   
   } else if ( month === 1 ) {
     
      if ( ( year % 4 ) === 0 ) {
          monthDays = 29;
      } else {
          monthDays = 28;
      }
   
   }

   return monthDays;

}

