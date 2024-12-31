<?php

$dir_settings = dirname($_SERVER['DOCUMENT_ROOT']) . "/wmo/settings";
$filename = "$dir_settings/actions.json";

if (($useDB ?? "no") == "yes") {
    $actions = (db_entry_exists($filename, $conn)) ? json_decode(db_get_contents($filename, $conn), true) : [];
} else {
    $actions = (file_exists($filename)) ? json_decode(file_get_contents($filename), true) : [];
}
?>

<p>Data-Action</p>

<datalist id="dav">
<?php 
    foreach ($actions as $key => $value) {
        if ($value['enabled'] == "true" ? true : false) {
            $id = str_replace(".js", "", $key);
            $label = ucwords($id);
             echo "<option value=\"$id\">$label</option>";
        }
    }
?>
</datalist>

<input id="dataActionValue" value="<?php echo $_GET['da'] ?>" list="dav">
<button id="setDataAction">Save</button>
