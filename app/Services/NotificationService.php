<?php

namespace App\Services;

use App\Models\RefundRequest;
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

        self::notifyAdmins(
            'Suspicious Transaction Pending',
            sprintf(
                'Transaction #%d (%s → %s) requires review.',
                $transaction->id,
                $transaction->sender?->name ?? 'Unknown',
                $transaction->receiver?->name ?? 'Unknown'
            ),
            $transaction
        );
    }

    public static function refundRequestSubmitted(Transaction $transaction, User $requester, string $type): void
    {
        $typeLabel = $type === 'dispute' ? 'dispute' : 'refund';
        $amount = self::formatAmount($transaction);

        self::sendUserNotification(
            $requester,
            'refund_request',
            ucfirst($typeLabel) . ' request submitted',
            "We received your {$typeLabel} request for {$amount}. Our team will review it shortly.",
            $transaction
        );

        self::notifyAdmins(
            'New ' . ucfirst($typeLabel) . ' request',
            sprintf(
                '%s submitted a %s request for transaction #%d.',
                $requester->name,
                $typeLabel,
                $transaction->id
            ),
            $transaction
        );
    }

    public static function refundRequestApproved(RefundRequest $refundRequest): void
    {
        $transaction = $refundRequest->transaction;
        $transaction?->loadMissing(['sender', 'receiver']);

        $currency = $refundRequest->currency ?? $transaction?->currency ?? 'USD';
        $amount = $refundRequest->requested_amount ?? $transaction?->amount ?? 0;
        $formatted = CurrencyService::format($amount, $currency);

        if ($transaction?->sender) {
            self::sendUserNotification(
                $transaction->sender,
                'refund_approved',
                'Refund approved',
                "Your {$refundRequest->type} request was approved. {$formatted} will be available in your wallet shortly.",
                $transaction
            );
        }

        if ($transaction?->receiver) {
            self::sendUserNotification(
                $transaction->receiver,
                'refund_approved',
                'Transaction refunded',
                "Transaction #{$transaction->id} was refunded. {$formatted} has been deducted from your wallet.",
                $transaction
            );
        }
    }

    public static function refundRequestRejected(RefundRequest $refundRequest): void
    {
        $transaction = $refundRequest->transaction;
        $transaction?->loadMissing(['sender', 'receiver']);

        if ($refundRequest->user) {
            $note = $refundRequest->resolution_note
                ? ' Reason: ' . $refundRequest->resolution_note
                : '';

            self::sendUserNotification(
                $refundRequest->user,
                'refund_rejected',
                ucfirst($refundRequest->type) . ' request rejected',
                "Your {$refundRequest->type} request for transaction #{$transaction?->id} was rejected." . $note,
                $transaction
            );
        }
    }

    protected static function formatAmount(Transaction $transaction): string
    {
        $currency = $transaction->currency ?? 'USD';
        return CurrencyService::format($transaction->amount, $currency);
    }

    public static function notifyAdmins(string $title, string $message, ?Transaction $transaction = null): void
    {
        $admins = User::role('Admin')->get();

        foreach ($admins as $admin) {
            self::sendUserNotification(
                $admin,
                'admin_alert',
                $title,
                $message,
                $transaction
            );
        }
    }
    
    public static function sendAgentNotification(User $agent, string $title, string $message, Transaction $transaction)
    {
        // Save notification inside DB
        UserNotification::create([
            'user_id'       => $agent->id,
            'title'         => $title,
            'message'       => $message,
            'transaction_id'=> $transaction->id,
            'is_read'       => false,
        ]);

        // OPTIONAL: Send email if agent has email
        // Mail::to($agent->email)->send(new AgentTransferNotification($transaction));
    }


    /**
     * Generic notification method (in case you need it)
     */
    public static function sendNotification(User $user, string $title, string $message)
    {
        UserNotification::create([
            'user_id' => $user->id,
            'title'   => $title,
            'message' => $message,
            'is_read' => false,
        ]);
    }
}

