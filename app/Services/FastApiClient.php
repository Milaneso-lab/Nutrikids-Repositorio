<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FastApiClient
{
    public function get(string $path, ?string $token = null): array
    {
        return $this->request('get', $path, [], $token);
    }

    public function post(string $path, array $payload = [], ?string $token = null): array
    {
        return $this->request('post', $path, $payload, $token);
    }

    public function put(string $path, array $payload = [], ?string $token = null): array
    {
        return $this->request('put', $path, $payload, $token);
    }

    private function request(string $method, string $path, array $payload = [], ?string $token = null): array
    {
        $client = Http::baseUrl(config('services.nutrikids_api.base_url'))
            ->timeout((int) config('services.nutrikids_api.timeout'))
            ->acceptJson();

        if ($token) {
            $client = $client->withToken($token);
        }

        $response = $client->{$method}($path, $payload);

        if ($response->failed()) {
            return [
                'success' => false,
                'status' => $response->status(),
                'body' => $response->json(),
            ];
        }

        return [
            'success' => true,
            'status' => $response->status(),
            'body' => $response->json(),
        ];
    }
}
