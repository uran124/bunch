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
