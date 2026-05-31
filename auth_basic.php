<?php
function auth(&$request, $r) {
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        $response = array(
            'headers' => array(sprintf('WWW-Authenticate: Basic realm="%s"', conf('sitename')), 'HTTP/1.0 401 Unauthorized'),
            'entity' => theme('401', $request),
        );
        return $response;
    }
    $login = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];
    require_once('includes/db.php');
    $db = db_connect();
    $stmt = $db->prepare("SELECT id, login, pass_hash FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && md5($password) === $user['pass_hash']) {
        $request['user'] = $user;
        return;
    } else {
        $response = array(
            'headers' => array(sprintf('WWW-Authenticate: Basic realm="%s"', conf('sitename')), 'HTTP/1.0 401 Unauthorized'),
            'entity' => theme('401', $request),
        );
        return $response;
    }
}