<?php

namespace App\Repositories;

use App\Events\Customer\CustomerWasCreated;
use App\Events\Customer\CustomerWasUpdated;
use App\Models\Account;
use App\Models\Customer;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Requests\SearchRequest;
use App\Search\CustomerSearch;
use Carbon\Carbon;
use Illuminate\Support\Collection as Support;
use Illuminate\Support\Facades\DB;

/**
 * Description of CustomerRepository
 *
 * @author michael.hampton
 */
class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{

    /**
     * CustomerRepository constructor.
     * @param Customer $customer
     */
    public function __construct(Customer $customer)
    {
        parent::__construct($customer);
        $this->model = $customer;
    }

    /**
     * @param SearchRequest $search_request
     * @param Account $account
     * @return Support
     */
    public function getAll(SearchRequest $search_request, Account $account)
    {
        return (new CustomerSearch($this))->filter($search_request, $account);
    }

    /**
     * @param int $id
     * @return Customer
     */
    public function findCustomerById(int $id): Customer
    {
        return $this->findOneOrFail($id);
    }


    /**
     * @param array $data
     * @param Customer $customer
     * @return Customer
     */
    public function create(array $data, Customer $customer): Customer
    {
        $customer->fill($data);
        $customer->setNumber();
        $customer->save();

        event(new CustomerWasCreated($customer));

        return $customer->fresh();
    }

    /**
     * @param array $data
     * @param Customer $customer
     * @return Customer
     */
    public function update(array $data, Customer $customer): Customer
    {
        $customer->update($data);

        event(new CustomerWasUpdated($customer));

        return $customer;
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param int $number_of_days
     * @param int $account_id
     * @return int
     */
    public function getRecentCustomers(int $number_of_days, int $account_id)
    {
        $date = Carbon::today()->subDays($number_of_days);
        $result = $this->model->select(DB::raw('count(*) as total'))->where('created_at', '>=', $date)
                              ->where('account_id', '=', $account_id)->get();

        return !empty($result[0]) ? $result[0]['total'] : 0;
    }

    /**
     * Find the address attached to the customer
     *
     * @return mixed
     */
    public function findAddresses(): Support
    {
        return $this->model->addresses;
    }

}
