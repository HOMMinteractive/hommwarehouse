/**
 HOMM warehouse plugin for Craft CMS
 *
 * HOMMWarehouseField Field JS
 *
 * @author    Benjamin Ammann
 * @copyright Copyright (c) 2025 HOMM interactive
 * @link      https://github.com/HOMMinteractive
 * @package   HOMMWarehouse
 * @since     0.0.1
 */

$(function () {
    const minusButtons = document.querySelector('[data-number="minus"]');
    const plusButtons = document.querySelector('[data-number="plus"]');

    if (minusButtons) {
        minusButtons.addEventListener('click', function () {
            const input = this.nextElementSibling;
            const currentValue = parseInt(input.value, 10) || 0;
            if (input.stepDown) {
                input.stepDown();
            } else {
                input.value = currentValue - 1;
            }
            input.dispatchEvent(new Event('change'));
        });
    }

    if (plusButtons) {
        plusButtons.addEventListener('click', function () {
            const input = this.previousElementSibling;
            const currentValue = parseInt(input.value, 10) || 0;
            if (input.stepUp) {
                input.stepUp();
            } else {
                input.value = currentValue + 1;
            }
            input.dispatchEvent(new Event('change'));
        });
    }
});


$(function () {
    const infoIcon = document.querySelector('.warehouse-attribute [data-icon="info"]');

    if (infoIcon) {
        infoIcon.addEventListener('click', function () {
            let infoContainer = infoIcon.firstChild;

            if (infoContainer.style.display == 'none') {
                infoContainer.style.display = 'block';
            } else {
                infoContainer.style.display = 'none';
            }
        });
    }
});
