<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use Stripe\Error\InvalidRequest;
use Stripe\Stripe as StripeClient;
use Stripe\Charge as StripeCharge;
use Stripe\Error\Card as CardException;
use Stripe\Error\Authentication as AuthException;
use Stripe\Error\ApiConnection as ConnectionException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Vinkla\Hashids\Facades\Hashids;

class PaymentControlller extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function pay(Request $request)
    {

        // curl 'https://api.stripe.com/v1/tokens' -H 'Content-Type: application/x-www-form-urlencoded' --data 'key=pk_test_gGD5angznT3xQIkvIGYHMprN&payment_user_agent=stripe.js%2F15cb6da&card[name]=Martins+Paymill+Test&card[number]=4111111111111111&card[exp_month]=11&card[exp_year]=2019&card[cvc]=123'

        $this->validate($request, [
            'token'    => 'required',
            'offer_id' => 'required',
        ]);

        $bid_id = $request->input('offer_id');

        $bid = Bid::find(Hashids::decode($bid_id))->first();

        $amount        = $bid->offer_price;
        $transction_id = $bid->transaction->id_hash;

        StripeClient::setApiKey(env('STRIPE_SECRET_KEY'));

        try {
            $charge = StripeCharge::create([
                'amount'   => $amount,
                'currency' => 'eur',
                'source'   => $request->input('token'),
                'metadata' => [
                    'transaction_id' => $transction_id,
                    'bid_id'         => $bid_id,
                ],
            ]);
        } catch (InvalidRequest $e) {
            \Log::warning($e);
            throw new ServiceUnavailableHttpException(null, $e);
        } catch (ConnectionException $e) {
            \Log::warning($e);
            throw new ServiceUnavailableHttpException(null, $e);
        } catch (AuthException $e) {
            \Log::warning($e);
            throw new ServiceUnavailableHttpException(null, $e);
        } catch (CardException $e) {
            \Log::warning('Card was declined');
            throw new BadRequestHttpException('Card declined');
        }

        // @todo: dispatch payment success here

        return [
            'status' => 'ok',
        ];
    }
}
