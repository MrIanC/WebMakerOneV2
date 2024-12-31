/*start
{
    "password":"Password to view site"
}
end*/
/*startDescription This script creates a full-page password prompt that hides the content of a website until the correct password is entered. Intended for low security content !NB This website data is not secured and still can be viewed by bypassing this screen. endDescription*/
$(document).ready(function () {
    $.getScript("https://cdnjs.cloudflare.com/ajax/libs/js-cookie/3.0.1/js.cookie.min.js", function () {
        if (Cookies.get("privateView")) {
            console.log("private View Password Done");
        } else {
            var fullScreenDiv = $('<div>')
                .attr("id", "hidemeDiv")
                .css({
                    position: 'fixed',
                    top: 0,
                    left: 0,
                    width: '100%',
                    height: '100%',
                    backgroundColor: 'rgba(0, 0, 0, 1)',
                    zIndex: 9999
                })
                .append(
                    $("<div>")
                        .addClass("d-flex justify-content-center align-items-center vh-100")
                        .append(
                            $("<div>")
                                .addClass("text-center")
                                .append(
                                    $("<div>")
                                        .text("Please enter the password to continue.")
                                        .addClass("text-white mb-3"),
                                    $("<div>")
                                        .attr("id", "passwordMessage")
                                        .text("Password is incorrect.")
                                        .addClass("mb-3 d-none form-control border-danger"),
                                    $("<input>").attr("type", "text")
                                        .addClass("form-control mb-3")
                                        .attr("id", "hidemePassword")
                                        .attr("placeholder", "Password"),
                                    $("<button>")
                                        .addClass("btn btn-primary")
                                        .text("Submit")
                                        .attr("id", "hidemeSubmit")
                                        .click(function () {
                                            if ($('#hidemePassword').val() == '#password#') {
                                                Cookies.set('privateView', 'Yes', { expires: (1 / 24), path: '/' });
                                                $("#hidemeDiv").hide();
                                            } else {
                                                $("#passwordMessage").removeClass("d-none");
                                                $("#hidemePassword").
                                                    val("")
                                                    .addClass("d-none");
                                                setTimeout(function () {
                                                    $("#passwordMessage").addClass("d-none");
                                                    $("#hidemePassword").removeClass("d-none");
                                                }, 3000);
                                            }
                                        })

                                )
                        )
                );
            $('body').append(fullScreenDiv);
        }
    });



});