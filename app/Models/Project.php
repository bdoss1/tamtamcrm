<?php

namespace App\Models;

use App\Traits\Archiveable;
use App\Traits\SearchableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Project extends Model
{

    use SearchableTrait;
    use SoftDeletes;
    use HasFactory;
    use Archiveable;
    use QueryCacheable;

    protected static $flushCacheOnUpdate = true;
    protected $fillable = [
        'name',
        'description',
        'customer_id',
        'number',
        'account_id',
        'assigned_to',
        'user_id',
        'account_id',
        'private_notes',
        'public_notes',
        'due_date',
        'start_date',
        'budgeted_hours',
        'task_rate',
        'column_color'
    ];
    protected $casts = [
        'updated_at' => 'timestamp',
    ];
    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchable = [
        /**
         * Columns and their priority in search results.
         * Columns with higher values are more important.
         * Columns with equal values have equal importance.
         *
         * @var array
         */
        'columns' => [
            'projects.title' => 10,
        ]
    ];

    /**
     * When invalidating automatically on update, you can specify
     * which tags to invalidate.
     *
     * @return array
     */
    public function getCacheTagsToInvalidateOnUpdate(): array
    {
        return [
            'projects',
        ];
    }

    /**
     * @return HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * @param $term
     *
     * @return mixed
     */
    public function searchProject($term)
    {
        return self::search($term);
    }

    /**
     * @return BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return mixed
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function setNumber()
    {
        if (empty($this->number)) {
            $this->number = (new NumberGenerator)->getNextNumberForEntity($this);
            return true;
        }

        return true;
    }
}
