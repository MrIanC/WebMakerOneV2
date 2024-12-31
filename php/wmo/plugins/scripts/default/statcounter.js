/*start
{
    "sc_project":"Project",
    "sc_invisible":"Visible",
    "sc_security": "Security"
}
end*/
/*startDescription Stat Counter endDescription*/

var sc_project = #sc_project#;
var sc_invisible = #sc_invisible#;
var sc_security = "#sc_security#";

$.getScript("https://www.statcounter.com/counter/counter.js")
    .done(function () {
        console.log('Statcounter Loaded');
    })
    .fail(function () {
        console.log('oops ' + scriptPath);

    });

/*
<noscript>
    <div class="statcounter">
        <a title="web counter" href="https://statcounter.com/" target="_blank">
            <img class="statcounter" src="https://c.statcounter.com/13066376/0/f6373e0e/1/" alt="web counter" referrerPolicy="no-referrer-when-downgrade">
        </a>
    </div>
</noscript>

*/