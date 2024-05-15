<?php

namespace Noorfarooqy\Flexcube\Services;

use Illuminate\Support\Facades\Log;
use Noorfarooqy\NoorAuth\Services\NoorServices;

class FlexcubeServices extends NoorServices implements CoreBankingContract
{

    use HasFlexcubeBankingSystem;
    public function AccountDetails($account, $branch = null)
    {
        Log::info($account);
        return $this->QueryIACustAccount([
            'Cust-Account-IO' => [
                'BRN' => $branch == null ? substr($account, 0, 3) : $branch,
                'ACC' => $account,
            ],
        ], $branch == null ? substr($account, 0, 3) : $branch);
    }
    public function AccountIsDormant($account, $branch = null)
    {
        $account_details = $this->AccountDetails($account, $branch);
        if (!$account_details)
            return -1;

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
            'XREF' => $origin['xref'],
            'PRD' => $product,
            'BRN' => !isset($origin['branch']) ? substr($origin['account'], 0, 3) : $origin['branch'],
            'TXNBRN' => !isset($origin['branch']) ? substr($origin['account'], 0, 3) : $origin['branch'],
            'TXNACC' => $origin['account'],
            'TXNCCY' => $origin['ccy'],
            'TXNAMT' => $amount,
            'NARRATIVE' => !isset($origin['narrative']) ? $origin['narrative'] : $product . ' - ref: ' . $origin['xref'] . ' - a/c ' . $origin['account'],
        ];
        if ($offset) {
            $request['OFFSETBRN'] = !isset($offset['offset_branch']) ? substr($offset['offset_account'], 0, 3) : $offset['offset_branch'];
            $request['OFFSETACC'] = $offset['offset_account'];
            $request['OFFSETCCY'] = $offset['offset_ccy'];
            $request['OFFSETAMT'] = $amount;
        }
        $response = $this->CreateTransaction([
            'Transaction-Details' => $request,
        ]);
        Log::info('--flexcube response--gateway--');
        Log::info(json_encode($response));
        return $response;
    }
    public function AccountQueryTransaction($reference)
    {
        return $this->QueryTransactionDetails([
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

    public function AccountBlockAmount($account, $branch, $amount, $hp_code, $ref, $expires_at = null)
    {
        return $this->BlockAmount([
            'Amount-Blocks-IO' => [
                'ACC' => $account,
                'AMTBLKNO' => $ref,
                'AMT' => $amount,
                'EXPDATE' => $expires_at,
                'ABLKTYPE' => 'F',
                'REFERENCE_NO' => $ref,
                'HPCODE' => 'MPESA',
                'EFFDATE' => now(),
                'HOLDDESC' => 'Hold for ' . $hp_code . ' purpose ref: ' . $ref,
                "REMARKS" => 'Hold for ' . $hp_code . ' purpose ref: ' . $ref,
                'BRANCH' => $branch

            ],
        ]);
    }
    public function AccountUnblockAmount($ref)
    {
        return $this->UnBlockAmount([
            'Amount-Blocks-IO' => [
                'AMTBLKNO' => $ref,
            ],
        ]);
    }

    public function QueryAmountBlock($account_number, $block_no)
    {
        return $this->QueryAmtBlk([
            'Amount-Blocks-IO' => [
                'AMTBLKNO' => $block_no,
            ]
        ], substr($account_number, 0, 3));
    }

    public function GetAccountAmountBlocks($account)
    {
        return $this->QueryBlocks([
            'Account-IO' => [
                'BRANCH' => substr($account, 0, 3),
                'ACCOUNT' => $account
            ]
        ]);
    }

    public function GenerateReference($prefix = 'LFC')
    {
        $transactions = gmdate('YmdHis', time());
        $reference = $prefix . (config('flexcube.reference_salt', 100000000) + $transactions);
        return $reference;
    }
}
