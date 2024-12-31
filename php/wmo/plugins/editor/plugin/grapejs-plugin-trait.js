

// bs-class-selector.js (or directly in your script)
grapesjs.plugins.add('grapejs-plugin-trait', (editor) => {
    editor.Components.addType('input', {
        isComponent: (el) => el.tagName === 'INPUT',
        model: {
            defaults: {
                traits: [
                    // Strings are automatically converted to text types
                    'name', // Same as: { type: 'text', name: 'name' }
                    'placeholder',
                    'value',
                    {
                        type: 'select', // Type of the trait
                        name: 'type', // (required) The name of the attribute/property to use on component
                        label: 'Type', // The label you will see in Settings
                        options: [
                            { id: 'text', label: 'Text' },
                            { id: 'password', label: 'Password' },
                            { id: 'email', label: 'Email' },
                            { id: 'tel', label: 'Telephone' },
                            { id: 'url', label: 'URL' },
                            { id: 'search', label: 'Search' },
                            { id: 'number', label: 'Number' },
                            { id: 'range', label: 'Range' },
                            { id: 'date', label: 'Date' },
                            { id: 'datetime-local', label: 'Date & Time (Local)' },
                            { id: 'month', label: 'Month' },
                            { id: 'week', label: 'Week' },
                            { id: 'time', label: 'Time' },
                            { id: 'checkbox', label: 'Checkbox' },
                            { id: 'radio', label: 'Radio Button' },
                            { id: 'file', label: 'File Upload' },
                            { id: 'image', label: 'Image Button' },
                            { id: 'color', label: 'Color Picker' },
                            { id: 'hidden', label: 'Hidden' },
                            { id: 'button', label: 'Button' },
                            { id: 'submit', label: 'Submit Button' },
                            { id: 'reset', label: 'Reset Button' }
                        ],
                    },
                    {
                        type: 'checkbox',
                        name: 'required',
                    },
                ],
                // As by default, traits are bound to attributes, so to define
                // their initial value we can use attributes
                attributes: { type: 'text', required: false },
            },
        },
    });
    editor.Components.addType('textarea', {
        isComponent: (el) => el.tagName === 'TEXTAREA',
        model: {
            defaults: {
                traits: [
                    'name', // Same as: { type: 'text', name: 'name' }
                    {
                        type: 'text',
                        name: 'id',
                        label: 'Id'
                    },
                    {
                        type: 'text',
                        name: 'title',
                        label: 'Title',
                    },
                    {
                        type: 'number',
                        name: 'rows',
                        label: 'Row count',
                    },
                    {
                        type: 'checkbox',
                        name: 'required',
                    },
                ],
                attributes: { row: '5', required: false },
            },
        },
    });

    editor.Components.addType('Form', {
        isComponent: (el) => el.tagName === 'FORM',
        model: {
            defaults: {
                tagName: 'form',
                droppable: ':not(form)',
                draggable: ':not(form)',
                attributes: { method: 'GET' },
                traits: [
                    {
                        type: 'text',
                        name: 'id',
                        label: 'Id'
                    },
                    {
                        type: 'text',
                        name: 'title',
                        label: 'Title',
                    },
                    {
                        type: 'text',
                        name: 'action',
                        label: 'Url',
                        placeholder: "https://eg.co.za"
                    },
                    {
                        type: 'select',
                        name: 'method',
                        options: [
                            { value: 'get', name: 'GET' },
                            { value: 'post', name: 'POST' },
                        ],
                    },
                ],
                // As by default, traits are bound to attributes, so to define
                // their initial value we can use attributes

            },
        },
    });

    editor.Components.addType('time', {
        isComponent: (el) => el.tagName === 'TIME',
        model: {
            defaults: {
                traits: [
                    {
                        type: 'text', // Type of the trait
                        name: 'datetime', // (required) The name of the attribute/property to use on component
                        label: 'Date Time', // The label you will see in Settings
                    },
                ],
                attributes: { datetime: '2024-12-12' },
            },
        },
    });

});