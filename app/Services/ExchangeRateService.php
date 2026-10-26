<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ExchangeRateService
{
    private bool $usedFallbackProvider = false;

    private ?string $primaryFailureMessage = null;

    private ?string $fallbackFailureMessage = null;

    /**
     * @param  array<int, string>  $currencyCodes
     * @return array<string, float>
     */
    public function fetchRatesToIdr(array $currencyCodes): array
    {
        $this->usedFallbackProvider = false;
        $this->primaryFailureMessage = null;
        $this->fallbackFailureMessage = null;

        if (count($currencyCodes) === 0) {
            return [];
        }

        $rates = $this->fetchRatesFromPrimaryProvider($currencyCodes);

        if (count($rates) > 0) {
            return $rates;
        }

        $rates = $this->fetchRatesFromFallbackProvider($currencyCodes);

        if (count($rates) > 0) {
            $this->usedFallbackProvider = true;

            return $rates;
        }

        throw new RuntimeException('Exchange rate provider and fallback provider returned no valid rates.');
    }

    public function usedFallbackProvider(): bool
    {
        return $this->usedFallbackProvider;
    }

    public function getPrimaryFailureMessage(): ?string
    {
        return $this->primaryFailureMessage;
    }

    public function getFallbackFailureMessage(): ?string
    {
        return $this->fallbackFailureMessage;
    }

    /**
     * @param  array<int, string>  $currencyCodes
     * @return array<string, float>
     */
    private function fetchRatesFromPrimaryProvider(array $currencyCodes): array
    {
        $baseUrl = (string) config('services.exchange_rates.base_url', 'https://open.er-api.com');

        try {
            $response = Http::baseUrl($baseUrl)
                ->acceptJson()
                ->timeout(15)
                ->retry(2, 500)
                ->get('/v6/latest/IDR');

            $response->throw();

            /** @var array<string, mixed> $payload */
            $payload = $response->json();
            $rates = data_get($payload, 'rates', []);

            if (! is_array($rates)) {
                return [];
            }

            return $this->mapForeignPerIdrToRateToIdr($currencyCodes, $rates);
        } catch (RequestException $exception) {
            $this->primaryFailureMessage = $exception->getMessage();

            Log::warning('Primary exchange-rate provider request failed.', [
                'provider' => 'open.er-api.com',
                'message' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @param  array<int, string>  $currencyCodes
     * @return array<string, float>
     */
    private function fetchRatesFromFallbackProvider(array $currencyCodes): array
    {
        $baseUrl = (string) config('services.exchange_rates.fallback_base_url', 'https://api.frankfurter.app');

        try {
            $symbols = implode(',', $currencyCodes);

            $response = Http::baseUrl($baseUrl)
                ->acceptJson()
                ->timeout(15)
                ->retry(1, 500)
                ->get('/latest', [
                    'from' => 'IDR',
                    'to' => $symbols,
                ]);

            $response->throw();

            /** @var array<string, mixed> $payload */
            $payload = $response->json();
            $rates = data_get($payload, 'rates', []);

            if (! is_array($rates)) {
                return [];
            }

            return $this->mapForeignPerIdrToRateToIdr($currencyCodes, $rates);
        } catch (RequestException $exception) {
            $this->fallbackFailureMessage = $exception->getMessage();

            Log::warning('Fallback exchange-rate provider request failed.', [
                'provider' => 'api.frankfurter.app',
                'message' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @param  array<int, string>  $currencyCodes
     * @param  array<string, mixed>  $foreignPerIdrRates
     * @return array<string, float>
     */
    private function mapForeignPerIdrToRateToIdr(array $currencyCodes, array $foreignPerIdrRates): array
    {
        $result = [];

        foreach ($currencyCodes as $currencyCode) {
            $foreignPerIdr = $foreignPerIdrRates[$currencyCode] ?? null;

            if (! is_numeric($foreignPerIdr) || (float) $foreignPerIdr <= 0) {
                continue;
            }

            // Providers return how much foreign currency equals 1 IDR, so invert to store 1 currency -> IDR.
            $result[$currencyCode] = round(1 / (float) $foreignPerIdr, 2);
        }

        return $result;
    }
}
