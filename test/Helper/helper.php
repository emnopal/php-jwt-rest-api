<?php

namespace BadHabit\LoginManagement\App {

    // Mock function header, to be used in the test
    function header(string $value)
    {
        echo $value;
    }
}


namespace BadHabit\LoginManagement\Service {

    function setcookie(string $name, string $value)
    {
        echo "$name: $value";
    }
}
