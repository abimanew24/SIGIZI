<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\JournalItem;
use Illuminate\Support\Facades\DB;

class JournalService
{
    /**
     * FUNGSI STATIC (REVISI)
     * Ditambahkan kata 'static' agar bisa dipanggil dengan ::
     */
    public static function createEntry($date, $description, $reference, array $items)
    {
        return DB::transaction(function () use ($date, $description, $reference, $items) {
            
            // 1. Buat Header Jurnal
            // Pastikan kolom 'date' sesuai dengan database (tadi kita fix jadi 'date')
            $entry = JournalEntry::create([
                'date'        => $date,
                'description' => $description,
                'reference'   => $reference,
            ]);

            // 2. Buat Detail Jurnal
            foreach ($items as $item) {
                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $item['account_id'],
                    'debit'            => $item['debit'] ?? 0,
                    'credit'           => $item['credit'] ?? 0,
                ]);
            }

            return $entry;
        });
    }
}