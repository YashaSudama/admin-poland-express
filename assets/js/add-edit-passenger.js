"use strict";

function addOrEditPassengerFunc(event, elem) {
    event.preventDefault();

    let nameForm = elem.getAttribute('name');

    elem.querySelector('button').setAttribute('disabled', 'true');

    let inputs = elem.querySelectorAll('input'),
        selects = elem.querySelectorAll('select'),
        textarea = elem.querySelectorAll('textarea');

    for (let input of inputs) {
        if (input.classList.contains('error-form')) input.classList.remove('error-form');
    }

    for (let select of selects) {
        if (select.classList.contains('error-form')) select.classList.remove('error-form');
    }

    for (let area of textarea) {
        if (area.classList.contains('error-form')) area.classList.remove('error-form');
    }

    let formData = new FormData(elem),
        method = nameForm === 'add-passenger' ? 'addPassenger' : 'editPassenger';

    if (nameForm === 'add-passenger') {

        let params = new URLSearchParams(window.location.search),
            day = params.get('day'),
            month = params.get('month'),
            year = params.get('year'),
            fromPoland = params.get('from-poland');

        formData.append('day', day);
        formData.append('month', month);
        formData.append('year', year);
        formData.append('from-poland', fromPoland);
    } else if (nameForm === 'edit-passenger') {
        let id = elem.dataset.id;

        formData.append('id', id);
    }

    const jsonData = formDataToJson(formData);
    
    fetch('/App/Api.php?class=Passengers&method=' + method, {
        method: 'POST',
        body: JSON.stringify(jsonData)
    })
    .then(res => res.json())
    .then(data => {
        let message = '';
        
        if (data.result) {
            message = nameForm === 'add-passenger' ? 'Бронь успешно добавлена!' : 'Бронь успешно обновлена!';

            messageOutput(message, 'success');

            setTimeout(() => {
                elem.scrollIntoView({behavior: 'smooth'});
            }, 1000);
            
        } else if (data.novalidate) {

            for (let type in data.novalidate) {
                let typeObj = data.novalidate[type];

                for (let item in typeObj) {
                    let input;

                    if (item === 'phone[]') {
                        input = elem.querySelectorAll('[name="' + item + '"]');

                        for (let subItem in typeObj[item]) {
                            
                            if (!input[+subItem].classList.contains('error-form')) input[+subItem].classList.add('error-form');

                            messageOutput(typeObj[item][subItem] + ' (' + (+subItem + 1) + ')', 'error');

                        }

                    } else {
                        input = elem.querySelector('[name="' + item + '"]');

                        if (typeObj[item] === '') {

                            if (!input.classList.contains('error-form')) input.classList.add('error-form');
                            
                            continue;
                        } else {

                            if (!input.classList.contains('error-form')) input.classList.add('error-form'); 

                            messageOutput(typeObj[item], 'error');
                        }

                    }

                }

            }

            setTimeout(() => {
                elem.scrollIntoView({behavior: 'smooth'});
            }, 1000);
            
        } else if (data.info) {
            messageOutput(data.info, 'info');
        } else if (data.error) {
            messageOutput(data.error, 'error');
        } else {
            message = nameForm === 'add-passenger' ? 'Ошибка! Не удалось добавить бронь!' : 'Ошибка! Не удалось обновить бронь!';

            messageOutput(message, 'error');
        }
        
        setTimeout(() => {
            elem.querySelector('button').removeAttribute('disabled');
        }, 2000);

    });
    
}

function formDataToJson(formData) {
    let obj = {};
    
    for (let [key, value] of formData.entries()) {

        if (key.endsWith('[]')) {
            let actualKey = key.slice(0, -2);

            if (!obj[actualKey]) obj[actualKey] = [];

            obj[actualKey].push(value);
        } else {
            obj[key] = value;
        }

    }

    return obj;

}

function searchRoute(event, elem) {
    let string = elem.value,
        outputRoutes = elem.nextElementSibling;

    if (string.length > 1) {
    
        fetch('/App/Api.php?class=Routes&method=searchRoute&string=' + string, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            
            if (data.result) {

                if (data.result.length > 0) {
                    let routes = data.result;
                
                    outputRoutes.innerHTML = routes.map(route => {
                        let routeName = route.route_name;

                        return '<span class="route-item d-block" data-id="' + route.id + '">' + routeName + '</span>';

                    }).join('');

                    let getRoutes = Array.from(outputRoutes.children);

                    getRoutes.forEach(item => {
                        item.addEventListener('click', function(event) {
                            elem.value = item.innerHTML;
                            elem.previousElementSibling.value = item.dataset.id;
                            outputRoutes.innerHTML = '';
                        })
                    });

                } else {
                    outputRoutes.innerHTML = '<span class="d-block">Маршрут не найден!</span>' +
                                             '<button type="button" id="add-route">Добавить</button>';

                    addRoute(outputRoutes);

                }

            } else if (data.error) {
                messageOutput(data.error, 'error');
            } else {
                messageOutput('Ошибка! Не удалось получить маршруты!', 'error');
            }

        });

    } else {
        outputRoutes.innerHTML = '<span>Вводите еще!</span>';
    }

    if (string.length === 0)  outputRoutes.innerHTML = '';
    
}

function addRoute(parent) {
    let addRoute = document.getElementById('add-route');

    if (addRoute) {

        addRoute.onclick = function(event) {
            event.preventDefault();
            this.setAttribute('disabled', 'true');

            let route = document.getElementById('search-route'),
                routeName = route.value,
                formData = new FormData();
            
            if (route.classList.contains('error-form')) route.classList.remove('error-form');

            formData.append('route-name', routeName);

            let jsonData = JSON.stringify(Object.fromEntries(formData))
            
            fetch('/App/Api.php?class=Routes&method=addRoute', {
                method: 'POST',
                body: jsonData
            })
            .then(res => res.json())
            .then(data => {
                
                if (data.result) {
                    messageOutput('Маршрут успешно добавлен!', 'success');

                    if (data.id) document.getElementById('route-id').value = data.id;
                   
                    setTimeout(() => { 
                        parent.innerHTML = '';
                    }, 2500);

                } else if (data.novalidate) {

                    for (let type in data.novalidate) {
                        let typeObj = data.novalidate[type];

                        for (let item in typeObj) {
                            let input = document.querySelector('[name="' + item + '"]');

                            if (!input.classList.contains('error-form')) input.classList.add('error-form');

                            messageOutput(typeObj[item], 'error');
                            
                        }

                    }

                } else if (data.error) {
                    messageOutput(data.error, 'error');
                } else {
                    messageOutput('Ошибка! Не удалось добавить маршрут!', 'error');
                }

                setTimeout(() => {
                    this.removeAttribute('disabled');
                }, 2000);
                
            });

        }

    }

}

function numberOfSeats(event, elem) {
    let target = event.target,
        input = elem.querySelector('input'),
        priceRoute = document.getElementById('price-route'),
        amountRoute = priceRoute.nextElementSibling,
        inputValue = +input.value;

    if (document.getElementById('parcel').checked) {
        input.value = 1;
        return;
    }

    if (priceRoute.value === '') {

        if (!priceRoute.classList.contains('error-form')) priceRoute.classList.add('error-form'); 

        input.readOnly = true;

        messageOutput('Поле цена маршрута не заполнено!', 'error');
        return;
    } else {
        input.readOnly = false;
    }

    if (event.type === 'click') {

        if (target.classList.contains('action-minus')) {
            inputValue = Math.max(1, inputValue - 1);
        }

        if (target.classList.contains('action-plus')) {
            inputValue += 1;
        }

    }

    if (inputValue > 16) inputValue = 16;

    if (inputValue > 9) {
        input.style.setProperty('padding-left', '10px', 'important');
    }

    if (inputValue < 10 && input.style.cssText !== '') input.style.cssText = '';
    if (inputValue < 1) inputValue = 1;

    amountRoute.innerHTML = 'Сумма ' + (inputValue * priceRoute.value) + ' грн';

    if (priceRoute.classList.contains('error-form')) priceRoute.classList.remove('error-form'); 

    input.value = inputValue;
    
}

function addPhone(event, elem) {
    let phoneContainer = elem.closest('.phone-container'),
        currentPhoneBlock = elem.closest('.phone'),
        clone = currentPhoneBlock.cloneNode(true),
        cloneInput = clone.querySelector('input');
        
    cloneInput.value = '';

    if (cloneInput.classList.contains('error-form')) cloneInput.classList.remove('error-form');
    
    let icon = clone.querySelector('i');

    icon.classList.remove('fa-plus');
    icon.classList.add('fa-minus');
    icon.setAttribute('onclick', 'removePhone(event, this)');

    phoneContainer.append(clone);
}

function removePhone(event, elem) {
    let phoneBlock = elem.closest('.phone');
    phoneBlock.remove();
}

function checkParcel(event, elem) {
    if (elem.checked) document.getElementById('number').value = 1;
}

function amount(event, elem) {
    let amountRoute = elem.nextElementSibling,
    inputNumber = document.getElementById('number');

    if (elem.value === '') {
        amountRoute.innerHTML = 'Сумма 0.00 грн';
    } else {
        amountRoute.innerHTML = 'Сумма ' + (elem.value * inputNumber.value) + ' грн';
    }
    
}

function addOperatorWithoutPassword(event, elem) {
    let form = elem.querySelector('.add-operator-without-password-form'),
        target = event.target;

    if (target.classList.contains('fa-plus')) {

        requestAnimationFrame(() => {

            if (form.classList.contains('d-none')) {
                form.classList.remove('d-none')

                setTimeout(() => {
                    form.style.zIndex = '10';
                    form.style.opacity = '1';
                }, 500);
            }

        });

    } else if (target.classList.contains('close')) {
        
        if (!form.classList.contains('d-none')) {
            form.style.opacity = '0';
            
            setTimeout(() => {
                form.classList.add('d-none')
                form.style.zIndex = '-1';
            }, 500);

        }

    } else if (target.classList.contains('add-operator')) {
        event.preventDefault();
        
        let input = form.querySelector('input'),
            operatorName = input.value.trim(),
            formData = new FormData();
        
        target.setAttribute('disabled', 'true');
            
        if (input.classList.contains('error-form')) input.classList.remove('error-form');

        formData.append('operator-password', 0);
        formData.append('login', operatorName);

        let jsonData = JSON.stringify(Object.fromEntries(formData))
        
        fetch('/App/Api.php?class=Registration&method=registration', {
            method: 'POST',
            body: jsonData
        })
        .then(res => res.json())
        .then(data => {
            
            if (data.result) {
                let received = document.getElementById('received'),
                    given = document.getElementById('given'),
                    elem = '<option value="' + data.id + '">' + data.login + '</option>';

                if (received) {
                    received.insertAdjacentHTML('beforeend', elem);
                }

                if (given) {
                    given.insertAdjacentHTML('beforeend', elem);
                }
                
                messageOutput('Оператор успешно добавлен!', 'success');

            } else if (data.novalidate) {

                for (let type in data.novalidate) {
                    let typeObj = data.novalidate[type];

                    for (let item in typeObj) {
                        
                        let input = elem.querySelector('[name="' + item + '"]');

                        if (!input.classList.contains('error-form')) input.classList.add('error-form');

                        messageOutput(typeObj[item], 'error');
                        
                    }

                }

            } else if (data.info) {
                messageOutput(data.info, 'info');
            } else if (data.error) {
                messageOutput(data.error, 'error');
            } else {
                messageOutput('Ошибка! Не удалось добавить оператора!', 'error');
            }

            setTimeout(() => {
                target.removeAttribute('disabled');
            }, 2000);
            
        });
        
    } else {
        return;
    }

}

function editStatusBooking(event, elem) {
    event.preventDefault();

    elem.setAttribute('disabled', 'true');

    let nameForm = elem.getAttribute('name'),
        formData = new FormData(),
        id = elem.dataset.id,
        statusBooking = nameForm === 'canceled-booking' ? 1 : 0;

    formData.append('id', id);
    formData.append('status-booking', statusBooking);

    let jsonData = JSON.stringify(Object.fromEntries(formData))
        
    fetch('/App/Api.php?class=Passengers&method=editStatusBooking', {
        method: 'POST',
        body: jsonData
    })
    .then(res => res.json())
    .then(data => {
        
        if (data.result) {
            let message = +data.status ? 'Бронь успешно отменена!' : 'Бронь успешно восстановлена!';

            messageOutput(message, 'success');

            setTimeout(() => {
                location.reload();
            }, 3000)

        } else if (data.error) {
            messageOutput(data.error, 'error');
        } else {
            messageOutput('Ошибка! Не удалось обновить статус брони!', 'error');
        }

        setTimeout(() => {
            elem.removeAttribute('disabled');
        }, 2000);
        
    });

}

function withoutPhone(event, elem) {
    let phone = document.querySelectorAll('.phone');

    if (elem.checked) {

        for (let item of phone) {

            if (!item.classList.contains('d-none')) {
                item.style.opacity = '0';

                setTimeout(() => {
                    item.style.zIndex = '-1';
                    item.classList.add('d-none');
                }, 500);

            } 

        }
    
    } else {

        for (let item of phone) {
            
            if (item.classList.contains('d-none')) {
                item.classList.remove('d-none');
                
                setTimeout(() => {
                    item.style.cssText = '';
                }, 500);

            } 

        }

    }
    
}