"use strict";

function messageOutput(message, type = 'info') {
    const containerId = 'messages-container';
    let container = document.getElementById(containerId),
        windowHeight = window.innerHeight,
        timeout = 500;

    if (!container) {
        container = document.createElement('div');
        container.id = containerId;
        container.className = 'pos-fixed';
        document.body.append(container);
    } else {

        if (container.scrollHeight > windowHeight) if (container.style.height === '') container.style.cssText = 'height: 90vh; overflow-y: auto;';
        
    }

    const msg = document.createElement('div');
    msg.className = 'pos-rel message-content ' + type;

    msg.innerHTML = `
        <span>${message}</span>
        <button class="pos-abs close-btn"><i class="fas fa-times"></i></button>
    `;

    msg.querySelector('.close-btn').addEventListener('click', () => {
        removeMessage(msg);

        if (container.scrollHeight < windowHeight) if (container.style.height !== '') container.style.cssText = '';

    });
    
    container.append(msg);

    setTimeout(() => {
        requestAnimationFrame(() => msg.classList.add('show'));
    }, timeout * 2);
    
    setTimeout(() => removeMessage(msg), timeout * 20);

    function removeMessage(elem) {
        elem.classList.remove('show');
        setTimeout(() => elem.remove(), timeout);
        
        if (container.style.height !== '') container.style.cssText = '';
        if (document.querySelector('.script-message-output')) document.querySelector('.script-message-output').remove();

    }
    
};