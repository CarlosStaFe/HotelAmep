<?php

namespace Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

trait WithFullMenuAccess
{
    public function actingAs(Authenticatable $user, $guard = null)
    {
        $rows = array_map(
            fn ($key) => ['user_id' => $user->getAuthIdentifier(), 'menu_key' => $key],
            array_keys(config('menu_access'))
        );
        DB::table('user_menu_access')->insertOrIgnore($rows);

        return parent::actingAs($user, $guard);
    }
}
