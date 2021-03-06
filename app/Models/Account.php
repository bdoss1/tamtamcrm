<?php
/**
 * Created by PhpStorm.
 * User: michael.hampton
 * Date: 08/12/2019
 * Time: 17:10
 */

namespace App\Models;


use App\Events\Account\AccountWasDeleted;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracasts\Presenter\PresentableTrait;

class Account extends Model
{
    use PresentableTrait, SoftDeletes, HasFactory;

    protected $presenter = 'App\Presenters\AccountPresenter';

    protected $dispatchesEvents = [
        'deleted' => AccountWasDeleted::class,
    ];

    protected $fillable = [
        'subdomain',
        'custom_fields',
        'portal_domain',
        'support_email',
        'settings'
    ];
    protected $casts = [
        'country_id'    => 'string',
        'custom_fields' => 'object',
        'settings'      => 'object',
        'updated_at'    => 'timestamp',
        'created_at'    => 'timestamp',
        'deleted_at'    => 'timestamp',
    ];

    public function locale()
    {
        return $this->getLocale();
    }

    public function getLocale()
    {
        return isset($this->settings->language_id) && $this->language()
            ? $this->language()->locale
            : config(
                'taskmanager.locale'
            );
    }

    /**
     * @return BelongsTo
     */
    public function language()
    {
        return Language::find($this->settings->language_id);
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function getSetting($setting)
    {
        if (property_exists($this->settings, $setting) != false) {
            return $this->settings->{$setting};
        }

        return null;
    }

    public function comments()
    {
        return $this->morphMany('App\Models\Comment', 'commentable');
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, AccountUser::class, 'account_id', 'id', 'id', 'user_id');
    }

    public function designs()
    {
        return $this->hasMany(Design::class)->whereAccountId($this->id)->orWhere('account_id', null);
    }

    /**
     * @return HasMany
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class)->withTrashed();
    }

    /**
     * @return HasMany
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class)->withTrashed();
    }


    /**
     * @return HasMany
     */
    public function credits()
    {
        return $this->hasMany(Credit::class)->withTrashed();
    }

    /**
     * @return HasMany
     */
    public function customers()
    {
        return $this->hasMany(Customer::class)->withTrashed();
    }

    public function domains()
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }


    /**
     * @return mixed
     */
    public function payments()
    {
        return $this->hasMany(Payment::class)->withTrashed();
    }


    /**
     * @return HasMany
     */
    public function tax_rates()
    {
        return $this->hasMany(TaxRate::class);
    }

    /**
     * @return HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * @return BelongsTo
     */
    public function country()
    {
        //return $this->belongsTo(Country::class);
        return Country::find($this->settings->country_id);
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * @return BelongsTo
     */
    public function getCurrency()
    {
        if (!empty($this->settings->currency_id)) {
            return Currency::whereId($this->settings->currency_id)->first();
        }

        return false;
    }

    public function getLogo()
    {
        return $this->settings->company_logo ?: null;
    }

    public function domain()
    {
        return 'https://' . $this->subdomain . config('taskmanager.app_domain');
    }

    /**
     * @return HasMany
     */
    public function companies()
    {
        return $this->hasMany(Company::class)->withTrashed();
    }

    public function routeNotificationForSlack($notification)
    {
        return $this->slack_webhook_url;
    }

    public function account_users()
    {
        return $this->hasMany(AccountUser::class);
    }

    public function owner()
    {
        $c = $this->account_users->where('is_owner', true)->first();

        return User::find($c->user_id);
    }

    public function getNumberOfAllowedUsers()
    {
        $plan = $this->getActiveSubscription();

        if (empty($plan)) {
            return 99999;
        }

        return $plan->number_of_licences;
    }

    public function getActiveSubscription()
    {
        return $this->plans()->where('ends_at', '>', now())->where('plan_id', $this->domains->plan_id)->first();
    }

    public function plans()
    {
        return $this->hasMany(PlanSubscription::class);
    }

    public function getNumberOfAllowedDocuments()
    {
        if (empty($this->domains->plan)) {
            return 99999;
        }

        $plan_feature = $this->domains->plan->features->where('slug', '=', 'DOCUMENT')->first();

        return $plan_feature->value;
    }

    public function getNumberOfAllowedCustomers()
    {
        if (empty($this->domains->plan)) {
            return 99999;
        }

        $plan_feature = $this->domains->plan->features->where('slug', '=', 'CUSTOMER')->first();

        return $plan_feature->value;
    }

    public function getNumberOfAllowedEmails()
    {
        if (empty($this->domains->plan)) {
            return 99999;
        }

        $plan_feature = $this->domains->plan->features->where('slug', '=', 'EMAIL')->first();

        return $plan_feature->value;
    }
}
