<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Bekambeyene\Telebirr\Facades\Telebirr;
use Bekambeyene\Telebirr\Exceptions\TelebirrException;
use Bekambeyene\Telebirr\Exceptions\TelebirrServerException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function pay($id)
    {
        $order = Order::findOrFail($id);

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Order already paid'
            ], 422);
        }

        try {
            $telebirrRef = str_replace('-', '', $order->order_number); // ORD-GED3QWLG6A → ORDGED3QWLG6A
            $title       = 'FoodOrder';                                 // plain alphanumeric only

            $paymentUrl = Telebirr::createOrder(
                $title,
                (float) $order->total,
                $telebirrRef
            );

            return response()->json([
                'success'      => true,
                'order_number' => $order->order_number,
                'amount'       => $order->total,
                'payment_url'  => $paymentUrl,
            ]);

        } catch (TelebirrServerException $e) {
            Log::warning('Telebirr server busy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment service is busy. Please try again.'
            ], 503);

        } catch (TelebirrException $e) {
            Log::error('Telebirr error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Could not initiate payment: ' . $e->getMessage()
            ], 400);
        }
    }

    public function webhook(Request $request)
    {
        try {
            $payload = Telebirr::handleWebhook($request);

            // Convert ORDGED3QWLG6A back to ORD-GED3QWLG6A
            $orderNumber = preg_replace('/^(ORD)/', '$1-', $payload->outTradeNo);
            $order = Order::where('order_number', $orderNumber)->firstOrFail();

            $order->update([
                'payment_status' => 'paid',
                'status'         => 'confirmed',
                'payment_ref'    => $payload->tradeNo ?? null,
            ]);

            Log::info('Payment confirmed for order: ' . $order->order_number);

            return response()->json(['code' => '0', 'msg' => 'success']);

        } catch (\Exception $e) {
            Log::error('Webhook failed: ' . $e->getMessage());
            return response()->json(['code' => '1', 'msg' => 'failed'], 400);
        }
    }
}