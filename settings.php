<?php

class Settings
{
    public $settings = [];
    public $log = [];
    function __construct()
    {
        $this->settings['out_dir'] = dirname($_SERVER['DOCUMENT_ROOT']);
        $this->settings['app_dir'] = __DIR__;
        $this->settings['web_dir'] = $_SERVER['DOCUMENT_ROOT'];
        $this->settings['html_dir'] = __DIR__ . '/html';
        $this->settings['js_dir'] = $this->settings['app_dir'] . '/js';
        $this->settings['media_dir'] = $this->settings['app_dir'] . '/media';
        $this->settings['php_dir'] = $this->settings['app_dir'] . '/php';

        $this->settings['web_js_dir'] = $_SERVER['DOCUMENT_ROOT'] . "/js";

        $this->available_plugins();
    }
    private function available_plugins()
    {
        foreach ((array) glob($this->settings['php_dir'] . "/*/plugin.json") as $pluginsJSON) {
            $this->log[] = "filename: $pluginsJSON";
            $required = false;
            $pluginJSON = json_decode(file_get_contents($pluginsJSON), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->log[] = "Invalid JSON in $pluginsJSON: " . json_last_error_msg();
                continue;
            }

            $required = isset($pluginJSON, $pluginJSON['plugin'], $pluginJSON['plugin']['filename'], $pluginJSON['plugin']['name']);
            $this->log[] = "required JSON: " . ($required ? "YES" : "NO");
            if ($required) {
                $pluginDIR = dirname($pluginsJSON);
                $this->settings['plugins'][$pluginJSON['plugin']['name']] = "$pluginDIR/{$pluginJSON['plugin']['filename']}";
            }
        }
    }

    public function logs()
    {
        return $this->log;
    }
}

$settings = new Settings();

$settingsJSON = json_decode(file_get_contents("settings.json"), true);
foreach ($settingsJSON['use_plugins'] as $key => $value) {
    if (isset($settings->settings['plugins'][$value])) {
        $settings->log[] = "$value Plugin Loaded";
        include $settings->settings['plugins'][$value];
    } else {
        //$settings->log[] = print_r($value,true) . " Plugin Not Found";
    }

}
