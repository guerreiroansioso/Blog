<?php

declare(strict_types=1);

namespace App\Site;

final class App {
    private ResponseHandler $responseHandler;
    private array $routeHandlers;

    public function __construct(
        private Repository $repository,
        private Parser $parser,
        private ViewRenderer $viewRenderer
    ) {
        $this->responseHandler = new ResponseHandler(
            $this->repository,
            $this->parser,
            $this->viewRenderer
        );

        $this->routeHandlers = [
            new HomeRoute(),
            new PageRoute(),
            new NotFoundRoute(),
        ];
    }

    public function handle(string $requestUri, string $slug = ''): void {
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '';

        foreach ($this->routeHandlers as $routeHandler) {
            if (!$routeHandler->supports($path)) { continue; }

            $routeHandler->handle($slug, $this->responseHandler);
            return;
        }
    }
}
