productPrice = $(clickedAction).parent('.product-item').find('.product-price').text();
productName = $(clickedAction).parent('.product-item').find('.product-name').text();
productId = $(clickedAction).parent('.product-item').find('.product-id').text();
productDescription = $(clickedAction).parent('.product-item').find('.product-description').text();

// Get cartObject from localStorage or initialize it as an empty object if it's null
var cartObject = JSON.parse(localStorage.getItem('cartObject')) || {};

// Check if the productId exists in the cartObject
if (cartObject[productId] === undefined) {
    // Initialize cartObject[productId] as an object and set its properties
    cartObject[productId] = {
        'price': productPrice,
        'name': productName,
        'description': productDescription,
        'Id': productId,
        'qty': 1
    };
} else {
    // Update the quantity and other properties if the product already exists in the cart
    cartObject[productId]['qty'] = cartObject[productId]['qty'] + 1;
    cartObject[productId]['price'] = productPrice; // Optionally update the price
    cartObject[productId]['name'] = productName; // Optionally update the name
    cartObject[productId]['description'] = productDescription; // Optionally update the name
}

// Save the updated cartObject back to localStorage
localStorage.setItem('cartObject', JSON.stringify(cartObject));

$("#myToast").remove();
var toast = $("<div>")
    .addClass("toast-container position-fixed bottom-0 end-0 p-3") // Toast container
    .attr("id", "myToast")
    .append(
        $("<div>")
            .addClass("toast") // Toast body
            .attr("id", "cartToast")
            .attr("role", "alert")
            .attr("aria-live", "assertive")
            .attr("aria-atomic", "true")
            .attr("data-bs-autohide", "true") // Auto-hide the toast
            .append(
                $("<div>")
                    .addClass("toast-header bg-custom-dark text-light fw-bold") // Toast header
                    .append($("<strong>").addClass("me-auto").text("Cart Update")) // Title
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
                    .addClass("toast-body") // Toast body content
                    .text(productName + " has been added to your cart!")
            )
    );

// Append the toast to the body
$("body").append(toast);

// Initialize and show the toast using Bootstrap's toast API
var toastElement = new bootstrap.Toast($("#cartToast"));
toastElement.show();
setTimeout(function () {
    toastElement.hide('fast', function(){
        $("#myToast").remove();
    });
}, 2000);
