<?php
declare(strict_types=1);

namespace App\Helpers;

class Format
{
    public static function currency(float|int|string $amount, string $symbol = '$'): string
    {
        return $symbol . number_format((float)$amount, 2, '.', ',');
    }

    public static function currencyPrecise(float|int|string $amount, string $symbol = '$'): string
    {
        return $symbol . number_format((float)$amount, 4, '.', ',');
    }

    public static function date(?string $date, string $format = 'M d, Y H:i'): string
    {
        if (!$date) {
            return '-';
        }
        $timestamp = strtotime($date);
        return $timestamp ? date($format, $timestamp) : '-';
    }

    public static function badge(?string $status): string
    {
        return self::statusBadge($status ?? 'pending');
    }

    public static function statusBadge(string $status): string
    {
        $status = strtolower($status);
        $classes = match ($status) {
            'completed', 'active', 'approved', 'success' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
            'processing', 'inprogress', 'customer_reply' => 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
            'pending', 'open' => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
            'partial' => 'bg-purple-500/10 text-purple-400 border border-purple-500/20',
            'canceled', 'cancelled', 'failed', 'fail', 'rejected', 'suspended', 'closed' => 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
            default => 'bg-slate-700/50 text-slate-300 border border-slate-600/30',
        };

        return sprintf(
            '<span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold %s">%s</span>',
            $classes,
            htmlspecialchars(ucwords(str_replace('_', ' ', $status)), ENT_QUOTES, 'UTF-8')
        );
    }
}
