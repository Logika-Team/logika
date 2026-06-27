class DoubleRangeSlider {
    constructor(selector) {
        if (!window.rangeSliderInstances) {
            window.rangeSliderInstances = new Map();
        }

        if (window.rangeSliderInstances.has(selector)) {
            return window.rangeSliderInstances.get(selector);
        }

        this.selector = selector;
        this.sliders = [];
        this.handlers = new WeakMap();
        this.states = new Map();

        const containers = document.querySelectorAll(selector);
        containers.forEach(container => {
            const slider = {
                container,
                minSlider: container.querySelector('.slider-min'),
                maxSlider: container.querySelector('.slider-max'),
                rangeMin: container.querySelector('.range-min'),
                rangeMax: container.querySelector('.range-max'),
                range: container.querySelector('.slider-range')
            };

            if (this.validateSlider(slider)) {
                this.sliders.push(slider);
            }
        });

        window.rangeSliderInstances.set(selector, this);

        if (this.sliders.length) {
            this.init();
        }

        return this;
    }

    validateSlider(slider) {
        return slider.minSlider && slider.maxSlider &&
            slider.rangeMin && slider.rangeMax && slider.range;
    }

    init() {
        this.sliders.forEach(slider => {
            const savedState = this.states.get(slider.container);

            if (savedState) {
                slider.minSlider.value = savedState.min;
                slider.maxSlider.value = savedState.max;
            } else {
                const minVal = parseInt(slider.minSlider.getAttribute('value')) || slider.minSlider.min;
                const maxVal = parseInt(slider.maxSlider.getAttribute('value')) || slider.maxSlider.max;

                slider.minSlider.value = minVal;
                slider.maxSlider.value = maxVal;
                slider.rangeMin.value = minVal;
                slider.rangeMax.value = maxVal;
            }

            this.setupEventListeners(slider);
            this.updateRange(slider);
        });
    }

    setupEventListeners(slider) {
        const oldMinHandler = this.handlers.get(slider.minSlider);
        const oldMaxHandler = this.handlers.get(slider.maxSlider);
        const oldInputMinHandler = this.handlers.get(slider.rangeMin);
        const oldInputMaxHandler = this.handlers.get(slider.rangeMax);

        if (oldMinHandler) slider.minSlider.removeEventListener('input', oldMinHandler);
        if (oldMaxHandler) slider.maxSlider.removeEventListener('input', oldMaxHandler);
        if (oldInputMinHandler) slider.rangeMin.removeEventListener('input', oldInputMinHandler);
        if (oldInputMaxHandler) slider.rangeMax.removeEventListener('input', oldInputMaxHandler);

        const minHandler = () => this.handleMinInput(slider);
        const maxHandler = () => this.handleMaxInput(slider);
        const manualMinHandler = () => this.handleManualMinInput(slider);
        const manualMaxHandler = () => this.handleManualMaxInput(slider);

        this.handlers.set(slider.minSlider, minHandler);
        this.handlers.set(slider.maxSlider, maxHandler);
        this.handlers.set(slider.rangeMin, manualMinHandler);
        this.handlers.set(slider.rangeMax, manualMaxHandler);

        slider.minSlider.addEventListener('input', minHandler);
        slider.maxSlider.addEventListener('input', maxHandler);
        slider.rangeMin.addEventListener('input', manualMinHandler);
        slider.rangeMax.addEventListener('input', manualMaxHandler);

        slider.rangeMin.addEventListener('blur', () => this.correctMinOnBlur(slider));
        slider.rangeMax.addEventListener('blur', () => this.correctMaxOnBlur(slider));
    }

    handleMinInput(slider) {
        let minValue = parseInt(slider.minSlider.value);
        const maxValue = parseInt(slider.maxSlider.value);

        if (minValue >= maxValue) {
            minValue = maxValue - 1;
            slider.minSlider.value = minValue;
        }

        this.updateRange(slider);
        this.saveState(slider);
    }

    handleMaxInput(slider) {
        let maxValue = parseInt(slider.maxSlider.value);
        const minValue = parseInt(slider.minSlider.value);

        if (maxValue <= minValue) {
            maxValue = minValue + 1;
            slider.maxSlider.value = maxValue;
        }

        this.updateRange(slider);
        this.saveState(slider);
    }

    handleManualMinInput(slider) {
        const inputVal = slider.rangeMin.value.trim();
        if (inputVal === '') return;

        let newMin = parseInt(inputVal, 10);
        if (isNaN(newMin)) return;

        slider.minSlider.value = newMin;
        this.updateRange(slider);
        this.saveState(slider);
    }

    handleManualMaxInput(slider) {
        const inputVal = slider.rangeMax.value.trim();
        if (inputVal === '') return;

        let newMax = parseInt(inputVal, 10);
        if (isNaN(newMax)) return;

        slider.maxSlider.value = newMax;
        this.updateRange(slider);
        this.saveState(slider);
    }

    correctMinOnBlur(slider) {
        let newMin = parseInt(slider.rangeMin.value);
        const maxValue = parseInt(slider.maxSlider.value);
        const minLimit = parseInt(slider.minSlider.min);

        if (isNaN(newMin)) {
            newMin = minLimit;
        }

        if (newMin >= maxValue) newMin = maxValue - 1;
        if (newMin < minLimit) newMin = minLimit;

        slider.minSlider.value = newMin;
        slider.rangeMin.value = newMin;

        this.updateRange(slider);
        this.saveState(slider);
    }

    correctMaxOnBlur(slider) {
        let newMax = parseInt(slider.rangeMax.value);
        const minValue = parseInt(slider.minSlider.value);
        const maxLimit = parseInt(slider.maxSlider.max);

        if (isNaN(newMax)) {
            newMax = maxLimit;
        }

        if (newMax <= minValue) newMax = minValue + 1;
        if (newMax > maxLimit) newMax = maxLimit;

        slider.maxSlider.value = newMax;
        slider.rangeMax.value = newMax;

        this.updateRange(slider);
        this.saveState(slider);
    }

    updateRange(slider) {
        const rawMin = parseInt(slider.minSlider.value);
        const rawMax = parseInt(slider.maxSlider.value);
        const minLimit = parseInt(slider.minSlider.min);
        const maxLimit = parseInt(slider.maxSlider.max);
        const totalRange = maxLimit - minLimit;

        // Ограничиваем визуальное отображение
        let visualMin = Math.max(minLimit, Math.min(rawMin, maxLimit - 1));
        let visualMax = Math.min(maxLimit, Math.max(rawMax, minLimit + 1));

        if (visualMin >= visualMax) visualMin = visualMax - 1;
        if (visualMax <= visualMin) visualMax = visualMin + 1;

        slider.rangeMin.value = rawMin;
        slider.rangeMax.value = rawMax;

        const percent1 = ((visualMin - minLimit) / totalRange) * 100;
        const percent2 = ((visualMax - minLimit) / totalRange) * 100;

        requestAnimationFrame(() => {
            slider.range.style.left = percent1 + '%';
            slider.range.style.width = (percent2 - percent1) + '%';
            slider.rangeMin.style.left = percent1 + '%';
            slider.rangeMax.style.left = percent2 + '%';
        });
    }

    saveState(slider) {
        this.states.set(slider.container, {
            min: slider.minSlider.value,
            max: slider.maxSlider.value
        });
    }

    reinit() {
        this.sliders.forEach(slider => this.saveState(slider));
        this.sliders = [];

        const containers = document.querySelectorAll(this.selector);
        containers.forEach(container => {
            const slider = {
                container,
                minSlider: container.querySelector('.slider-min'),
                maxSlider: container.querySelector('.slider-max'),
                rangeMin: container.querySelector('.range-min'),
                rangeMax: container.querySelector('.range-max'),
                range: container.querySelector('.slider-range')
            };

            if (this.validateSlider(slider)) {
                this.sliders.push(slider);
            }
        });

        this.init();
    }

    destroy() {
        this.sliders.forEach(slider => {
            const minHandler = this.handlers.get(slider.minSlider);
            const maxHandler = this.handlers.get(slider.maxSlider);
            const minInputHandler = this.handlers.get(slider.rangeMin);
            const maxInputHandler = this.handlers.get(slider.rangeMax);

            if (minHandler) slider.minSlider.removeEventListener('input', minHandler);
            if (maxHandler) slider.maxSlider.removeEventListener('input', maxHandler);
            if (minInputHandler) slider.rangeMin.removeEventListener('input', minInputHandler);
            if (maxInputHandler) slider.rangeMax.removeEventListener('input', maxInputHandler);
        });

        this.handlers = new WeakMap();
        this.states.clear();
        this.sliders = [];
        window.rangeSliderInstances.delete(this.selector);
    }
}

window.DoubleRangeSlider = DoubleRangeSlider;
