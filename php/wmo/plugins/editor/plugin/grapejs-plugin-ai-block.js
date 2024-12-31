// bs-class-selector.js (or directly in your script)
grapesjs.plugins.add('grapejs-plugin-ai-block', (editor) => {

    editor.Panels.addButton('options', {
        id: 'grapejs-plugin-ai-block',
        className: 'bi bi-cpu-fill',
        command: 'grapejs-plugin-ai-block-cmd',
        attributes: { title: 'Ask Gemini AI to create a block' },
        active: false,
    });
    editor.Commands.add('grapejs-plugin-ai-block-cmd', {
        run(editor, sender, options) {
            const modal = editor.Modal;
            modal.setTitle('Describe Your block');
            modal.setContent('<div id="ai_area">    <div>        <textarea id="ai_input" style="width:100%" rows="4"></textarea>    </div>    <div>        <button id="ai_submit">Generate Block</button>    </div>    <div id="answer"></div></div>');
            modal.open();
            $(document).on("click", "#ai_submit", function () {
                modal.close();
            });
        }
    });
    $(document).on("click", "#ai_submit", function () {

        function generateRandomString(length) {
            const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            let randomString = '';
            for (let i = 0; i < length; i++) {
                const randomIndex = Math.floor(Math.random() * characters.length);
                randomString += characters[randomIndex];
            }
            return randomString;
        }
        question = $("#ai_input").val();

        $("#ai_area").remove();

        $.ajax({
            url: "aiblock.php",
            data: { data: question },
            type: "POST",
            success: function (response) {
                rr = $("<div>")
                    .html(response);
                console.log(response);

                section = "<section>" + rr.find("section").html() + "</section>" ?? "<section>No Section Found</section>";

                key = generateRandomString(16);
                editor.BlockManager.add(key, {
                    id: key,
                    label: "AI - " + key,
                    content: section,
                    category: {
                        label: "AI Generated",
                        open: false
                    },
                });
            }
        });
    });

});