<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета участника</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" style="max-width:800px; margin: 40px auto;">
    <h1 class="form-title">Анкета участника</h1>
    <?php if (!empty($c['messages'])): ?>
        <div class="form-message success" style="display:block;">
            <?php foreach ($c['messages'] as $msg): ?>
                <p><?php echo $msg; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div id="formMessage" class="form-message" style="display:none;"></div>
    <form id="mainForm" action="/" method="POST">
        <div class="form-group">
            <label>ФИО *</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($c['values']['name']); ?>" required>
            <?php if ($c['errors']['name']): ?><div class="error-text">Ошибка в имени</div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Телефон</label>
            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($c['values']['phone']); ?>">
            <?php if ($c['errors']['phone']): ?><div class="error-text">Некорректный телефон</div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($c['values']['email']); ?>">
            <?php if ($c['errors']['email']): ?><div class="error-text">Некорректный email</div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Дата рождения</label>
            <input type="date" name="birthdate" class="form-control" value="<?php echo htmlspecialchars($c['values']['birthdate']); ?>">
            <?php if ($c['errors']['birthdate']): ?><div class="error-text">Некорректная дата</div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Пол *</label>
            <div>
                <label><input type="radio" name="sex" value="male" <?php if ($c['values']['sex'] == 'male') echo 'checked'; ?>> Мужской</label>
                <label><input type="radio" name="sex" value="female" <?php if ($c['values']['sex'] == 'female') echo 'checked'; ?>> Женский</label>
            </div>
            <?php if ($c['errors']['sex']): ?><div class="error-text">Выберите пол</div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Любимые языки программирования *</label>
            <div>
                <?php
                $langs = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go'];
                foreach ($langs as $lang):
                    $checked = in_array($lang, $c['values']['languages']) ? 'checked' : '';
                ?>
                <label><input type="checkbox" name="languages[]" value="<?php echo $lang; ?>" <?php echo $checked; ?>> <?php echo $lang; ?></label><br>
                <?php endforeach; ?>
            </div>
            <?php if ($c['errors']['languages']): ?><div class="error-text">Выберите хотя бы один язык</div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Биография</label>
            <textarea name="biography" class="form-control" rows="4"><?php echo htmlspecialchars($c['values']['biography'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="contract" value="1" <?php if ($c['values']['contract'] == '1') echo 'checked'; ?>> Ознакомлен с условиями *</label>
            <?php if ($c['errors']['contract']): ?><div class="error-text">Необходимо согласие</div><?php endif; ?>
        </div>
        <button type="submit" class="btn" style="width:100%;">Отправить</button>
    </form>

    <hr style="margin: 40px 0;">
    <h2>Вход для редактирования</h2>
    <div id="loginBlock">
        <input type="text" id="editLogin" placeholder="Логин" class="form-control" style="margin-bottom:10px;">
        <input type="password" id="editPassword" placeholder="Пароль" class="form-control" style="margin-bottom:10px;">
        <button id="loadDataBtn" class="btn">Загрузить данные</button>
    </div>
    <div id="editMessage"></div>
</div>

<script src="script.js"></script>
</body>
</html>