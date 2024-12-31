/*startDeactionion 
This will fill the screen with the image that was clicked on. <br />
The image will be displayed in a popup with a dark background. <br />
The image will be displayed in the center of the screen. <br />
The image will be displayed with a close button in the top right corner. <br />
There will be previous and next buttons to navigate through the images that are in the gallery-thumbnails class <br />
endDeactionion*/
{
    /*
    let touchStartX = 0;
    let touchEndX = 0;

    function touchStart(event) {
        touchStartX = event.touches[0].clientX;
    }

    function touchMove(event) {
        touchEndX = event.touches[0].clientX;
    }

    function touchEnd(event) {
        const swipeDistance = touchEndX - touchStartX;
        const swipeThreshold = 150;

        if (swipeDistance > swipeThreshold) {
            prevImage();
        } else if (swipeDistance < -swipeThreshold) {
            nextImage();
        }
    }
*/
    function nextImage() {
        console.log("Checking");
        currentIndex = $('.gallery-thumbnails img').index($(clickedAction));
        $('.gallery-thumbnails img').each(function (index, element) {
            if (index == (currentIndex + 1)) {
                clickedAction = element;
                /*$("#imageDiv").animate({ 'background-position-x': '-100vw' }, 130, "linear", function () {
                    $("#imageDiv")
                        .css("background-image", "url('" + $(clickedAction).attr("src") + "')")
                        .css('background-position-x', "100vw");

                    $("#imageDiv").animate({ 'background-position-x': '0vw' }, 130);
                });*/
                $("#imageDiv").fadeOut(100, function () {
                    $(this).css("background-image", "url('" + $(clickedAction).attr("src") + "')").fadeIn(50);
                });
            }
        });

    };

    function prevImage() {
        console.log("Checking");
        currentIndex = $('.gallery-thumbnails img').index($(clickedAction));
        $('.gallery-thumbnails img').each(function (index, element) {
            if (index == (currentIndex - 1)) {
                clickedAction = element;
                /*$("#imageDiv").animate({ 'background-position-x': '100vw' }, 130, "linear", function () {
                    $("#imageDiv")
                        .css("background-image", "url('" + $(clickedAction).attr("src") + "')")
                        .css('background-position-x', "-100vw");

                    $("#imageDiv").animate({ 'background-position-x': '0vw' }, 130);
                });*/
                $("#imageDiv").fadeOut(100, function () {
                    $(this).css("background-image", "url('" + $(clickedAction).attr("src") + "')").fadeIn(50);
                });
            }
        });
    };

    $("body")
        .append(
            $("<div>")
                .append(
                    $("<div>")
                        .addClass("bg-dark")
                        .attr("id", "imagePopup")
                        .css("position", "fixed")
                        .css("top", "0px")
                        .css("bottom", "0px")
                        .css("left", "0px")
                        .css("right", "0px")
                        .css("z-index", "100000000")

                        .append($("<div>")
                            .attr("id", "imageDiv")
                            /*.attr("onTouchStart", "touchStart(event)")
                            .attr("onTouchMove", "touchMove(event)")
                            .attr("onTouchEnd", "touchEnd(event)")*/
                            .css("height", "100%")
                            .css("width", "100%")
                            .css("background-image", "url('" + $(clickedAction).attr("src") + "')")
                            .css("background-size", "contain")
                            .css("background-repeat", "no-repeat")
                            .css("background-position", "50% 50%")
                            .append(
                                $("<div>")
                                    .css("height", "100%")
                                    .addClass("row align-items-center")
                                    .append(
                                        $("<div>")
                                            .addClass("col")
                                            .append(
                                                $("<div>")
                                                    .addClass("d-flex justify-content-between")
                                                    .append($("<span>")
                                                        .attr("id", "prevImage")
                                                        .attr("onclick", `prevImage()`)
                                                        .html(`<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16"><path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/></svg>`)
                                                        .addClass("p-1 m-2 text-dark bg-light rounded-circle shadow bg-opacity-50 text-opacity-50")
                                                    )
                                                    .append($("<span>")
                                                        .attr("id", "nextImage")
                                                        .attr("onclick", "nextImage();")
                                                        .html(`<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-arrow-right-circle-fill" viewBox="0 0 16 16">  <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0M4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5z"/></svg>`)
                                                        .addClass("p-1 m-2 text-dark bg-light rounded-circle shadow bg-opacity-50 text-opacity-50")
                                                    )
                                            )
                                    )
                            )

                        )
                        .append($("<div>")
                            .css("position", "fixed")
                            .css("top", "0px")
                            .css("left", "0px")
                            .css("right", "0px")
                            .addClass("d-flex justify-content-center")
                            .append($("<div>")
                                .attr("onclick", `$("#imagePopup").fadeOut(function(){$("#imagePopup").remove()});`)
                                .html(`<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/></svg>`)
                                .addClass("p-1 m-2 text-dark bg-light rounded-circle shadow bg-opacity-50 text-opacity-50")
                            )
                        )
                )
                .css("display", "none")
                .fadeIn('fast')
        )
}