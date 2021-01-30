<?php

namespace App\Search;

use App\Models\Account;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use App\Requests\SearchRequest;
use App\Transformations\InvoiceTransformable;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceSearch extends BaseSearch
{
    private InvoiceRepository $invoiceRepository;

    private Invoice $model;

    /**
     * InvoiceSearch constructor.
     * @param InvoiceRepository $invoiceRepository
     */
    public function __construct(InvoiceRepository $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->model = $invoiceRepository->getModel();
    }

    /**
     * @param SearchRequest $request
     * @param Account $account
     * @return LengthAwarePaginator|mixed
     */
    public function filter(SearchRequest $request, Account $account)
    {
        $recordsPerPage = !$request->per_page ? 0 : $request->per_page;
        $orderBy = !$request->column ? 'due_date' : $request->column;
        $orderDir = !$request->order ? 'asc' : $request->order;

        $this->query = $this->model->select('*');

        if ($request->filled('search_term')) {
            $this->searchFilter($request->search_term);
        }

        if ($request->filled('status')) {
            $this->status('invoices', $request->status);
        }

        if ($request->filled('customer_id')) {
            $this->query->whereCustomerId($request->customer_id);
        }

        if ($request->filled('project_id')) {
            $this->query->whereProjectId($request->project_id);
        }

        if ($request->filled('id')) {
            $this->query->whereId($request->id);
        }

        if ($request->filled('user_id')) {
            $this->query->where('assigned_to', '=', $request->user_id);
        }

        if ($request->input('start_date') <> '' && $request->input('end_date') <> '') {
            $this->filterDates($request);
        }

        $this->addAccount($account);

        $this->checkPermissions('invoicecontroller.index');

        $this->orderBy($orderBy, $orderDir);

        $invoices = $this->transformList();

        if ($recordsPerPage > 0) {
            $paginatedResults = $this->invoiceRepository->paginateArrayResults($invoices, $recordsPerPage);

            return $paginatedResults;
        }

        return $invoices;
    }

    /**
     * Filter based on search text
     *
     * @param string query filter
     * @return bool
     * @deprecated
     */
    public function searchFilter(string $filter = ''): bool
    {
        if (strlen($filter) == 0) {
            return false;
        }

        $this->query->where(
            function ($query) use ($filter) {
                $query->where('invoices.number', 'like', '%' . $filter . '%')
                      ->orWhere('invoices.po_number', 'like', '%' . $filter . '%')
                      ->orWhere('invoices.date', 'like', '%' . $filter . '%')
                      ->orWhere('invoices.custom_value1', 'like', '%' . $filter . '%')
                      ->orWhere('invoices.custom_value2', 'like', '%' . $filter . '%')
                      ->orWhere('invoices.custom_value3', 'like', '%' . $filter . '%')
                      ->orWhere('invoices.custom_value4', 'like', '%' . $filter . '%');
            }
        );

        return true;
    }

    public function currencyReport (Request $request)
    {
        $this->query =!DB::table('invoices')
             ->select(DB::raw('count(*) as count, currencies.name, SUM(total) as total, SUM(balance) AS balance'))
             ->join('currencies', 'currencies.id', '=', 'invoices.currency_id')
             ->where('currency_id', '<>', 0)
             ->groupBy('currency_id');
    }

    public function report (Request $request)
    {
        $this->query = DB::table('invoices');
        
         if(!empty($request->input('group_by')) {
            $this->query->select(DB::raw('count(*) as count, customers.name, SUM(total) as total, SUM(balance) AS balance'))
            $this->query->groupBy($request->input('group_by'));
        } else {
            $this->query->select(customers.name, total, number, balance, date, due_date');
        }

         $this->query->join('customers', 'customers.id', '=', 'invoices.customer_id')->orderBy('invoices.date_created');
       
             //$this->query->where('status', '<>', 1)
            
    }

    /**
     * @return mixed
     */
    private function transformList()
    {
        $list = $this->query->get();

        $invoices = $list->map(
            function (Invoice $invoice) {
                return (new InvoiceTransformable())->transformInvoice($invoice);
            }
        )->all();

        return $invoices;
    }

}
