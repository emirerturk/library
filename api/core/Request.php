<?php

class Request{
    private $method;
    private $uri;
    private $data;

    public function __construct(array $server) {
        $uri = parse_url($server['REQUEST_URI'], PHP_URL_PATH);
        $basePath = '/projects/library'; 
    
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
    
        $this->uri = $uri;
    }
    public function getMethod() {
        return $this->method;
    }

    public function getUri() {
        return $this->uri;
    }

    public function getData() {
        if ($this->data === null) {
            $input = file_get_contents('php://input');
            $this->data = json_decode($input);
        }
        return $this->data;
    }

    public function isAction(string $action) {
        $regex = '/' . str_replace('\:id', '\d+', preg_quote($action, '/')) . '$/i';

        return preg_match($regex, $this->uri) === 1;
    }
    
    public function isActionWithId(string $pattern): bool {
        $regex = '#^' . str_replace(':id', '(\d+)', $pattern) . '$#i';
        return preg_match($regex, $this->uri) === 1;
    }

    public function getIdFromUri(string $pattern): ?int {
        $regex = '#^' . str_replace(':id', '(\d+)', $pattern) . '$#i';
        if (preg_match($regex, $this->uri, $matches)) {
            return (int)$matches[1];
        }
        return null;
    }

    public function isMethod(string $method): bool {
        return strtolower($_SERVER['REQUEST_METHOD']) === strtolower($method);
    }

    public function getId() {
        preg_match('/\d+$/', $this->uri, $id);

        return $id[0];
    }
}
?>
