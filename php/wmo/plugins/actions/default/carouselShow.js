{
    console.log($(clickedAction).attr("src"));
    setTimeout(function () {
        $nextimage = $(clickedAction).closest('.carouselbutton').next().find('img');
        if ($nextimage.length == 0) {
            $(clickedAction).closest('.carouselmain').find(".carouselbutton").first().find("img").click();
        } else {
            $nextimage.click();
        }

        //$(clickedAction).closest('.carouselbutton').next().find('img').click(); // Find the next image

    }, 10000);
    conMain = $(clickedAction).closest(".carouselmain");
    conMain.animate({ opacity: 0 }, 160, function () {
        $(clickedAction).closest(".carouselmain").css("background-image", "url('" + $(clickedAction).attr("src") + "')");
        conMain.animate({ opacity: 1 }, 160, function () {
        });
    });
}



