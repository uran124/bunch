<?php /** @var array $users */ ?>
<?php /** @var array $groups */ ?>
<?php /** @var array $memberships */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $selectedGroupId = $selectedGroupId ?? null; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Рассылки</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Группы для рассылки', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="text-sm text-slate-500">Выберите группу или соберите новую и сохраните список участников.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="/?page=admin-broadcast"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <span class="material-symbols-rounded text-base">send</span>
                К рассылкам
            </a>
            <a
                href="/?page=admin-users"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <span class="material-symbols-rounded text-base">arrow_back</span>
                К пользователям
            </a>
        </div>
    </header>

    <?php if (!empty($message) && $message === 'saved'): ?>
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
            Группа сохранена.
        </div>
    <?php elseif (!empty($message) && $message === 'not-found'): ?>
        <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm">
            Не удалось найти выбранную группу.
        </div>
    <?php endif; ?>

    <form method="post" action="/?page=admin-group-create" class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <input type="hidden" name="group_id" id="group-id" value="<?php echo $selectedGroupId ? (int) $selectedGroupId : ''; ?>">

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-700">Название группы</span>
                <input
                    id="group-name"
                    name="name"
                    type="text"
                    value=""
                    placeholder="Например, VIP клиенты"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                >
            </label>
            <label class="flex flex-col gap-2 sm:col-span-2 lg:col-span-2">
                <span class="text-sm font-semibold text-slate-700">Описание</span>
                <textarea
                    id="group-description"
                    name="description"
                    rows="2"
                    placeholder="Коротко о том, кому отправляем сообщения"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                ></textarea>
            </label>
        </div>

        <div class="grid gap-3 lg:grid-cols-[1fr_2fr]">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-700">Группы</p>
                    <button type="button" id="reset-selection" class="text-xs font-semibold text-rose-600 hover:text-rose-700">Создать новую</button>
                </div>
                <div class="grid gap-3 lg:grid-cols-1">
                    <?php foreach ($groups as $group): ?>
                        <article
                            class="cursor-pointer rounded-xl border border-slate-100 bg-slate-50 p-4 transition hover:-translate-y-0.5 hover:shadow-sm"
                            data-group-id="<?php echo (int) $group['id']; ?>"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                <span class="text-sm font-semibold text-slate-600"><?php echo (int) $group['members']; ?> уч.</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-600"><?php echo htmlspecialchars($group['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-700">Пользователи</p>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow-xl">
                        <span class="material-symbols-rounded text-base">save</span>
                        Сохранить группу
                    </button>
                </div>
                <div id="group-user-list" class="divide-y divide-slate-100 rounded-xl border border-slate-100 bg-slate-50">
                    <?php foreach ($users as $user): ?>
                        <article class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                            <div class="space-y-1">
                                <div class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-sm text-slate-500"><?php echo htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <label class="relative inline-flex h-9 w-20 cursor-pointer items-center rounded-lg bg-white px-2 shadow-sm ring-1 ring-slate-200">
                                <input
                                    type="checkbox"
                                    class="peer sr-only group-user-toggle"
                                    name="users[]"
                                    value="<?php echo (int) $user['id']; ?>"
                                    data-user-id="<?php echo (int) $user['id']; ?>"
                                >
                                <span class="text-xs font-semibold text-slate-600">В группе</span>
                                <span class="ml-auto inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 transition peer-checked:bg-emerald-500">
                                    <span class="h-4 w-4 rounded-full bg-white shadow-sm transition peer-checked:translate-x-0.5"></span>
                                </span>
                            </label>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </form>
</section>

<div
    id="group-meta"
    data-membership='<?php echo json_encode($memberships ?? [], JSON_HEX_APOS | JSON_UNESCAPED_UNICODE); ?>'
    data-groups='<?php echo json_encode($groups ?? [], JSON_HEX_APOS | JSON_UNESCAPED_UNICODE); ?>'
    data-selected-id="<?php echo $selectedGroupId ? (int) $selectedGroupId : ''; ?>"
></div>

<script>
    const metaNode = document.getElementById('group-meta');
    const membership = metaNode.dataset.membership ? JSON.parse(metaNode.dataset.membership) : {};
    const groups = metaNode.dataset.groups ? JSON.parse(metaNode.dataset.groups) : [];
    const groupsMap = groups.reduce((acc, group) => {
        acc[group.id] = group;
        return acc;
    }, {});

    const groupIdInput = document.getElementById('group-id');
    const groupNameInput = document.getElementById('group-name');
    const groupDescriptionInput = document.getElementById('group-description');
    const groupCards = document.querySelectorAll('[data-group-id]');
    const userToggles = document.querySelectorAll('.group-user-toggle');
    const resetButton = document.getElementById('reset-selection');

    function applySelection(groupId) {
        groupIdInput.value = groupId || '';

        groupCards.forEach((card) => {
            const isActive = Number(card.dataset.groupId) === groupId;
            card.classList.toggle('ring-2', isActive);
            card.classList.toggle('ring-rose-200', isActive);
            card.classList.toggle('bg-white', isActive);
        });

        const group = groupsMap[groupId] || null;
        groupNameInput.value = group ? group.name : '';
        groupDescriptionInput.value = group ? (group.description || '') : '';

        const members = group ? (membership[groupId] || []) : [];
        userToggles.forEach((toggle) => {
            const userId = Number(toggle.dataset.userId);
            toggle.checked = group ? members.includes(userId) : false;
        });
    }

    groupCards.forEach((card) => {
        card.addEventListener('click', () => {
            applySelection(Number(card.dataset.groupId));
        });
    });

    resetButton.addEventListener('click', () => {
        applySelection(null);
        groupNameInput.focus();
    });

    const initialGroupId = metaNode.dataset.selectedId ? Number(metaNode.dataset.selectedId) : null;
    if (initialGroupId) {
        applySelection(initialGroupId);
    }
</script>
