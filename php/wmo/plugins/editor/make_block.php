<?php
echo $_POST['savehtml'];
file_put_contents(__DIR__ . "/blocks/User/{$_POST['block_name']}.html" ,$_POST['savehtml']);