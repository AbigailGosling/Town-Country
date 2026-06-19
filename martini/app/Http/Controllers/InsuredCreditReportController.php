<?php

namespace App\Http\Controllers;

use App\Models\Customer;

class InsuredCreditReportController extends Controller
{
    public function index()
    {
        $customers = Customer::query()
            ->leftJoin('customer_outstanding_cache', 'customer_outstanding_cache.customer_id', '=', 'customers.id')
            //->whereNotIn('customers.insured_credit',[0,''])->whereNotNull('customers.insured_credit')
            ->where('customers.credit_enabled', true)
            ->where('customers.disabled', false)
            ->whereNotIn('customers.businessname', ['', '.. search'])
            ->orderBy('customers.businessname')
            ->get([
                'customers.id',
                'customers.businessname',
                'customers.insured_credit',
                'customer_outstanding_cache.outstanding',
            ]);

        $data = $customers->map(function ($customer) {
            $insuredCredit = (float) ($customer->insured_credit ?? 0);
            $outstanding = (float) ($customer->outstanding ?? 0);
            $difference = $insuredCredit - $outstanding;

            return [
                'Customer ID' => $customer->id,
                'Customer' => $customer->businessname,
                'Insured Credit' => $insuredCredit,
                'Outstanding Balance' => $outstanding,
                'Difference' => $difference,
            ];
        })->values();
        $data = $data->sortByDesc(function ($customer){
            return $customer['Outstanding Balance'];
        })->values();
        $totals = [
            'customer_count' => $data->count(),
            'insured_credit' => (float) $customers->sum(function ($customer) {
                return (float) ($customer->insured_credit ?? 0);
            }),
            'outstanding' => (float) $customers->sum(function ($customer) {
                return (float) ($customer->outstanding ?? 0);
            }),
        ];
        $totals['difference'] = $totals['insured_credit'] - $totals['outstanding'];
        $totals['over_limit_count'] = $customers->filter(function ($customer) {
            return (float) ($customer->outstanding ?? 0) > (float) ($customer->insured_credit ?? 0);
        })->count();

        if ($data->isNotEmpty()) {
            $data->push($this->buildTotalRow($totals));
        }

        return view('reports.insuredcredit', [
            'data' => $data,
            'totals' => $totals,
        ]);
    }

    private function buildTotalRow(array $totals): array
    {
        return [
            'Customer ID' => '',
            'Customer' => 'TOTAL',
            'Insured Credit' => $totals['insured_credit'],
            'Outstanding Balance' => $totals['outstanding'],
            'Difference' => $totals['difference'],
        ];
    }
}
