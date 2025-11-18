<?php

namespace App\Services;

use App\Models\RefundRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RefundService
{
    /**
     * Ensure the transaction can be refunded or disputed.
     *
     * @throws RuntimeException
     */
    public static function assertRefundable(Transaction $transaction): void
    {
        $isWalletToWallet = $transaction->service_type === 'wallet_to_wallet';
        $isWalletToPerson = $transaction->service_type === 'transfer_via_agent';

        if (!$isWalletToWallet && !$isWalletToPerson) {
            throw new RuntimeException('Refunds are only available for wallet-to-wallet or wallet-to-person transfers.');
        }

        if ($isWalletToWallet && !in_array($transaction->status, ['completed', 'disputed'], true)) {
            throw new RuntimeException('Wallet-to-wallet refunds are only available for completed transfers.');
        }

        if ($isWalletToPerson && !in_array($transaction->status, ['in_progress', 'disputed'], true)) {
            throw new RuntimeException('Wallet-to-person refunds are only available for transfers that are in progress.');
        }
    }

    /**
     * Calculate normalized amounts used across refund flows.
     */
    public static function calculateAmounts(Transaction $transaction): array
    {
        $currency = $transaction->currency ?? 'USD';
        $amountUsd = $transaction->amount_usd ?? round(
            CurrencyService::convert($transaction->amount, 'USD', $currency),
            2
        );

        $feeUsd = $transaction->fee_amount_usd ?? 0.0;

        if (!$feeUsd && $transaction->fee_percent !== null) {
            $feeUsd = round($amountUsd * ((float) $transaction->fee_percent) / 100, 2);
        }

        $netUsd = max($amountUsd - $feeUsd, 0);
        $netCurrency = round(CurrencyService::convert($netUsd, $currency, 'USD'), 2);

        return [
            'amount_usd' => $amountUsd,
            'fee_usd' => $feeUsd,
            'net_usd' => $netUsd,
            'net_currency' => $netCurrency,
            'currency' => $currency,
        ];
    }

    /**
     * Approve a refund/dispute request and revert balances.
     *
     * @throws RuntimeException
     */
    public static function approve(RefundRequest $request, User $admin, ?string $note = null): void
    {
        DB::transaction(function () use ($request, $admin, $note) {
            $transaction = $request->transaction()->lockForUpdate()->firstOrFail();
            $transaction->loadMissing(['sender', 'receiver']);

            self::assertRefundable($transaction);

            if ($transaction->service_type === 'wallet_to_wallet') {
            $amounts = self::calculateAmounts($transaction);

            $sender = $transaction->sender;
            $receiver = $transaction->receiver;

            if (!$sender || !$receiver) {
                throw new RuntimeException('Transaction participants are missing.');
            }

            if ($receiver->balance < $amounts['net_usd']) {
                throw new RuntimeException('Receiver does not have enough balance to process the refund.');
            }

            $receiver->balance -= $amounts['net_usd'];
            $sender->balance += $amounts['net_usd'];

            $receiver->save();
            $sender->save();
            }

            $transaction->status = 'refunded';
            $transaction->save();

            $request->update([
                'status' => 'approved',
                'resolution_note' => $note,
                'resolved_by' => $admin->id,
                'resolved_at' => now(),
            ]);
        });
    }

    /**
     * Reject a refund/dispute request.
     */
    public static function reject(RefundRequest $request, User $admin, ?string $note = null): void
    {
        $request->update([
            'status' => 'rejected',
            'resolution_note' => $note,
            'resolved_by' => $admin->id,
            'resolved_at' => now(),
        ]);

        if ($request->transaction) {
            $request->transaction->update([
                'status' => $request->transaction->service_type === 'wallet_to_wallet'
                    ? 'completed'
                    : 'in_progress',
            ]);
        }
    }
}

