<?php
require_once('db.php');

function validate_form_data($data) {
    $errors = [];
    // name
    if (empty($data['name'])) {
        $errors['name'] = 'Введите имя.';
    } elseif (mb_strlen($data['name']) > 150) {
        $errors['name'] = 'Имя не должно превышать 150 символов.';
    } elseif (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s]+$/u', $data['name'])) {
        $errors['name'] = 'Имя должно содержать только буквы и пробелы.';
    }
    // phone
    if (!empty($data['phone']) && !preg_match('/^[\+0-9\s\-\(\)]{10,20}$/', $data['phone'])) {
        $errors['phone'] = 'Некорректный номер телефона.';
    }
    // email
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный email.';
    }
    // birthdate
    if (!empty($data['birthdate'])) {
        $date = DateTime::createFromFormat('Y-m-d', $data['birthdate']);
        if (!$date || $date->format('Y-m-d') !== $data['birthdate']) {
            $errors['birthdate'] = 'Некорректная дата рождения.';
        }
    }
    // sex
    if (empty($data['sex']) || !in_array($data['sex'], ['male', 'female'])) {
        $errors['sex'] = 'Выберите пол.';
    }
    // languages
    $allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go'];
    if (empty($data['languages']) || !is_array($data['languages'])) {
        $errors['languages'] = 'Выберите хотя бы один язык программирования.';
    } else {
        foreach ($data['languages'] as $lang) {
            if (!in_array($lang, $allowed_languages)) {
                $errors['languages'] = 'Недопустимый язык программирования.';
                break;
            }
        }
    }
    // contract
    if (empty($data['contract']) || $data['contract'] != '1') {
        $errors['contract'] = 'Необходимо подтвердить согласие.';
    }
    return $errors;
}

function generate_login() {
    return 'user_' . rand(1000, 9999) . uniqid();
}

function generate_password() {
    return substr(md5(uniqid(rand(), true)), 0, 8);
}

function create_user($data) {
    $db = db_connect();
    $login = generate_login();
    $pass = generate_password();
    $pass_hash = md5($pass);
    $stmt = $db->prepare("INSERT INTO users (login, pass_hash, name, phone, email, birthdate, sex, biography) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$login, $pass_hash, $data['name'], $data['phone'] ?? null, $data['email'] ?? null, $data['birthdate'] ?? null, $data['sex'], $data['biography'] ?? null]);
    $user_id = $db->lastInsertId();
    // languages
    $lang_stmt = $db->prepare("INSERT INTO user_languages (user_id, language) VALUES (?, ?)");
    foreach ($data['languages'] as $lang) {
        $lang_stmt->execute([$user_id, $lang]);
    }
    return ['id' => $user_id, 'login' => $login, 'password' => $pass];
}

function update_user($user_id, $data) {
    $db = db_connect();
    $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, email = ?, birthdate = ?, sex = ?, biography = ? WHERE id = ?");
    $stmt->execute([$data['name'], $data['phone'] ?? null, $data['email'] ?? null, $data['birthdate'] ?? null, $data['sex'], $data['biography'] ?? null, $user_id]);
    // update languages: delete old, insert new
    $del_stmt = $db->prepare("DELETE FROM user_languages WHERE user_id = ?");
    $del_stmt->execute([$user_id]);
    $lang_stmt = $db->prepare("INSERT INTO user_languages (user_id, language) VALUES (?, ?)");
    foreach ($data['languages'] as $lang) {
        $lang_stmt->execute([$user_id, $lang]);
    }
}

function get_user($user_id) {
    $db = db_connect();
    $stmt = $db->prepare("SELECT id, login, name, phone, email, birthdate, sex, biography FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) return null;
    $lang_stmt = $db->prepare("SELECT language FROM user_languages WHERE user_id = ?");
    $lang_stmt->execute([$user_id]);
    $user['languages'] = $lang_stmt->fetchAll(PDO::FETCH_COLUMN);
    return $user;
}