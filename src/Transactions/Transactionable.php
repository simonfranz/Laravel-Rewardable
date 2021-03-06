<?php

/*
 * This file is part of Laravel Rewardable.
 *
 * (c) DraperStudio <hello@draperstudio.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraperStudio\Rewardable\Transactions;

use DraperStudio\Rewardable\Exceptions\InsufficientFundsException;
use DraperStudio\Rewardable\Credits\CreditType;
use DraperStudio\Rewardable\Transaction\Transaction;

/**
 * Class Transactionable.
 *
 * @author DraperStudio <hello@draperstudio.tech>
 */
trait Transactionable
{
    /**
     * @return mixed
     */
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    /**
     * @param $amount
     * @param $typeId
     *
     * @return bool
     *
     * @throws InsufficientFundsException
     */
    public function chargeCredits($amount, $typeId)
    {
        // Check if the type of credit exists
        $type = CreditType::find($typeId);

        if (!$type) {
            return false;
        }

        // check if the Model has sufficient balance to trade
        if ($this->getBalanceByType($type->slug) < $amount) {
            throw new InsufficientFundsException(
                $this, $this->id, $this->getBalanceByType($type->id) - $amount
            );
        }

        // All fine, take the cash
        $transaction = (new Transaction())->fill([
            'amount' => $amount,
            'credit_type_id' => $type->id,
        ]);

        $this->transactions()->save($transaction);

        return $transaction;
    }
}
