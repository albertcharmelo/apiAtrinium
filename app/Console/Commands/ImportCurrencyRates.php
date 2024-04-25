<?php

namespace App\Console\Commands;

use App\Models\ConvertCurrency;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class ImportCurrencyRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-currency-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa los tipos de cambio históricos de la moneda ofrecidos por el Banco Central Europeo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = new Client();
        $response = $client->get('https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist.xml');

        if ($response->getStatusCode() == 200) {
            try {
                DB::beginTransaction();
                $xml = $response->getBody()->getContents();
                $simpleXml = new SimpleXMLElement($xml);
                $cube = $simpleXml->Cube->Cube[0];
                $date = new Carbon((string) $cube['time']);

                foreach ($cube->Cube as $rate) {
                    $currency = (string) $rate['currency'];
                    $rateValue = (float) $rate['rate'];
                    ConvertCurrency::updateOrCreate(
                        ['from_currency' => $currency, 'last_updated' => $date->format('Y-m-d')],
                        ['rate' => $rateValue, 'last_updated' => $date->format('Y-m-d')]
                    );
                }
                DB::commit();

                $this->info('Rates updated successfully.');
            } catch (Exception $e) {
                DB::rollBack();

                $this->error('Failed to update rates to database.');
            }
        } else {

            $this->error('Failed to fetch rates from ECB.');
        }
    }
}
