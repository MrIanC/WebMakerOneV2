/*start
{
    "google_tag_id":"Google Tag ID"
}
end*/
/*startDescription When embedded in a webpage, it collects data about user interactions, such as page views, session duration, and events like clicks or downloads endDescription*/

googleTagID = "#google_tag_id#";
$.getScript("https://www.googletagmanager.com/gtag/js?id=" + googleTagID)
    .done(function () {
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', googleTagID);
    })
    .fail(function () {
        console.log('oops ' + scriptPath);
    });