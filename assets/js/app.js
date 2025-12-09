const pageId = document.body.dataset.page || '';

function updateCartIndicator(count) {
    document.querySelectorAll('[data-cart-count]').forEach((badge) => {
        badge.textContent = count;
        if (Number(count) > 0) {
            badge.classList.remove('hidden');
            badge.classList.add('flex');
        } else {
            badge.classList.add('hidden');
            badge.classList.remove('flex');
        }
    });
}

function updateCartTotal(total) {
    const target = document.querySelector('[data-cart-total]');
    if (target) {
        const number = Number(total || 0);
        target.textContent = number.toLocaleString('ru-RU', { maximumFractionDigits: 0 }) + ' ₽';
    }
}

async function addProductToCart(productId, qty = 1, attributes = []) {
    const response = await fetch('/?page=cart-add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ product_id: productId, qty, attributes }),
    });

    if (!response.ok) {
        throw new Error('Не удалось добавить товар в корзину');
    }

    const data = await response.json();
    if (!data.ok) {
        throw new Error(data.error || 'Ошибка добавления товара');
    }

    updateCartIndicator(data.totals?.count || 0);
    updateCartTotal(data.totals?.total || 0);

    return data;
}

function initCartPage() {
    const modeButtons = document.querySelectorAll('[data-order-mode]');
    const sections = document.querySelectorAll('[data-order-section]');

    const setMode = (mode) => {
        modeButtons.forEach((btn) => {
            const isActive = btn.dataset.orderMode === mode;
            btn.classList.toggle('border-rose-200', isActive);
            btn.classList.toggle('bg-rose-50', isActive);
            btn.classList.toggle('text-rose-700', isActive);
            btn.classList.toggle('shadow-sm', isActive);
        });

        sections.forEach((section) => {
            section.classList.toggle('hidden', section.dataset.orderSection !== mode);
        });
    };

    if (modeButtons.length) {
        setMode(modeButtons[0].dataset.orderMode || 'pickup');
    }

    modeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            setMode(button.dataset.orderMode);
        });
    });

    document.querySelectorAll('[data-add-accessory]').forEach((button) => {
        button.addEventListener('click', async () => {
            const productId = Number(button.dataset.productId || 0);
            button.disabled = true;
            button.classList.add('opacity-70');

            try {
                await addProductToCart(productId, 1, []);
                window.location.reload();
            } catch (error) {
                alert(error.message || 'Не удалось добавить товар');
            } finally {
                button.disabled = false;
                button.classList.remove('opacity-70');
            }
        });
    });
}

if (pageId === 'cart') {
    initCartPage();
}
