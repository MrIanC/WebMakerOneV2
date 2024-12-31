grapesjs.plugins.add('grapejs-plugin-make-block', (editor) => {

    // Function to get HTML with inline styles
    function getElementStylesRecursive(component, editor) {
        const cssComposer = editor.CssComposer;

        // Get inline styles
        const inlineStyles = component.getStyle();

        // Get styles from associated classes
        const classes = component.getClasses();
        let classStyles = {};
        classes.forEach(cls => {
            const rule = cssComposer.getRule(`.${cls}`);
            if (rule) {
                Object.assign(classStyles, rule.getStyle());
            }
        });

        // Merge inline and class-based styles (inline takes precedence)
        const mergedStyles = { ...classStyles, ...inlineStyles };

        // Convert styles to CSS string
        const styleString = Object.entries(mergedStyles)
            .map(([key, value]) => `${key}: ${value};`)
            .join(' ');

        // Handle text nodes directly
        if (component.is('textnode')) {
            return component.get('content') || '';
        }

        // Handle regular components
        const tagName = component.get('tagName') || 'div';
        const attributes = component.getAttributes();
        const attrString = Object.entries(attributes)
            .map(([key, value]) => `${key}="${value}"`)
            .join(' ');

        // Recursively process child components
        const children = component.components();
        const childrenHtml = children.map(child => getElementStylesRecursive(child, editor)).join('');

        // Build and return the HTML for this component
        return `<${tagName} ${attrString} style='${styleString}'>${childrenHtml}</${tagName}>`;
    }

    // Add the button to the panel
    editor.Panels.addButton('options', {
        id: 'grapejs-plugin-make-block',
        className: 'bi bi-file-earmark-plus-fill',
        command: 'grapejs-plugin-make-block-cmd',
        attributes: { title: 'Save this block of code' },
        active: false,
    });

    // Add the command for the button
    editor.Commands.add('grapejs-plugin-make-block-cmd', {
        run(editor, sender, options) {
            const modal = editor.Modal;

            const selected = editor.getSelected();
            if (!selected) {
                alert('Please select an element first.');
                return;
            }

            // Generate HTML with inline styles
            const htmlWithStyles = getElementStylesRecursive(selected, editor);

            modal.setTitle('Name your block');
            modal.setContent(`
                <div id="save_block">
                    <div><input id="block_name" style="width:100%" placeholder="Block Name"/></div>
                    <div><button id="saveblock">Generate Block</button></div>
                    <textarea id="savehtml" style="width:100%;height:200px;">${htmlWithStyles}</textarea>
                </div>
            `);
            modal.open();

            // Close the modal and send the data on save
            $(document).on("click", "#saveblock", function () {
                modal.close();
                const blockName = $("#block_name").val();
                const blockHtml = $("#savehtml").val();

                if (blockName && blockHtml) {
                    $.ajax({
                        url: "make_block.php",
                        data: {
                            block_name: blockName,
                            savehtml: blockHtml,
                        },
                        type: "POST",
                        success: function (response) {
                            console.log("Block saved:", response);
                            editor.BlockManager.add(blockName, {
                                id: blockName,
                                label: blockName,
                                content: response,
                                category: {
                                    label: "User",
                                    open: false
                                },
                            });
                        }
                    });
                } else {
                    alert("Please enter a block name and ensure the block HTML is valid.");
                }
            });
        }
    });

});
