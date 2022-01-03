<?php

namespace BadHabit\LoginManagement\App;

class View
{

    public static function render(string $view, $model): void
    {
        require __DIR__ . '/../View/header.php';
        require __DIR__ . '/../View/' . $view . '.php';
        require __DIR__ . '/../View/footer.php';

        // Problem with PHPUnit Test, the PHPUnit Test
        // will stop the execution prematurely
        // So we need to use this logic to avoid error
        getenv('mode') != 'test' ?? exit();
    }

    public static function redirect(string $url): void
    {
        header('Location: ' . $url);

        // Problem with PHPUnit Test, the PHPUnit Test
        // will stop the execution prematurely
        // So we need to use this logic to avoid error
        getenv('mode') != 'test' ?? exit();
    }

}