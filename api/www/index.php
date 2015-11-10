<?php

require '../vendor/autoload.php';

if (!function_exists('t')) {
    function t($template, array $data = [])
    {
        $templateFile = '../templates/'.$template.'.php';
        if (!is_readable($templateFile)) {
            throw new \Exception('Unreadable template '.$template);
        }

        ob_start();
        extract($data);
        include $templateFile;
        return ob_get_clean();
    }
}

$app = Bono\App::getInstance();

$app->run();
