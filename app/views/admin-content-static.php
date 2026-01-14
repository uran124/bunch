<?php /** @var array $staticPages */ ?>
<?php /** @var array|null $editPage */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $isEditing = $editPage !== null; ?>
<?php $editPage = $editPage ?? []; ?>
<?php
$status = $_GET['status'] ?? null;
$editorMode = $editPage['content_format'] ?? 'visual';
$statusMessages = [
    'saved' => 'Страница сохранена.',
    'deleted' => 'Страница удалена.',
    'updated' => 'Статус страницы обновлён.',
    'error' => 'Не удалось сохранить данные. Проверьте поля и попробуйте ещё раз.',
    'notfound' => 'Страница не найдена.',
];
?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Контент · Статичные страницы</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Статичные страницы', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Создавайте страницы для меню и футера, управляйте их контентом и активностью.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/?page=admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                В панель
            </a>
        </div>
    </header>

    <?php if ($status && isset($statusMessages[$status])): ?>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
            <?php echo htmlspecialchars($statusMessages[$status], ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Форма</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-900">
                    <?php echo htmlspecialchars($isEditing ? 'Редактировать страницу' : 'Новая страница', ENT_QUOTES, 'UTF-8'); ?>
                </h2>
            </div>
            <?php if ($isEditing): ?>
                <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" href="/?page=admin-content-static">
                    <span class="material-symbols-rounded text-base">add</span>
                    Создать новую
                </a>
            <?php endif; ?>
        </div>

        <form class="mt-4 grid gap-4" action="/?page=admin-static-page-save" method="post">
            <?php if ($isEditing): ?>
                <input type="hidden" name="id" value="<?php echo (int) $editPage['id']; ?>">
            <?php endif; ?>
            <div class="grid gap-3 lg:grid-cols-2">
                <label class="space-y-2 text-sm font-semibold text-slate-700">
                    <span>Название страницы</span>
                    <input
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                        type="text"
                        name="title"
                        required
                        value="<?php echo htmlspecialchars($editPage['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-700">
                    <span>Slug (URL)</span>
                    <input
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                        type="text"
                        name="slug"
                        placeholder="about-company"
                        required
                        value="<?php echo htmlspecialchars($editPage['slug'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    >
                    <span class="text-xs font-normal text-slate-400">Используйте латиницу, цифры и дефисы. Страница будет доступна по /?page=static&slug=...</span>
                </label>
            </div>
            <div class="space-y-3 text-sm font-semibold text-slate-700">
                <span>Контент</span>
                <div class="flex flex-wrap gap-2">
                    <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm">
                        <input class="h-4 w-4" type="radio" name="editor_mode" value="visual" <?php echo $editorMode !== 'html' ? 'checked' : ''; ?>>
                        Визуальный редактор
                    </label>
                    <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm">
                        <input class="h-4 w-4" type="radio" name="editor_mode" value="html" <?php echo $editorMode === 'html' ? 'checked' : ''; ?>>
                        &lt;HTML&gt;
                    </label>
                </div>
                <div class="flex flex-wrap gap-2" data-editor-toolbar>
                    <button class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-rose-200 hover:text-rose-600" type="button" data-tag="b">B</button>
                    <button class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-rose-200 hover:text-rose-600" type="button" data-tag="i">I</button>
                    <button class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-rose-200 hover:text-rose-600" type="button" data-tag="h2">H2</button>
                    <button class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-rose-200 hover:text-rose-600" type="button" data-tag="h3">H3</button>
                    <button class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-rose-200 hover:text-rose-600" type="button" data-tag="li">LI</button>
                    <button class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-rose-200 hover:text-rose-600" type="button" data-tag="a">URL</button>
                </div>
                <input type="hidden" name="content" value="<?php echo htmlspecialchars($editPage['content'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-editor-content>
                <div
                    class="min-h-[160px] w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm focus-within:border-rose-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-rose-200 <?php echo $editorMode === 'html' ? 'hidden' : ''; ?>"
                    contenteditable="true"
                    data-editor-visual
                ><?php echo $editPage['content'] ?? ''; ?></div>
                <textarea
                    class="min-h-[160px] w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200 <?php echo $editorMode === 'html' ? '' : 'hidden'; ?>"
                    data-editor-html
                ><?php echo htmlspecialchars($editPage['content'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="grid gap-3 lg:grid-cols-5">
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm">
                    <input type="checkbox" name="show_in_footer" value="1" <?php echo !$isEditing || (int) ($editPage['show_in_footer'] ?? 0) === 1 ? 'checked' : ''; ?>>
                    Показать в футере
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm">
                    <input type="checkbox" name="show_in_menu" value="1" <?php echo !$isEditing || (int) ($editPage['show_in_menu'] ?? 0) === 1 ? 'checked' : ''; ?>>
                    Показать в меню
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-700">
                    <span>Номер столбика</span>
                    <select
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                        name="footer_column"
                    >
                        <?php $footerColumnValue = (int) ($editPage['footer_column'] ?? 1); ?>
                        <option value="1" <?php echo $footerColumnValue === 1 ? 'selected' : ''; ?>>1</option>
                        <option value="2" <?php echo $footerColumnValue === 2 ? 'selected' : ''; ?>>2</option>
                    </select>
                    <span class="text-xs font-normal text-slate-400">Используется для колонок футера.</span>
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm">
                    <input type="checkbox" name="is_active" value="1" <?php echo !$isEditing || (int) ($editPage['is_active'] ?? 0) === 1 ? 'checked' : ''; ?>>
                    Активна
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-700">
                    <span>Порядок</span>
                    <input
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                        type="number"
                        name="sort_order"
                        value="<?php echo htmlspecialchars((string) ($editPage['sort_order'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </label>
            </div>
            <div class="flex flex-wrap justify-end gap-2">
                <button class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-rose-200 hover:text-rose-600" type="reset">
                    Сбросить
                </button>
                <button class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700" type="submit">
                    <?php echo htmlspecialchars($isEditing ? 'Сохранить изменения' : 'Создать страницу', ENT_QUOTES, 'UTF-8'); ?>
                </button>
            </div>
        </form>
    </article>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid grid-cols-[70px_1.2fr_1fr_0.6fr_1fr_1fr_160px] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>ID</span>
            <span>Страница</span>
            <span>Расположение</span>
            <span>Колонка</span>
            <span>Статус</span>
            <span>Обновлено</span>
            <span class="text-right">Действие</span>
        </div>
        <?php foreach ($staticPages as $page): ?>
            <article class="grid grid-cols-[70px_1.2fr_1fr_0.6fr_1fr_1fr_160px] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div class="text-sm font-semibold text-slate-900">#<?php echo (int) $page['id']; ?></div>
                <div class="space-y-1">
                    <div class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-sm text-slate-500"><?php echo htmlspecialchars('/?page=static&slug=' . $page['slug'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="space-y-1 text-sm text-slate-700">
                    <div><?php echo (int) $page['show_in_footer'] === 1 ? 'Футер' : '—'; ?></div>
                    <div><?php echo (int) $page['show_in_menu'] === 1 ? 'Меню' : '—'; ?></div>
                </div>
                <div class="text-sm text-slate-700">
                    <?php echo (int) $page['show_in_footer'] === 1 ? (int) ($page['footer_column'] ?? 1) : '—'; ?>
                </div>
                <div class="text-sm text-slate-700">
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold <?php echo (int) $page['is_active'] === 1 ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'; ?>">
                        <span class="material-symbols-rounded text-base"><?php echo (int) $page['is_active'] === 1 ? 'toggle_on' : 'toggle_off'; ?></span>
                        <?php echo (int) $page['is_active'] === 1 ? 'Активна' : 'Выключена'; ?>
                    </span>
                </div>
                <div class="text-sm text-slate-700">
                    <?php echo htmlspecialchars($page['updated_at'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div class="flex flex-col items-end gap-2 text-sm font-semibold">
                    <a href="/?page=admin-content-static&edit_id=<?php echo (int) $page['id']; ?>" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                        <span class="material-symbols-rounded text-base">edit</span>
                        Изменить
                    </a>
                    <form action="/?page=admin-static-page-toggle" method="post">
                        <input type="hidden" name="id" value="<?php echo (int) $page['id']; ?>">
                        <input type="hidden" name="active" value="<?php echo (int) $page['is_active'] === 1 ? 0 : 1; ?>">
                        <button class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" type="submit">
                            <span class="material-symbols-rounded text-base"><?php echo (int) $page['is_active'] === 1 ? 'visibility_off' : 'visibility'; ?></span>
                            <?php echo (int) $page['is_active'] === 1 ? 'Деактивировать' : 'Активировать'; ?>
                        </button>
                    </form>
                    <form action="/?page=admin-static-page-delete" method="post" onsubmit="return confirm('Удалить страницу?');">
                        <input type="hidden" name="id" value="<?php echo (int) $page['id']; ?>">
                        <button class="inline-flex items-center gap-1 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-rose-700 hover:-translate-y-0.5 hover:border-rose-200" type="submit">
                            <span class="material-symbols-rounded text-base">delete</span>
                            Удалить
                        </button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<script>
    const editorModeInputs = document.querySelectorAll('input[name="editor_mode"]');
    const editorToolbar = document.querySelector('[data-editor-toolbar]');
    const editorContentInput = document.querySelector('[data-editor-content]');
    const editorVisual = document.querySelector('[data-editor-visual]');
    const editorHtml = document.querySelector('[data-editor-html]');
    const editorForm = document.querySelector('form[action="/?page=admin-static-page-save"]');

    const updateEditorMode = () => {
        const selectedMode = document.querySelector('input[name="editor_mode"]:checked')?.value;
        if (!editorToolbar || !editorVisual || !editorHtml) {
            return;
        }
        if (selectedMode === 'html') {
            editorHtml.value = editorVisual.innerHTML;
        } else {
            editorVisual.innerHTML = editorHtml.value;
        }
        editorToolbar.classList.toggle('hidden', selectedMode !== 'visual');
        editorVisual.classList.toggle('hidden', selectedMode === 'html');
        editorHtml.classList.toggle('hidden', selectedMode !== 'html');
    };

    const wrapSelection = (openTag, closeTag) => {
        if (!editorHtml) {
            return;
        }
        const start = editorHtml.selectionStart;
        const end = editorHtml.selectionEnd;
        const value = editorHtml.value;
        const before = value.slice(0, start);
        const selected = value.slice(start, end);
        const after = value.slice(end);
        editorHtml.value = `${before}${openTag}${selected}${closeTag}${after}`;
        const cursorPosition = start + openTag.length + selected.length + closeTag.length;
        editorHtml.setSelectionRange(cursorPosition, cursorPosition);
        editorHtml.focus();
    };

    const wrapList = () => {
        if (!editorHtml) {
            return;
        }
        const start = editorHtml.selectionStart;
        const end = editorHtml.selectionEnd;
        const value = editorHtml.value;
        const selected = value.slice(start, end);
        const lines = selected.split(/\r?\n/).filter(line => line.trim().length > 0);
        if (!lines.length) {
            return;
        }
        const listItems = lines.map(line => `  <li>${line.trim()}</li>`).join('\n');
        const replacement = `<ul>\n${listItems}\n</ul>`;
        editorHtml.value = `${value.slice(0, start)}${replacement}${value.slice(end)}`;
        const cursorPosition = start + replacement.length;
        editorHtml.setSelectionRange(cursorPosition, cursorPosition);
        editorHtml.focus();
    };

    const wrapLink = () => {
        if (!editorHtml) {
            return;
        }
        const start = editorHtml.selectionStart;
        const end = editorHtml.selectionEnd;
        const value = editorHtml.value;
        const selected = value.slice(start, end);
        if (!selected.trim()) {
            return;
        }
        const href = selected.trim();
        const replacement = `<a href="${href}">${selected}</a>`;
        editorHtml.value = `${value.slice(0, start)}${replacement}${value.slice(end)}`;
        const cursorPosition = start + replacement.length;
        editorHtml.setSelectionRange(cursorPosition, cursorPosition);
        editorHtml.focus();
    };

    editorModeInputs.forEach(input => {
        input.addEventListener('change', updateEditorMode);
    });

    if (editorToolbar) {
        editorToolbar.addEventListener('click', event => {
            const button = event.target.closest('button[data-tag]');
            if (!button) {
                return;
            }
            const tag = button.getAttribute('data-tag');
            const selectedMode = document.querySelector('input[name="editor_mode"]:checked')?.value;
            if (selectedMode === 'visual' && editorVisual) {
                editorVisual.focus();
                if (tag === 'b') {
                    document.execCommand('bold');
                    return;
                }
                if (tag === 'i') {
                    document.execCommand('italic');
                    return;
                }
                if (tag === 'h2' || tag === 'h3') {
                    document.execCommand('formatBlock', false, tag);
                    return;
                }
                if (tag === 'li') {
                    document.execCommand('insertUnorderedList');
                    return;
                }
                if (tag === 'a') {
                    const href = window.prompt('Введите URL', 'https://');
                    if (!href) {
                        return;
                    }
                    document.execCommand('createLink', false, href);
                    return;
                }
            }
            if (tag === 'li') {
                wrapList();
                return;
            }
            if (tag === 'a') {
                wrapLink();
                return;
            }
            wrapSelection(`<${tag}>`, `</${tag}>`);
        });
    }

    const syncContent = () => {
        if (!editorContentInput) {
            return;
        }
        const selectedMode = document.querySelector('input[name="editor_mode"]:checked')?.value;
        if (selectedMode === 'html') {
            editorContentInput.value = editorHtml ? editorHtml.value.trim() : '';
            return;
        }
        editorContentInput.value = editorVisual ? editorVisual.innerHTML.trim() : '';
    };

    if (editorForm) {
        editorForm.addEventListener('submit', () => {
            syncContent();
        });
    }

    updateEditorMode();
</script>
