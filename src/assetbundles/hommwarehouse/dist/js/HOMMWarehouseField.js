/**
 * HOMM warehouse plugin for Craft CMS
 *
 * HOMMWarehouseField Field JS
 *
 * @author    Benjamin Ammann
 * @copyright Copyright (c) 2025 HOMM interactive
 * @link      https://github.com/HOMMinteractive
 * @package   HOMMWarehouse
 * @since     0.0.1
 */

document.addEventListener('DOMContentLoaded', function () {

    function handleNumberButton(e, direction) {
        const input = direction === 'minus' ? e.target.nextElementSibling : e.target.previousElementSibling;
        if (!input) return;
        const currentValue = parseInt(input.value, 10) || 0;
        if (direction === 'minus') {
            input.stepDown ? input.stepDown() : input.value = currentValue - 1;
        } else {
            input.stepUp ? input.stepUp() : input.value = currentValue + 1;
        }
        input.dispatchEvent(new Event('change'));
    }

    document.querySelectorAll('[data-number="minus"]').forEach(btn =>
        btn.addEventListener('click', e => handleNumberButton(e, 'minus'))
    );
    document.querySelectorAll('[data-number="plus"]').forEach(btn =>
        btn.addEventListener('click', e => handleNumberButton(e, 'plus'))
    );

    document.querySelectorAll('.warehouse-attribute').forEach(attr => {
        attr.addEventListener('click', function(e) {
            const infoIcon = e.target.closest('[data-icon="info"]');
            if (infoIcon) {
                let infoContainer = infoIcon.querySelector('div');
                if (!infoContainer) return;
                infoContainer.style.display = (infoContainer.style.display === 'block') ? 'none' : 'block';
            }
        });
    });
});