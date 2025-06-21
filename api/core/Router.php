<?php
class Router {
    private array $routes = [];

    public function add(string $method, string $pattern, callable $callback): void {
        $method = strtoupper($method);
        $pattern = preg_replace('#:([\w]+)#', '(?P<\1>[^/]+)', $pattern);
        $pattern = "#^" . rtrim($pattern, '/') . "$#";
        $this->routes[$method][] = ['pattern' => $pattern, 'callback' => $callback];
    }

    public function dispatch(string $method, string $uri): void {
        $method = strtoupper($method);
        $uri = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                call_user_func_array($route['callback'], $params);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Böyle bir endpoint bulunamadı.']);
    }
}
