showCart();

function showCart() {
    var cartObject = JSON.parse(localStorage.getItem('cartObject')) || {};
    if (Object.keys(cartObject).length === 0) {
        $("#myToast").remove();
        var toast = $("<div>")
            .addClass("toast-container position-fixed bottom-0 end-0 p-3")
            .attr("id", "myToast")
            .append(
                $("<div>")
                    .addClass("toast")
                    .attr("id", "cartToast")
                    .attr("role", "alert")
                    .attr("aria-live", "assertive")
                    .attr("aria-atomic", "true")
                    .attr("data-bs-autohide", "true")
                    .append(
                        $("<div>")
                            .addClass("toast-header bg-custom-dark text-light fw-bold")
                            .append($("<strong>").addClass("me-auto").text("Empty Cart"))
                            .append(
                                $("<button>")
                                    .addClass("btn-close")
                                    .attr("type", "button")
                                    .attr("data-bs-dismiss", "toast")
                                    .attr("aria-label", "Close")
                            )
                    )
                    .append(
                        $("<div>")
                            .addClass("toast-body")
                            .text("Your Cart is empty")
                    )
            );
        $("body").append(toast);
        var toastElement = new bootstrap.Toast($("#cartToast"));
        toastElement.show();
        setTimeout(function () {
            toastElement.hide('fast', function () {
                $("#myToast").remove();
            });
        }, 2000);
    } else {
        table = $("<table>")

            .attr("id", "productTable")
            .addClass("table table-striped table-hover table-bordered small");

        table.append(
            $("<tr>")
                .append(
                    $("<th>")
                        .html("ID")
                    ,
                    $("<th>")
                        .html("Name")
                    ,
                    $("<th>")
                        .css("text-align", "center")
                        .html('Qty')
                    ,
                    $("<th>")
                    .css("text-align","right")
                        .html('Price')
                    ,
                    $("<th>")
                        .css("text-align","right")
                        .html('Total')
                    ,
                    $("<th>")
                        .css("text-align","right")
                        .html('Actions')
                    ,
                )
        );
        cartTotal = 0;
        $.each(cartObject, function (pname, pdata) {
            cartTotal += pdata['qty'] * pdata['price'];
            table.append(
                $("<tr>").append(
                    $("<td>")
                        .html(pname)
                        .addClass("small")
                        .attr("title", pdata['description'])
                    ,
                    $("<td>")
                        .html(pdata['name'])
                        .attr("title", pdata['description'])
                    ,
                    $("<td>")
                        .css("text-align", "center")
                        .html(pdata['qty'])
                    ,
                    $("<td>")
                        .css("text-align","right")
                        .html(pdata['price'])
                    ,
                    $("<td>")
                        .css("text-align","right")
                        .html(pdata['qty'] * pdata['price'])
                    ,
                    $("<td>")
                        .css("text-align","right")
                        .append(
                            $("<i>")
                                .addClass("bi bi-plus-circle btn")
                                .click(function () {
                                    cartObject[pname]['qty'] = cartObject[pname]['qty'] + 1;
                                    localStorage.setItem('cartObject', JSON.stringify(cartObject));
                                    $('#mycart').remove();
                                    showCart();
                                }),
                            $("<i>")
                                .addClass("bi bi-dash-circle btn")
                                .click(function () {
                                    cartObject[pname]['qty'] = cartObject[pname]['qty'] - 1;

                                    if (cartObject[pname]['qty'] == 0) {
                                        delete cartObject[pname];
                                    }
                                    localStorage.setItem('cartObject', JSON.stringify(cartObject));
                                    $('#mycart').remove();
                                    showCart();
                                }),
                        )
                )
            );
        })
        table.append(
            $("<tr>")
                .append(
                    $("<th>")
                        .attr("colspan", "4")
                        .html('Cart Total')
                    ,
                    $("<th>")
                        .css("text-align","right")
                        .html(cartTotal)
                    ,
                    $("<th>")
                        .html('')
                    ,
                )
        );
        $("body").append(
            $("<div>")
                .attr("id", "mycart")
                .css("position", "fixed")
                .css("top", "0px")
                .css("left", "0px")
                .css("bottom", "0px")
                .css("right", "0px")
                .css("z-index", "10000000")
                .css("overflow-y", "auto")
                .css("overflow-x", "hidden")
                .addClass("bg-dark bg-opacity-75")
                .append(
                    $("<div>")
                        .addClass("bg-light container my-3 p-3 rounded shadow")
                        .append(
                            $("<div>")
                                .addClass("d-flex justify-content-between")
                                .attr("onclick", "$('#mycart').remove()")
                                .append(
                                    $("<div>")
                                        .addClass("display-5 pb-5")
                                        .html("Cart"),
                                    $("<i>")
                                        .addClass("bi bi-x h3")
                                )
                            , table,
                            $("<div>").append(
                                $("<div>").html("Checkout")
                                    .addClass("form-control btn btn-custom-dark")
                                    .click(function () {
                                        $(this).remove();
                                        $("#productTable").after(
                                            $("<div>")
                                                .attr("id", "detailsform")
                                                .addClass("p-3")
                                                .append(
                                                    $("<label>")
                                                        .addClass("form-label")
                                                        .html("First and Last Name"),
                                                    $("<input>")
                                                        .attr("id", "fullName")
                                                        .addClass("form-control"),
                                                    $("<label>")
                                                        .addClass("form-label")
                                                        .html("Email Address:"),
                                                    $("<input>")
                                                        .attr("id", "email")
                                                        .addClass("form-control"),
                                                    $("<label>")
                                                        .addClass("form-label")
                                                        .html("Phone Number:"),
                                                    $("<input>")
                                                        .attr("id", "number")
                                                        .addClass("form-control"),
                                                    $("<label>")
                                                        .addClass("form-label")
                                                        .html("Physical Address:"),
                                                    $("<textarea>")
                                                        .attr("id", "address")
                                                        .addClass("form-control"),
                                                    $("<div>")
                                                        .attr("id", "carterrors")
                                                        .addClass("form-label"),
                                                    $("<button>")
                                                        .attr("id", "email")
                                                        .addClass("form-control btn btn-custom-dark")
                                                        .html("Complete")
                                                        .click(function () {
                                                            ok = true;
                                                            if (!(/^[a-zA-Z]+(?:['-][a-zA-Z]+)*\s+[a-zA-Z]+(?:['-][a-zA-Z]+)*$/.test($("#fullName").val()))) {
                                                                $("#fullName")
                                                                    .addClass("is-invalid")
                                                                    .on("keypress", function () {
                                                                        $("#fullName")
                                                                            .removeClass("is-invalid");
                                                                    });
                                                                ok = false;
                                                            }
                                                            if (!(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test($("#email").val()))) {
                                                                $("#email")
                                                                    .addClass("is-invalid")
                                                                    .on("keypress", function () {
                                                                        $("#email")
                                                                            .removeClass("is-invalid");
                                                                    });
                                                                ok = false;
                                                            };
                                                            if (!(/^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/.test($("#number").val()))) {
                                                                $("#number")
                                                                    .addClass("is-invalid")
                                                                    .on("keypress", function () {
                                                                        $("#number")
                                                                            .removeClass("is-invalid");
                                                                    });
                                                                ok = false;
                                                            };
                                                            if (!(/^[a-zA-Z0-9\s,'-\.#\n]+$/.test($("#address").val()))) {
                                                                $("#address")
                                                                    .addClass("is-invalid")
                                                                    .on("keypress", function () {
                                                                        $("#address")
                                                                            .removeClass("is-invalid");
                                                                    });
                                                                ok = false;
                                                            };
                                                            if (ok) {
                                                                thisbutton = $(this);
                                                                thisbutton
                                                                    .addClass("disabled");
                                                                message = $("<div>").append(
                                                                    $("<p>").append(
                                                                        $("<table>").css("width", "100%").append($("#productTable").html()),
                                                                        $("<pre>").text(
                                                                            $("#address").val()
                                                                        )
                                                                    )
                                                                );

                                                                $.ajax({
                                                                    url: $("#sendcarttoaddress").text(),
                                                                    type: "POST",
                                                                    data: { "name": $("#fullName").val(), "number": $("#number").val(), "email": $("#email").val(), "message": message.html(), "from": $("#sendfromtoaddress").text(), "sent": "recieved", "fail": "tryagain" },
                                                                    success: function (response) {
                                                                        if (response == "pass") {

                                                                            $("#productTable").after(
                                                                                $("<div>")
                                                                                    .addClass("text-center bg-light text-dark p-2")
                                                                                    .append(
                                                                                        $("<div>")
                                                                                            .addClass("h1")
                                                                                            .html("Cart Sent"),
                                                                                        $("<div>")
                                                                                            .html("Your cart has been sent for validation. An email will be sent to the provided email address with an invoice and further instructrions."),
                                                                                        $("<button>")
                                                                                            .addClass("form-control btn btn-custom-dark my-4 disabled")
                                                                                            .attr("id", "okButton")
                                                                                            .html("OK")
                                                                                            .click(function () {
                                                                                                localStorage.removeItem('cartObject');
                                                                                                $("#mycart").remove();
                                                                                            })

                                                                                    )
                                                                            );
                                                                            setTimeout(function () {
                                                                                $("#okButton").removeClass("disabled")
                                                                            }, 3000);

                                                                            $("#productTable").remove();
                                                                            $("#detailsform").remove();
                                                                            $("#carterrors").remove();
                                                                            thisbutton.remove();
                                                                        } else {
                                                                            $("#carterrors")
                                                                                .addClass("text-center bg-warning text-danger p-2 border border-danger rounded")
                                                                                .html("<strong>An Error Occoured!</strong> Please check the details you have entered.");
                                                                            thisbutton
                                                                                .removeClass("disabled");
                                                                        }
                                                                    },
                                                                    error: function (xhr, status, error) {
                                                                        thisbutton
                                                                            .removeClass("disabled");
                                                                        $("#carterrors")
                                                                            .addClass("text-center bg-warning text-danger p-2 border border-danger rounded")
                                                                            .html("<strong>An Error Occoured!</strong>" + error);
                                                                        console.error();
                                                                    }
                                                                });
                                                            }
                                                        }),
                                                )
                                        )
                                    }),
                            )
                        )
                )
        )
    }
}