const pageId = document.body.dataset.page || '';
let cartSubtotal = Number(document.querySelector('[data-cart-bouquet-total]')?.dataset.amount || 0);
let deliveryPrice = Number(document.querySelector('[data-delivery-total]')?.dataset.amount || 0);

function formatCurrency(value) {
    const number = Number(value || 0);
    return `${number.toLocaleString('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 })} ₽`;
}

function updateCartIndicator(count) {
    const isActive = Number(count) > 0;
    document.querySelectorAll('[data-cart-indicator]').forEach((indicator) => {
        indicator.dataset.cartActive = isActive ? 'true' : 'false';
    });
}

function updateCartCountStatic(count) {
    document.querySelectorAll('[data-cart-count-static]').forEach((badge) => {
        badge.textContent = count;
    });
}

function updateCartTotal(total) {
    cartSubtotal = Number(total || 0);
    const bouquetTarget = document.querySelector('[data-cart-bouquet-total]');
    if (bouquetTarget) {
        bouquetTarget.textContent = formatCurrency(cartSubtotal);
    }
    recalculateGrandTotal();
}

function updateDeliveryPriceDisplay(value) {
    deliveryPrice = Number(value || 0);
    const deliveryTarget = document.querySelector('[data-delivery-total]');
    if (deliveryTarget) {
        deliveryTarget.textContent = formatCurrency(deliveryPrice);
    }
    recalculateGrandTotal();
}

function recalculateGrandTotal() {
    const total = cartSubtotal + deliveryPrice;
    document.querySelectorAll('[data-order-grand-total]').forEach((target) => {
        target.textContent = formatCurrency(total);
    });
    const legacyTotal = document.querySelector('[data-cart-total]');
    if (legacyTotal) {
        legacyTotal.textContent = formatCurrency(total);
    }
}

async function addProductToCart(productId, qty = 1, attributes = []) {
    const response = await fetch('/cart-add', {
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
    const response = await fetch('/cart-update', {
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
    const response = await fetch('/cart-remove', {
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
    const addressButtons = Array.from(orderSection.querySelectorAll('[data-address-option]'));
    const streetInput = orderSection.querySelector('[data-address-street]');
    const apartmentInput = orderSection.querySelector('[data-address-apartment]');
    const addressCommentInput = orderSection.querySelector('[data-address-comment]');
    const addressNew = orderSection.querySelector('[data-address-new]');
    const addressSuggestionList = document.createElement('div');
    const deliveryHint = orderSection.querySelector('[data-delivery-pricing-hint]');
    const deliveryRow = document.querySelector('[data-delivery-row]');
    const recipientButtons = Array.from(orderSection.querySelectorAll('.recipient-btn'));
    const recipientExtra = orderSection.querySelectorAll('[data-recipient-extra]');
    const recipientName = orderSection.querySelector('[data-recipient-name]');
    const recipientPhone = orderSection.querySelector('[data-recipient-phone]');
    const commentInput = orderSection.querySelector('[data-order-comment]');
    const paymentButtons = Array.from(document.querySelectorAll('[data-payment-method]'));

    const attachPicker = (input) => {
        if (!input) return;

        const openPicker = () => {
            if (typeof input.showPicker === 'function') {
                try {
                    input.showPicker();
                } catch (error) {
                    // Safari throws if showPicker is called without focus.
                }
            }
        };

        input.addEventListener('click', () => {
            input.focus({ preventScroll: true });
            requestAnimationFrame(openPicker);
        });
    };

    attachPicker(dateInput);
    attachPicker(timeInput);

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

    const testAddresses = (() => {
        try {
            return JSON.parse(orderSection.dataset.testAddresses || '[]');
        } catch (e) {
            return [];
        }
    })();

    let dadataConfig = (() => {
        try {
            return JSON.parse(orderSection.dataset.dadataConfig || '{}');
        } catch (e) {
            return {};
        }
    })();

    const mergeCredentialsFromStorage = (config) => {
        try {
            const cached = localStorage.getItem('dadataCredentials');
            if (cached) {
                const parsed = JSON.parse(cached);
                if (parsed && typeof parsed === 'object') {
                    return {
                        ...config,
                        apiKey: config.apiKey || parsed.apiKey || '',
                        secretKey: config.secretKey || parsed.secretKey || '',
                    };
                }
            }
        } catch (e) {
            console.error('Не удалось загрузить ключи DaData из localStorage', e);
        }

        return config;
    };

    dadataConfig = mergeCredentialsFromStorage(dadataConfig);

    const fallbackDeliveryPrice = Number(
        orderSection.dataset.deliveryFallback || dadataConfig.defaultDeliveryPrice || 0,
    ) || 350;
    const defaultSettlement = orderSection.dataset.defaultSettlement || '';
    const deliveryPricingVersion = orderSection.dataset.deliveryPricingVersion || null;
    let lastDeliveryQuote = null;
    let lastSuggestionRequestId = 0;

    let currentMode = 'pickup';

    const findAddressById = (id) => addresses.find((item) => Number(item.raw?.id || 0) === Number(id));

    const normalizeText = (value = '') => value.trim().toLowerCase();

    const composeBaseAddress = () => (streetInput?.value?.trim() || '');

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

    const setPaymentMethod = (method) => {
        paymentButtons.forEach((btn) => {
            const isActive = btn.dataset.paymentMethod === method;
            btn.classList.toggle('border-rose-200', isActive);
            btn.classList.toggle('bg-rose-50', isActive);
            btn.classList.toggle('text-rose-700', isActive);
            btn.classList.toggle('shadow-sm', isActive);
            btn.classList.toggle('border-slate-200', !isActive);
            btn.classList.toggle('bg-white', !isActive);
            btn.classList.toggle('text-slate-700', !isActive);
        });
    };

    const composeAddressText = () => {
        const baseAddress = composeBaseAddress();
        const apartment = (apartmentInput?.value || '').trim();

        return [baseAddress, apartment ? `кв/офис ${apartment}` : null].filter(Boolean).join(', ');
    };

    const parseManualAddress = (rawValue) => {
        const parts = rawValue
            .split(',')
            .map((part) => part.trim())
            .filter(Boolean);

        if (!parts.length) {
            return { settlement: '', street: '', house: '' };
        }

        if (parts.length >= 3) {
            return {
                settlement: parts[0],
                street: parts[1],
                house: parts.slice(2).join(', '),
            };
        }

        if (parts.length === 2) {
            return {
                settlement: '',
                street: parts[0],
                house: parts[1],
            };
        }

        return {
            settlement: '',
            street: parts[0],
            house: '',
        };
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

    let selectedAddressId = null;

    const setActiveAddressButton = (id) => {
        addressButtons.forEach((button) => {
            const isActive = id !== null && Number(button.dataset.addressId || 0) === Number(id);
            button.classList.toggle('border-rose-200', isActive);
            button.classList.toggle('bg-rose-50', isActive);
            button.classList.toggle('text-rose-700', isActive);
            button.classList.toggle('shadow-sm', isActive);
            button.classList.toggle('border-slate-200', !isActive);
            button.classList.toggle('bg-white', !isActive);
            button.classList.toggle('text-slate-700', !isActive);
        });
    };

    const setAddressFromButton = (button) => {
        if (!button) return;
        selectedAddressId = Number(button.dataset.addressId || 0) || null;
        setActiveAddressButton(selectedAddressId);
        const chosen = findAddressById(selectedAddressId);
        const streetValue = [chosen?.raw?.street || '', chosen?.raw?.house || ''].filter(Boolean).join(', ');
        if (streetInput) streetInput.value = streetValue || chosen?.address || '';
        if (apartmentInput) {
            apartmentInput.value = chosen?.raw?.apartment || '';
        }
        if (addressCommentInput) addressCommentInput.value = chosen?.raw?.delivery_comment || '';
        setRecipientFromAddress(chosen);
    };

    const toggleDelivery = (mode) => {
        currentMode = mode;
        if (deliveryExtra) {
            deliveryExtra.hidden = mode !== 'delivery';
        }
        if (deliveryRow) {
            deliveryRow.hidden = mode !== 'delivery';
        }
        highlightMode(mode);

        if (mode === 'delivery') {
            const defaultButton = addressButtons.find((button) => button.hasAttribute('data-address-primary')) || addressButtons[0];
            if (defaultButton && selectedAddressId === null) {
                setAddressFromButton(defaultButton);
            }
            updateDeliveryQuote();
        } else {
            lastDeliveryQuote = null;
            updateDeliveryPriceDisplay(0);
        }
    };

    modeButtons.forEach((button) => {
        button.addEventListener('click', () => toggleDelivery(button.dataset.orderMode || 'pickup'));
    });

    recipientButtons.forEach((button) => {
        button.addEventListener('click', () => setRecipientMode(button.dataset.recipientMode || 'self'));
    });

    addressButtons.forEach((button) => {
        button.addEventListener('click', () => {
            setAddressFromButton(button);
            updateDeliveryQuote();
        });
    });

    addressNew?.addEventListener('click', () => {
        selectedAddressId = null;
        setActiveAddressButton(null);
        if (streetInput) {
            streetInput.value = '';
            streetInput.focus();
        }
        if (apartmentInput) apartmentInput.value = '';
        if (addressCommentInput) addressCommentInput.value = '';
        setRecipientFromAddress(null);
    });

    if (streetInput) {
        const addressWrapper = streetInput.parentElement;
        if (addressWrapper) {
            addressWrapper.classList.add('relative');
            addressSuggestionList.className =
                'absolute left-0 right-0 top-full z-30 mt-1 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg';
            addressWrapper.appendChild(addressSuggestionList);
        }
    }

    document.addEventListener('click', (event) => {
        if (!addressSuggestionList.contains(event.target) && streetInput !== event.target) {
            addressSuggestionList.classList.add('hidden');
        }
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

    const useFallbackDeliveryQuote = (addressText, reason) => {
        const price = Number(fallbackDeliveryPrice) || 0;

        lastDeliveryQuote = {
            address_text: addressText,
            label: addressText || 'Адрес доставки',
            lat: null,
            lon: null,
            zone_id: null,
            delivery_price: price,
            zone_version: deliveryPricingVersion,
            zone_calculated_at: new Date().toISOString(),
            location_source: 'fallback',
            geo_quality: null,
            settlement: '',
            street: '',
            house: '',
        };

        const priceText = price.toLocaleString('ru-RU');
        const reasonText = reason ? `${reason} ` : '';
        setDeliveryHint(`${reasonText}Применили доставку ${priceText} ₽ по умолчанию.`, 'warn');
        updateDeliveryPriceDisplay(price);
    };

    const formatAddressFromDadata = (data) => {
        if (!data) return '';

        const cityName = data.city || data.settlement || '';
        const cityLabel = data.settlement_with_type || data.city_with_type || '';
        const street = data.street_with_type || data.street || '';
        const house = data.house ? `д ${data.house}` : '';

        return [cityLabel || cityName, street, house].filter(Boolean).join(', ');
    };

    const isKrasnoyarskAddress = (data) => {
        if (!data) return false;
        const cityName = data.city || data.settlement || '';
        const cityLabel = data.settlement_with_type || data.city_with_type || '';

        return (cityName || cityLabel).toLowerCase().includes('красноярск');
    };

    const renderSuggestions = (suggestions) => {
        if (!streetInput || !addressSuggestionList) return;

        addressSuggestionList.innerHTML = '';
        if (!suggestions.length) {
            addressSuggestionList.classList.add('hidden');
            return;
        }

        suggestions.forEach((item) => {
            const formatted = formatAddressFromDadata(item.data) || item.value || '';
            const row = document.createElement('button');
            row.type = 'button';
            row.className =
                'flex w-full items-start gap-2 rounded-xl px-3 py-2 text-left text-sm font-semibold text-slate-800 hover:bg-rose-50';
            row.innerHTML = `
                <span class="material-symbols-rounded text-base text-rose-500">location_on</span>
                <span class="flex-1">
                    <span class="block">${formatted}</span>
                </span>
            `;

            row.addEventListener('click', () => {
                const data = item.data || {};
                if (streetInput) {
                    streetInput.value = formatAddressFromDadata(data) || item.value || '';
                }
                addressSuggestionList.classList.add('hidden');
                setTimeout(updateDeliveryQuote, 50);
            });

            addressSuggestionList.appendChild(row);
        });

        addressSuggestionList.classList.remove('hidden');
    };

    const DADATA_CENTER = { lat: 56.233717, lon: 92.8426 };

    const fetchSuggestions = async (query, requestId) => {
        if (!query || query.length < 3) return [];

        if (dadataConfig.apiKey) {
            const response = await fetch('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    Authorization: `Token ${dadataConfig.apiKey}`,
                },
                body: JSON.stringify({
                    query,
                    count: 10,
                    locations_geo: [
                        {
                            lat: DADATA_CENTER.lat,
                            lon: DADATA_CENTER.lon,
                            radius_meters: 60000,
                        },
                    ],
                }),
            }).catch(() => null);

            if (response?.ok) {
                const data = await response.json().catch(() => null);
                if (requestId === lastSuggestionRequestId) {
                    const suggestions = data?.suggestions || [];
                    return suggestions.sort((a, b) => {
                        const aIsKrasnoyarsk = isKrasnoyarskAddress(a?.data) ? 0 : 1;
                        const bIsKrasnoyarsk = isKrasnoyarskAddress(b?.data) ? 0 : 1;
                        return aIsKrasnoyarsk - bIsKrasnoyarsk;
                    });
                }
            }
        }

        return testAddresses
            .filter((item) => normalizeText(item.label).includes(normalizeText(query)))
            .map((item) => ({
                value: item.label,
                data: { city_with_type: item.label, street_with_type: '' },
            }));
    };

    const debouncedSuggest = (() => {
        let timer;
        return (value) => {
            clearTimeout(timer);
            timer = setTimeout(async () => {
                lastSuggestionRequestId += 1;
                const requestId = lastSuggestionRequestId;
                const suggestions = await fetchSuggestions(value.trim(), requestId);
                renderSuggestions(suggestions);
            }, 250);
        };
    })();

    const geocodeWithDadata = async (addressText) => {
        if (!addressText || !dadataConfig.apiKey || !dadataConfig.secretKey) return null;

        const response = await fetch('/api/dadata/clean-address', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ query: addressText }),
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
            label: formatAddressFromDadata(row) || row.result || addressText,
            settlement:
                row.settlement_with_type ||
                row.city_with_type ||
                row.settlement ||
                row.city ||
                row.region_with_type ||
                row.region ||
                '',
            street: row.street_with_type || row.street || '',
            house: row.house || '',
        };
    };

    const buildQuoteFromSavedAddress = (address) => {
        const lat = Number(address?.raw?.lat || 0);
        const lon = Number(address?.raw?.lon || 0);
        if (!lat || !lon) return null;

        const zoneFromPoint = findZoneForPoint([lon, lat]);
        const zoneFromId = deliveryZones.find((zone) => Number(zone.id) === Number(address?.raw?.zone_id || 0));
        const zone = zoneFromPoint || zoneFromId || null;
        if (!zone) {
            return {
                address_text: composeAddressText(),
                label: address?.address || composeAddressText() || 'Адрес доставки',
                lat,
                lon,
                zone_id: address?.raw?.zone_id || null,
                delivery_price: Number(address?.raw?.last_delivery_price_hint || fallbackDeliveryPrice || 0),
                zone_version: address?.raw?.zone_version || deliveryPricingVersion,
                zone_calculated_at: address?.raw?.zone_calculated_at || new Date().toISOString(),
                location_source: address?.raw?.location_source || 'stored',
                geo_quality: address?.raw?.geo_quality || null,
                settlement: address?.raw?.settlement || '',
                street: address?.raw?.street || '',
                house: address?.raw?.house || '',
                missing_zone: true,
            };
        }

        return {
            address_text: composeAddressText(),
            label: address?.address || composeAddressText() || 'Адрес доставки',
            lat,
            lon,
            zone_id: zone.id,
            delivery_price: zone.price,
            zone_version: address?.raw?.zone_version || deliveryPricingVersion,
            zone_calculated_at: address?.raw?.zone_calculated_at || new Date().toISOString(),
            location_source: address?.raw?.location_source || 'stored',
            geo_quality: address?.raw?.geo_quality || null,
            settlement: address?.raw?.settlement || '',
            street: address?.raw?.street || '',
            house: address?.raw?.house || '',
        };
    };

    const updateDeliveryQuote = async () => {
        if (currentMode !== 'delivery') return;
        const baseAddress = composeBaseAddress();
        const addressText = composeAddressText();
        if (!baseAddress) {
            setDeliveryHint('Введите адрес, чтобы получить подсказку DaData, геокодировать точку и определить зону доставки.');
            lastDeliveryQuote = null;
            updateDeliveryPriceDisplay(0);
            return;
        }

        const chosenAddress = selectedAddressId ? findAddressById(selectedAddressId) : null;
        const savedQuote = chosenAddress ? buildQuoteFromSavedAddress(chosenAddress) : null;
        if (savedQuote) {
            const zoneName = deliveryZones.find((zone) => Number(zone.id) === Number(savedQuote.zone_id))?.name || '';
            lastDeliveryQuote = savedQuote;
            if (savedQuote.missing_zone) {
                setDeliveryHint(
                    `${savedQuote.label} — зона не найдена, применили цену ${savedQuote.delivery_price.toLocaleString('ru-RU')} ₽.`,
                    'warn',
                );
            } else {
                setDeliveryHint(
                    `${savedQuote.label}${zoneName ? ` в зоне «${zoneName}»` : ''}. Доставка ${savedQuote.delivery_price.toLocaleString('ru-RU')} ₽`,
                    'success',
                );
            }
            updateDeliveryPriceDisplay(savedQuote.delivery_price);
            return;
        }

        if (chosenAddress) {
            useFallbackDeliveryQuote(addressText, 'Для сохранённого адреса нет координат или зоны.');
            return;
        }

        setDeliveryHint('Ищем адрес в DaData и определяем зону...', 'muted');

        try {
            const geocoded = await geocodeWithDadata(baseAddress);
            if (!geocoded) {
                useFallbackDeliveryQuote(
                    addressText,
                    'Не удалось получить координаты этого адреса. Попробуйте уточнить улицу и дом.',
                );
                return;
            }

            const zone = findZoneForPoint([geocoded.lon, geocoded.lat]);
            if (!zone) {
                useFallbackDeliveryQuote(
                    addressText,
                    'Адрес найден, но не попал ни в одну зону. Добавьте полигон или расширьте границы.',
                );
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
                settlement: geocoded.settlement || '',
                street: geocoded.street || '',
                house: geocoded.house || '',
            };

            setDeliveryHint(
                `${geocoded.label} в зоне «${zone.name}». Доставка ${zone.price.toLocaleString('ru-RU')} ₽`,
                'success',
            );
            updateDeliveryPriceDisplay(lastDeliveryQuote.delivery_price);
        } catch (e) {
            useFallbackDeliveryQuote(addressText, 'Ошибка при расчёте зоны. Проверьте соединение и попробуйте снова.');
        }
    };

    [streetInput].forEach((field) => {
        if (!field) return;
        field.addEventListener('blur', updateDeliveryQuote);
        field.addEventListener('change', updateDeliveryQuote);
        field.addEventListener('input', () => debouncedSuggest(composeBaseAddress()));
        field.addEventListener('focus', () => debouncedSuggest(composeBaseAddress()));
    });

    const collectPayload = () => {
        const activePayment = paymentButtons.find((btn) => btn.classList.contains('border-rose-200'));
        const payload = {
            mode: currentMode,
            date: dateInput?.value || '',
            time: timeInput?.value || '',
            comment: commentInput?.value || '',
            payment_method: activePayment?.dataset.paymentMethod || 'cash',
        };

        if (currentMode === 'delivery') {
            payload.address_id = selectedAddressId;
            payload.address_text = composeAddressText();

            const activeRecipient = orderSection.querySelector('.recipient-btn.border-rose-100') || orderSection.querySelector('[data-recipient-mode="self"]');
            const recipientMode = activeRecipient?.dataset.recipientMode || 'self';
            if (recipientMode === 'other') {
                payload.recipient = {
                    name: recipientName?.value || '',
                    phone: recipientPhone?.value || '',
                };
            }

            if (!payload.address) {
                payload.address = {};
            }

            const chosenAddress = selectedAddressId ? findAddressById(selectedAddressId) : null;
            payload.address.settlement = lastDeliveryQuote?.settlement || chosenAddress?.raw?.settlement || '';
            payload.address.street = lastDeliveryQuote?.street || chosenAddress?.raw?.street || '';
            payload.address.house = lastDeliveryQuote?.house || chosenAddress?.raw?.house || '';

            const rawStreet = streetInput?.value || '';
            const parsedManual = parseManualAddress(rawStreet);

            if (!payload.address.street) {
                payload.address.street = parsedManual.street || rawStreet.trim();
            }

            if (!payload.address.house) {
                payload.address.house = (parsedManual.house || '').replace(/^д\\.?/i, '').trim();
            }

            if (!payload.address.settlement) {
                payload.address.settlement = parsedManual.settlement || defaultSettlement;
            }

            if (apartmentInput) {
                payload.address.apartment = apartmentInput.value || '';
            }

            if (addressCommentInput) {
                payload.address.delivery_comment = addressCommentInput.value || '';
            }

            if (lastDeliveryQuote) {
                payload.delivery_price = lastDeliveryQuote.delivery_price;
                payload.zone_id = lastDeliveryQuote.zone_id;
                payload.delivery_pricing_version = lastDeliveryQuote.zone_version;
                payload.zone_calculated_at = lastDeliveryQuote.zone_calculated_at;
                payload.address = {
                    ...payload.address,
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
            const response = await fetch('/cart-checkout', {
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

            if (data.payment_link) {
                window.location.href = data.payment_link;
                return;
            }

            window.location.href = '/orders';
        } catch (error) {
            alert(error.message || 'Ошибка оформления заказа');
        } finally {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-70');
        }
    };

    submitButton.addEventListener('click', submitOrder);

    if (paymentButtons.length) {
        const defaultMethod = paymentButtons[0].dataset.paymentMethod || 'cash';
        setPaymentMethod(defaultMethod);
        paymentButtons.forEach((btn) => {
            btn.addEventListener('click', () => setPaymentMethod(btn.dataset.paymentMethod || 'cash'));
        });
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
    const endpoint = container.dataset.endpoint || '/orders-history';

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
    const response = await fetch('/account-notifications', {
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
    const response = await fetch('/account-pin', {
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

function initBirthdayReminders() {
    const modal = document.querySelector('[data-birthday-reminder-modal]');
    if (!modal) return;

    const closeButtons = modal.querySelectorAll('[data-birthday-reminder-close]');
    const form = modal.querySelector('[data-birthday-reminder-form]');
    const deleteButton = modal.querySelector('[data-birthday-reminder-delete]');
    const recipientInput = modal.querySelector('[data-birthday-reminder-field="recipient"]');
    const occasionInput = modal.querySelector('[data-birthday-reminder-field="occasion"]');
    const dateInput = modal.querySelector('[data-birthday-reminder-field="date"]');
    const status = document.querySelector('[data-birthday-reminder-status]');
    const addButton = document.querySelector('[data-birthday-reminder-add]');
    const title = modal.querySelector('[data-birthday-reminder-title]');
    const submitLabel = modal.querySelector('[data-birthday-reminder-submit]');
    const list = document.querySelector('[data-birthday-reminder-list]');
    const emptyState = document.querySelector('[data-birthday-reminder-empty]');
    const countBadges = document.querySelectorAll('[data-birthday-reminder-count]');
    const leadDayInputs = Array.from(document.querySelectorAll('input[name="birthday-reminder-days"]'));

    let activeReminderId = null;

    const formatDate = (value) => {
        if (!value) return '—';
        const parts = value.split('-');
        if (parts.length !== 3) return value;
        return `${parts[2]}.${parts[1]}.${parts[0]}`;
    };

    const updateCounts = () => {
        if (!list || !countBadges.length) return;
        const rows = list.querySelectorAll('[data-birthday-reminder-row]');
        countBadges.forEach((badge) => {
            const value = badge.querySelector('[data-birthday-reminder-count-value]');
            if (value) {
                value.textContent = `${rows.length}`;
            }
        });
    };

    const open = (data) => {
        activeReminderId = data.id || null;
        if (recipientInput) recipientInput.value = data.recipient || '';
        if (occasionInput) occasionInput.value = data.occasion || '';
        if (dateInput) dateInput.value = data.date || '';
        if (title) {
            title.textContent = activeReminderId ? 'Редактировать напоминание' : 'Добавить напоминание';
        }
        if (submitLabel) {
            submitLabel.textContent = activeReminderId ? 'Сохранить' : 'Добавить';
        }
        if (deleteButton) {
            deleteButton.classList.toggle('hidden', !activeReminderId);
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    const close = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        activeReminderId = null;
    };

    const showStatus = (message, isError = false) => {
        if (!status) return;
        status.textContent = message;
        status.classList.toggle('text-rose-600', isError);
        status.classList.toggle('text-emerald-700', !isError);
        status.classList.remove('hidden');
        setTimeout(() => status.classList.add('hidden'), 2500);
    };

    const ensureEmptyState = () => {
        if (!emptyState || !list) return;
        const rows = list.querySelectorAll('[data-birthday-reminder-row]');
        emptyState.classList.toggle('hidden', rows.length > 0);
    };

    const buildDataAttrs = (element, reminder) => {
        element.dataset.birthdayReminderEdit = '';
        element.dataset.birthdayReminderId = reminder.id;
        element.dataset.birthdayReminderRecipient = reminder.recipient;
        element.dataset.birthdayReminderOccasion = reminder.occasion;
        element.dataset.birthdayReminderDate = reminder.reminder_date;
    };

    const renderRow = (reminder) => {
        if (!list) return;
        const row = document.createElement('div');
        row.className =
            'grid grid-cols-3 gap-2 rounded-xl bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 sm:text-sm';
        row.dataset.birthdayReminderRow = reminder.id;

        const recipientLink = document.createElement('a');
        recipientLink.href = '#';
        recipientLink.className =
            'underline decoration-emerald-100 decoration-2 underline-offset-4 hover:text-emerald-700';
        recipientLink.textContent = reminder.recipient;
        buildDataAttrs(recipientLink, reminder);

        const occasionLink = document.createElement('a');
        occasionLink.href = '#';
        occasionLink.className =
            'underline decoration-emerald-100 decoration-2 underline-offset-4 hover:text-emerald-700';
        occasionLink.textContent = reminder.occasion;
        buildDataAttrs(occasionLink, reminder);

        const dateLink = document.createElement('a');
        dateLink.href = '#';
        dateLink.className =
            'underline decoration-emerald-100 decoration-2 underline-offset-4 hover:text-emerald-700';
        dateLink.textContent = formatDate(reminder.reminder_date);
        buildDataAttrs(dateLink, reminder);

        row.append(recipientLink, occasionLink, dateLink);
        list.appendChild(row);
    };

    closeButtons.forEach((button) => button.addEventListener('click', close));

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            close();
        }
    });

    if (list) {
        list.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-birthday-reminder-edit]');
            if (!trigger) return;
            event.preventDefault();
            open({
                id: Number(trigger.dataset.birthdayReminderId || 0),
                recipient: trigger.dataset.birthdayReminderRecipient,
                occasion: trigger.dataset.birthdayReminderOccasion,
                date: trigger.dataset.birthdayReminderDate,
            });
        });
    }

    if (addButton) {
        addButton.addEventListener('click', () => open({}));
    }

    if (form) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!recipientInput || !occasionInput || !dateInput) return;

            const payload = {
                recipient: recipientInput.value.trim(),
                occasion: occasionInput.value.trim(),
                date: dateInput.value,
            };

            if (!payload.recipient || !payload.occasion || !payload.date) {
                showStatus('Заполните все поля.', true);
                return;
            }

            try {
                const url = activeReminderId ? `/api/account/calendar/${activeReminderId}` : '/api/account/calendar';
                const method = activeReminderId ? 'PUT' : 'POST';
                const response = await fetchJson(url, {
                    method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const reminder = response.reminder;
                if (reminder && list) {
                    const existing = list.querySelector(`[data-birthday-reminder-row="${reminder.id}"]`);
                    if (existing) {
                        const links = existing.querySelectorAll('[data-birthday-reminder-edit]');
                        const [recipientLink, occasionLink, dateLink] = links;
                        if (recipientLink) recipientLink.textContent = reminder.recipient;
                        if (occasionLink) occasionLink.textContent = reminder.occasion;
                        if (dateLink) dateLink.textContent = formatDate(reminder.reminder_date);
                        links.forEach((link) => buildDataAttrs(link, reminder));
                    } else {
                        renderRow(reminder);
                    }
                    ensureEmptyState();
                    updateCounts();
                }

                close();
                showStatus(activeReminderId ? 'Изменения сохранены.' : 'Напоминание добавлено.');
            } catch (error) {
                showStatus(error.message || 'Не удалось сохранить напоминание.', true);
            }
        });
    }

    if (deleteButton) {
        deleteButton.addEventListener('click', async () => {
            if (!activeReminderId) return;
            if (!window.confirm('Удалить это напоминание?')) return;
            try {
                await fetchJson(`/api/account/calendar/${activeReminderId}`, { method: 'DELETE' });
                const row = list?.querySelector(`[data-birthday-reminder-row="${activeReminderId}"]`);
                row?.remove();
                ensureEmptyState();
                updateCounts();
                close();
                showStatus('Напоминание удалено.');
            } catch (error) {
                showStatus(error.message || 'Не удалось удалить напоминание.', true);
            }
        });
    }

    leadDayInputs.forEach((input) => {
        input.addEventListener('change', async () => {
            if (!input.checked) return;
            const leadDays = Number(input.value || 0);
            try {
                await fetchJson('/api/account/calendar/settings', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ lead_days: leadDays }),
                });
                showStatus('Настройки напоминаний обновлены.');
            } catch (error) {
                showStatus(error.message || 'Не удалось обновить настройки.', true);
            }
        });
    });

    ensureEmptyState();
    updateCounts();
}

function initAccountPage() {
    initNotificationToggles();
    initPinModal();
    initAccountProfile();
    initAccountAddresses();
}

if (pageId === 'account') {
    initAccountPage();
}

if (pageId === 'account-calendar') {
    initBirthdayReminders();
}

function initAccountAddresses() {
    const modal = document.querySelector('[data-address-modal]');
    if (!modal) return;

    const form = modal.querySelector('[data-address-form]');
    const title = modal.querySelector('[data-address-title]');
    const status = modal.querySelector('[data-address-status]');
    const closeButton = modal.querySelector('[data-address-close]');
    const fields = Array.from(modal.querySelectorAll('[data-address-field]'));
    const streetInput = modal.querySelector('[data-address-street]');
    const settlementField = modal.querySelector('[data-address-field="settlement"]');
    const streetField = modal.querySelector('[data-address-field="street"]');
    const houseField = modal.querySelector('[data-address-field="house"]');
    const apartmentField = modal.querySelector('[data-address-field="apartment"]');
    const addressTextField = modal.querySelector('[data-address-field="address_text"]');
    const recipientField = modal.querySelector('[data-address-field="recipient_name"]');
    const phoneField = modal.querySelector('[data-address-field="recipient_phone"]');
    const zoneLabel = modal.querySelector('[data-address-zone-label]');
    const addressPreview = modal.querySelector('[data-address-preview]');
    const triggers = document.querySelectorAll('[data-address-action]');
    const addressSuggestionList = document.createElement('div');

    const deliveryZones = (() => {
        try {
            return JSON.parse(modal.dataset.deliveryZones || '[]');
        } catch (e) {
            return [];
        }
    })();

    const testAddresses = (() => {
        try {
            return JSON.parse(modal.dataset.testAddresses || '[]');
        } catch (e) {
            return [];
        }
    })();

    let dadataConfig = (() => {
        try {
            return JSON.parse(modal.dataset.dadataConfig || '{}');
        } catch (e) {
            return {};
        }
    })();

    const mergeCredentialsFromStorage = (config) => {
        try {
            const cached = localStorage.getItem('dadataCredentials');
            if (cached) {
                const parsed = JSON.parse(cached);
                if (parsed && typeof parsed === 'object') {
                    return {
                        ...config,
                        apiKey: config.apiKey || parsed.apiKey || '',
                        secretKey: config.secretKey || parsed.secretKey || '',
                    };
                }
            }
        } catch (e) {
            console.error('Не удалось загрузить ключи DaData из localStorage', e);
        }

        return config;
    };

    dadataConfig = mergeCredentialsFromStorage(dadataConfig);

    const deliveryPricingVersion = modal.dataset.deliveryPricingVersion || null;
    const defaultSettlement = modal.dataset.defaultSettlement || '';
    const defaultRecipientName = modal.dataset.userName || '';
    const defaultRecipientPhone = modal.dataset.userPhone || '';
    let lastSuggestionRequestId = 0;

    let activeAddressId = null;

    const resetFields = () => {
        fields.forEach((field) => {
            field.value = '';
        });
    };

    const normalizeText = (value = '') => value.trim().toLowerCase();

    const composeBaseAddress = () => (streetInput?.value?.trim() || '');

    const composeAddressText = () => {
        const base = composeBaseAddress();
        const apartment = apartmentField?.value?.trim() || '';
        return [base, apartment ? `кв/офис ${apartment}` : null].filter(Boolean).join(', ');
    };

    const parseManualAddress = (rawValue) => {
        const parts = rawValue
            .split(',')
            .map((part) => part.trim())
            .filter(Boolean);

        if (!parts.length) {
            return { settlement: '', street: '', house: '' };
        }

        if (parts.length >= 3) {
            return {
                settlement: parts[0],
                street: parts[1],
                house: parts.slice(2).join(', '),
            };
        }

        if (parts.length === 2) {
            return {
                settlement: '',
                street: parts[0],
                house: parts[1],
            };
        }

        return {
            settlement: '',
            street: parts[0],
            house: '',
        };
    };

    const updateAddressPreview = () => {
        const text = composeAddressText();
        if (addressTextField) {
            addressTextField.value = text;
        }
        if (addressPreview) {
            addressPreview.textContent = text ? `Итоговый адрес: ${text}` : 'Полный адрес будет сформирован автоматически.';
        }
    };

    const setZoneLabel = (text, tone = 'muted') => {
        if (!zoneLabel) return;
        zoneLabel.value = text;
        zoneLabel.classList.toggle('text-emerald-700', tone === 'success');
        zoneLabel.classList.toggle('text-amber-700', tone === 'warn');
        zoneLabel.classList.toggle('text-slate-700', tone === 'muted');
    };

    const setHiddenField = (key, value) => {
        const field = fields.find((item) => item.dataset.addressField === key);
        if (field) field.value = value || '';
    };

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

    const DADATA_CENTER = { lat: 56.233717, lon: 92.8426 };

    const formatAddressFromDadata = (data) => {
        if (!data) return '';

        const cityName = data.city || data.settlement || '';
        const cityLabel = data.settlement_with_type || data.city_with_type || '';
        const street = data.street_with_type || '';
        const house = data.house ? `д ${data.house}` : '';

        return [cityLabel || cityName, street, house].filter(Boolean).join(', ');
    };

    const isKrasnoyarskAddress = (data) => {
        if (!data) return false;
        const cityName = data.city || data.settlement || '';
        const cityLabel = data.settlement_with_type || data.city_with_type || '';

        return (cityName || cityLabel).toLowerCase().includes('красноярск');
    };

    const renderSuggestions = (suggestions) => {
        if (!streetInput) return;

        addressSuggestionList.innerHTML = '';
        if (!suggestions.length) {
            addressSuggestionList.classList.add('hidden');
            return;
        }

        suggestions.forEach((item) => {
            const formatted = formatAddressFromDadata(item.data) || item.value || '';
            const row = document.createElement('button');
            row.type = 'button';
            row.className =
                'flex w-full items-start gap-2 rounded-xl px-3 py-2 text-left text-sm font-semibold text-slate-800 hover:bg-rose-50';
            row.innerHTML = `
                <span class="material-symbols-rounded text-base text-rose-500">location_on</span>
                <span class="flex-1">
                    <span class="block">${formatted}</span>
                </span>
            `;

            row.addEventListener('click', () => {
                const data = item.data || {};
                const formatted = formatAddressFromDadata(data) || item.value || '';
                if (streetInput) {
                    streetInput.value = formatted;
                }
                updateAddressPreview();
                addressSuggestionList.classList.add('hidden');
                setTimeout(updateDeliveryZone, 50);
            });

            addressSuggestionList.appendChild(row);
        });

        addressSuggestionList.classList.remove('hidden');
    };

    const fetchSuggestions = async (query, requestId) => {
        if (!query || query.length < 3) return [];

        if (dadataConfig.apiKey) {
            const response = await fetch('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    Authorization: `Token ${dadataConfig.apiKey}`,
                },
                body: JSON.stringify({
                    query,
                    count: 10,
                    locations_geo: [
                        {
                            lat: DADATA_CENTER.lat,
                            lon: DADATA_CENTER.lon,
                            radius_meters: 60000,
                        },
                    ],
                }),
            }).catch(() => null);

            if (response?.ok) {
                const data = await response.json().catch(() => null);
                if (requestId === lastSuggestionRequestId) {
                    const suggestions = data?.suggestions || [];
                    return suggestions.sort((a, b) => {
                        const aIsKrasnoyarsk = isKrasnoyarskAddress(a?.data) ? 0 : 1;
                        const bIsKrasnoyarsk = isKrasnoyarskAddress(b?.data) ? 0 : 1;
                        return aIsKrasnoyarsk - bIsKrasnoyarsk;
                    });
                }
            }
        }

        return testAddresses
            .filter((item) => normalizeText(item.label).includes(normalizeText(query)))
            .map((item) => ({
                value: item.label,
                data: { city_with_type: item.label, street_with_type: '' },
            }));
    };

    const debouncedSuggest = (() => {
        let timer;
        return (value) => {
            clearTimeout(timer);
            timer = setTimeout(async () => {
                lastSuggestionRequestId += 1;
                const requestId = lastSuggestionRequestId;
                const suggestions = await fetchSuggestions(value.trim(), requestId);
                renderSuggestions(suggestions);
            }, 250);
        };
    })();

    const geocodeWithDadata = async (addressText) => {
        if (!addressText || !dadataConfig.apiKey || !dadataConfig.secretKey) return null;

        const response = await fetch('/api/dadata/clean-address', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ query: addressText }),
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
            label: formatAddressFromDadata(row) || row.result || addressText,
            settlement:
                row.settlement_with_type ||
                row.city_with_type ||
                row.settlement ||
                row.city ||
                row.region_with_type ||
                row.region ||
                '',
            street: row.street_with_type || row.street || '',
            house: row.house || '',
        };
    };

    const updateDeliveryZone = async () => {
        const baseAddress = composeBaseAddress();
        updateAddressPreview();

        if (!baseAddress) {
            setZoneLabel('Введите адрес доставки для определения зоны.', 'muted');
            ['lat', 'lon', 'zone_id', 'zone_version', 'zone_calculated_at', 'location_source', 'geo_quality', 'last_delivery_price_hint'].forEach(
                (key) => setHiddenField(key, ''),
            );
            ['settlement', 'street', 'house'].forEach((key) => setHiddenField(key, ''));
            return;
        }

        setZoneLabel('Определяем зону доставки...', 'muted');

        try {
            const geocoded = await geocodeWithDadata(baseAddress);
            if (!geocoded) {
                setZoneLabel('Не удалось определить координаты. Проверьте адрес.', 'warn');
                ['lat', 'lon', 'zone_id', 'zone_version', 'zone_calculated_at', 'location_source', 'geo_quality', 'last_delivery_price_hint'].forEach(
                    (key) => setHiddenField(key, ''),
                );
                const parsedManual = parseManualAddress(baseAddress);
                setHiddenField('settlement', parsedManual.settlement || defaultSettlement);
                setHiddenField('street', parsedManual.street || baseAddress);
                setHiddenField('house', (parsedManual.house || '').replace(/^д\\.?/i, '').trim());
                return;
            }

            const zone = findZoneForPoint([geocoded.lon, geocoded.lat]);
            setHiddenField('lat', geocoded.lat);
            setHiddenField('lon', geocoded.lon);
            setHiddenField('location_source', 'dadata');
            setHiddenField('geo_quality', geocoded.qc ?? '');
            setHiddenField('zone_calculated_at', new Date().toISOString());
            setHiddenField('zone_version', deliveryPricingVersion || '');
            setHiddenField('settlement', geocoded.settlement || defaultSettlement);
            setHiddenField('street', geocoded.street || '');
            setHiddenField('house', geocoded.house || '');

            if (!zone) {
                setZoneLabel('Адрес найден, но зона не определена.', 'warn');
                setHiddenField('zone_id', '');
                setHiddenField('last_delivery_price_hint', '');
                return;
            }

            setHiddenField('zone_id', zone.id);
            setHiddenField('last_delivery_price_hint', zone.price);
            setZoneLabel(`Зона «${zone.name}» · ${zone.price.toLocaleString('ru-RU')} ₽`, 'success');
        } catch (e) {
            setZoneLabel('Ошибка при определении зоны доставки.', 'warn');
        }
    };

    const fillField = (key, value) => {
        const field = fields.find((item) => item.dataset.addressField === key);
        if (field) field.value = value || '';
    };

    const showError = (message) => {
        if (!status) return;
        status.textContent = message;
        status.classList.remove('hidden');
    };

    const clearStatus = () => {
        if (!status) return;
        status.textContent = '';
        status.classList.add('hidden');
    };

    const openModal = (mode, dataset = {}) => {
        clearStatus();
        resetFields();
        activeAddressId = null;

        if (mode === 'edit') {
            activeAddressId = Number(dataset.addressId || 0) || null;
            if (title) title.textContent = 'Редактировать адрес';
            fillField('label', dataset.addressLabel);
            fillField('address_text', dataset.addressText);
            fillField('settlement', dataset.addressSettlement);
            fillField('street', dataset.addressStreet);
            fillField('house', dataset.addressHouse);
            fillField('apartment', dataset.addressApartment);
            fillField('recipient_name', dataset.addressRecipientName);
            fillField('recipient_phone', dataset.addressRecipientPhone);
            fillField('entrance', dataset.addressEntrance);
            fillField('floor', dataset.addressFloor);
            fillField('intercom', dataset.addressIntercom);
            fillField('delivery_comment', dataset.addressComment);
            fillField('zone_id', dataset.addressZoneId);
            fillField('zone_version', dataset.addressZoneVersion);
            fillField('zone_calculated_at', dataset.addressZoneCalculatedAt);
            fillField('location_source', dataset.addressLocationSource);
            fillField('geo_quality', dataset.addressGeoQuality);
            fillField('lat', dataset.addressLat);
            fillField('lon', dataset.addressLon);
            fillField('last_delivery_price_hint', dataset.addressLastDeliveryPriceHint);
            if (streetInput) {
                const baseParts = [
                    dataset.addressSettlement,
                    dataset.addressStreet,
                    dataset.addressHouse ? `д. ${dataset.addressHouse}` : '',
                ]
                    .filter(Boolean)
                    .join(', ');
                streetInput.value = baseParts || dataset.addressText || '';
            }
        } else {
            if (title) title.textContent = 'Новый адрес';
            fillField('label', 'Адрес доставки');
            fillField('recipient_name', defaultRecipientName);
            fillField('recipient_phone', defaultRecipientPhone);
            if (streetInput) streetInput.value = '';
        }

        updateAddressPreview();
        const zoneId = Number(dataset.addressZoneId || 0);
        if (zoneId) {
            const zone = deliveryZones.find((item) => Number(item.id) === zoneId);
            if (zone) {
                setZoneLabel(`Зона «${zone.name}» · ${zone.price.toLocaleString('ru-RU')} ₽`, 'success');
            } else {
                setZoneLabel('Зона доставки не найдена.', 'warn');
            }
        } else if (mode === 'edit' && composeBaseAddress()) {
            setZoneLabel('Зона доставки будет определена при сохранении.', 'warn');
        } else {
            setZoneLabel('Введите адрес доставки для определения зоны.', 'muted');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        clearStatus();
        resetFields();
        activeAddressId = null;
        addressSuggestionList.classList.add('hidden');
        if (zoneLabel) zoneLabel.value = '';
        if (addressPreview) addressPreview.textContent = 'Полный адрес будет сформирован автоматически.';
        if (streetInput) streetInput.value = '';
    };

    const sendRequest = async (url, options) => {
        try {
            await fetchJson(url, options);
            window.location.reload();
        } catch (error) {
            showError(error.message);
        }
    };

    triggers.forEach((button) => {
        button.addEventListener('click', () => {
            const action = button.dataset.addressAction;
            if (action === 'new') {
                openModal('new');
                return;
            }

            const card = button.closest('[data-address-id]');
            if (!card) return;

            if (action === 'edit') {
                openModal('edit', card.dataset);
                return;
            }

            const addressId = Number(card.dataset.addressId || 0);
            if (!addressId) return;

            if (action === 'delete') {
                if (!window.confirm('Удалить этот адрес?')) {
                    return;
                }
                sendRequest(`/api/account/addresses/${addressId}`, { method: 'DELETE' });
                return;
            }

            if (action === 'primary') {
                sendRequest(`/api/account/addresses/${addressId}/primary`, { method: 'POST' });
            }
        });
    });

    if (streetInput) {
        const wrapper = streetInput.parentElement;
        if (wrapper) {
            wrapper.classList.add('relative');
            addressSuggestionList.className =
                'absolute left-0 right-0 top-full z-30 mt-1 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg';
            wrapper.appendChild(addressSuggestionList);
        }
    }

    document.addEventListener('click', (event) => {
        if (!addressSuggestionList.contains(event.target) && streetInput !== event.target) {
            addressSuggestionList.classList.add('hidden');
        }
    });

    [streetInput].forEach((field) => {
        if (!field) return;
        field.addEventListener('input', () => {
            updateAddressPreview();
            debouncedSuggest(composeBaseAddress());
        });
        field.addEventListener('focus', () => debouncedSuggest(composeBaseAddress()));
        field.addEventListener('blur', updateDeliveryZone);
        field.addEventListener('change', updateDeliveryZone);
    });

    apartmentField?.addEventListener('input', updateAddressPreview);

    if (closeButton) {
        closeButton.addEventListener('click', closeModal);
    }

    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    if (form) {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            clearStatus();

            updateAddressPreview();

            const payload = fields.reduce((acc, field) => {
                acc[field.dataset.addressField] = field.value;
                return acc;
            }, {});

            const baseAddress = composeBaseAddress();
            if (!baseAddress) {
                showError('Укажите адрес доставки.');
                return;
            }

            if (!payload.address_text || !payload.address_text.trim()) {
                showError('Не удалось сформировать адрес. Проверьте поля.');
                return;
            }

            const parsedManual = parseManualAddress(baseAddress);
            payload.settlement = payload.settlement || parsedManual.settlement || defaultSettlement;
            payload.street = payload.street || parsedManual.street || baseAddress.trim();
            payload.house = payload.house || (parsedManual.house || '').replace(/^д\\.?/i, '').trim();
            payload.apartment = apartmentField?.value || '';
            payload.label = payload.label || baseAddress || 'Адрес доставки';
            payload.recipient_name = payload.recipient_name || defaultRecipientName;
            payload.recipient_phone = payload.recipient_phone || defaultRecipientPhone;
            payload.delivery_comment = payload.delivery_comment || '';

            if (activeAddressId) {
                sendRequest(`/api/account/addresses/${activeAddressId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
            } else {
                sendRequest('/api/account/addresses', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
            }
        });
    }
}

function initAccountProfile() {
    const editButton = document.querySelector('[data-account-edit]');
    const nameInput = document.querySelector('[data-account-name]');
    const status = document.querySelector('[data-account-name-status]');
    if (!editButton || !nameInput) return;

    let initialValue = nameInput.value;
    let saving = false;

    const showStatus = (message, isError = false) => {
        if (!status) return;
        status.textContent = message;
        status.classList.toggle('text-rose-600', isError);
        status.classList.toggle('text-emerald-700', !isError);
        status.classList.remove('hidden');
        setTimeout(() => status.classList.add('hidden'), 2500);
    };

    const saveName = async () => {
        const value = nameInput.value.trim();
        if (!value || value === initialValue || saving) {
            nameInput.value = initialValue;
            return;
        }

        saving = true;
        try {
            await fetchJson('/api/account/profile', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: value }),
            });
            initialValue = value;
            showStatus('Имя обновлено.');
        } catch (error) {
            nameInput.value = initialValue;
            showStatus(error.message || 'Не удалось сохранить имя.', true);
        } finally {
            saving = false;
        }
    };

    editButton.addEventListener('click', () => {
        nameInput.removeAttribute('readonly');
        nameInput.focus();
        nameInput.select();
    });

    nameInput.addEventListener('blur', () => {
        nameInput.setAttribute('readonly', 'readonly');
        saveName();
    });

    nameInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            nameInput.blur();
        }
    });
}

if (pageId === 'orders') {
    initOrdersHistory();
}

async function fetchJson(url, options) {
    const response = await fetch(url, options);
    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        const message = data.error || 'Не удалось выполнить запрос';
        throw new Error(message);
    }
    return data;
}

function initLotteryModal() {
    const modal = document.querySelector('[data-lottery-modal]');
    const triggers = document.querySelectorAll('[data-lottery-open]');
    if (!modal || !triggers.length) return;

    const guardAuth = getPromoAuthGuard();
    const guardBot = getPromoBotGuard();

    const title = modal.querySelector('[data-lottery-title]');
    const subtitle = modal.querySelector('[data-lottery-subtitle]');
    const price = modal.querySelector('[data-lottery-price]');
    const availability = modal.querySelector('[data-lottery-availability]');
    const limitNote = modal.querySelector('[data-lottery-limit]');
    const ticketsContainer = modal.querySelector('[data-lottery-tickets]');
    const payButton = modal.querySelector('[data-lottery-pay]');
    const randomButton = modal.querySelector('[data-lottery-random]');
    const selectButton = modal.querySelector('[data-lottery-select]');
    const selectedLabel = modal.querySelector('[data-lottery-selected]');
    const closeButtons = modal.querySelectorAll('[data-lottery-close]');

    let activeLotteryId = null;
    let payTicketId = null;
    let selectedTicketNumber = null;
    let ticketsCache = [];
    let selectionLocked = false;

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        activeLotteryId = null;
        payTicketId = null;
        selectedTicketNumber = null;
        ticketsCache = [];
        selectionLocked = false;
        updateSelectedLabel();
    };

    closeButtons.forEach((btn) => btn.addEventListener('click', closeModal));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    const updateSelectedLabel = () => {
        if (selectedLabel) {
            selectedLabel.textContent = selectedTicketNumber ? `Выбран номер: ${selectedTicketNumber}` : 'Выбран номер: —';
        }
        if (selectButton) {
            const disabled = selectionLocked || !selectedTicketNumber;
            selectButton.disabled = disabled;
            selectButton.classList.toggle('opacity-60', disabled);
        }
        if (randomButton) {
            randomButton.disabled = selectionLocked;
            randomButton.classList.toggle('opacity-60', selectionLocked);
        }
    };

    const renderTickets = (tickets) => {
        ticketsCache = tickets;
        const hasMine = tickets.some((ticket) => ticket.is_mine);
        ticketsContainer.innerHTML = '';
        tickets.forEach((ticket) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className =
                'flex flex-col items-center justify-center gap-1 rounded-xl border px-2 py-2 text-center text-sm font-semibold transition';
            const number = document.createElement('span');
            number.textContent = ticket.ticket_number;
            number.className = 'text-base font-semibold sm:text-lg';

            if (ticket.status === 'free') {
                item.classList.add(
                    'border-slate-200',
                    'bg-slate-100',
                    'text-slate-600',
                    'hover:border-violet-200',
                    'hover:bg-violet-100',
                    'hover:text-violet-700'
                );
                if (selectionLocked && hasMine) {
                    item.disabled = true;
                    item.classList.add('opacity-60');
                } else {
                    item.addEventListener('click', () => {
                        selectedTicketNumber = ticket.ticket_number;
                        renderTickets(ticketsCache);
                        updateSelectedLabel();
                    });
                }
            } else {
                item.disabled = true;
                item.classList.add('border-rose-500', 'bg-rose-600', 'text-white');
            }

            if (ticket.is_mine) {
                item.classList.add('ring-2', 'ring-emerald-200');
            }

            if (ticket.status === 'paid') {
                item.classList.add('opacity-80');
            }

            if (selectedTicketNumber === ticket.ticket_number) {
                item.classList.remove('bg-slate-100', 'text-slate-600', 'border-slate-200');
                item.classList.add('border-violet-500', 'bg-violet-600', 'text-white');
            }

            item.appendChild(number);

            if (ticket.status !== 'free') {
                const status = document.createElement('span');
                status.className = 'text-[10px] font-semibold text-white/80';
                status.textContent = ticket.phone_last4 ? `····${ticket.phone_last4}` : 'Занят';
                item.appendChild(status);
            }

            ticketsContainer.appendChild(item);
        });
    };

    const updatePayState = (tickets) => {
        if (!payButton || payButton.classList.contains('hidden')) {
            payTicketId = null;
            return;
        }
        const mine = tickets.find((ticket) => ticket.is_mine && ticket.status === 'reserved');
        if (mine) {
            payTicketId = mine.id;
            payButton.disabled = false;
            payButton.classList.remove('opacity-70');
        } else {
            payTicketId = null;
            payButton.disabled = true;
            payButton.classList.add('opacity-70');
        }
    };

    const loadTickets = async (lotteryId) => {
        const data = await fetchJson(`/api/lottery/tickets?lottery_id=${lotteryId}`);
        const ticketPrice = Number(data.lottery?.ticket_price ?? 0);
        const isFree = !Number.isFinite(ticketPrice) || ticketPrice <= 0;
        const myTicket = data.tickets.find((ticket) => ticket.is_mine);
        selectionLocked = isFree && Boolean(myTicket);
        if (title) title.textContent = 'Выбери номер';
        if (subtitle) {
            const reserveText = isFree ? '' : ` Резерв ${data.reserve_ttl} мин.`;
            subtitle.textContent = `${data.lottery.title}. Билетов всего ${data.lottery.tickets_total}.${reserveText}`;
        }
        if (price) {
            price.textContent = isFree ? 'Участие бесплатное' : `Билет ${formatCurrency(data.lottery.ticket_price)}`;
        }
        if (availability) {
            availability.textContent = `Свободно ${data.lottery.tickets_free} из ${data.lottery.tickets_total}`;
        }
        if (limitNote) {
            const limit = Number(data.free_monthly_limit || 0);
            if (isFree && limit > 0) {
                const lastDigit = limit % 10;
                const lastTwo = limit % 100;
                let suffix = 'раз';
                if (lastTwo < 10 || lastTwo >= 20) {
                    if (lastDigit >= 2 && lastDigit <= 4) {
                        suffix = 'раза';
                    } else if (lastDigit === 1) {
                        suffix = 'раз';
                    }
                }
                limitNote.textContent = `В бесплатном розыгрыше можно участвовать не чаще, чем ${limit} ${suffix} в месяц.`;
                limitNote.classList.remove('hidden');
            } else {
                limitNote.textContent = '';
                limitNote.classList.add('hidden');
            }
        }
        if (payButton) {
            payButton.classList.toggle('hidden', isFree);
            payButton.disabled = isFree;
            payButton.classList.toggle('opacity-70', isFree);
            payButton.style.display = isFree ? 'none' : '';
        }
        if (selectionLocked && myTicket) {
            selectedTicketNumber = myTicket.ticket_number;
        } else if (selectedTicketNumber) {
            const stillFree = data.tickets.some(
                (ticket) => ticket.ticket_number === selectedTicketNumber && ticket.status === 'free'
            );
            if (!stillFree) {
                selectedTicketNumber = null;
            }
        }
        renderTickets(data.tickets);
        updateSelectedLabel();
        updatePayState(data.tickets);
    };

    const reserveTicket = async (lotteryId, ticketNumber) => {
        if (!guardBot()) {
            return;
        }
        await fetchJson('/api/lottery/reserve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ lottery_id: lotteryId, ticket_number: ticketNumber }),
        });
        await loadTickets(lotteryId);
    };

    randomButton?.addEventListener('click', async () => {
        if (!activeLotteryId) return;
        try {
            const freeTickets = ticketsCache.filter((ticket) => ticket.status === 'free');
            if (!freeTickets.length) {
                alert('Свободные номера закончились');
                return;
            }
            const choice = freeTickets[Math.floor(Math.random() * freeTickets.length)];
            selectedTicketNumber = choice.ticket_number;
            renderTickets(ticketsCache);
            updateSelectedLabel();
        } catch (error) {
            alert(error.message);
        }
    });

    selectButton?.addEventListener('click', async () => {
        if (!activeLotteryId || !selectedTicketNumber) return;
        if (!guardBot()) return;
        try {
            await reserveTicket(activeLotteryId, selectedTicketNumber);
            selectedTicketNumber = null;
            updateSelectedLabel();
        } catch (error) {
            alert(error.message);
        }
    });

    payButton?.addEventListener('click', async () => {
        if (!payTicketId) return;
        if (!guardBot()) return;
        try {
            await fetchJson('/api/lottery/pay', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ ticket_id: payTicketId }),
            });
            await loadTickets(activeLotteryId);
        } catch (error) {
            alert(error.message);
        }
    });

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', async () => {
            if (!guardAuth()) {
                return;
            }
            const lotteryId = Number(trigger.dataset.lotteryId);
            if (!lotteryId) return;
            if (trigger.dataset.requiresBot !== undefined && !guardBot()) {
                return;
            }
            activeLotteryId = lotteryId;
            selectedTicketNumber = null;
            updateSelectedLabel();
            try {
                await loadTickets(lotteryId);
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } catch (error) {
                alert(error.message);
            }
        });
    });
}

let auctionBlitzConfirm;

function getAuctionBlitzConfirm() {
    if (auctionBlitzConfirm) {
        return auctionBlitzConfirm;
    }

    const modal = document.querySelector('[data-auction-blitz-confirm]');
    if (!modal) {
        auctionBlitzConfirm = async () => true;
        return auctionBlitzConfirm;
    }

    const title = modal.querySelector('[data-auction-blitz-title]');
    const price = modal.querySelector('[data-auction-blitz-price]');
    const confirmButton = modal.querySelector('[data-auction-blitz-confirm]');
    const cancelButtons = modal.querySelectorAll('[data-auction-blitz-cancel]');
    let resolver = null;

    const closeModal = (result) => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        if (resolver) {
            resolver(result);
            resolver = null;
        }
    };

    confirmButton?.addEventListener('click', () => closeModal(true));
    cancelButtons.forEach((button) => button.addEventListener('click', () => closeModal(false)));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal(false);
    });

    auctionBlitzConfirm = (lot) =>
        new Promise((resolve) => {
            resolver = resolve;
            if (title) title.textContent = lot.title || 'Лот';
            if (price) price.textContent = lot.blitz_price ? formatCurrency(lot.blitz_price) : '—';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });

    return auctionBlitzConfirm;
}

function initAuctionModal() {
    const modal = document.querySelector('[data-auction-modal]');
    const triggers = document.querySelectorAll('[data-auction-open]');
    if (!modal || !triggers.length) return;

    const guardAuth = getPromoAuthGuard();
    const title = modal.querySelector('[data-auction-title]');
    const subtitle = modal.querySelector('[data-auction-subtitle]');
    const currentEl = modal.querySelector('[data-auction-current]');
    const stepEl = modal.querySelector('[data-auction-step]');
    const endsEl = modal.querySelector('[data-auction-ends]');
    const bidsEl = modal.querySelector('[data-auction-bids]');
    const historyToggle = modal.querySelector('[data-auction-history-toggle]');
    const historyList = modal.querySelector('[data-auction-history]');
    const amountInput = modal.querySelector('[data-auction-amount]');
    const bidButton = modal.querySelector('[data-auction-bid]');
    const blitzButton = modal.querySelector('[data-auction-blitz]');
    const closeButtons = modal.querySelectorAll('[data-auction-close]');

    let activeLotId = null;
    let activeLot = null;
    let historyVisible = false;
    let historyLoaded = false;
    let historyBids = [];
    const guardBot = getPromoBotGuard();
    const confirmBlitz = getAuctionBlitzConfirm();

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        activeLotId = null;
        activeLot = null;
        historyVisible = false;
        historyLoaded = false;
        historyBids = [];
    };

    closeButtons.forEach((btn) => btn.addEventListener('click', closeModal));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    const renderBidRows = (container, bids) => {
        container.innerHTML = '';
        if (!bids.length) {
            container.textContent = 'Ставок ещё нет.';
            return;
        }
        bids.forEach((bid) => {
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600';
            row.innerHTML = `<span>${formatCurrency(bid.amount)}</span><span>……${bid.phone_last4}</span><span>${bid.created_at}</span>`;
            container.appendChild(row);
        });
    };

    const renderBids = (bids, lot) => {
        bidsEl.classList.remove('hidden');
        historyList?.classList.add('hidden');
        if (historyToggle) {
            historyToggle.classList.add('hidden');
            historyToggle.innerHTML = '';
        }

        if (lot?.status === 'finished') {
            bidsEl.classList.add('hidden');
            if (historyToggle) {
                const winnerText =
                    lot?.winner_last4 && lot?.winning_amount
                        ? `Победитель ……${lot.winner_last4} ${formatCurrency(lot.winning_amount)}`
                        : 'Аукцион завершён';
                historyToggle.classList.remove('hidden');
                historyToggle.innerHTML = `<span>${winnerText}</span><span>${historyVisible ? 'Скрыть историю' : 'История ставок'}</span>`;
            }
            if (historyVisible && historyList) {
                historyList.classList.remove('hidden');
                renderBidRows(historyList, historyBids);
            }
            return;
        }

        renderBidRows(bidsEl, bids);
    };

    const loadLot = async (lotId, options = {}) => {
        const query = options.history ? '&history=1' : '';
        const data = await fetchJson(`/api/auction/lot?id=${lotId}${query}`);
        const lot = data.lot;
        activeLot = lot;
        if (options.history) {
            historyBids = data.bids;
        }
        if (title) title.textContent = lot.title;
        if (subtitle) subtitle.textContent = lot.status === 'finished' ? 'Аукцион завершён.' : 'Ставка обновляется при каждом действии.';
        if (currentEl) currentEl.textContent = formatCurrency(lot.current_price);
        if (stepEl) stepEl.textContent = formatCurrency(lot.bid_step);
        if (endsEl) endsEl.textContent = lot.ends_at || '—';
        if (amountInput) amountInput.value = lot.min_bid;

        if (blitzButton) {
            if (lot.blitz_price) {
                blitzButton.disabled = false;
                blitzButton.classList.remove('opacity-60');
                blitzButton.innerHTML = `<span class="material-symbols-rounded text-base">bolt</span>Выкупить за ${formatCurrency(lot.blitz_price)}`;
            } else {
                blitzButton.disabled = true;
                blitzButton.classList.add('opacity-60');
                blitzButton.innerHTML = '<span class="material-symbols-rounded text-base">bolt</span>Блиц не задан';
            }
        }

        renderBids(data.bids, lot);
    };

    historyToggle?.addEventListener('click', async () => {
        if (!activeLotId) return;
        try {
            historyVisible = !historyVisible;
            if (!historyLoaded) {
                historyLoaded = true;
                historyVisible = true;
                await loadLot(activeLotId, { history: true });
            } else {
                renderBids(historyBids, activeLot);
            }
        } catch (error) {
            alert(error.message);
        }
    });

    bidButton?.addEventListener('click', async () => {
        if (!activeLotId) return;
        if (!guardBot()) return;
        try {
            await fetchJson('/api/auction/bid', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ lot_id: activeLotId, amount: amountInput?.value }),
            });
            await loadLot(activeLotId);
        } catch (error) {
            alert(error.message);
        }
    });

    blitzButton?.addEventListener('click', async () => {
        if (!activeLotId) return;
        if (!guardBot()) return;
        if (activeLot && activeLot.blitz_price) {
            const confirmed = await confirmBlitz(activeLot);
            if (!confirmed) return;
        }
        try {
            await fetchJson('/api/auction/blitz', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ lot_id: activeLotId }),
            });
            await loadLot(activeLotId);
        } catch (error) {
            alert(error.message);
        }
    });

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', async () => {
            if (!guardAuth()) {
                return;
            }
            const lotId = Number(trigger.dataset.auctionId);
            if (!lotId) return;
            activeLotId = lotId;
            historyVisible = false;
            historyLoaded = false;
            historyBids = [];
            try {
                await loadLot(lotId);
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } catch (error) {
                alert(error.message);
            }
        });
    });
}

function initPromoActions() {
    const root = document.querySelector('[data-promo-root]');
    if (!root) return;

    const guardBot = getPromoBotGuard();
    const confirmBlitz = getAuctionBlitzConfirm();

    const updateCartIndicator = (count) => {
        const isActive = Number(count) > 0;
        document.querySelectorAll('[data-cart-indicator]').forEach((indicator) => {
            indicator.dataset.cartActive = isActive ? 'true' : 'false';
        });
    };

    const addPromoItemToCart = async (productId) => {
        const response = await fetch('/cart-add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ product_id: productId, qty: 1, attributes: [] }),
        });

        if (!response.ok) {
            throw new Error('Не удалось добавить товар в корзину');
        }

        const data = await response.json();
        if (!data.ok) {
            throw new Error(data.error || 'Ошибка добавления в корзину');
        }

        updateCartIndicator(data.totals?.count || 0);
    };

    const updateAuctionCard = (lot) => {
        if (!lot?.id) {
            return;
        }
        const button = root.querySelector(`[data-auction-open][data-auction-id="${lot.id}"]`);
        if (!button) {
            return;
        }
        if (lot.status === 'finished' && lot.winner_last4 && lot.winning_amount !== null) {
            button.textContent = `Победитель …${lot.winner_last4} ${formatCurrency(lot.winning_amount)}`;
            return;
        }
        const bidCount = Number(lot.bid_count || 0);
        button.textContent = `${formatCurrency(lot.current_price)} (${bidCount} ставок)`;
    };

    const refreshAuctionCard = async (lotId) => {
        const data = await fetchJson(`/api/auction/lot?id=${lotId}`);
        updateAuctionCard(data.lot);
    };

    root.querySelectorAll('[data-product-card][data-product-id]').forEach((card) => {
        const productId = Number(card.dataset.productId || 0);
        if (productId <= 0) {
            return;
        }

        card.querySelectorAll('[data-add-to-cart]').forEach((button) => {
            button.addEventListener('click', async () => {
                if (!guardBot()) {
                    return;
                }
                button.disabled = true;
                button.classList.add('opacity-70');
                try {
                    await addPromoItemToCart(productId);
                } catch (error) {
                    alert(error.message || 'Ошибка добавления в корзину');
                } finally {
                    button.disabled = false;
                    button.classList.remove('opacity-70');
                }
            });
        });
    });

    root.querySelectorAll('[data-auction-step]').forEach((button) => {
        button.addEventListener('click', async () => {
            if (button.disabled) return;
            if (!guardBot()) return;
            const lotId = Number(button.dataset.auctionId || 0);
            if (!lotId) return;
            try {
                await fetchJson('/api/auction/bid', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ lot_id: lotId }),
                });
                await refreshAuctionCard(lotId);
            } catch (error) {
                alert(error.message);
            }
        });
    });

    root.querySelectorAll('[data-auction-blitz]').forEach((button) => {
        button.addEventListener('click', async () => {
            if (button.disabled) return;
            if (!guardBot()) return;
            const lotId = Number(button.dataset.auctionId || 0);
            if (!lotId) return;
            const lotTitle = button.dataset.auctionTitle || 'Лот';
            const blitzPrice = Number(button.dataset.auctionBlitzPrice || 0);
            if (blitzPrice > 0) {
                const confirmed = await confirmBlitz({ title: lotTitle, blitz_price: blitzPrice });
                if (!confirmed) return;
            }
            try {
                await fetchJson('/api/auction/blitz', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ lot_id: lotId }),
                });
                await refreshAuctionCard(lotId);
            } catch (error) {
                alert(error.message);
            }
        });
    });
}

let promoAuthGuard;

function getPromoAuthGuard() {
    if (promoAuthGuard) {
        return promoAuthGuard;
    }

    const root = document.querySelector('[data-promo-root]');
    const modal = document.querySelector('[data-auth-modal]');
    if (!root || !modal) {
        promoAuthGuard = () => true;
        return promoAuthGuard;
    }

    const openModal = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };
    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    promoAuthGuard = () => {
        const isAuthenticated = root.dataset.authenticated === 'true';
        if (isAuthenticated) {
            return true;
        }
        openModal();
        return false;
    };

    return promoAuthGuard;
}

let promoBotGuard;

function getPromoBotGuard() {
    if (promoBotGuard) {
        return promoBotGuard;
    }

    const root = document.querySelector('[data-promo-root]');
    const modal = document.querySelector('[data-bot-modal]');
    if (!root || !modal) {
        promoBotGuard = () => true;
        return promoBotGuard;
    }

    const guardAuth = getPromoAuthGuard();
    const closeButtons = modal.querySelectorAll('[data-bot-cancel]');
    const enableButton = modal.querySelector('[data-bot-enable]');
    const botLink = root.dataset.botLink || '';
    const openModal = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };
    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    closeButtons.forEach((button) => button.addEventListener('click', closeModal));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    enableButton?.addEventListener('click', async () => {
        enableButton.disabled = true;
        enableButton.classList.add('opacity-70');
        try {
            await fetchJson('/account-notifications', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ notifications: {} }),
            });
            if (botLink && botLink !== '#') {
                window.open(botLink, '_blank', 'noopener');
            }
        } catch (error) {
            alert(error.message || 'Не удалось включить уведомления');
        } finally {
            enableButton.disabled = false;
            enableButton.classList.remove('opacity-70');
            closeModal();
        }
    });

    promoBotGuard = () => {
        if (!guardAuth()) {
            return false;
        }
        const botConnected = root.dataset.botConnected === 'true';
        if (botConnected) {
            return true;
        }
        openModal();
        return false;
    };

    return promoBotGuard;
}

function initCountdownTimers() {
    const items = document.querySelectorAll('[data-countdown]');
    if (!items.length) return;

    const updateTimer = (element) => {
        const target = element.dataset.countdownTarget;
        if (!target) return;
        const finishedText = element.dataset.countdownFinishedText || 'Завершено';
        const end = new Date(target);
        if (Number.isNaN(end.getTime())) {
            return;
        }
        const now = new Date();
        const diff = end.getTime() - now.getTime();
        if (diff <= 0) {
            element.textContent = finishedText;
            return;
        }
        const totalSeconds = Math.floor(diff / 1000);
        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        element.textContent = `${days}д ${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    };

    items.forEach(updateTimer);
    setInterval(() => items.forEach(updateTimer), 1000);
}

function initPromoFilters() {
    const filters = document.querySelector('[data-promo-filters]');
    const items = Array.from(document.querySelectorAll('[data-promo-item]'));
    if (!filters || !items.length) return;

    const buttons = Array.from(filters.querySelectorAll('[data-promo-filter]'));
    if (!buttons.length) return;

    const setActiveFilter = (value) => {
        buttons.forEach((button) => {
            const isActive = button.dataset.promoFilter === value;
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            button.classList.toggle('bg-rose-600', isActive);
            button.classList.toggle('text-white', isActive);
            button.classList.toggle('border-rose-200', isActive);
            button.classList.toggle('shadow-md', isActive);
            button.classList.toggle('shadow-rose-200', isActive);
            button.classList.toggle('bg-white', !isActive);
            button.classList.toggle('text-slate-700', !isActive);
            button.classList.toggle('border-slate-200', !isActive);
        });

        items.forEach((item) => {
            const type = item.dataset.promoType;
            const isVisible = value === 'all' || type === value;
            item.classList.toggle('hidden', !isVisible);
        });
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            setActiveFilter(button.dataset.promoFilter);
        });
    });

    const initial = buttons.find((button) => button.getAttribute('aria-pressed') === 'true')?.dataset.promoFilter || 'all';
    setActiveFilter(initial);
}

if (pageId === 'promo') {
    initPromoFilters();
    initLotteryModal();
    initAuctionModal();
    initPromoActions();
    initCountdownTimers();
}

function initCookieConsent() {
    const banner = document.querySelector('[data-cookie-banner]');
    const settingsModal = document.querySelector('[data-cookie-settings]');
    if (!banner || !settingsModal) return;

    const analyticsToggle = settingsModal.querySelector('[data-cookie-analytics]');
    const marketingToggle = settingsModal.querySelector('[data-cookie-marketing]');
    const acceptAllButton = banner.querySelector('[data-cookie-accept-all]');
    const openSettingsButton = banner.querySelector('[data-cookie-settings-open]');
    const closeButtons = settingsModal.querySelectorAll('[data-cookie-settings-close]');
    const saveButton = settingsModal.querySelector('[data-cookie-save]');

    const storageKey = 'bunch_cookie_preferences';

    const readPrefs = () => {
        try {
            const raw = localStorage.getItem(storageKey);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    };

    const writePrefs = (prefs) => {
        localStorage.setItem(storageKey, JSON.stringify(prefs));
    };

    const applyPrefs = (prefs) => {
        if (!prefs) return;
        if (prefs.analytics) {
            // Здесь можно подключить Яндекс.Метрику / Google Analytics после согласия.
        }
        if (prefs.marketing) {
            // Здесь можно подключить пиксель VK после согласия.
        }
    };

    const setBannerVisible = (isVisible) => {
        banner.classList.toggle('hidden', !isVisible);
    };

    const openSettings = () => {
        const prefs = readPrefs();
        analyticsToggle.checked = prefs?.analytics ?? false;
        marketingToggle.checked = prefs?.marketing ?? false;
        settingsModal.classList.remove('hidden');
        settingsModal.classList.add('flex');
    };

    const closeSettings = () => {
        settingsModal.classList.add('hidden');
        settingsModal.classList.remove('flex');
    };

    const saveSettings = () => {
        const prefs = {
            necessary: true,
            analytics: analyticsToggle.checked,
            marketing: marketingToggle.checked,
            savedAt: new Date().toISOString(),
        };
        writePrefs(prefs);
        applyPrefs(prefs);
        closeSettings();
        setBannerVisible(false);
    };

    const acceptAll = () => {
        const prefs = {
            necessary: true,
            analytics: true,
            marketing: true,
            savedAt: new Date().toISOString(),
        };
        writePrefs(prefs);
        applyPrefs(prefs);
        setBannerVisible(false);
    };

    const existing = readPrefs();
    if (existing) {
        applyPrefs(existing);
        setBannerVisible(false);
    } else {
        setBannerVisible(true);
    }

    openSettingsButton?.addEventListener('click', openSettings);
    acceptAllButton?.addEventListener('click', acceptAll);
    saveButton?.addEventListener('click', saveSettings);
    closeButtons.forEach((btn) => btn.addEventListener('click', closeSettings));
    settingsModal.addEventListener('click', (event) => {
        if (event.target === settingsModal) closeSettings();
    });
}

function initInfoPanel() {
    const openButton = document.querySelector('[data-info-open]');
    const panel = document.querySelector('[data-info-panel]');
    const drawer = panel?.querySelector('[data-info-drawer]');
    const closeButtons = panel?.querySelectorAll('[data-info-close]');
    const overlay = panel?.querySelector('[data-info-overlay]');

    if (!openButton || !panel || !drawer) return;

    const openPanel = () => {
        panel.classList.remove('hidden');
        requestAnimationFrame(() => {
            drawer.classList.remove('translate-x-full');
        });
        document.body.classList.add('overflow-hidden');
    };

    const closePanel = () => {
        drawer.classList.add('translate-x-full');
        const handleTransition = () => {
            panel.classList.add('hidden');
            drawer.removeEventListener('transitionend', handleTransition);
        };
        drawer.addEventListener('transitionend', handleTransition);
        document.body.classList.remove('overflow-hidden');
    };

    openButton.addEventListener('click', openPanel);
    closeButtons?.forEach((button) => {
        button.addEventListener('click', closePanel);
    });
    overlay?.addEventListener('click', closePanel);
}

function initSupportChat() {
    const modal = document.querySelector('[data-support-modal]');
    const openButtons = document.querySelectorAll('[data-support-open]');
    if (!modal || !openButtons.length) return;

    const closeButtons = modal.querySelectorAll('[data-support-close]');
    const list = modal.querySelector('[data-support-messages]');
    const emptyState = modal.querySelector('[data-support-empty]');
    const form = modal.querySelector('[data-support-form]');
    const textarea = modal.querySelector('[data-support-input]');
    const status = modal.querySelector('[data-support-status]');
    let pollTimer = null;
    let lastMessageId = null;

    const formatTime = (iso) => {
        if (!iso) return '';
        const date = new Date(iso);
        if (Number.isNaN(date.getTime())) return '';
        return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
    };

    const renderMessage = (message) => {
        const wrapper = document.createElement('div');
        wrapper.className = `flex ${message.sender === 'user' ? 'justify-end' : 'justify-start'}`;

        const bubble = document.createElement('div');
        bubble.className = message.sender === 'user'
            ? 'max-w-[80%] rounded-2xl bg-rose-600 px-3 py-2 text-sm text-white shadow'
            : 'max-w-[80%] rounded-2xl bg-white px-3 py-2 text-sm text-slate-700 shadow';
        bubble.textContent = message.text || '';

        const meta = document.createElement('div');
        meta.className = message.sender === 'user'
            ? 'mt-1 text-xs text-rose-100'
            : 'mt-1 text-xs text-slate-400';
        meta.textContent = formatTime(message.created_at);

        bubble.appendChild(meta);
        wrapper.appendChild(bubble);
        list.appendChild(wrapper);
    };

    const setStatus = (text, tone = 'text-slate-500') => {
        if (!status) return;
        status.textContent = text;
        status.className = `text-xs font-semibold ${tone}`;
    };

    const scrollToBottom = () => {
        if (!list) return;
        list.scrollTop = list.scrollHeight;
    };

    const loadMessages = async (initial = false) => {
        const url = new URL('/support-messages', window.location.origin);
        if (lastMessageId) {
            url.searchParams.set('after', lastMessageId);
        }

        const response = await fetch(url.toString(), {
            headers: {
                'Accept': 'application/json',
            },
        });

        if (response.status === 401) {
            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            setStatus('Не удалось загрузить сообщения.', 'text-rose-600');
            return;
        }

        const data = await response.json();
        const messages = Array.isArray(data.messages) ? data.messages : [];
        if (initial) {
            list.innerHTML = '';
            if (emptyState) {
                list.appendChild(emptyState);
            }
        }

        messages.forEach((message) => {
            renderMessage(message);
            lastMessageId = message.id || lastMessageId;
        });

        if (emptyState) {
            const messageCount = Array.from(list.children).filter((child) => child !== emptyState).length;
            emptyState.classList.toggle('hidden', messageCount > 0);
        }

        scrollToBottom();
    };

    const openModal = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        lastMessageId = null;
        setStatus('');
        loadMessages(true);
        pollTimer = window.setInterval(() => {
            loadMessages();
        }, 5000);
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        if (pollTimer) {
            window.clearInterval(pollTimer);
            pollTimer = null;
        }
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', openModal);
    });
    closeButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!textarea) return;
        const text = textarea.value.trim();
        if (!text) {
            setStatus('Введите сообщение.', 'text-rose-600');
            return;
        }

        setStatus('Отправляем...', 'text-slate-500');
        const response = await fetch('/support-message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ message: text }),
        });

        if (response.status === 401) {
            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            setStatus('Не удалось отправить сообщение.', 'text-rose-600');
            return;
        }

        const data = await response.json();
        if (data.message) {
            renderMessage(data.message);
            lastMessageId = data.message.id || lastMessageId;
            textarea.value = '';
            if (emptyState) {
                emptyState.classList.add('hidden');
            }
            scrollToBottom();
        }

        setStatus('Отправлено.', 'text-emerald-600');
        setTimeout(() => setStatus(''), 2000);
    });
}

initCookieConsent();
initInfoPanel();
initSupportChat();
