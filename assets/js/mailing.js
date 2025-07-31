function mailingFunc(event, elem) {
    event.preventDefault();

    elem.querySelector('button').setAttribute('disabled', 'true');

    let textarea = elem.querySelector('textarea');

    if (textarea.classList.contains('error-form')) textarea.classList.remove('error-form');

    const formData = new FormData(elem),
          jsonData = JSON.stringify(Object.fromEntries(formData));
    
    fetch('/App/Api.php?class=Mailing&method=mailing', {
        method: 'POST',
        body: jsonData
    })
    .then(res => res.json())
    .then(data => {
        
        if (data.result) {
            let objMessage = objectStatuses[data.result.response_code],
                result = data.result;

            console.log(result);
            
            messageOutput(objMessage.responseDescription, objMessage.status);

            if (result.response_result) {
                console.log('Hari');
            }

        } else if (data.novalidate) {

            for (let type in data.novalidate) {
                let typeObj = data.novalidate[type];

                for (let item in typeObj) {
                    let input = elem.querySelector('[name="' + item + '"]');

                    if (!input.classList.contains('error-form')) input.classList.add('error-form');

                    messageOutput(typeObj[item], 'error');

                }

            }
            
        } else if (data.error) {
            messageOutput(data.error, 'error');
        } else {
            messageOutput('Ошибка! Не удалось совершить рассылку!', 'error');
        }
        
        setTimeout(() => {
            elem.querySelector('button').removeAttribute('disabled');
        }, 2000);

    });

}

objectStatuses = {

    0 : {
        responseStatus: 'OK',
        responseDescription: 'Запрос обработан успешно',
        status: 'success'
    },
    1 : {
        responseStatus: 'PONG',
        responseDescription: 'Успешный результат вызова метода ping',
        status: 'success'
    },
    103 : {
        responseStatus: 'REQUIRED_TOKEN',
        responseDescription: 'Отсутствует токен аутентификации',
        status: 'info'
    },
    104 : {
        responseStatus: 'REQUIRED_CONTENT',
        responseDescription: 'Отсутствуют данные запроса',
        status: 'info'
    },
    105 : {
        responseStatus: 'REQUIRED_AUTH',
        responseDescription: 'Аутентификация не пройдена, не верный токен',
        status: 'info'
    },
    106 : {
        responseStatus: 'REQUIRED_AUTH',
        responseDescription: 'Аутентификация не пройдена, не верный токен',
        status: 'info'
    },
    107 : {
        responseStatus: 'REQUIRED_VIBER_SESSION',
        responseDescription: 'Аутентификация не пройдена, не верный токен',
        status: 'info'
    },
    200 : {
        responseStatus: 'REQUIRED_MESSAGE_SENDER',
        responseDescription: 'Отсутствует или пустой параметр отправителя сообщения',
        status: 'info'
    },
    201 : {
        responseStatus: 'REQUIRED_MESSAGE_TEXT',
        responseDescription: 'Отсутствует или пустой параметр текста сообщения',
        status: 'info'
    },
    202 : {
        responseStatus: 'REQUIRED_MESSAGE_RECIPIENT',
        responseDescription: 'Отсутствует или пустой список получателей сообщения',
        status: 'info'
    },
    203 : {
        responseStatus: 'REQUIRED_BALANCE',
        responseDescription: 'Не достаточно средств на балансе для создания рассылки',
        status: 'info'
    },
    204 : {
        responseStatus: 'REQUIRED_MESSAGE_BUTTON ',
        responseDescription: 'Отсутствуют или пустые параметры кнопки в сообщении, когда она обязательна',
        status: 'info'
    },
    205 : {
        responseStatus: 'REQUIRED_MESSAGE_BUTTON_CAPTION',
        responseDescription: 'Отсутствует или пустой параметр текста на кнопке в сообщении',
        status: 'info'
    },
    206 : {
        responseStatus: 'REQUIRED_MESSAGE_BUTTON_ACTION',
        responseDescription: 'Отсутствует или пустой параметр URL адреса, куда перейдёт получатель сообщения при нажатии на кнопку',
        status: 'info'
    },
    300 : {
        responseStatus: 'INVALID_REQUEST',
        responseDescription: 'Неверный запрос, проверьте его структуру и корректность данных',
        status: 'error'
    },
    301 : {
        responseStatus: 'INVALID_TOKEN',
        responseDescription: 'Неверный токен аутентификации',
        status: 'error'
    },
    302 : {
        responseStatus: 'INVALID_MESSAGE_SENDER',
        responseDescription: 'Неверный отправитель сообщения',
        status: 'error'
    },
    303 : {
        responseStatus: 'INVALID_START_TIME',
        responseDescription: 'Неверная дата отложенной отправки сообщения',
        status: 'error'
    },
    304 : {
        responseStatus: 'INVALID_MESSAGE_TEXT',
        responseDescription: 'Недопустимое значение текста сообщения. Возвращается если передано не строковое значение или кодировка символов не входит в набор UTF-8',
        status: 'error'
    },
    305 : {
        responseStatus: 'INVALID_PHONE',
        responseDescription: 'Недопустимый номер получателя, система не смогла распознать страну и оператора получателя',
        status: 'error'
    },
    306 : {
        responseStatus: 'INVALID_TTL',
        responseDescription: 'Недопустимое значение параметра ttl, значение должно быть целочисленным и не представлено в виде строки',
        status: 'error'
    },
    307 : {
        responseStatus: 'INVALID_MESSAGE_ID',
        responseDescription: 'Недопустимое значение параметра message_id, неверный формат',
        status: 'error'
    },
    308 : {
        responseStatus: 'INVALID_FILE_ID',
        responseDescription: 'Недопустимое значение параметра id при вызове метода file/details, неверный формат',
        status: 'error'
    },
    309 : {
        responseStatus: 'INVALID_SENDER_ID',
        responseDescription: 'Недопустимое значение параметра sender_id при вызове метода chat/recipients, неверный формат',
        status: 'error'
    },
    310 : {
        responseStatus: 'INVALID_CHAT_ID',
        responseDescription: 'Недопустимое значение параметра chat_id при вызове методов модуля chat, неверный формат',
        status: 'error'
    },
    311 : {
        responseStatus: 'INVALID_SESSION_ID',
        responseDescription: 'Недопустимое значение параметра session_id при вызове метода chat/session, неверный формат',
        status: 'error'
    },
    312 : {
        responseStatus: 'INVALID_PAGE',
        responseDescription: 'Недопустимое значение параметра пагинации page',
        status: 'error'
    },
    313 : {
        responseStatus: 'INVALID_ROWS',
        responseDescription: 'Недопустимое значение параметра пагинации rows',
        status: 'error'
    },
    400 : {
        responseStatus: 'NOT_ALLOWED_MESSAGE_SENDER',
        responseDescription: 'Не разрешённый отправитель для текущего пользователя',
        status: 'error'
    },
    401 : {
        responseStatus: 'NOT_ALLOWED_MESSAGE_SENDER_NOT_ACTIVE',
        responseDescription: 'Отправитель разрешён, но не активирован на данный момент (не оплачено использование в текущем месяце, не завершена регистрация и т.п.)',
        status: 'error'
    },
    402 : {
        responseStatus: 'NOT_ALLOWED_MESSAGE_IMAGE',
        responseDescription: 'Недопустимый тип файла изображения',
        status: 'error'
    },
    403 : {
        responseStatus: 'NOT_ALLOWED_START_TIME',
        responseDescription: 'Недопустимая дата отложенной отправки сообщения (выходит за пределы установленных ограничений)',
        status: 'error'
    },
    404 : {
        responseStatus: 'NOT_ALLOWED_NUMBER_STOPLIST',
        responseDescription: 'Номер получателя находится в стоплисте (для sms) или в игнорлисте (для Viber), отправка невозможна',
        status: 'error'
    },
    405 : {
        responseStatus: 'NOT_ALLOWED_RECIPIENTS_LIMIT',
        responseDescription: 'Недопустимое количество получателей',
        status: 'error'
    },
    406 : {
        responseStatus: 'NOT_ALLOWED_RECIPIENT_COUNTRY',
        responseDescription: 'Недопустимая страна получателя. У пользователя не активирована возможность отправлять сообщения получателям данной страны. Для активации такой возможности свяжитесь с нашим отделом поддержки клиентов',
        status: 'error'
    },
    407 : {
        responseStatus: 'NOT_ALLOWED_RECIPIENT_DUPLICATE',
        responseDescription: 'Получатель уже присутствует в рассылке, дубликаты игнорируются',
        status: 'error'
    },
    408 : {
        responseStatus: 'NOT_ALLOWED_MESSAGE_BUTTON_TEXT_LENGTH',
        responseDescription: 'Текст на кнопке слишком длинный, допускается не более 30 символов',
        status: 'error'
    },
    409 : {
        responseStatus: 'NOT_ALLOWED_MESSAGE_TTL',
        responseDescription: 'Недопустимое значение параметра ttl (выходит за пределы установленных ограничений)',
        status: 'error'
    },
    410 : {
        responseStatus: 'NOT_ALLOWED_MESSAGE_TRANSACTION_CONTENT',
        responseDescription: 'Недопустимый контент в транзакционном сообщении. В таких сообщениях можно отправлять только текст, а кнопка и изображения запрещены',
        status: 'error'
    },
    411 : {
        responseStatus: 'NOT_ALLOWED_MESSAGE_DATA',
        responseDescription: 'Какой-то из параметров имеет недопустимое значение, свяжитесь с нашим отделом поддержки клиентов для выяснения деталей',
        status: 'error'
    },
    412 : {
        responseStatus: 'NOT_ALLOWED_MESSAGE_TEXT',
        responseDescription: 'Текст содержит запрещённые фрагменты',
        status: 'error'
    },
    413 : {
        responseStatus: 'NOT_ALLOWED_MESSAGE_TEXT_LENGTH',
        responseDescription: 'Превышена допустимая длина текста сообщения',
        status: 'error'
    },
    414 : {
        responseStatus: 'NOT_ALLOWED_MESSAGE_ID',
        responseDescription: 'Данные сообщения с переданным message_id недоступны для текущего пользователя',
        status: 'error'
    },
    415 : {
        responseStatus: 'NOT_ALLOWED_MESSAGE_TRANSACTION_SENDER',
        responseDescription: 'Запрещено отправлять транзакционные сообщения от общего отправителя',
        status: 'error'
    },
    416 : {
        responseStatus: 'NOT_ALLOWED_MESSAGE_TRANSACTION_PATTERN',
        responseDescription: 'Не найден шаблон, соответствующий переданному транзакционному сообщению',
        status: 'error'
    },
    417 : {
        responseStatus: 'NOT_ALLOWED_FILE_ID',
        responseDescription: 'Файл с переданным id не существует или недоступен для текущего пользователя',
        status: 'error'
    },
    418 : {
        responseStatus: 'NOT_ALLOWED_FILE_EMPTY',
        responseDescription: 'Указанный загружаемый файл не найден или пустой',
        status: 'error'
    },
    419 : {
        responseStatus: 'NOT_ALLOWED_FILE_TYPE',
        responseDescription: 'Неподдерживаемый тип файла',
        status: 'error'
    },
    420 : {
        responseStatus: 'NOT_ALLOWED_FILE_SIZE',
        responseDescription: 'Размер файла превышает максимально допустимый размер 3Мб',
        status: 'error'
    },
    421 : {
        responseStatus: 'NOT_ALLOWED_MESSAGE_TRAFFIC_TYPE',
        responseDescription: 'Отправитель не поддерживает отправку сообщения указанного типа',
        status: 'error'
    },
    422 : {
        responseStatus: 'NOT_ALLOWED_SESSION_OUTBOX_LIMIT',
        responseDescription: 'Запрещено отправлять более 5 сессионных сообщений подряд без ответа получателя',
        status: 'error'
    },
    423 : {
        responseStatus: 'NOT_ALLOWED_FILE_NAME_LENGTH',
        responseDescription: 'Имя файла в Viber сообщении слишком длинное, допускается не более 25 символов',
        status: 'error'
    },
    424 : {
        responseStatus: 'NOT_ALLOWED_PERMISSION',
        responseDescription: 'Попытка оправить Viber сообщение в чужой чат',
        status: 'error'
    },
    425 : {
        responseStatus: 'NOT_ALLOWED_TEST_NUMBER',
        responseDescription: 'Viber сообщение от тестового отправителя может быть отправлено только на номер, привязанный к профилю пользователя',
        status: 'error'
    },
    426 : {
        responseStatus: 'NOT_ALLOWED_TEST_MESSAGES_LIMIT',
        responseDescription: 'Достигнут лимит отправленных Viber сообщений от тестового отправителя',
        status: 'error'
    },
    427 : {
        responseStatus: 'NOT_ALLOWED_MESSAGE_DUPLICATE',
        responseDescription: 'Попытка создать сообщение, которое уже было создано в течение последних 90 секунд',
        status: 'error'
    },
    500 : {
        responseStatus: 'FAILED_CONVERT_RESULT2JSON',
        responseDescription: 'Не удалось сконвертировать данные результата в JSON формат, незамедлительно свяжитесь с нашим отделом поддержки клиентов для выяснения деталей',
        status: 'error'
    },
    501 : {
        responseStatus: 'FAILED_CONVERT_RESULT2XML',
        responseDescription: 'Не удалось сконвертировать данные результата в XML формат, незамедлительно свяжитесь с нашим отделом поддержки клиентов для выяснения деталей',
        status: 'error'
    },
    502 : {
        responseStatus: 'FAILED_PARSE_BODY',
        responseDescription: 'Не удалось распознать тело запроса (неверный формат)',
        status: 'error'
    },
    503 : {
        responseStatus: 'FAILED_SMS_SEND',
        responseDescription: 'Не удалось отправить SMS сообщение',
        status: 'error'
    },
    504 : {
        responseStatus: 'FAILED_VIBER_SEND',
        responseDescription: 'Не удалось отправить Viber сообщение',
        status: 'error'
    },
    505 : {
        responseStatus: 'FAILED_SAVE_IMAGE',
        responseDescription: 'Не удалось сохранить изображение',
        status: 'error'
    },
    506 : {
        responseStatus: 'FAILED_SAVE_FILE',
        responseDescription: 'Не удалось сохранить файл',
        status: 'error'
    },
    507 : {
        responseStatus: 'FAILED_DUPLICATE_REQUEST',
        responseDescription: 'Дублирующий запрос, возвращены результаты предыдущего запроса',
        status: 'error'
    },
    800 : {
        responseStatus: 'SUCCESS_MESSAGE_ACCEPTED',
        responseDescription: 'Сообщения успешно созданы и добавлены в очередь отправки. Некоторые сообщения могут попадать на предварительную модерацию',
        status: 'success'
    },
    801 : {
        responseStatus: 'SUCCESS_MESSAGE_SENT',
        responseDescription: 'Сообщения успешно отправлены',
        status: 'success'
    },
    802 : {
        responseStatus: 'SUCCESS_MESSAGE_PARTIAL_ACCEPTED',
        responseDescription: 'Сообщения успешно созданы и добавлены в очередь отправки, но некоторые получатели не попали в список рассылки, детали смотрите в ответе',
        status: 'success'
    },
    803 : {
        responseStatus: 'SUCCESS_MESSAGE_PARTIAL_SENT',
        responseDescription: 'Сообщения успешно отправлены, но некоторые получатели не попали в список рассылки, детали смотрите в ответе',
        status: 'success'
    },
    999 : {
        responseStatus: 'FATAL_ERROR',
        responseDescription: 'Ошибка выполнения запроса, свяжитесь с отделом поддержки для выяснения деталей',
        status: 'error'
    }
    
}