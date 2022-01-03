<?php

namespace BadHabit\LoginManagement\Helper;

class ControllerMessage
{
    public static function message(string|bool $success, string|int $status, ?string $message, mixed $data=null): void
    {
        if ($data === null){
            $response = [
                'success' => $success,
                'status' => $status,
                "http_method" => $_SERVER['REQUEST_METHOD'],
                'message' => $message,
            ];
        } else {
            $response = [
                'success' => $success,
                'status' => $status,
                'message' => $message,
                "http_method" => $_SERVER['REQUEST_METHOD'],
                'data' => $data,
            ];
        }
        http_response_code($status);
        echo json_encode($response);
        exit();
    }
}