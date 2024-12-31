// bs-class-selector.js (or directly in your script)
grapesjs.plugins.add('grapejs-plugin-save', (editor) => {
    editor.Panels.addButton('options', {
        id: 'grapejs-plugin-save',
        className: 'bi bi-floppy',
        command: 'grapejs-plugin-save-cmd',
        attributes: { title: 'Save HTML and CSS' },
        active: false,
    });
    editor.Commands.add('grapejs-plugin-save-cmd', {
        run(editor, sender, options) {
            const html = editor.getHtml();
            const css = editor.getCss();
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            const page = urlParams.get('page');
            jQuery.ajax({
                url: 'save.php',
                type: 'POST',
                data: {
                    html: html,
                    css: css,
                    page: page
                },
                success: function (response) {
                    if (response === 'Saved') {
                        alert('Saved');
                    }
                    console.log(response);
                },
                error: function (xhr, status, error) {
                    console.log(response);
                    console.error('AJAX Error:', status, error);
                }
            });
        }
    });
});