<?php

use Slim\App;
use Tuupola\Middleware\JwtAuthentication;

return function (App $app) {
    // e.g: $app->add(new \Slim\Csrf\Guard);

    // Middleware to validate JWT tokens
    $app->add(new JwtAuthentication([
        "path" => "/api", /* or ["/api", "/admin"] */
        "secure" => false,
        "attribute" => "decoded_token_data",
        "secret" => "supersecretkeyyoushouldnotcommittogithub",
        "algorithm" => ["HS256"],
        "error" => function ($req, $res, $args) {
            $data["status"] = "error";
            $data["message"] = $args["message"];
            return $res
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    ]));
};
