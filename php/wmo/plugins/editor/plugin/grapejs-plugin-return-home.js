// bs-class-selector.js (or directly in your script)
grapesjs.plugins.add('grapejs-plugin-return-home', (editor) => {
    editor.Panels.addButton('options', {
        id: 'grapejs-plugin-return-home',
        className: 'bi bi-house',
        command: 'grapejs-plugin-return-home-cmd',
        attributes: { title: 'Return Home' },
        active: false,
    });
    editor.Commands.add('grapejs-plugin-return-home-cmd', {
        run(editor, sender) {
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
                    console.log(response);
                    window.location = "/app/?wmo=content";
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        },
    });
});