<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Solicita;
use App\Ldap\Group as LdapGroup;
use Adldap\Laravel\Facades\Adldap;

class RevokeLocalAdminGroupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        # TODO: o job vai rodar a cada 12 horas, essa query tem que buscar as
        # solicitações de mais de 1 hora apenas
        $solicitations = Solicita::where('expired',false)->get();
        foreach($solicitations as $solicitation){

            $groupname = config('web-ldap-admin.localAdminGroupLdap');
            $group = LdapGroup::createOrUpdate($groupname);

            $ldapuser = Adldap::search()->users()->where('cn', '=', $solicitation->user->username)->first();

            if($ldapuser->inGroup($groupname)){
                $ldapuser->removeGroup($group);
                $ldapuser->save();
            }
            $solicitation->expired = true;
            $solicitation->save();

        }
    }
}
