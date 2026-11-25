<?php

namespace App\Menu;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class MenuAccessFilter implements FilterInterface
{
    public function transform($item)
    {
        if (! isset($item['url'])) {
            return $item;
        }

        $user = auth()->user();
        if ($user && $user->hasRole('Administrador')) {
            return $item;
        }

        foreach (config('menu_access') as $key => $option) {
            if ($item['url'] === $option['url']) {
                $item['restricted'] = ! $user || ! $user->canAccessMenu($key);

                return $item;
            }
        }

        $item['restricted'] = true;

        return $item;
    }
}
