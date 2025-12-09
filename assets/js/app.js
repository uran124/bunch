const pageId = document.body.dataset.page || '';

function formatCurrency(value) {
    return Number(value || 0).toLocaleString('ru-RU', { maximumFractionDigits: 0 }) + ' ₽';
}

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

function updateCartCountStatic(count) {
    document.querySelectorAll('[data-cart-count-static]').forEach((badge) => {
        badge.textContent = count;
    });
}

function updateCartTotal(total) {
    const target = document.querySelector('[data-cart-total]');
    if (target) {
        target.textContent = formatCurrency(total);
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
    updateCartCountStatic(data.totals?.count || 0);

    return data;
}

async function updateCartItemRequest(key, qty, attributes = []) {
    const response = await fetch('/?page=cart-update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ key, qty, attributes }),
    });

    if (!response.ok) {
        throw new Error('Не удалось обновить позицию');
    }

    const data = await response.json();
    if (!data.ok) {
        throw new Error(data.error || 'Ошибка обновления позиции');
    }

    return data;
}

async function removeCartItemRequest(key) {
    const response = await fetch('/?page=cart-remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ key }),
    });

    if (!response.ok) {
        throw new Error('Не удалось удалить позицию');
    }

    const data = await response.json();
    if (!data.ok) {
        throw new Error(data.error || 'Ошибка удаления позиции');
    }

    return data;
}

function getSelectedAttributesFromItem(itemEl) {
    const selected = [];
    itemEl.querySelectorAll('[data-attribute-group]').forEach((group) => {
        const active = group.querySelector('.attribute-selected') || group.querySelector('[data-selected="true"]');
        if (active) {
            selected.push(Number(active.dataset.valueId || 0));
        }
    });
    return selected;
}

function toggleAttributeButton(button, isActive) {
    button.classList.toggle('attribute-selected', isActive);
    button.dataset.selected = isActive ? 'true' : 'false';
    button.classList.toggle('border-rose-200', isActive);
    button.classList.toggle('bg-white', true);
    button.classList.toggle('text-rose-700', isActive);
    button.classList.toggle('shadow-sm', isActive);
    button.classList.toggle('shadow-rose-100', isActive);
    button.classList.toggle('border-slate-200', !isActive);
    button.classList.toggle('text-slate-700', !isActive);
}

function bindCartItem(itemEl) {
    const qtyInput = itemEl.querySelector('[data-qty-input]');
    const qtyDecrease = itemEl.querySelector('[data-qty-decrease]');
    const qtyIncrease = itemEl.querySelector('[data-qty-increase]');
    const lineTotal = itemEl.querySelector('[data-line-total]');
    const qtyLabel = itemEl.querySelector('[data-qty-label]');
    const removeButton = itemEl.querySelector('[data-remove-item]');

    const setLoading = (state) => {
        itemEl.classList.toggle('opacity-60', state);
        itemEl.classList.toggle('pointer-events-none', state);
    };

    const applyUpdate = async (nextQty) => {
        const safeQty = Math.max(1, Number(nextQty) || 1);
        const attributes = getSelectedAttributesFromItem(itemEl);
        setLoading(true);

        try {
            const data = await updateCartItemRequest(itemEl.dataset.itemKey, safeQty, attributes);
            const newQty = data.item?.qty || safeQty;
            qtyInput.value = newQty;
            if (qtyLabel) {
                qtyLabel.textContent = `${newQty} стеблей`;
            }
            if (lineTotal) {
                lineTotal.textContent = formatCurrency(data.item?.line_total || 0);
            }
            if (data.item?.key) {
                itemEl.dataset.itemKey = data.item.key;
            }
            updateCartIndicator(data.totals?.count || 0);
            updateCartTotal(data.totals?.total || 0);
            updateCartCountStatic(data.totals?.count || 0);
        } catch (error) {
            alert(error.message || 'Не удалось обновить позицию');
            qtyInput.value = safeQty;
        } finally {
            setLoading(false);
        }
    };

    if (qtyDecrease) {
        qtyDecrease.addEventListener('click', () => {
            const current = Math.max(1, Number(qtyInput.value) || 1);
            const next = Math.max(1, current - 1);
            applyUpdate(next);
        });
    }

    if (qtyIncrease) {
        qtyIncrease.addEventListener('click', () => {
            const current = Math.max(1, Number(qtyInput.value) || 1);
            applyUpdate(current + 1);
        });
    }

    if (qtyInput) {
        qtyInput.addEventListener('change', () => {
            applyUpdate(qtyInput.value);
        });
        qtyInput.addEventListener('blur', () => {
            applyUpdate(qtyInput.value);
        });
    }

    itemEl.querySelectorAll('[data-attribute-group]').forEach((group) => {
        const options = group.querySelectorAll('[data-attribute-option]');
        options.forEach((option) => {
            if (option.dataset.selected === 'true') {
                toggleAttributeButton(option, true);
            }
            option.addEventListener('click', () => {
                options.forEach((btn) => toggleAttributeButton(btn, btn === option));
                applyUpdate(qtyInput?.value || 1);
            });
        });
    });

    if (removeButton) {
        removeButton.addEventListener('click', async () => {
            const confirmed = window.confirm('Удалить товар из корзины? Да / Нет');
            if (!confirmed) return;

            removeButton.disabled = true;
            removeButton.classList.add('opacity-60');
            setLoading(true);

            try {
                const data = await removeCartItemRequest(itemEl.dataset.itemKey);
                itemEl.remove();
                updateCartIndicator(data.totals?.count || 0);
                updateCartTotal(data.totals?.total || 0);
                updateCartCountStatic(data.totals?.count || 0);

                if ((data.totals?.count || 0) === 0) {
                    window.location.reload();
                }
            } catch (error) {
                alert(error.message || 'Не удалось удалить позицию');
                removeButton.disabled = false;
                removeButton.classList.remove('opacity-60');
            } finally {
                setLoading(false);
            }
        });
    }
}

function initCartItems() {
    document.querySelectorAll('[data-cart-item]').forEach((item) => {
        bindCartItem(item);
    });
}

function initAccessories() {
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

function initOrderFlow() {
    const modeButtons = document.querySelectorAll('[data-order-mode]');
    const modal = document.querySelector('[data-order-modal]');
    const modalBody = modal?.querySelector('[data-order-modal-body]');
    const modalTitle = modal?.querySelector('[data-order-modal-title]');
    const applyButton = modal?.querySelector('[data-order-modal-apply]');
    const todayISO = new Date().toISOString().slice(0, 10);

    const orderState = {
        pickup: { date: todayISO, time: '' },
        delivery: {
            date: todayISO,
            time: '',
            address: '',
            recipientMode: 'self',
            recipient: { name: '', phone: '', note: '' },
        },
    };

    const summaries = {
        pickup: document.querySelector('[data-order-summary="pickup"]'),
        delivery: document.querySelector('[data-order-summary="delivery"]'),
    };

    let currentMode = null;

    const highlightMode = (mode) => {
        modeButtons.forEach((btn) => {
            const isActive = btn.dataset.orderMode === mode;
            btn.classList.toggle('border-rose-200', isActive);
            btn.classList.toggle('bg-rose-50', isActive);
            btn.classList.toggle('text-rose-700', isActive);
            btn.classList.toggle('shadow-sm', isActive);
        });
    };

    const formatDateLabel = (value) => {
        if (!value) return 'Уточните дату';
        if (value === todayISO) return 'Сегодня';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return 'Уточните дату';
        return date.toLocaleDateString('ru-RU', { day: '2-digit', month: 'short' });
    };

    const formatTimeLabel = (value) => (value ? value : 'Ближайшее');

    const renderSummary = (mode) => {
        const state = orderState[mode];
        if (!state || !summaries[mode]) return;

        const parts = [formatDateLabel(state.date), formatTimeLabel(state.time)];
        if (mode === 'delivery' && state.address) {
            const address = state.address.trim();
            parts.push(address.length > 40 ? `${address.slice(0, 40)}…` : address);
        }
        summaries[mode].textContent = parts.join(' · ');
        highlightMode(mode);
    };

    const closeModal = () => {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    const setRecipientMode = (container, mode) => {
        const extras = container.querySelectorAll('[data-recipient-extra]');
        const buttons = container.querySelectorAll('.recipient-btn');
        buttons.forEach((btn) => {
            const active = btn.dataset.recipientMode === mode;
            btn.classList.toggle('border-rose-100', active);
            btn.classList.toggle('bg-rose-50', active);
            btn.classList.toggle('text-rose-700', active);
            btn.classList.toggle('border-slate-200', !active);
            btn.classList.toggle('text-slate-700', !active);
        });
        extras.forEach((extra) => {
            extra.hidden = mode !== 'other';
        });
    };

    const openModal = (mode) => {
        if (!modal || !modalBody) return;
        currentMode = mode;
        modalBody.innerHTML = '';
        const template = document.getElementById(`order-template-${mode}`);
        if (template) {
            modalBody.appendChild(template.content.cloneNode(true));
        }

        const state = orderState[mode];
        if (modalTitle) {
            modalTitle.textContent = mode === 'pickup' ? 'Самовывоз' : 'Доставка';
        }

        if (mode === 'pickup') {
            const dateInput = modalBody.querySelector('[data-pickup-date]');
            const timeInput = modalBody.querySelector('[data-pickup-time]');
            if (dateInput) {
                dateInput.value = state.date || todayISO;
            }
            if (timeInput) {
                timeInput.value = state.time || '';
            }
        }

        if (mode === 'delivery') {
            const dateInput = modalBody.querySelector('[data-delivery-date]');
            const timeInput = modalBody.querySelector('[data-delivery-time]');
            const addressInput = modalBody.querySelector('[data-delivery-address]');
            const savedSelect = modalBody.querySelector('[data-delivery-saved]');
            const recipientButtons = modalBody.querySelectorAll('.recipient-btn');

            if (dateInput) {
                dateInput.value = state.date || todayISO;
            }
            if (timeInput) {
                timeInput.value = state.time || '';
            }
            if (addressInput) {
                addressInput.value = state.address || '';
            }
            if (savedSelect) {
                savedSelect.addEventListener('change', () => {
                    const selectedValue = savedSelect.value || '';
                    if (addressInput) {
                        addressInput.value = selectedValue;
                    }
                });
            }

            if (recipientButtons.length) {
                setRecipientMode(modalBody, state.recipientMode || 'self');
                recipientButtons.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        setRecipientMode(modalBody, btn.dataset.recipientMode || 'self');
                    });
                });
            }

            if (state.recipientMode === 'other') {
                const nameInput = modalBody.querySelector('[data-recipient-name]');
                const phoneInput = modalBody.querySelector('[data-recipient-phone]');
                const noteInput = modalBody.querySelector('[data-recipient-note]');
                if (nameInput) nameInput.value = state.recipient.name || '';
                if (phoneInput) phoneInput.value = state.recipient.phone || '';
                if (noteInput) noteInput.value = state.recipient.note || '';
            }
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    const collectModalState = () => {
        if (!modalBody || !currentMode) return null;

        if (currentMode === 'pickup') {
            const dateInput = modalBody.querySelector('[data-pickup-date]');
            const timeInput = modalBody.querySelector('[data-pickup-time]');
            return {
                date: dateInput?.value || todayISO,
                time: timeInput?.value || '',
            };
        }

        const dateInput = modalBody.querySelector('[data-delivery-date]');
        const timeInput = modalBody.querySelector('[data-delivery-time]');
        const addressInput = modalBody.querySelector('[data-delivery-address]');
        const activeRecipient = modalBody.querySelector('.recipient-btn.border-rose-100') || modalBody.querySelector('[data-recipient-mode="self"]');
        const mode = activeRecipient?.dataset.recipientMode || 'self';

        const state = {
            date: dateInput?.value || todayISO,
            time: timeInput?.value || '',
            address: addressInput?.value?.trim() || '',
            recipientMode: mode,
            recipient: { name: '', phone: '', note: '' },
        };

        if (mode === 'other') {
            state.recipient.name = modalBody.querySelector('[data-recipient-name]')?.value || '';
            state.recipient.phone = modalBody.querySelector('[data-recipient-phone]')?.value || '';
            state.recipient.note = modalBody.querySelector('[data-recipient-note]')?.value || '';
        }

        return state;
    };

    if (applyButton) {
        applyButton.addEventListener('click', () => {
            if (!currentMode) return;
            const newState = collectModalState();
            if (newState) {
                orderState[currentMode] = newState;
                renderSummary(currentMode);
            }
            closeModal();
        });
    }

    modal?.querySelectorAll('[data-order-modal-close]').forEach((btn) => {
        btn.addEventListener('click', closeModal);
    });

    modeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            openModal(button.dataset.orderMode);
        });
    });

    renderSummary('pickup');
    renderSummary('delivery');
    highlightMode('pickup');
}

function initCartPage() {
    initCartItems();
    initAccessories();
    initOrderFlow();
}

if (pageId === 'cart') {
    initCartPage();
}

function buildStatusBadgeClass(status) {
    switch (status) {
        case 'delivered':
            return 'bg-emerald-50 text-emerald-700 ring-emerald-100';
        case 'cancelled':
            return 'bg-rose-50 text-rose-700 ring-rose-100';
        case 'delivering':
            return 'bg-sky-50 text-sky-700 ring-sky-100';
        case 'confirmed':
            return 'bg-amber-50 text-amber-700 ring-amber-100';
        default:
            return 'bg-slate-50 text-slate-700 ring-slate-100';
    }
}

function createHistoryCard(order) {
    const article = document.createElement('article');
    article.className = 'flex gap-3 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm ring-1 ring-slate-100 sm:gap-4 sm:p-4';
    article.dataset.orderCard = 'true';

    const imageWrap = document.createElement('div');
    imageWrap.className = 'h-16 w-20 overflow-hidden rounded-xl bg-slate-100';
    const img = document.createElement('img');
    img.src = order.item?.image || '/assets/images/products/bouquet.svg';
    img.alt = order.item?.title || 'Товар';
    img.className = 'h-full w-full object-cover';
    imageWrap.appendChild(img);

    const body = document.createElement('div');
    body.className = 'flex-1 space-y-2';

    const header = document.createElement('div');
    header.className = 'flex flex-wrap items-center justify-between gap-2 text-sm font-semibold text-slate-900';

    const headerLeft = document.createElement('div');
    headerLeft.className = 'flex flex-wrap items-center gap-2';
    const numberEl = document.createElement('span');
    numberEl.textContent = order.number;
    const dateEl = document.createElement('span');
    dateEl.className = 'text-slate-500';
    dateEl.textContent = `· ${order.createdAt}`;
    headerLeft.append(numberEl, dateEl);

    const badge = document.createElement('span');
    badge.className = `inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${buildStatusBadgeClass(order.status)}`;
    const badgeIcon = document.createElement('span');
    badgeIcon.className = 'material-symbols-rounded text-base';
    badgeIcon.textContent = order.status === 'cancelled' ? 'block' : 'verified';
    const badgeText = document.createElement('span');
    badgeText.textContent = order.statusLabel;
    badge.append(badgeIcon, badgeText);

    header.append(headerLeft, badge);

    const titleRow = document.createElement('p');
    titleRow.className = 'text-sm font-semibold text-slate-900';
    if (order.item) {
        titleRow.textContent = `${order.item.title} ×${order.item.qty}`;
    } else {
        titleRow.textContent = 'Состав уточняется';
    }

    const priceHint = document.createElement('p');
    priceHint.className = 'text-xs text-slate-600';
    if (order.item?.price) {
        priceHint.textContent = order.item.price;
    } else {
        priceHint.textContent = order.total;
    }

    const footer = document.createElement('div');
    footer.className = 'flex flex-wrap items-center justify-between gap-2 text-sm text-slate-700';
    const delivery = document.createElement('span');
    delivery.className = 'inline-flex items-center gap-1';
    const icon = document.createElement('span');
    icon.className = 'material-symbols-rounded text-base text-slate-500';
    icon.textContent = order.deliveryType === 'Доставка' ? 'local_shipping' : 'storefront';
    const deliveryText = document.createElement('span');
    deliveryText.textContent = order.deliveryType;
    delivery.append(icon, deliveryText);
    if (order.scheduled) {
        const scheduled = document.createElement('span');
        scheduled.textContent = `· ${order.scheduled}`;
        delivery.append(scheduled);
    }

    const total = document.createElement('span');
    total.className = 'text-base font-semibold text-slate-900';
    total.textContent = order.total;

    footer.append(delivery, total);

    body.append(header, titleRow, priceHint, footer);
    article.append(imageWrap, body);

    return article;
}

function initOrdersHistory() {
    const container = document.querySelector('[data-orders-history]');
    if (!container) return;

    const list = container.querySelector('[data-history-list]');
    const loader = container.querySelector('[data-history-loader]');
    const endMarker = container.querySelector('[data-history-end]');
    const sentinel = container.querySelector('[data-history-sentinel]');
    const limit = Number(container.dataset.limit || 10);
    const endpoint = container.dataset.endpoint || '/?page=orders-history';

    let hasMore = container.dataset.hasMore === 'true';
    let currentPage = 1;
    let loading = false;

    if (loader) {
        loader.hidden = !hasMore;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                loadMore();
            }
        });
    }, { rootMargin: '200px' });

    const stopLoading = () => {
        if (loader) loader.hidden = true;
        if (endMarker) endMarker.hidden = false;
        observer.disconnect();
    };

    const showError = (message) => {
        const alert = document.createElement('div');
        alert.className = 'rounded-2xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700';
        alert.textContent = message || 'Не удалось загрузить заказы';
        list.appendChild(alert);
    };

    const loadMore = async () => {
        if (loading || !hasMore) return;
        loading = true;
        if (loader) loader.hidden = false;

        const nextPage = currentPage + 1;

        try {
            const response = await fetch(`${endpoint}&page=${nextPage}&limit=${limit}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error('Ошибка загрузки истории');
            }

            const data = await response.json();
            if (!data.ok) {
                throw new Error(data.error || 'Ошибка загрузки истории');
            }

            (data.orders || []).forEach((order) => {
                list.appendChild(createHistoryCard(order));
            });

            hasMore = Boolean(data.hasMore);
            currentPage = nextPage;

            if (!hasMore) {
                stopLoading();
            }
        } catch (error) {
            stopLoading();
            showError(error.message);
        } finally {
            loading = false;
        }
    };

    if (hasMore && sentinel) {
        observer.observe(sentinel);
    } else if (!hasMore) {
        stopLoading();
    }
}

async function updateNotificationSettings(payload) {
    const response = await fetch('/?page=account-notifications', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ notifications: payload }),
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok || !data.ok) {
        throw new Error(data.error || 'Не удалось обновить уведомления');
    }
}

function initNotificationToggles() {
    const toggles = Array.from(document.querySelectorAll('[data-notification-toggle]'));
    if (!toggles.length) return;

    const status = document.querySelector('[data-notification-status]');

    const collect = () => {
        const payload = {};
        toggles.forEach((toggle) => {
            const code = toggle.dataset.notificationToggle;
            if (!code) return;
            payload[code] = toggle.disabled ? true : toggle.checked;
        });
        return payload;
    };

    const showStatus = (message) => {
        if (!status) return;
        status.textContent = message;
        status.classList.remove('hidden');
        setTimeout(() => status.classList.add('hidden'), 2500);
    };

    toggles.forEach((toggle) => {
        if (toggle.disabled) return;

        toggle.addEventListener('change', async () => {
            try {
                await updateNotificationSettings(collect());
                showStatus('Настройки обновлены.');
            } catch (error) {
                toggle.checked = !toggle.checked;
                alert(error.message || 'Не удалось сохранить настройку');
            }
        });
    });
}

async function submitPinChange(pin, pinConfirm) {
    const response = await fetch('/?page=account-pin', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ pin, pin_confirm: pinConfirm }),
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok || !data.ok) {
        throw new Error(data.error || 'Не удалось обновить PIN');
    }
}

function initPinModal() {
    const modal = document.querySelector('[data-pin-modal]');
    const openers = document.querySelectorAll('[data-open-pin-modal]');
    if (!modal || !openers.length) return;

    const inputs = Array.from(modal.querySelectorAll('[data-pin-input]'));
    const success = modal.querySelector('[data-pin-success]');
    const errorBox = modal.querySelector('[data-pin-error]');
    const closeButtons = modal.querySelectorAll('[data-close-pin-modal]');
    let saving = false;

    const setMessage = (type, text = '') => {
        if (success) success.classList.add('hidden');
        if (errorBox) errorBox.classList.add('hidden');

        if (type === 'success' && success) {
            success.textContent = text;
            success.classList.remove('hidden');
        }

        if (type === 'error' && errorBox) {
            errorBox.textContent = text;
            errorBox.classList.remove('hidden');
        }
    };

    const orderedInputs = (group) =>
        inputs
            .filter((input) => input.dataset.pinGroup === group)
            .sort((a, b) => Number(a.dataset.pinIndex || 0) - Number(b.dataset.pinIndex || 0));

    const reset = () => {
        inputs.forEach((input) => {
            input.value = '';
        });
        setMessage(null);
    };

    const open = () => {
        reset();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        const first = orderedInputs('new')[0];
        first?.focus();
    };

    const close = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    const collect = (group) => orderedInputs(group).map((input) => input.value.trim()).join('');

    const maybeSubmit = async () => {
        const pin = collect('new');
        const pinConfirm = collect('confirm');

        if (pin.length !== 4 || pinConfirm.length !== 4) {
            return;
        }

        setMessage(null);
        saving = true;

        try {
            await submitPinChange(pin, pinConfirm);
            setMessage('success', 'PIN обновлен.');
            setTimeout(() => {
                close();
            }, 700);
        } catch (error) {
            setMessage('error', error.message || 'Не удалось сохранить PIN');
        } finally {
            saving = false;
        }
    };

    inputs.forEach((input) => {
        input.addEventListener('input', () => {
            const cleaned = (input.value || '').replace(/\D/g, '').slice(-1);
            input.value = cleaned;

            const group = input.dataset.pinGroup;
            const index = Number(input.dataset.pinIndex || 0);

            if (cleaned !== '') {
                if (group === 'new' && index === 3) {
                    orderedInputs('confirm')[0]?.focus();
                } else {
                    const next = orderedInputs(group)[index + 1];
                    next?.focus();
                }
            }

            if (!saving && collect('new').length === 4 && collect('confirm').length === 4) {
                maybeSubmit();
            }
        });

        input.addEventListener('keydown', (event) => {
            if (event.key !== 'Backspace') return;
            if (input.value !== '') return;

            const group = input.dataset.pinGroup;
            const index = Number(input.dataset.pinIndex || 0);
            const prev = orderedInputs(group)[index - 1];
            prev?.focus();
        });
    });

    openers.forEach((button) => button.addEventListener('click', open));
    closeButtons.forEach((button) => button.addEventListener('click', close));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            close();
        }
    });
}

function initAccountPage() {
    initNotificationToggles();
    initPinModal();
}

if (pageId === 'account') {
    initAccountPage();
}

if (pageId === 'orders') {
    initOrdersHistory();
}
