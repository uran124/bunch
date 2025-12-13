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
    try {
        const data = JSON.parse(itemEl.dataset.selectedAttributes || '[]');
        if (Array.isArray(data) && data.length) {
            return data.map((id) => Number(id)).filter(Boolean);
        }
    } catch (e) {
        // ignore
    }

    const fallback = [];
    itemEl.querySelectorAll('[data-attribute-group]').forEach((group) => {
        const active = group.querySelector('.attribute-selected') || group.querySelector('[data-selected="true"]');
        if (active) {
            fallback.push(Number(active.dataset.valueId || 0));
        }
    });
    return fallback;
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
    const attributePreview = itemEl.querySelector('[data-attribute-preview]');

    const setLoading = (state) => {
        itemEl.classList.toggle('opacity-60', state);
        itemEl.classList.toggle('pointer-events-none', state);
    };

    const extractValueIds = (attributes = []) => attributes.map((attr) => Number(attr.value_id || 0)).filter(Boolean);

    const updatePreview = (attributes = []) => {
        if (!attributePreview) return;
        const priority = [1, 3, 8];
        const priorityLines = [];
        const fallback = [];

        attributes.forEach((attr) => {
            const line = `${attr.label}: ${attr.value}`;
            if (priority.includes(Number(attr.attribute_id))) {
                priorityLines.push(line);
            } else if (fallback.length < 3) {
                fallback.push(line);
            }
        });

        const lines = priorityLines.length ? priorityLines : fallback;
        attributePreview.innerHTML = '';
        lines.forEach((line) => {
            const span = document.createElement('span');
            span.className = 'inline-flex items-center gap-1';
            const dot = document.createElement('span');
            dot.className = 'h-1.5 w-1.5 rounded-full bg-rose-200';
            span.appendChild(dot);
            span.appendChild(document.createTextNode(line));
            attributePreview.appendChild(span);
        });
    };

    const updateAttributeDataset = (selectedIds = []) => {
        itemEl.dataset.selectedAttributes = JSON.stringify(selectedIds);
        itemEl.querySelectorAll('[data-attribute-modal-trigger]').forEach((trigger) => {
            const raw = trigger.dataset.attributeData || '[]';
            let rows = [];
            try {
                rows = JSON.parse(raw);
            } catch (e) {
                rows = [];
            }

            rows = (rows || []).map((row) => {
                const next = { ...row };
                next.selected = null;
                (next.values || []).forEach((value) => {
                    if (selectedIds.includes(Number(value.id))) {
                        next.selected = Number(value.id);
                    }
                });
                return next;
            });

            trigger.dataset.attributeData = JSON.stringify(rows);
        });
    };

    const applyUpdate = async (nextQty, customAttributes) => {
        const safeQty = Math.max(1, Number(nextQty) || 1);
        const attributes = Array.isArray(customAttributes)
            ? customAttributes
            : getSelectedAttributesFromItem(itemEl);
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
            const selectedIds = extractValueIds(data.item?.attributes || []);
            updateAttributeDataset(selectedIds);
            updatePreview(data.item?.attributes || []);
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

    itemEl._applyUpdate = applyUpdate;

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
    const orderSection = document.querySelector('[data-order-flow]');
    const submitButton = document.querySelector('[data-submit-order]');
    if (!orderSection || !submitButton) return;

    const modeButtons = Array.from(orderSection.querySelectorAll('[data-order-mode]'));
    const dateInput = orderSection.querySelector('[data-order-date]');
    const timeInput = orderSection.querySelector('[data-order-time]');
    const deliveryExtra = orderSection.querySelector('[data-delivery-extra]');
    const addressSelect = orderSection.querySelector('[data-address-select]');
    const addressInput = orderSection.querySelector('[data-address-input]');
    const addressNew = orderSection.querySelector('[data-address-new]');
    const deliveryHint = orderSection.querySelector('[data-delivery-pricing-hint]');
    const recipientButtons = Array.from(orderSection.querySelectorAll('.recipient-btn'));
    const recipientExtra = orderSection.querySelectorAll('[data-recipient-extra]');
    const recipientName = orderSection.querySelector('[data-recipient-name]');
    const recipientPhone = orderSection.querySelector('[data-recipient-phone]');
    const commentInput = orderSection.querySelector('[data-order-comment]');

    const addresses = (() => {
        try {
            return JSON.parse(orderSection.dataset.addresses || '[]');
        } catch (e) {
            return [];
        }
    })();

    const deliveryZones = (() => {
        try {
            return JSON.parse(orderSection.dataset.deliveryZones || '[]');
        } catch (e) {
            return [];
        }
    })();

    const dadataConfig = (() => {
        try {
            return JSON.parse(orderSection.dataset.dadataConfig || '{}');
        } catch (e) {
            return {};
        }
    })();

    const deliveryPricingVersion = orderSection.dataset.deliveryPricingVersion || null;
    let lastDeliveryQuote = null;

    let currentMode = 'pickup';

    const findAddressById = (id) => addresses.find((item) => Number(item.raw?.id || 0) === Number(id));

    const highlightMode = (mode) => {
        modeButtons.forEach((btn) => {
            const isActive = btn.dataset.orderMode === mode;
            btn.classList.toggle('border-rose-200', isActive);
            btn.classList.toggle('bg-rose-50', isActive);
            btn.classList.toggle('text-rose-700', isActive);
            btn.classList.toggle('shadow-sm', isActive);
            btn.classList.toggle('border-slate-200', !isActive);
            btn.classList.toggle('bg-slate-50', !isActive);
            btn.classList.toggle('text-slate-800', !isActive);
        });
    };

    const setRecipientMode = (mode) => {
        recipientButtons.forEach((btn) => {
            const active = btn.dataset.recipientMode === mode;
            btn.classList.toggle('border-rose-100', active);
            btn.classList.toggle('bg-rose-50', active);
            btn.classList.toggle('text-rose-700', active);
            btn.classList.toggle('border-slate-200', !active);
            btn.classList.toggle('text-slate-700', !active);
        });
        recipientExtra.forEach((extra) => {
            extra.hidden = mode !== 'other';
        });
    };

    const setRecipientFromAddress = (address) => {
        const recipientNameValue = address?.raw?.recipient_name || '';
        const recipientPhoneValue = address?.raw?.recipient_phone || '';
        const hasRecipient = recipientNameValue || recipientPhoneValue;

        if (hasRecipient) {
            setRecipientMode('other');
            if (recipientName) recipientName.value = recipientNameValue;
            if (recipientPhone) recipientPhone.value = recipientPhoneValue;
        } else {
            setRecipientMode('self');
            if (recipientName) recipientName.value = '';
            if (recipientPhone) recipientPhone.value = '';
        }
    };

    const setAddressFromSelect = () => {
        if (!addressSelect || !addressInput) return;
        const selectedOption = addressSelect.selectedOptions[0];
        if (selectedOption) {
            addressInput.value = selectedOption.dataset.addressText || '';
            const chosen = findAddressById(selectedOption.value);
            setRecipientFromAddress(chosen);
        } else {
            setRecipientFromAddress(null);
        }
    };

    const toggleDelivery = (mode) => {
        currentMode = mode;
        if (deliveryExtra) {
            deliveryExtra.hidden = mode !== 'delivery';
        }
        highlightMode(mode);

        if (mode === 'delivery') {
            setAddressFromSelect();
        }
    };

    modeButtons.forEach((button) => {
        button.addEventListener('click', () => toggleDelivery(button.dataset.orderMode || 'pickup'));
    });

    recipientButtons.forEach((button) => {
        button.addEventListener('click', () => setRecipientMode(button.dataset.recipientMode || 'self'));
    });

    addressSelect?.addEventListener('change', setAddressFromSelect);

    addressNew?.addEventListener('click', () => {
        if (addressSelect) {
            addressSelect.selectedIndex = -1;
        }
        if (addressInput) {
            addressInput.value = '';
            addressInput.focus();
        }
        setRecipientFromAddress(null);
    });

    const buildPolygon = (zone) => {
        const closed = [...(zone.polygon || [])];
        const first = zone.polygon?.[0];
        if (first) {
            const last = zone.polygon[zone.polygon.length - 1];
            if (first[0] !== last[0] || first[1] !== last[1]) {
                closed.push(first);
            }
        }
        return turf.polygon([closed]);
    };

    const findZoneForPoint = (coords) => {
        if (!Array.isArray(coords) || coords.length < 2) return null;
        const point = turf.point(coords);
        for (const zone of deliveryZones) {
            const polygon = buildPolygon(zone);
            if (turf.booleanPointInPolygon(point, polygon)) {
                return zone;
            }
        }
        return null;
    };

    const setDeliveryHint = (text, tone = 'muted') => {
        if (!deliveryHint) return;
        deliveryHint.textContent = text;
        deliveryHint.classList.toggle('text-slate-600', tone === 'muted');
        deliveryHint.classList.toggle('text-emerald-700', tone === 'success');
        deliveryHint.classList.toggle('text-amber-700', tone === 'warn');
    };

    const geocodeWithDadata = async (addressText) => {
        if (!addressText || !dadataConfig.apiKey || !dadataConfig.secretKey) return null;

        const response = await fetch('https://cleaner.dadata.ru/api/v1/clean/address', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Authorization: `Token ${dadataConfig.apiKey}`,
                'X-Secret': dadataConfig.secretKey,
            },
            body: JSON.stringify([addressText]),
        });

        if (!response.ok) return null;
        const data = await response.json().catch(() => null);
        if (!Array.isArray(data) || !data[0]) return null;
        const row = data[0];
        if (!row.geo_lon || !row.geo_lat) return null;

        return {
            lon: Number(row.geo_lon),
            lat: Number(row.geo_lat),
            qc: row.qc_geo,
            label: row.result || addressText,
        };
    };

    const updateDeliveryQuote = async () => {
        if (!addressInput || currentMode !== 'delivery') return;
        const addressText = (addressInput.value || '').trim();
        if (!addressText) {
            setDeliveryHint('Введите адрес, чтобы получить подсказку DaData, геокодировать точку и определить зону доставки.');
            lastDeliveryQuote = null;
            return;
        }

        setDeliveryHint('Ищем адрес в DaData и определяем зону...', 'muted');

        try {
            const geocoded = await geocodeWithDadata(addressText);
            if (!geocoded) {
                setDeliveryHint('Не удалось получить координаты этого адреса. Попробуйте уточнить улицу и дом.', 'warn');
                lastDeliveryQuote = null;
                return;
            }

            const zone = findZoneForPoint([geocoded.lon, geocoded.lat]);
            if (!zone) {
                setDeliveryHint('Адрес найден, но не попал ни в одну зону. Добавьте полигон или расширьте границы.', 'warn');
                lastDeliveryQuote = null;
                return;
            }

            lastDeliveryQuote = {
                address_text: addressText,
                label: geocoded.label,
                lat: geocoded.lat,
                lon: geocoded.lon,
                zone_id: zone.id,
                delivery_price: zone.price,
                zone_version: deliveryPricingVersion,
                zone_calculated_at: new Date().toISOString(),
                location_source: 'dadata',
                geo_quality: geocoded.qc,
            };

            setDeliveryHint(
                `${geocoded.label} в зоне «${zone.name}». Доставка ${zone.price.toLocaleString('ru-RU')} ₽`,
                'success',
            );
        } catch (e) {
            setDeliveryHint('Ошибка при расчёте зоны. Проверьте соединение и попробуйте снова.', 'warn');
            lastDeliveryQuote = null;
        }
    };

    addressInput?.addEventListener('blur', updateDeliveryQuote);
    addressInput?.addEventListener('change', updateDeliveryQuote);

    const collectPayload = () => {
        const payload = {
            mode: currentMode,
            date: dateInput?.value || '',
            time: timeInput?.value || '',
            comment: commentInput?.value || '',
        };

        if (currentMode === 'delivery') {
            payload.address_id = addressSelect ? Number(addressSelect.value || 0) || null : null;
            payload.address_text = addressInput?.value || '';

            const activeRecipient = orderSection.querySelector('.recipient-btn.border-rose-100') || orderSection.querySelector('[data-recipient-mode="self"]');
            const recipientMode = activeRecipient?.dataset.recipientMode || 'self';
            if (recipientMode === 'other') {
                payload.recipient = {
                    name: recipientName?.value || '',
                    phone: recipientPhone?.value || '',
                };
            }

            if (lastDeliveryQuote) {
                payload.delivery_price = lastDeliveryQuote.delivery_price;
                payload.zone_id = lastDeliveryQuote.zone_id;
                payload.delivery_pricing_version = lastDeliveryQuote.zone_version;
                payload.zone_calculated_at = lastDeliveryQuote.zone_calculated_at;
                payload.address = {
                    location_source: lastDeliveryQuote.location_source,
                    geo_quality: lastDeliveryQuote.geo_quality,
                    lat: lastDeliveryQuote.lat,
                    lon: lastDeliveryQuote.lon,
                    zone_id: lastDeliveryQuote.zone_id,
                    zone_version: lastDeliveryQuote.zone_version,
                    zone_calculated_at: lastDeliveryQuote.zone_calculated_at,
                };
            }
        }

        return payload;
    };

    const submitOrder = async () => {
        submitButton.disabled = true;
        submitButton.classList.add('opacity-70');

        try {
            const response = await fetch('/?page=cart-checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(collectPayload()),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok || !data.ok) {
                throw new Error(data.error || 'Не удалось сохранить заказ');
            }

            window.location.href = '/?page=orders';
        } catch (error) {
            alert(error.message || 'Ошибка оформления заказа');
        } finally {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-70');
        }
    };

    submitButton.addEventListener('click', submitOrder);

    if (addresses.length && !addressInput?.value) {
        addressInput.value = orderSection.dataset.primaryAddress || addresses[0]?.address || '';
    }

    setRecipientMode('self');
    toggleDelivery('pickup');
}

function initAttributeModal() {
    const triggers = document.querySelectorAll('[data-attribute-modal-trigger]');
    const modal = document.querySelector('[data-attribute-modal]');
    const body = modal?.querySelector('[data-attribute-modal-body]');
    const title = modal?.querySelector('[data-attribute-modal-title]');
    const applyButton = modal?.querySelector('[data-attribute-modal-apply]');

    if (!triggers.length || !modal || !body) return;

    let activeItem = null;
    let activeRows = [];

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        activeItem = null;
        activeRows = [];
    };

    modal.querySelectorAll('[data-attribute-modal-close]').forEach((btn) => btn.addEventListener('click', closeModal));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    const renderRows = (rows) => {
        activeRows = rows.map((row) => ({ ...row }));
        body.innerHTML = '';
        if (!activeRows.length) {
            const empty = document.createElement('p');
            empty.className = 'text-sm text-slate-500';
            empty.textContent = 'Дополнительные параметры отсутствуют.';
            body.appendChild(empty);
            return;
        }

        activeRows.forEach((row) => {
            const card = document.createElement('div');
            card.className = 'space-y-2 rounded-xl border border-slate-100 bg-slate-50 p-3';

            const header = document.createElement('div');
            header.className = 'flex items-center justify-between gap-2';
            const name = document.createElement('p');
            name.className = 'text-sm font-semibold text-slate-900';
            name.textContent = row.name || 'Атрибут';
            const scope = document.createElement('span');
            scope.className = 'text-[11px] font-semibold text-slate-500';
            scope.textContent = row.applies_to === 'bouquet' ? 'к букету' : 'к стеблю';
            header.appendChild(name);
            header.appendChild(scope);
            card.appendChild(header);

            const values = document.createElement('div');
            values.className = 'flex flex-wrap gap-2';

            (row.values || []).forEach((value) => {
                const pill = document.createElement('button');
                pill.type = 'button';
                const isActive = Number(row.selected) === Number(value.id);
                pill.className = `attribute-option inline-flex items-center gap-1 rounded-lg border px-3 py-2 text-xs font-semibold transition ${isActive ? 'border-rose-200 bg-white text-rose-700 shadow-sm shadow-rose-100 attribute-selected' : 'border-slate-200 bg-white text-slate-700 hover:border-rose-200 hover:text-rose-700'}`;
                pill.textContent = value.value;

                if (Number(value.price_delta || 0) !== 0) {
                    const delta = document.createElement('span');
                    delta.className = 'text-[11px] font-semibold text-rose-500';
                    const price = Number(value.price_delta || 0);
                    delta.textContent = `${price > 0 ? '+' : '−'} ${Math.abs(price).toLocaleString('ru-RU')} ₽`;
                    pill.appendChild(delta);
                }

                pill.addEventListener('click', () => {
                    row.selected = Number(value.id);
                    values.querySelectorAll('.attribute-option').forEach((btn) => {
                        toggleAttributeButton(btn, btn === pill);
                    });
                });

                values.appendChild(pill);
            });

            card.appendChild(values);
            body.appendChild(card);
        });
    };

    const collectSelectedIds = () =>
        activeRows
            .map((row) => Number(row.selected || 0))
            .filter(Boolean);

    const applySelection = async () => {
        if (!activeItem) return;
        const qtyInput = activeItem.querySelector('[data-qty-input]');
        const qtyValue = qtyInput?.value || 1;
        const selected = collectSelectedIds();
        try {
            await activeItem._applyUpdate?.(qtyValue, selected);
            closeModal();
        } catch (error) {
            alert(error.message || 'Не удалось применить атрибуты');
        }
    };

    applyButton?.addEventListener('click', applySelection);

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            if (!modal || !body) return;
            const raw = trigger.dataset.attributeData || '[]';
            let parsed = [];
            try {
                parsed = JSON.parse(raw);
            } catch (e) {
                parsed = [];
            }

            if (title) {
                title.textContent = trigger.dataset.attributeTitle || 'Параметры';
            }

            activeItem = trigger.closest('[data-cart-item]');
            renderRows(parsed);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });
}

function initCartPage() {
    initCartItems();
    initAccessories();
    initOrderFlow();
    initAttributeModal();
}

if (pageId === 'cart') {
    initCartPage();
}

function buildStatusBadgeClass(status) {
    switch (status) {
        case 'new':
            return 'bg-rose-50 text-rose-700 ring-rose-100';
        case 'confirmed':
            return 'bg-emerald-50 text-emerald-700 ring-emerald-100';
        case 'assembled':
            return 'bg-sky-50 text-sky-700 ring-sky-100';
        case 'delivering':
            return 'bg-amber-50 text-amber-700 ring-amber-100';
        case 'delivered':
            return 'bg-white text-slate-700 ring-slate-200';
        case 'cancelled':
            return 'bg-slate-100 text-slate-500 ring-slate-200';
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
