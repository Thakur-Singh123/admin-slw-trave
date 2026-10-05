<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class WalletHelper
{
    // Total Balance
    public static function balance($agentId)
    {
        return DB::table('agent_wallets')
            ->where('add_agent_id', $agentId)
            ->where('isactive', 1)
            ->sum('total_amount');
    }

    // Total Deposit
    public static function deposit($agentId)
    {
        return DB::table('agent_wallets')
            ->where('add_agent_id', $agentId)
            ->where('isactive', 1)
            ->sum('deposit_amount');
    }

    // Total Withdrawal
    public static function withdrawal($agentId)
    {
        return DB::table('agent_wallets')
            ->where('add_agent_id', $agentId)
            ->where('isactive', 1)
            ->sum('withdrawal_amount');
    }
}