<?php

namespace App\Listeners\Department;

use App\Events\DepartmentCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\User;
use App\Delegation;
use App\Department;


class CreateDepartmentDelegations
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  DepartmentCreated  $event
     * @return void
     */
    public function handle(DepartmentCreated $event)
    {
        $department = $event->department;

        $users          = User::notCouncillor()->get();
        $councillors    = User::councillor()->get();

        if($councillors->isEmpty()){
            return true;
        }

        foreach($users as $user){
            $newDelegation = new Delegation;
            $newDelegation->department_id       =   $department->id;
            $newDelegation->delegate_from_id    =   $user->id;
            $newDelegation->delegate_to_id      =   $councillors->random()->id;
            $newDelegation->save();
        }
        
    }
}
