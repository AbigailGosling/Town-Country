<?php

namespace App\Observers;

use App\Models\CommentLogging;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     *
     * @param  \App\Models\Customer  $customer
     * @return void
     */
    public function created(Customer $customer)
    {
        //
    }

    /**
     * Handle the Customer "updated" event.
     *
     * @param  \App\Models\Customer  $customer
     * @return void
     */
    public function updated(Customer $customer)
    {
        if ($customer->wasChanged("credit_enabled"))
        {
            $comment = new CommentLogging();
            $comment->type = "customer_credit_checking";
            $comment->user_id = Auth::id();
            $comment->entity_id = $customer->id;
            $comment->body = ($comment->credit_enabled == 1)?"Credit Checking Enabled":"Credit Checking Disabled";
            $comment->save();
        }
    }

    /**
     * Handle the Customer "deleted" event.
     *
     * @param  \App\Models\Customer  $customer
     * @return void
     */
    public function deleted(Customer $customer)
    {
        //
    }

    /**
     * Handle the Customer "restored" event.
     *
     * @param  \App\Models\Customer  $customer
     * @return void
     */
    public function restored(Customer $customer)
    {
        //
    }

    /**
     * Handle the Customer "force deleted" event.
     *
     * @param  \App\Models\Customer  $customer
     * @return void
     */
    public function forceDeleted(Customer $customer)
    {
        //
    }
}
