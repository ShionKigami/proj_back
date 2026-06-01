<?php
// form_module.php

function form_module_get($request) {
    $c = array();
    
    // Проверяем, авторизован ли пользователь
    $is_authenticated = !empty($request['user']['login']);
    $c['is_authenticated'] = $is_authenticated;
    
    if ($is_authenticated) {
        $c['username'] = $request['user']['login'];
    }
    
    // Получаем сообщения из cookies
    $messages = array();
    if (!empty($_COOKIE['form_message'])) {
        $messages[] = '<div class="message ' . ($_COOKIE['form_type'] ?? 'info') . '">' . strip_tags($_COOKIE['form_message']) . '</div>';
        setcookie('form_message', '', 100000);
        setcookie('form_type', '', 100000);
    }
    
    if (!empty($_COOKIE['form_login']) && !empty($_COOKIE['form_pass'])) {
        $messages[] = sprintf(
            '<div class="message success">Ваш логин: <strong>%s</strong>, пароль: <strong>%s</strong>. <a href="login.php">Войти</a></div>',
            strip_tags($_COOKIE['form_login']),
            strip_tags($_COOKIE['form_pass'])
        );
        setcookie('form_login', '', 100000);
        setcookie('form_pass', '', 100000);
    }
    
    $c['messages'] = $messages;
    
    return theme('new_form', $c);
}
?>