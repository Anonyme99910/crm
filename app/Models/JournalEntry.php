<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'description',
        'type',
        'status',
        'reference_type',
        'reference_id',
        'total_debit',
        'total_credit',
        'fiscal_year',
        'fiscal_period',
        'created_by',
        'posted_by',
        'posted_at',
        'reversed_by',
        'reversed_at',
        'reversal_of',
        'notes',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'posted_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->entry_number = 'JE-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            $model->fiscal_year = date('Y');
            $model->fiscal_period = date('m');
        });
    }

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    public function post($userId)
    {
        if ($this->status !== 'draft') {
            return false;
        }

        foreach ($this->lines as $line) {
            $line->account->updateBalance($line->debit, $line->credit);
        }

        $this->status = 'posted';
        $this->posted_by = $userId;
        $this->posted_at = now();
        $this->save();

        return true;
    }

    public function isBalanced()
    {
        return $this->total_debit == $this->total_credit;
    }
}
