<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * RazorpayService — talks to Razorpay's REST API directly (no SDK needed).
 *
 * Flow:
 *   1. createOrder()  -> creates an order on Razorpay, returns order_id
 *   2. (frontend)     -> Razorpay Checkout popup collects payment
 *   3. verifySignature() -> confirms the payment is genuine before activating a plan
 *
 * Uses test keys from .env (RAZORPAY_KEY_ID / RAZORPAY_KEY_SECRET).
 */
class RazorpayService
{
    private function keyId(): ?string
    {
        return config('services.razorpay.key');
    }

    private function keySecret(): ?string
    {
        return config('services.razorpay.secret');
    }

    public function configured(): bool
    {
        return $this->keyId() && $this->keySecret();
    }

    /**
     * Create an order on Razorpay.
     * $amount is in RUPEES; Razorpay wants paise, so we multiply by 100.
     * Returns ['id' => 'order_xxx', ...] or null on failure.
     */
    public function createOrder(int $amountRupees, string $receipt): ?array
    {
        if (! $this->configured()) {
            return null;
        }

        $res = Http::withBasicAuth($this->keyId(), $this->keySecret())
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $amountRupees * 100, // paise
                'currency' => 'INR',
                'receipt' => $receipt,
                'payment_capture' => 1,
            ]);

        if (! $res->successful()) {
            Log::warning('Razorpay createOrder failed: '.$res->body());
            return null;
        }

        return $res->json();
    }

    /**
     * Verify the payment signature Razorpay returns after checkout.
     * Signature = HMAC_SHA256(order_id + "|" + payment_id, key_secret).
     */
    public function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $orderId.'|'.$paymentId, $this->keySecret());
        return hash_equals($expected, $signature);
    }
}
