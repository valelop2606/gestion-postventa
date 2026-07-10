<?php
function render_view(string $template, array $data = []): void {
    extract($data, EXTR_SKIP);

    $templatePath = dirname(__DIR__) . '/public/' . ltrim($template, '/');
    if (!file_exists($templatePath)) {
        http_response_code(404);
        echo 'Plantilla no encontrada.';
        return;
    }

    include $templatePath;
}
