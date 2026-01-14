<?php /** @var array|null $page */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="mx-auto flex w-full max-w-3xl flex-col gap-6">
    <?php if (!$page): ?>
        <div class="rounded-3xl border border-rose-200 bg-rose-50 p-6 text-sm text-rose-900 shadow-sm">
            Страница не найдена или отключена.
        </div>
    <?php else: ?>
        <header class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Информация</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
        </header>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 text-base text-slate-700 shadow-sm">
            <?php echo nl2br(htmlspecialchars((string) $page['content'], ENT_QUOTES, 'UTF-8')); ?>
        </div>
    <?php endif; ?>
</section>
