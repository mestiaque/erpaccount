<?php

namespace ME\Erpaccount\Support;

use ME\Erpaccount\Models\JournalMaster;

class VoucherNumberGenerator
{
    public static function next(string $prefix): string
    {
        $year = date('Y');
        $seriesPrefix = strtoupper($prefix) . '-' . $year . '-';

        $lastVoucher = JournalMaster::query()
            ->where('voucher_no', 'like', $seriesPrefix . '%')
            ->lockForUpdate()
            ->orderByDesc('voucher_no')
            ->value('voucher_no');

        $nextNumber = 1;

        if ($lastVoucher !== null) {
            $parts = explode('-', $lastVoucher);
            $numericPart = end($parts);
            $nextNumber = ((int) $numericPart) + 1;
        }

        do {
            $voucherNo = $seriesPrefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $exists = JournalMaster::query()->where('voucher_no', $voucherNo)->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $voucherNo;
    }
}
