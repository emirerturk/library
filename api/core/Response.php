<?php
    class Response {
        public static function send(int $code, array $headers, string $data) {
            foreach ($headers as $key => $value) {
                header("$key: $value");
            }
            http_response_code($code);
            echo $data;
            exit();
        }

        public static function json(int $statusCode, array $payload) {
            self::send($statusCode, ['Content-Type' => 'application/json'], json_encode($payload, JSON_UNESCAPED_UNICODE));
        }
    
        public static function success(array $data = [], string $message = 'İşlem başarılı', int $code = 200) {
            self::json($code, [
                'success' => true,
                'message' => $message,
                'data' => $data
            ]);
        }
    
        public static function error(string $message = 'İşlem başarısız', int $code = 400, array $data = []) {
            self::json($code, [
                'success' => false,
                'message' => $message,
                'data' => $data
            ]);
        }
    }
?>
