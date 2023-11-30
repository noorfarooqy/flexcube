<?php

namespace Noorfarooqy\Flexcube\Helpers;

enum ErrorCodes: string
{
    case DEFAULT_ERROR = "Request to the CBS Failed. Please contact admin for assistance";
    case ONGOING_EOD = "EOD is ongoing. Try again later";
}
