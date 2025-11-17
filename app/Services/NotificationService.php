<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\CurrencyService;

class NotificationService
{
    public static function sendUserNotification(
        ?User $user,
        string $type,
        string $title,
        string $message,
        ?Transaction $transaction = null
    ): void {
        if (!$user) {
            return;
        }

        UserNotification::create([
            'user_id' => $user->id,
            'transaction_id' => $transaction?->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ]);
    }

    public static function transferInitiated(Transaction $transaction): void
    {
        $transaction->loadMissing(['sender', 'receiver']);

        $sender = $transaction->sender;
        $receiver = $transaction->receiver;

        if ($sender) {
            $amount = self::formatAmount($transaction);
            $recipientName = $receiver?->name ?? 'recipient';
            self::sendUserNotification(
                $sender,
                'transfer_initiated',
                'Transfer Initiated',
                "Your transfer of {$amount} to {$recipientName} has been initiated.",
                $transaction
            );
        }
    }

    public static function transferCompleted(Transaction $transaction): void
    {
        $transaction->loadMissing(['sender', 'receiver']);

        $amount = self::formatAmount($transaction);

        if ($transaction->sender) {
            $recipientName = $transaction->receiver?->name ?? 'recipient';
            self::sendUserNotification(
                $transaction->sender,
                'transfer_completed',
                'Transfer Completed',
                "Your transfer of {$amount} to {$recipientName} has been completed.",
                $transaction
            );
        }

        if ($transaction->receiver) {
            $senderName = $transaction->sender?->name ?? 'sender';
            self::sendUserNotification(
                $transaction->receiver,
                'transfer_completed',
                'Transfer Received',
                "You received {$amount} from {$senderName}.",
                $transaction
            );
        }
    }

    public static function transferFailed(Transaction $transaction, ?string $reason = null): void
    {
        $transaction->loadMissing(['sender', 'receiver']);

        $amount = self::formatAmount($transaction);
        $recipientName = $transaction->receiver?->name ?? 'recipient';
        $message = "Your transfer of {$amount} to {$recipientName} failed.";
        if ($reason) {
            $message .= " {$reason}";
        }

        if ($transaction->sender) {
            self::sendUserNotification(
                $transaction->sender,
                'transfer_failed',
                'Transfer Failed',
                $message,
                $transaction
            );
        }
    }

    public static function transferPendingReview(Transaction $transaction): void
    {
        $transaction->loadMissing(['sender', 'receiver']);

        if ($transaction->sender) {
            $amount = self::formatAmount($transaction);
            $recipientName = $transaction->receiver?->name ?? 'recipient';
            self::sendUserNotification(
                $transaction->sender,
                'transfer_pending_review',
                'Transfer Pending Review',
                "Your transfer of {$amount} to {$recipientName} was flagged for additional review. We will notify you once it is approved.",
                $transaction
            );
        }
    }

    protected static function formatAmount(Transaction $transaction): string
    {
        $currency = $transaction->currency ?? 'USD';
        return CurrencyService::format($transaction->amount, $currency);
    }
}

