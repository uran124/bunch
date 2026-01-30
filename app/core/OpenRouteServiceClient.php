<?php

class OpenRouteServiceClient
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getDistanceKm(array $startCoords, array $endCoords): ?float
    {
        $payload = json_encode([
            'coordinates' => [
                [$startCoords[0], $startCoords[1]],
                [$endCoords[0], $endCoords[1]],
            ],
        ], JSON_UNESCAPED_UNICODE);

        $response = $this->request('https://api.openrouteservice.org/v2/directions/driving-car', $payload);
        if (!$response['success'] || !is_array($response['data'])) {
            return null;
        }

        $meters = $response['data']['routes'][0]['summary']['distance'] ?? null;
        if (!is_numeric($meters)) {
            return null;
        }

        return round(((float) $meters) / 1000, 2);
    }

    private function request(string $url, string $payload): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: ' . $this->apiKey,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE) ?: 0;
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'success' => false,
                'status' => $statusCode,
                'data' => null,
                'error' => $curlError ?: 'Ошибка запроса к OpenRouteService',
            ];
        }

        $data = json_decode($response, true);

        return [
            'success' => $statusCode >= 200 && $statusCode < 300,
            'status' => $statusCode,
            'data' => $data,
            'error' => is_array($data)
                ? ($data['message'] ?? $data['error'] ?? null)
                : (is_string($response) ? $response : null),
        ];
    }
}
