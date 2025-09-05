"use strict"

function autorisationFunc(event, elem) {
    event.preventDefault();

    elem.querySelector('button').setAttribute('disabled', 'true');

    for (let node of elem.children) {

        if ((node.querySelector('input'))?.classList.contains('error-form')) node.querySelector('input').classList.remove('error-form');

    }

    const formData = new FormData(elem),
          jsonData = JSON.stringify(Object.fromEntries(formData));
    
    fetch('/App/Api.php?class=Autorisation&method=autorisation', {
        method: 'POST',
        body: jsonData
    })
    .then(res => res.json())
    .then(data => {
        
        if (data.result) {
            messageOutput('Вы авторизованы!<br><b>Перенапраляем...</b>', 'success');

            setTimeout(() => {
                location.href = '/';
            }, 2500);

        } else if (data.novalidate) {

            for (let type in data.novalidate) {
                let typeObj = data.novalidate[type];

                for (let item in typeObj) {
                    let input = elem.querySelector('[name="' + item + '"]') || elem.querySelector('.' + item + '');

                    if (!input.classList.contains('error-form')) input.classList.add('error-form');

                    messageOutput(typeObj[item], 'error');
                }

            }
            
        } else if (data.error) {
            messageOutput(data.error, 'error');
        } else {
            messageOutput('Ошибка! Авторизация не удалась!', 'error');
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