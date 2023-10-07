<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function payment(Request $request)
    {
        $merchant_name = config('surjopay.merchant_name');
        $merchant_password = config('surjopay.merchant_password');
        $url = 'https://sandbox.shurjopayment.com/api/get_token';
        //For Live Use 'https://engine.shurjopayment.com/api/get_token';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "username": "'.$merchant_name.'",
                "password": "'.$merchant_password.'",
                "type": "json"
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $responseObject = json_decode($response, true);

        if (isset($responseObject['token']) && $responseObject['token'] != null) {
            $res = $this->createPayment($responseObject, $responseObject);
        }else{
            return redirect()->route('home')->with('error','Token Generation Failed');
        }

        return $response;

    }

    protected function createPayment($response, Request $request)
    {
        $merchant_name = config('surjopay.merchant_name');
        $merchant_password = config('surjopay.merchant_password');
        $url = 'https://sandbox.shurjopayment.com/api/get_token';
        //For Live Use 'https://engine.shurjopayment.com/api/get_token';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "username": "'.$merchant_name.'",
                "password": "'.$merchant_password.'",
                "type": "json"
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }

    public function success(Request $request)
    {
        dd('success');
    }

    public function cancel()
    {
        dd('cancel');
    }
}
