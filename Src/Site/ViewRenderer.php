<?php

declare(strict_types=1);

namespace App\Site;

final class ViewRenderer
{
    public function __construct(private string $viewsDirectory) {}

    public function render(string $templateName, array $data = []): string
    {
        $templatePath = rtrim($this->viewsDirectory, '/') .
            '/' . $templateName . '.php';

        if (!is_file($templatePath)) {
            throw new \RuntimeException(
                'Template não encontrado: ' . $templatePath
            );
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $templatePath;
        $content = ob_get_clean();

        return $content;
    }
}
