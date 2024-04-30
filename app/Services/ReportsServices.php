<?php

namespace Noorfarooqy\Flexcube\Services;
use Noorfarooqy\NoorAuth\Services\NoorServices;

class ReportsServices extends NoorServices
{
    use BusinessIntelligenceTrait;

    public function AccountStatementReport($account, $from_date, $to_date, $format = 'pdf')
    {
        return $this->BankStatementReport($account, $from_date, $to_date, $format);
    }

}
