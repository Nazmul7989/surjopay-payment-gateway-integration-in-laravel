See Documentation here - https://shurjopay.com.bd/developers/intregration-howto


### Update .env file
``` 
SURJOPAY_MERCHANT_NAME=mechant name goes here
SURJOPAY_MERCHANT_PASSWORD=merchant password goes here
```

### Create a file in config/surjopay.php and update it by the following code
``` 
<?php

return [
    'merchant_name'     => env('SURJOPAY_MERCHANT_NAME'),
    'merchant_password' => env('SURJOPAY_MERCHANT_PASSWORD'),
];
```

### Update routes/web.php file

``` 
Route::get('/payment',[PaymentController::class,'payment'])->name('payment');
Route::get('/success}',[PaymentController::class,'success'])->name('success');
Route::get('/cancel',[PaymentController::class,'cancel'])->name('cancel');

```

### Create PaymentController.php file and update it by the following code

``` 
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function payment(Request $request)
    {
        try {
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
                $res = $this->createPayment($responseObject, $request);

                if (isset($res['checkout_url']) && $res['checkout_url'] != null) {
                    return redirect()->away($res['checkout_url']);
                    //For Inertia Js, Use this to avoid whole tab opening as modal
//                 return inertia()->location($res['checkout_url']);
                }else{
                    return redirect()->route('home')->with('error','Payment Generation Failed');
                }
            }else{
                return redirect()->route('home')->with('error','Token Generation Failed');
            }
        }catch (\Exception $exception){
            return $exception->getMessage();
        }

    }

    protected function createPayment($response, Request $request)
    {
        try {
            $url = 'https://sandbox.shurjopayment.com/api/secret-pay';
            //For Live Use 'https://engine.shurjopayment.com/api/secret-pay';

            $token      = $response['token'];
            $store_id   = $response['store_id'];
            $authorizationToken = "Bearer $token";
            $order_id = rand(000000000000,999999999999);

            session()->put('token', $token);

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
                "prefix": "sp",
                "token": "'.$token.'",
                "return_url": "'.route('success').'",
                "cancel_url": "'.route('cancel').'",
                "store_id": "'.$store_id.'",
                "amount": "100",
                "order_id": "'.$order_id.'",
                "currency": "BDT",
                "customer_name": "Nazmul",
                "customer_address": "Jhenaidah, Khulna, Bangladesh",
                "customer_phone": "01700000000",
                "customer_city": "Jhenaidah",
                "customer_post_code": "7200",
                "client_ip": "102.101.1.1",
                "discount_amount": "",
                "disc_percent": "",
                "customer_email": "test@gmail.com",
                "customer_state": "Bangladesh",
                "customer_postcode": "7200",
                "customer_country": "Bangladesh",
                "shipping_address": "Jhenaidah, Khulna, Bangladesh",
                "shipping_city": "Jhenaidah",
                "shipping_country": "Bangladesh",
                "received_person_name": "Nazmul",
                "shipping_phone_number": "01700000000",
                "value1": "test value1",
                "value2": "",
                "value3": "",
                "value4": "",
                "type": "json"
            }',
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: $authorizationToken",
                ),
            ));

            $res = curl_exec($curl);

            curl_close($curl);

            $resObject = json_decode($res, true);

            return $resObject;

        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public function success(Request $request)
    {
        try {
            if (isset($request['order_id']) && $request['order_id'] != null) {
                $url = 'https://www.sandbox.shurjopayment.com/api/verification';
                //For Live Use 'https://www.engine.shurjopayment.com/api/verification';
                $token = session()->get('token');
                $order_id      = $request['order_id'];
                $authorizationToken = "Bearer $token";


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
                        "order_id": "'.$order_id.'",
                        "type": "json"
                    }',
                    CURLOPT_HTTPHEADER => array(
                        "Content-Type: application/json",
                        "Authorization: $authorizationToken",
                    ),
                ));

                $res = curl_exec($curl);

                curl_close($curl);

                $resObject = json_decode($res, true);
                //Store payment transaction and order details
                session()->forget('token');

                return redirect()->route('home')->with('success','Order placed successfully');
            }
        }catch (\Exception $exception){
            return $exception->getMessage();
        }
    }

    public function cancel(Request $request)
    {
        return redirect()->route('home')->with('error','Order Cancelled!');
    }
}

```
