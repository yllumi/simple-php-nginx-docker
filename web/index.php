<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello World - PHP + Nginx</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        .card {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 2.5rem 3.5rem;
            text-align: center;
            backdrop-filter: blur(6px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
        }
        h1 { margin: 0 0 0.75rem; font-size: 2rem; }
        p { margin: 0.35rem 0; opacity: 0.92; }
        code {
            background: rgba(0, 0, 0, 0.25);
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Hello, World! 👋</h1>
        <p>Halaman ini disajikan oleh <strong>Nginx</strong> dan diproses oleh <strong>PHP-FPM</strong>.</p>
        <p>Versi PHP: <code><?= PHP_VERSION ?></code></p>
        <p>Server: <code><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Nginx' ?></code></p>
        <p style="margin-top: 1rem;">
            <a href="redis_crud.php"
               style="color: #fff; background: rgba(0,0,0,0.25); padding: 0.5rem 1.2rem; border-radius: 8px; text-decoration: none;">
                🚀 Coba CRUD Redis →
            </a>
        </p>
    </div>
</body>
</html>
