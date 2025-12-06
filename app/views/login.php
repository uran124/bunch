<section class="auth-section">
    <h1 class="text-2xl font-semibold mb-4">Вход</h1>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error mb-4">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="POST" action="/?page=login" class="auth-form space-y-4">
        <div>
            <label for="phone" class="block text-sm font-medium">Телефон</label>
            <input type="text" id="phone" name="phone" class="input" placeholder="+7..." required>
        </div>
        <div>
            <label for="pin" class="block text-sm font-medium">PIN</label>
            <input type="password" id="pin" name="pin" class="input" maxlength="4" minlength="4" placeholder="****" required>
        </div>
        <button type="submit" class="btn btn-primary w-full">Войти</button>
    </form>
</section>
