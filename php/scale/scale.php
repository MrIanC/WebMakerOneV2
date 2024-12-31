<?php
function inject_scale($htmlDOC) {
    $htmlDOC->inject("head",'    <meta name="viewport" content="width=device-width, initial-scale=1.0" />',"start");
}

inject_scale($htmlDOC);