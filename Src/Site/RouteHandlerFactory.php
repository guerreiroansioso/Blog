<?php

declare(strict_types=1);

namespace App\Site;

final class RouteHandlerFactory {
    /**
     * @return list<RouteHandler>
     */
    public function createAll(): array {
        return [
            new HomeRoute(),
            new PageRoute(),
            new NotFoundRoute(),
        ];
    }
}
