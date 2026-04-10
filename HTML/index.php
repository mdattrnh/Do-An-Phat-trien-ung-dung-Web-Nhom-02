<?php
/**
 * Front Controller - Entry Point
 */

define('BASE_PATH', __DIR__);

// --- DYNAMIC URL DETECTION (Fix localhost issue) ---
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME']; // e.g. /HTML/index.php
$base_dir = str_replace('\\', '/', dirname($script_name));
if ($base_dir === '/') $base_dir = '';

define('BASE_URL', $protocol . "://" . $host . $base_dir);
define('ASSET_URL', BASE_URL);
// ----------------------------------------------------

// Autoloader
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/app/models/user/' . $class . '.php',
        BASE_PATH . '/app/models/product/' . $class . '.php',
        BASE_PATH . '/app/models/cart/' . $class . '.php',
        BASE_PATH . '/app/models/order/' . $class . '.php',
        BASE_PATH . '/app/controllers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Load config & routes
require_once BASE_PATH . '/config/database.php';
$routes = require_once BASE_PATH . '/routes.php';

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// --- DYNAMIC ROUTING (Strip Subdirectory - Case Insensitive Fix) ---
if ($base_dir !== '') {
    // find the base_dir in the uri (case-insensitive)
    $pos = stripos($uri, $base_dir);
    if ($pos === 0) {
        $uri = substr($uri, strlen($base_dir));
    }
}
$uri = '/' . trim($uri, '/');

// Route Dispatcher
$matched = false;
foreach ($routes as $route => $handler) {
    [$routeMethod, $routePath] = explode(' ', $route, 2);

    // Convert {param} to regex
    $pattern = preg_replace('/\{[a-z_]+\}/', '([^/]+)', $routePath);
    $pattern = '#^' . $pattern . '$#';

    if ($routeMethod === $method && preg_match($pattern, $uri, $matches)) {
        array_shift($matches);
        [$controllerName, $action] = explode('@', $handler);
        $controller = new $controllerName();
        call_user_func_array([$controller, $action], $matches);
        $matched = true;
        break;
    }
}

if (!$matched) {
    http_response_code(404);
    $base = BASE_URL;
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Page Not Found | SOFT EDGE</title>
        <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;700&family=Inter:wght@400&display=swap" rel="stylesheet">
        <style>
            :root {
                --cream: #f5f0e8;
                --charcoal: #1a1a1a;
                --blush: #e8d5c4;
            }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                background: var(--cream);
                color: var(--charcoal);
                font-family: 'Inter', sans-serif;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                padding: 20px;
            }
            .error-card {
                background: rgba(255, 255, 255, 0.4);
                backdrop-filter: blur(15px);
                border: 1px solid rgba(255, 255, 255, 0.5);
                padding: 4rem 2rem;
                border-radius: 40px;
                max-width: 500px;
                width: 100%;
                box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            }
            h1 {
                font-family: 'Urbanist', sans-serif;
                font-size: 8rem;
                font-weight: 700;
                line-height: 1;
                margin-bottom: 0.5rem;
                opacity: 0.1;
            }
            h2 {
                font-family: 'Urbanist', sans-serif;
                font-size: 2rem;
                margin-bottom: 1rem;
            }
            p {
                margin-bottom: 2.5rem;
                color: #666;
                line-height: 1.6;
            }
            .btn {
                display: inline-block;
                padding: 1rem 2.5rem;
                background: var(--charcoal);
                color: white;
                text-decoration: none;
                border-radius: 100px;
                font-family: 'Urbanist', sans-serif;
                font-weight: 600;
                letter-spacing: 0.05em;
                transition: transform 0.3s ease;
            }
            .btn:hover {
                transform: translateY(-3px);
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <h1>404</h1>
            <h2>KHÔNG TÌM THẤY TRANG</h2>
            <p>Có vẻ như đường dẫn này không tồn tại hoặc đã bị di chuyển. Hãy quay lại cửa hàng nhé!</p>
            <a href="{$base}" class="btn">VỀ TRANG CHỦ</a>
        </div>
    </body>
    </html>
HTML;
}
