<div class="flex items-center gap-x-4 shrink-0 px-4">
    <select wire:change="setLocale($event.target.value)" class="bg-transparent border-none text-sm text-gray-700 dark:text-gray-300 focus:ring-0 cursor-pointer font-medium">
        <option value="es" @if(app()->getLocale() === 'es') selected @endif class="bg-white dark:bg-gray-800">🇪🇸 Español</option>
        <option value="en" @if(app()->getLocale() === 'en') selected @endif class="bg-white dark:bg-gray-800">🇺🇸 English</option>
    </select>
</div>
