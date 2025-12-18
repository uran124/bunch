<?php

class DadataClient
{
    private string $apiKey;
    private string $secretKey;

    public function __construct(string $apiKey, string $secretKey)
    {
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
    }

    public function normalizeAddress(string $address): array
    {
        $cleanResponse = $this->request(
            'https://cleaner.dadata.ru/api/v1/clean/address',
            json_encode([$address], JSON_UNESCAPED_UNICODE)
        );

        if ($cleanResponse['success'] && is_array($cleanResponse['data'])) {
            return $cleanResponse;
        }

        $suggestionResponse = $this->request(
            'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address',
            json_encode(['query' => $address, 'count' => 1], JSON_UNESCAPED_UNICODE)
        );

        if (
            $suggestionResponse['success']
            && isset($suggestionResponse['data']['suggestions'][0]['data']['geo_lon'])
            && isset($suggestionResponse['data']['suggestions'][0]['data']['geo_lat'])
        ) {
            $row = $suggestionResponse['data']['suggestions'][0];
            $converted = [[
                'result' => $row['unrestricted_value'] ?? $row['value'] ?? $address,
                'geo_lon' => $row['data']['geo_lon'],
                'geo_lat' => $row['data']['geo_lat'],
                'qc_geo' => $row['data']['qc_geo'] ?? null,
            ]];

            return [
                'success' => true,
                'status' => $suggestionResponse['status'],
                'data' => $converted,
                'error' => null,
            ];
        }

        return [
            'success' => false,
            'status' => $suggestionResponse['status'] ?: $cleanResponse['status'],
            'data' => null,
            'error' => $suggestionResponse['error']
                ?: $cleanResponse['error']
                ?: 'Не удалось получить ответ от DaData',
        ];
    }

    private function request(string $url, string $payload): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Token ' . $this->apiKey,
            'X-Secret: ' . $this->secretKey,
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
                'error' => $curlError ?: 'Ошибка запроса к DaData',
            ];
        }

        $data = json_decode($response, true);

        return [
            'success' => $statusCode >= 200 && $statusCode < 300,
            'status' => $statusCode,
            'data' => $data,
            'error' => is_array($data)
                ? ($data['message'] ?? $data['detail'] ?? $data['reason'] ?? null)
                : (is_string($response) ? $response : null),
        ];
    }
}
