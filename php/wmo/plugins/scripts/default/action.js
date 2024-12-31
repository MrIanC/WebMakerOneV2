/*start
{
    "action-script-path":"Action Scripts Path (/js/action)"
}
end*/
/*startDescription  endDescription*/
dir = '#action-script-path#' !== '' ? '#action-script-path#' : '/js/action';
console.log(dir);
console.log("Action Script Loaded")
$(document).on('click', '.action', function (event) {
    event.preventDefault();
    clickedAction = this;
    scriptPath = $(clickedAction).data("action");
    $.getScript(dir + '/' + scriptPath + ".js")
        .done(function () {
            console.log('Script Executed: ' + scriptPath);
        })
        .fail(function () {
            console.log('No Script Found: ' + scriptPath);
        });
});
