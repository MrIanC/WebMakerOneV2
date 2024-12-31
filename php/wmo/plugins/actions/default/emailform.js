/*startDeactionion Use jquery to POST all the data to the form action URL. <br />
Your form should contain: <br />
- A form action URL <br />
- A form input with the name "sent" and value of the URL to redirect to on success <br />
- A form input with the name "fail" and value of the URL to redirect to on failure <br />
- All required fields should have the "required" attribute <br />
- All fields should have a name attribute <br />
- All fields should have a type attribute <br />

The form will be hidden on successful submission and the user will be redirected to the URL specified in the "sent" input. <br />
If the submission fails, the user will be redirected to the URL specified in the "fail" input. <br />
If the "sent" or "fail" inputs are not valid paths, the user will be redirected to the home page. <br />
The form will also validate all required fields and display an error message if any are empty. <br />
The form will also validate all fields and display a success message if all are filled. <br />
The form will also display an error message if the submission fails. <br />

This function requires jQuery. <br />
endDeactionion*/
{
    let allFieldsFilled = true;
    $(clickedAction).closest('form').find('input[required]').each(function () {
        if ($(this).val() === '') {
            allFieldsFilled = false;
            $(this).addClass("is-invalid");
        } else {
            if (allFieldsFilled == true) {
                $(this).addClass("is-valid");
                $(this).removeClass('is-invalid');
            }
        }
    });

    if (allFieldsFilled == true) {
        $(clickedAction).closest('form').addClass("d-none")
        $.ajax({
            url: $(clickedAction).closest('form').attr("action"),
            type: "POST",
            data: $(clickedAction).closest('form').serialize(),
            success: function (response) {
                var sentVal = $('input[name="sent"]').val();
                var failVal = $('input[name="fail"]').val();

                // Basic validation to ensure input values are relative paths
                function isValidPath(path) {
                    return path.startsWith("/") && !path.includes("http");  // Only allow relative paths
                }

                if (response == "pass" && isValidPath(sentVal)) {
                    window.location.href = sentVal;
                } else if (isValidPath(failVal)) {
                    window.location.href = failVal;
                    console.log(response);
                } else {
                    console.error("Invalid redirect path");
                }
            },

            error: function (xhr, status, error) {
                window.location.href = failVal;
                console.error('Form submission failed');
                console.error(error);
            }
        });
    }
}