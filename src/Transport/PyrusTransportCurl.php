<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Transport;

/**
 * Facade for HTTP client. Converts data and throws errors.
 */
final class PyrusTransportCurl implements PyrusTransport
{
    /**
     * {@inheritdoc}
     */
    public function request(PyrusRequest $request): PyrusResponse
    {
        $options = [
            \CURLOPT_FOLLOWLOCATION => true,
            \CURLOPT_FRESH_CONNECT => true,
            \CURLOPT_CONNECTTIMEOUT => 5,
            \CURLOPT_TIMEOUT => 60 * 25,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_URL => $request->url,
            \CURLOPT_CUSTOMREQUEST => $request->method->value,
            \CURLOPT_HTTPHEADER => [
                'Content-type: application/json',
            ],
            \CURLOPT_POSTFIELDS => json_encode($request->payload),
        ];

        $ch = curl_init();
        if (false === $ch) {
            throw new \RuntimeException("Can't init curl resource");
        }

        curl_setopt_array($ch, $options);
        $payload = (string) curl_exec($ch);
        $statusCode = PyrusResponseStatus::from((int) curl_getinfo($ch, \CURLINFO_HTTP_CODE));
        curl_close($ch);

        return new PyrusResponse($statusCode, $payload);
    }
}
