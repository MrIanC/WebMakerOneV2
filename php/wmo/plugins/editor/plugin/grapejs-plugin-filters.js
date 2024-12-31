// bs-class-selector.js (or directly in your script)
grapesjs.plugins.add('grapejs-plugin-filters', (editor) => {
    // Add a new sector for Filters
    editor.StyleManager.addSector('filters', {
        name: 'Filters', // Sector name
        open: false,
        properties: [
            {
                name: 'Blur',
                property: 'filter',
                type: 'slider', // Adds a slider for the filter value
                defaults: '0px',
                units: ['px'], // Allowable units
                functionName: 'blur', // Applies the blur function
            },
            {
                name: 'Brightness',
                property: 'filter',
                type: 'slider',
                defaults: '100%',
                units: ['%'],
                functionName: 'brightness', // Applies the brightness function
            },
            {
                name: 'Contrast',
                property: 'filter',
                type: 'slider',
                defaults: '100%',
                units: ['%'],
                functionName: 'contrast', // Applies the contrast function
            },
            {
                name: 'Grayscale',
                property: 'filter',
                type: 'slider',
                defaults: '0%',
                units: ['%'],
                functionName: 'grayscale', // Applies the grayscale function
            },
            {
                name: 'Hue Rotate',
                property: 'filter',
                type: 'slider',
                defaults: '0deg',
                units: ['deg'],
                functionName: 'hue-rotate', // Applies the hue-rotate function
            },
            {
                name: 'Saturate',
                property: 'filter',
                type: 'slider',
                defaults: '100%',
                units: ['%'],
                functionName: 'saturate', // Applies the saturate function
            },
            {
                name: 'Sepia',
                property: 'filter',
                type: 'slider',
                defaults: '0%',
                units: ['%'],
                functionName: 'sepia', // Applies the sepia function
            },
        ],
    });

    // Add a custom property type for filters (if needed)
    editor.StyleManager.addType('filter', {
        create({ props }) {
            const el = document.createElement('div');
            el.innerHTML = `<input type="text" class="gjs-sm-input"/>`;
            return el;
        },
        update({ el, value, props }) {
            const input = el.querySelector('input');
            input.value = value || props.defaults || '';
        },
        emit({ el, change }) {
            const input = el.querySelector('input');
            change({ value: input.value });
        },
    });
});
