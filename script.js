document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('mainForm');
    const formMessageDiv = document.getElementById('formMessage');
    const editLoginInput = document.getElementById('editLogin');
    const editPasswordInput = document.getElementById('editPassword');
    const loadDataBtn = document.getElementById('loadDataBtn');
    const editMessageDiv = document.getElementById('editMessage');

    // Попытка загрузить сохраненные данные из localStorage
    let savedUserId = localStorage.getItem('userId');
    let savedAuth = localStorage.getItem('userAuth');
    if (savedUserId && savedAuth) {
        try {
            savedAuth = JSON.parse(savedAuth);
            editLoginInput.value = savedAuth.login;
            editPasswordInput.value = savedAuth.password;
            // Можно автоматически загрузить данные
            loadUserData(savedUserId, savedAuth.login, savedAuth.password);
        } catch(e) {}
    }

    function showMessage(msg, isError, target = formMessageDiv) {
        target.textContent = msg;
        target.className = 'form-message ' + (isError ? 'error' : 'success');
        target.style.display = 'block';
        setTimeout(() => { target.style.display = 'none'; }, 7000);
    }

    function displayValidationErrors(errors) {
        document.querySelectorAll('.error-text').forEach(el => el.remove());
        for (const [field, msg] of Object.entries(errors)) {
            let container;
            if (field === 'languages') container = document.querySelector('.form-group:has(input[type="checkbox"])');
            else if (field === 'contract') container = document.querySelector('input[name="contract"]').closest('.form-group');
            else if (field === 'sex') container = document.querySelector('input[name="sex"]').closest('.form-group');
            else container = document.querySelector(`[name="${field}"]`)?.closest('.form-group');
            if (container) {
                const errDiv = document.createElement('div');
                errDiv.className = 'error-text';
                errDiv.textContent = msg;
                container.appendChild(errDiv);
            } else {
                showMessage(msg, true);
            }
        }
    }

    async function loadUserData(userId, login, password) {
        try {
            const response = await fetch(`/api/form/${userId}`, {
                headers: { 'Authorization': 'Basic ' + btoa(`${login}:${password}`) }
            });
            if (response.ok) {
                const userData = await response.json();
                document.querySelector('input[name="name"]').value = userData.name || '';
                document.querySelector('input[name="phone"]').value = userData.phone || '';
                document.querySelector('input[name="email"]').value = userData.email || '';
                document.querySelector('input[name="birthdate"]').value = userData.birthdate || '';
                if (userData.sex === 'male') document.querySelector('input[value="male"]').checked = true;
                else if (userData.sex === 'female') document.querySelector('input[value="female"]').checked = true;
                document.querySelectorAll('input[name="languages[]"]').forEach(cb => cb.checked = false);
                if (userData.languages) {
                    userData.languages.forEach(lang => {
                        const cb = document.querySelector(`input[name="languages[]"][value="${lang}"]`);
                        if (cb) cb.checked = true;
                    });
                }
                document.querySelector('textarea[name="biography"]').value = userData.biography || '';
                document.querySelector('input[name="contract"]').checked = true;
                editMessageDiv.innerHTML = '<div class="success-text">Данные загружены. Отредактируйте и отправьте.</div>';
            } else {
                editMessageDiv.innerHTML = '<div class="error-text">Не удалось загрузить данные</div>';
            }
        } catch (err) {
            editMessageDiv.innerHTML = '<div class="error-text">Ошибка сети</div>';
        }
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        const data = {};
        for (let [key, value] of formData.entries()) {
            if (key === 'languages[]') {
                if (!data.languages) data.languages = [];
                data.languages.push(value);
            } else if (key !== 'languages') {
                data[key] = value;
            }
        }
        if (!data.languages) data.languages = [];

        let url = '/api/form';
        let method = 'POST';
        let headers = { 'Content-Type': 'application/json' };
        let userId = localStorage.getItem('userId');
        let auth = localStorage.getItem('userAuth');
        if (userId && auth) {
            auth = JSON.parse(auth);
            url = `/api/form/${userId}`;
            method = 'PUT';
            headers['Authorization'] = 'Basic ' + btoa(`${auth.login}:${auth.password}`);
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: headers,
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (response.ok) {
                if (method === 'POST') {
                    showMessage(`Анкета создана! Логин: ${result.login}, Пароль: ${result.password}. Сохраните их.`);
                    if (result.id) {
                        localStorage.setItem('userId', result.id);
                        localStorage.setItem('userAuth', JSON.stringify({login: result.login, password: result.password}));
                        editLoginInput.value = result.login;
                        editPasswordInput.value = result.password;
                        // автоматически загружаем данные
                        await loadUserData(result.id, result.login, result.password);
                    }
                } else {
                    showMessage('Данные обновлены');
                }
            } else {
                if (result.errors) displayValidationErrors(result.errors);
                else showMessage(result.error || 'Ошибка', true);
            }
        } catch (err) {
            showMessage('Ошибка соединения', true);
        }
    });

    loadDataBtn.addEventListener('click', () => {
        const login = editLoginInput.value.trim();
        const password = editPasswordInput.value.trim();
        const userId = localStorage.getItem('userId');
        if (!userId || !login || !password) {
            editMessageDiv.innerHTML = '<div class="error-text">Сначала создайте анкету или введите логин/пароль</div>';
            return;
        }
        loadUserData(userId, login, password);
    });
});