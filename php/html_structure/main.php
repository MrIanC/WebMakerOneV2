<?php
class HTML_Structure
{
    public $doc;

    function __construct()
    {
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->loadHTMLFile(__DIR__ . '/html/boilerplate.html');
    }
    function inject($element, $html = "", $place = "end") {
        libxml_use_internal_errors(true);

        // Attempt to load the HTML fragment
        $tmp = new DOMDocument('1.0', 'UTF-8');
        if (!$tmp->loadXML($html)) {
            $errors = libxml_get_errors();
            print_r($errors);
            echo "Invalid XML: Unable to load the provided fragment.";
            print_r($html);
            return;
        }
    
        // Locate the target element in the main document
        $docelement = $this->doc->getElementsByTagName($element)->item(0);
        if (!$docelement) {
            echo "Element <$element> not found.";
            return;
        }
    
        // Import the fragment nodes into the main document
        foreach ($tmp->childNodes as $child) {
            $node = $this->doc->importNode($child, true);
    
            if ($place === "start") {
                $docelement->insertBefore($node, $docelement->firstChild);
            } else { // Default to "end"
                $docelement->appendChild($node);
            }
        }
    }
    
    function htmlString() {
        $this->doc->formatOutput;
        return  $this->doc->saveHTML();
    }

}
$htmlDOC = new HTML_Structure();