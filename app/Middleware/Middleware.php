<?php

/*
 * Prevents direct access to auth page after login
 * using Middleware
 *
 * or prevent direct access to some/any pages before login
 * using Middleware
 *
 *
 * */

namespace BadHabit\LoginManagement\Middleware;

interface Middleware
{

    function before(): void;

}