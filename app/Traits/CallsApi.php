<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait CallsApi
{
    protected function api()
    {
        $url = config("app.api_url") . "/api";
        return Http::timeout(10)
            ->withToken(session("api_token"))
            ->baseUrl($url);
    }
}