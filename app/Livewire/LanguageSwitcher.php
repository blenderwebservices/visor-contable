<?php

namespace App\Livewire;

use Livewire\Component;

class LanguageSwitcher extends Component
{
    public function setLocale($locale)
    {
        if (\Illuminate\Support\Facades\Auth::check() && in_array($locale, ['en', 'es'])) {
            $user = \Illuminate\Support\Facades\Auth::user();
            $user->locale = $locale;
            $user->save();

            return redirect(request()->header('Referer'));
        }
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}
