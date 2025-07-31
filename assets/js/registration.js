"use strict"

function registrationFunc(event, elem) {
    event.preventDefault();

    elem.querySelector('button').setAttribute('disabled', 'true');

    for (let node of elem.children) {

        if (node.classList.contains('error-form')) node.classList.remove('error-form');
        if ((node.querySelector('input'))?.classList.contains('error-form')) node.querySelector('input').classList.remove('error-form');

    }

    const formData = new FormData(elem);

    formData.append('operator-password', 1);

    let jsonData = JSON.stringify(Object.fromEntries(formData));
    
    fetch('/App/Api.php?class=Registration&method=registration', {
        method: 'POST',
        body: jsonData
    })
    .then(res => res.json())
    .then(data => {
        
        if (data.result) {

            messageOutput('Оператор зарегистрирован!', 'success');
            
        } else if (data.novalidate) {

            for (let type in data.novalidate) {
                let typeObj = data.novalidate[type];

                for (let item in typeObj) {
                    let input = elem.querySelector('[name="' + item + '"]') || elem.querySelector('.' + item + '');

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
            elem.querySelector('button').removeAttribute('disabled');
        }, 2000);

    });

}

function showHidePassword(event, elem) {
    let target = event.target,
        input = elem.querySelector('input'),
        icon = elem.querySelector('.show-hide');

    if (target.classList.contains('show-hide') && input.value.length > 0) {
        
        if (input.type === 'password') {
            icon.style.opacity = '0';

            setTimeout(() => {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                input.type = 'text';
                icon.style.opacity = '1';
            }, 200);

        } else if (input.type === 'text') {
            icon.style.opacity = '0';

            setTimeout(() => {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                input.type = 'password';
                icon.style.opacity = '1';
            }, 200);
        }
        
    } else {
        return;
    }

}