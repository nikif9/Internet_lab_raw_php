<?php
declare(strict_types=1);

namespace App\Router;

class Router {
    /**
     * @var array Список маршрутов
     */
    private array $routes = [];

    /**
     * Добавляет маршрут в маршрутизатор.
     *
     * @param string $method HTTP-метод (GET, POST и т. д.)
     * @param string $path Путь маршрута
     * @param callable $handler Функция-обработчик запроса
     *
     * @return void
     */
    public function add(string $method, string $path, callable $handler): void {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => $this->normalizePath($path),
            'handler' => $handler,
        ];
    }

    /**
     * Обрабатывает HTTP-запрос и вызывает соответствующий обработчик маршрута.
     *
     * @param string $method HTTP-метод запроса
     * @param string $uri Запрашиваемый URI
     *
     * @return void
     */
    public function dispatch(string $method, string $uri): void {
        $method = strtoupper($method);
        $path = $this->normalizePath($uri);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                // Преобразуем маршрут вида /users/{id} в регулярное выражение
                $pattern = preg_replace('/\{[a-zA-Z]+\}/', '([a-zA-Z0-9-_]+)', $route['path']);
                if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
                    array_shift($matches);
                    call_user_func_array($route['handler'], $matches);
                    return;
                }
            }
        }
        
        // Если маршрут не найден, возвращаем 404
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Эндпоинт не найден']);
    }

    /**
     * Нормализует путь, убирая лишние слэши.
     *
     * @param string $path Исходный путь
     *
     * @return string Нормализованный путь
     */
    private function normalizePath(string $path): string {
        return '/' . trim($path, '/');
    }
}
