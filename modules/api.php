<?php
function api_post($request) {
    // Получить JSON из тела запроса
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (!$data) {
        return ['headers' => ['HTTP/1.1 400 Bad Request'], 'entity' => json_encode(['error' => 'Invalid JSON'])];
    }
    $errors = validate_form_data($data);
    if (!empty($errors)) {
        return ['headers' => ['HTTP/1.1 400 Bad Request', 'Content-Type: application/json'], 'entity' => json_encode(['errors' => $errors])];
    }
    try {
        $user = create_user($data);
        $profile_url = url("/api/form/{$user['id']}");
        $response = [
            'login' => $user['login'],
            'password' => $user['password'],
            'profile_url' => $profile_url
        ];
        return ['headers' => ['HTTP/1.1 201 Created', 'Content-Type: application/json'], 'entity' => json_encode($response)];
    } catch (PDOException $e) {
        return ['headers' => ['HTTP/1.1 500 Internal Server Error'], 'entity' => json_encode(['error' => 'Database error'])];
    }
}

function api_get($request, $id) {
    // Авторизация уже пройдена в auth_basic, в $request['user'] данные пользователя
    if (!isset($request['user']) || $request['user']['id'] != $id) {
        return access_denied();
    }
    $user = get_user($id);
    if (!$user) {
        return not_found();
    }
    // Убираем login из ответа (не нужно)
    unset($user['login']);
    return ['headers' => ['Content-Type: application/json'], 'entity' => json_encode($user)];
}

function api_put($request, $id) {
    if (!isset($request['user']) || $request['user']['id'] != $id) {
        return access_denied();
    }
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (!$data) {
        return ['headers' => ['HTTP/1.1 400 Bad Request'], 'entity' => json_encode(['error' => 'Invalid JSON'])];
    }
    $errors = validate_form_data($data);
    if (!empty($errors)) {
        return ['headers' => ['HTTP/1.1 400 Bad Request', 'Content-Type: application/json'], 'entity' => json_encode(['errors' => $errors])];
    }
    try {
        update_user($id, $data);
        return ['headers' => ['HTTP/1.1 200 OK', 'Content-Type: application/json'], 'entity' => json_encode(['status' => 'updated'])];
    } catch (PDOException $e) {
        return ['headers' => ['HTTP/1.1 500 Internal Server Error'], 'entity' => json_encode(['error' => 'Database error'])];
    }
}