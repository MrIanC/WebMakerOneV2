/*start
{
    "phonesystem-url":"Phone System URL",
    "party":"Party"
}
end*/
/*startDescription The 3CX Webpage Embed Chat is a feature that allows businesses to integrate live chat functionality directly into their websites. It provides a customizable chat widget that visitors can use to communicate with your team in real time. endDescription*/
$(document).ready(function () {
    $("<call-us-selector>")
        .attr("phonesystem-url", "#phonesystem-url#")
        .attr("party", "#party#")
        .appendTo("body");
    $("<script>")
        .attr("src", "https://downloads-global.3cx.com/downloads/livechatandtalk/v1/callus.js")
        .attr("id", "tcx-callus-js")
        .attr("charset", "utf-8")
        .appendTo("body");
});
