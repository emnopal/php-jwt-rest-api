<?php

namespace BadHabit\LoginManagement\Helper;

class TimestampConv
{
    public static function readableTimestamp(float|int|string $timestamp): string
    {
        $readable = \DateTime::createFromFormat('U', $timestamp);
        $readable->setTimezone(new \DateTimeZone("Asia/Jakarta"));
        $readable->format('Y-m-d H:i:s');
        $readable = (array)$readable;
        return $readable['date'];
    }
}