<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\ExchangeRate;
use App\Services\ExchangeRateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncExchangeRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange-rates:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync exchange rates from provider and update local table';

    /**
     * Execute the console command.
     */
    public function handle(ExchangeRateService $exchangeRateService): int
    {
        $currencyCodes = ExchangeRate::query()
            ->orderBy('currency_code')
            ->pluck('currency_code')
            ->all();

        if (count($currencyCodes) === 0) {
            $this->warn('No exchange rates found in database. Seed initial currencies first.');

            return self::INVALID;
        }

        try {
            $ratesToIdr = $exchangeRateService->fetchRatesToIdr($currencyCodes);
        } catch (Throwable $throwable) {
            ActivityLog::log(
                'exchange_rate_sync_failed',
                'Exchange rate sync failed: primary and fallback providers are unavailable.',
                null,
                [
                    'error' => $throwable->getMessage(),
                    'primary_failure' => $exchangeRateService->getPrimaryFailureMessage(),
                    'fallback_failure' => $exchangeRateService->getFallbackFailureMessage(),
                ],
            );

            Log::critical('Exchange-rate sync failed for all providers.', [
                'error' => $throwable->getMessage(),
                'primary_failure' => $exchangeRateService->getPrimaryFailureMessage(),
                'fallback_failure' => $exchangeRateService->getFallbackFailureMessage(),
            ]);

            $this->error('Failed to fetch exchange rates: '.$throwable->getMessage());

            return self::FAILURE;
        }

        if ($exchangeRateService->usedFallbackProvider()) {
            ActivityLog::log(
                'exchange_rate_sync_fallback_used',
                'Primary exchange-rate provider failed. Fallback provider was used successfully.',
                null,
                [
                    'primary_failure' => $exchangeRateService->getPrimaryFailureMessage(),
                ],
            );

            Log::warning('Exchange-rate sync completed via fallback provider.', [
                'primary_failure' => $exchangeRateService->getPrimaryFailureMessage(),
            ]);
        }

        if (count($ratesToIdr) === 0) {
            $this->warn('No exchange rates returned from provider.');

            return self::INVALID;
        }

        $updated = 0;

        foreach ($ratesToIdr as $currencyCode => $rateToIdr) {
            ExchangeRate::query()
                ->where('currency_code', $currencyCode)
                ->update(['rate_to_idr' => $rateToIdr]);

            $updated++;
        }

        $this->info("Exchange rates synced successfully. Updated {$updated} currencies.");

        return self::SUCCESS;
    }
}
