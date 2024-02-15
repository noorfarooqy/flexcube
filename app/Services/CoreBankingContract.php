<?php

namespace Noorfarooqy\Flexcube\Services;

interface CoreBankingContract
{

    public function AccountDetails($account, $branch = null);
    public function AccountIsDormant($account);
    public function AccountIsNoDebit($account);
    public function AccountIsNoCredit($account);
    public function AccountBelongsToStaff($account);
    public function AccountIsFrozen($account);
    public function AccountIsIndividualCurrent($account);
    public function AccountIsIndividualSaving($account);
    public function AccountBalance($account, $branch);
    public function AccountTransaction($amount, $product, $origin, $offset = null);
    public function AccountQueryTransaction($account);
    public function AccountReverseTransaction($reference);
    public function AccountMinistatement($account);

    public function CustomerDetails($customer);
    public function CustomerAccounts($customer);

    public function ExchangeRate($from, $to, $branch = '000');

    public function AccountBlockAmount($account, $branch, $amount, $hp_code, $ref, $expires_at = now()->addDay());
    public function AccountUnblockAmount($ref);
    public function QueryAmountBlock($account_number, $block_no);
    public function GetAccountAmountBlocks($account);
}
