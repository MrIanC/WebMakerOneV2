<?php
$template = '
        <li class="nav-item">
            <a href="#url#" class="d-flex justify-content-between nav-link text-white #active#" aria-current="page">
                <i class="bi #bi-icon#">#bi-text#</i>
                <span>#link-text#</span>
            </a>
        </li>
';

$wmo_menu_html = "<div>";
foreach ($wmo_plugins as $key => $file) {
    $decode = json_decode(file_get_contents($file), true);
    $rep=[];
    $sea=[];
    
    if (isset($decode['menu']['show']) && ($decode['menu']['show'] == "yes")) {
        foreach ($decode['menu'] as $k => $val) {
            $rep[] = "#$k#";
            $sea[] = $val;
        }
        
        $rep[] = "#active#";
        $sea[] = (($_GET['wmo']??"") == $key) ? "active  bg-secondary": "text-white";
        $wmo_menu_html .= str_replace($rep, $sea, $template);
    }
}

$wmo_menu_html .= "</div>";
