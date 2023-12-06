<?php

namespace Noorfarooqy\Flexcube\Services;

use Noorfarooqy\NoorAuth\Services\NoorServices;

class FlexcubeServices extends NoorServices implements CoreBankingContract
{

    use HasFlexcubeBankingSystem;
    public function AccountDetails($account, $branch = null)
    {
        return $this->AccountDetails([
            'Cust-Account-IO' => [
                'BRN' => $branch == null ? substr($account, 0, 3) : $branch,
                'ACC' => $account,
            ],
        ]);
    }
    public function AccountIsDormant($account, $branch = null)
    {
        $account_details = $this->AccountDetails($account, $branch);
        if (!$account_details) return -1;

        return $account_details?->{'Summary'}?->{'DORM'} == 'Y';
    }
    public function AccountIsNoDebit($account, $branch = null)
    {
        $account_details = $this->AccountDetails($account, $branch);

        return $account_details?->{'ACSTATNODR'} == 'Y';
    }
    public function AccountIsNoCredit($account, $branch = null)
    {
        $account_details = $this->AccountDetails($account, $branch);
        return $account_details?->{'ACSTATNOCR'} == 'Y';
    }
    public function AccountBelongsToStaff($account, $branch = null)
    {
        $account_details = $this->AccountDetails($account, $branch);
        return $account_details?->{'ACCTYPE'} == 'S';
    }
    public function AccountIsFrozen($account, $branch = null)
    {
        $account_details = $this->AccountDetails($account, $branch);
        return $account_details?->{'FROZEN'} == 'Y';
    }
    public function AccountIsIndividualCurrent($account, $branch = null)
    {
        $account_details = $this->AccountDetails($account, $branch);
        return $account_details?->{'ACCLS'} == 'ICUR';
    }
    public function AccountIsIndividualSaving($account, $branch = null)
    {
        $account_details = $this->AccountDetails($account, $branch);
        return $account_details?->{'ACCLS'} == 'ISAV';
    }
    public function AccountBalance($account, $branch = null)
    {
        return $this->QueryAccountBalance([
            'ACC-Balance' => [
                'ACC_BAL' => [
                    'BRANCH_CODE' => $branch == null ? substr($account, 0, 3) : $branch,
                    'CUST_AC_NO' => $account,
                ],
            ],
        ]);
    }
    public function AccountTransaction($amount, $product, $origin, $offset = null)
    {
        $request = [
            'XREF' => $this->GenerateReference($product),
            'PRD' => $product,
            'BRN' => $origin['branch'] == null ? substr($origin['account'], 0, 3) : $origin['branch'],
            'TXNBRN' => $origin['branch'] == null ? substr($origin['account'], 0, 3) : $origin['branch'],
            'TXNACC' => $origin['account'],
            'TXNCCY' => $origin['ccy'],
            'TXNAMT' => $amount,
        ];
        if ($offset) {
            $request['OFFSETBRN'] = $offset['offset_branch'];
            $request['OFFSETACC'] = $offset['offset_account'];
            $request['OFFSETCCY'] = $offset['offset_ccy'];
        }
        $request['OFFSETAMT'] = $amount;
        return $this->CreateTransaction([
            'Transaction-Details' => $request,
        ]);
    }
    public function AccountQueryTransaction($reference)
    {
        return $this->QueryAccountTransactions([
            'Transaction-Details-IO' => [
                'FCCREF' => $reference
            ]
        ]);
    }
    public function AccountReverseTransaction($reference)
    {
        return $this->ReverseTransaction([
            'Transaction-Details-IO' => [
                'FCCREF' => $reference,
            ],
        ]);
    }
    public function AccountMinistatement($account, $branch = null)
    {
        return $this->QueryAccountTransactions([
            'Acc-Details-IO' => [
                'NUMOFTRN' => 5,
                'ACCNO' => $account,
                'ACCBRN' => $branch == null ? substr($account, 0, 3) : $branch,
            ],
        ]);
    }

    public function CustomerDetails($customer_cif)
    {
        return $this->QueryCustomerDetails([
            'Stvws-Stdcifqy-Query-IO' => [
                'KEY_ID' => 'C',
                'VALUE' => $customer_cif,
            ],
        ]);
    }
    public function CustomerAccounts($customer_cif)
    {
        return $this->QueryCustomerAccountDetails([
            'Sttms-Customer-IO' => [
                'CUSTNO' => $customer_cif
            ],
        ]);
    }

    public function ExchangeRate($from, $to, $branch = '000')
    {
        return $this->QueryExchangeRate([
            'Ccy-Rate-Master-IO' => [
                'BRNCD' => $branch,
                'CCY1' => $from,
                'CCY2' => $to
            ]
        ]);
    }

    public function GenerateReference($prefix = 'LFC')
    {
        $transactions = gmdate('YmdHis', time());
        $reference =  $prefix . (config('flexcube.reference_salt', 100000000) + $transactions);
        return $reference;
    }
}
