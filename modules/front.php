<?php
function front_get($request) {
    // Получить значения из кук (как в index_old.php)
    $values = [];
    $errors = [];
    $messages = [];

    if (!empty($_COOKIE['save'])) {
        $messages[] = 'Результаты сохранены.';
        if (!empty($_COOKIE['login']) && !empty($_COOKIE['pass'])) {
            $messages[] = sprintf('Логин: %s, пароль: %s', strip_tags($_COOKIE['login']), strip_tags($_COOKIE['pass']));
        }
        setcookie('save', '', 100000);
        setcookie('login', '', 100000);
        setcookie('pass', '', 100000);
    }

    $error_fields = ['name', 'phone', 'email', 'birthdate', 'sex', 'languages', 'contract'];
    foreach ($error_fields as $field) {
        $errors[$field] = !empty($_COOKIE[$field . '_error']);
        if ($errors[$field]) {
            setcookie($field . '_error', '', 100000);
        }
    }

    $values['name'] = empty($_COOKIE['name_value']) ? '' : strip_tags($_COOKIE['name_value']);
    $values['phone'] = empty($_COOKIE['phone_value']) ? '' : strip_tags($_COOKIE['phone_value']);
    $values['email'] = empty($_COOKIE['email_value']) ? '' : strip_tags($_COOKIE['email_value']);
    $values['birthdate'] = empty($_COOKIE['birthdate_value']) ? '' : strip_tags($_COOKIE['birthdate_value']);
    $values['sex'] = empty($_COOKIE['sex_value']) ? '' : strip_tags($_COOKIE['sex_value']);
    $values['languages'] = empty($_COOKIE['languages_value']) ? [] : explode('|', strip_tags($_COOKIE['languages_value']));
    $values['contract'] = empty($_COOKIE['contract_value']) ? '' : strip_tags($_COOKIE['contract_value']);

    // очистить куки значений
    setcookie('name_value', '', 100000);
    setcookie('phone_value', '', 100000);
    setcookie('email_value', '', 100000);
    setcookie('birthdate_value', '', 100000);
    setcookie('sex_value', '', 100000);
    setcookie('languages_value', '', 100000);
    setcookie('contract_value', '', 100000);

    return theme('form', ['values' => $values, 'errors' => $errors, 'messages' => $messages]);
}

function front_post($request) {
    // Обработка обычной отправки формы (без JS)
    $errors = false;

    if (empty($_POST['name'])) {
        setcookie('name_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    } elseif (strlen($_POST['name']) > 150) {
        setcookie('name_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    } elseif (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s]+$/u', $_POST['name'])) {
        setcookie('name_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    } else {
        setcookie('name_value', $_POST['name'], time() + 30 * 24 * 60 * 60);
    }

    if (!empty($_POST['phone']) && !preg_match('/^[\+0-9\s\-\(\)]{10,20}$/', $_POST['phone'])) {
        setcookie('phone_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    } else {
        setcookie('phone_value', $_POST['phone'], time() + 30 * 24 * 60 * 60);
    }

    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    } else {
        setcookie('email_value', $_POST['email'], time() + 30 * 24 * 60 * 60);
    }

    if (!empty($_POST['birthdate'])) {
        $date = DateTime::createFromFormat('Y-m-d', $_POST['birthdate']);
        if (!$date || $date->format('Y-m-d') !== $_POST['birthdate']) {
            setcookie('birthdate_error', '1', time() + 24 * 60 * 60);
            $errors = true;
        } else {
            setcookie('birthdate_value', $_POST['birthdate'], time() + 30 * 24 * 60 * 60);
        }
    }

    $allowed_genders = ['male', 'female'];
    if (empty($_POST['sex'])) {
        setcookie('sex_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    } elseif (!in_array($_POST['sex'], $allowed_genders)) {
        setcookie('sex_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    } else {
        setcookie('sex_value', $_POST['sex'], time() + 30 * 24 * 60 * 60);
    }

    $allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go'];
    if (empty($_POST['languages'])) {
        setcookie('languages_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    } else {
        $lang_valid = true;
        foreach ($_POST['languages'] as $lang) {
            if (!in_array($lang, $allowed_languages)) {
                $lang_valid = false;
                break;
            }
        }
        if (!$lang_valid) {
            setcookie('languages_error', '1', time() + 24 * 60 * 60);
            $errors = true;
        } else {
            setcookie('languages_value', implode('|', $_POST['languages']), time() + 30 * 24 * 60 * 60);
        }
    }

    if (empty($_POST['contract']) || $_POST['contract'] != '1') {
        setcookie('contract_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    } else {
        setcookie('contract_value', '1', time() + 30 * 24 * 60 * 60);
    }

    if ($errors) {
        header('Location: /');
        exit();
    }

    // Очистка ошибок
    $error_cookies = ['name', 'phone', 'email', 'birthdate', 'sex', 'languages', 'contract'];
    foreach ($error_cookies as $field) {
        setcookie($field . '_error', '', 100000);
    }

    // Сохраняем данные
    $data = [
        'name' => $_POST['name'],
        'phone' => $_POST['phone'] ?? null,
        'email' => $_POST['email'] ?? null,
        'birthdate' => $_POST['birthdate'] ?? null,
        'sex' => $_POST['sex'],
        'languages' => $_POST['languages'],
        'biography' => $_POST['biography'] ?? null,
        'contract' => $_POST['contract']
    ];
    require_once('includes/functions.php');
    $user = create_user($data);
    setcookie('save', '1');
    setcookie('login', $user['login']);
    setcookie('pass', $user['password']);
    header('Location: /');
    exit();
}