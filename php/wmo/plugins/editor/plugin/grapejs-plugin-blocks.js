// bs-class-selector.js (or directly in your script)
grapesjs.plugins.add('grapejs-plugin-blocks', (editor) => {
    $.ajax({
        url: "blocks/setup.php",
        dataType: "json",
        success: function (response) {
            $.each(response, function (key, value) {
                $.ajax({
                    url: "blocks/read.php?page="+value['path'],
                    dataType: "html",
                    success: function(blockHtml) {
                        //console.log(value['label'] + " loaded");
                        editor.BlockManager.add(key, {
                            id: key,
                            label: value['label'],
                            content: blockHtml,
                            category: {
                                label: value['category'],
                                open: false
                            },
                        });        
                    },
                    error: function() {
                        console.log("failed");
                    }
                });
            });
        },
        error: function () {
            console.log("unable to load blocks");
        }
    })
});