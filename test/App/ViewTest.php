<?php

namespace BadHabit\LoginManagement\App;

use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{

    public function testRender()
    {
        View::render('Home/index', [
            'title' => 'PHP Login Management'
        ]);

        // If nothing return except rendering the view, then expected output is
        // using keyword from rendered view.

        $this->expectOutputRegex('[PHP Login Management]');
        $this->expectOutputRegex('[html]');
        $this->expectOutputRegex('[body]');
        $this->expectOutputRegex('[Login Management]');
        $this->expectOutputRegex('[Login]');
        $this->expectOutputRegex('[Create Account]');
    }

}
