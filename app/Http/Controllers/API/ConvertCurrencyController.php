<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ConvertCurrency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ConvertCurrencyController extends Controller
{
    public function convert(Request $request)
    {

        $validatedData = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'to' => 'required|string|size:3',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validatedData->errors(),
            ], 400);
        }

        try {
            ConvertCurrencyController::updateHistorical();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to convert currency.',
            ], 500);
        }


        $to = $request->to;
        $amount = $request->amount;

        $rate = ConvertCurrency::where('from_currency', strtoupper($to))->first();
        if (!$rate) {
            return response()->json([
                'message' => 'Currency not found.',
            ], 404);
        }

        $result = $amount * $rate->rate;

        return response()->json([
            'amount' => $amount,
            'from' => 'EUR',
            'to' => $to,
            'rate' => $rate->rate,
            'last_updated' => $rate->last_updated,
            'result' => $result,
        ]);
    }



    static public function updateHistorical()
    {

        //verificar si el dia de hoy se ha actualizado
        $lastUpdated = ConvertCurrency::orderBy('last_updated', 'desc')->first();
        if ($lastUpdated && $lastUpdated->last_updated == date('Y-m-d')) {
            return response()->json([
                'message' => 'Rates already updated today.'
            ]);
        }

        //get url from env
        $url = "http://data.fixer.io/api/latest?access_key=b12e1c2bad853b2c5517cf0b20649008";

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        DB::beginTransaction();
        try {
            $data = json_decode($response, true);
            $date = $data['date'];
            $rates = $data['rates'];

            foreach ($rates as $currency => $rate) {
                ConvertCurrency::updateOrCreate(
                    ['from_currency' => $currency],
                    [
                        'rate' => number_format((float) $rate, 2, '.', ''),
                        'last_updated' => date('Y-m-d', strtotime($date))
                    ]
                );
            }

            DB::commit();
            return response()->json([
                'message' => 'Rates updated successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update rates.'
            ], 500);
        }
    }
}
