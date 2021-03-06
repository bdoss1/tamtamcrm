<?php
/**
 * Created by PhpStorm.
 * User: michael.hampton
 * Date: 27/02/2020
 * Time: 19:50
 */

namespace App\Models;

use App\Traits\Archiveable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laracasts\Presenter\PresentableTrait;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Lead extends Model
{
    use SoftDeletes;
    use PresentableTrait;
    use Notifiable;
    use HasFactory;
    use Archiveable;
    use QueryCacheable;

    const NEW_LEAD = 98;
    const IN_PROGRESS = 99;
    const STATUS_COMPLETED = 100;
    const UNQUALIFIED = 100;
    protected static $flushCacheOnUpdate = true;
    protected $presenter = 'App\Presenters\LeadPresenter';
    protected $fillable = [
        'task_sort_order',
        'design_id',
        'number',
        'account_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address_1',
        'address_2',
        'zip',
        'city',
        'job_title',
        'company_name',
        'description',
        'name',
        'valued_at',
        'source_type',
        'assigned_to',
        'project_id',
        'website',
        'industry_id',
        'private_notes',
        'public_notes',
        'task_status_id',
        'column_color'
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
            'leads',
            'dashboard_leads'
        ];
    }

    public function setNumber()
    {
        if (empty($this->number)) {
            $this->number = (new NumberGenerator)->getNextNumberForEntity($this);
            return true;
        }

        return true;
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function emails()
    {
        return Email::whereEntity(get_class($this))->whereEntityId($this->id)->get();
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function taskStatus()
    {
        return $this->belongsTo(TaskStatus::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function preferredLocale()
    {
        return 'en';
    }

    public function getDesignId()
    {
        return !empty($this->design_id) ? $this->design_id : $this->account->settings->lead_design_id;
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getPdfFilename()
    {
        return 'storage/' . $this->account->id . '/' . $this->id . '/leads/' . $this->number . '.pdf';
    }

    public function getTaskRate()
    {
        if (!empty($this->task_rate)) {
            return (float)$this->task_rate;
        }

        if (!empty($this->project) && !empty($this->project->task_rate)) {
            return (float)$this->project->task_rate;
        }

        return 0;
    }

    public function scopePermissions($query, User $user)
    {
        if ($user->isAdmin() || $user->isOwner() || $user->hasPermissionTo('leadcontroller.index')) {
            return $query;
        }

        $query->where(
            function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('assigned_to', auth()->user($user)->id);
            }
        );
    }
}
