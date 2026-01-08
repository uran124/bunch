<?php /** @var array $users */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Пользователи</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Пользователи', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="text-sm text-slate-500">Имя, телефон и быстрый переключатель активности.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="/?page=admin-broadcast"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <span class="material-symbols-rounded text-base">send</span>
                Рассылки
            </a>
            <a
                href="/?page=admin"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <span class="material-symbols-rounded text-base">arrow_back</span>
                В панель
            </a>
        </div>
    </header>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="divide-y divide-slate-100">
            <?php foreach ($users as $user): ?>
                <article class="flex flex-wrap items-center justify-between gap-3 py-3" data-user-card="<?php echo (int) $user['id']; ?>">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
                        <a
                            href="/?page=admin-user&id=<?php echo (int) $user['id']; ?>"
                            class="text-base font-semibold text-slate-900 transition hover:text-rose-600"
                        >
                            <?php echo htmlspecialchars($user['name'] ?? 'Без имени', ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <span class="text-sm text-slate-500"><?php echo htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <label class="relative inline-flex h-9 w-16 cursor-pointer items-center" aria-label="Активность пользователя">
                        <input
                            type="checkbox"
                            class="peer sr-only user-active-toggle"
                            data-user-id="<?php echo (int) $user['id']; ?>"
                            <?php echo $user['active'] ? 'checked' : ''; ?>
                        >
                        <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500"></span>
                        <span class="absolute left-1 top-1 h-7 w-7 rounded-full bg-white shadow-sm transition peer-checked:translate-x-7 peer-checked:shadow-md"></span>
                    </label>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    document.querySelectorAll('.user-active-toggle').forEach((checkbox) => {
        checkbox.addEventListener('change', async () => {
            const userId = Number(checkbox.dataset.userId);
            const isActive = checkbox.checked;
            checkbox.disabled = true;

            try {
                const response = await fetch('/?page=admin-users-toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ userId, active: isActive }),
                });

                if (!response.ok) {
                    checkbox.checked = !isActive;
                }
            } catch (error) {
                checkbox.checked = !isActive;
            } finally {
                checkbox.disabled = false;
            }
        });
    });
</script>
