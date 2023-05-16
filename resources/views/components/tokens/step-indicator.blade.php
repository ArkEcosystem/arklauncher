@props ([
    'active',
    'current',
])

<div @class([
    'flex-1 border-b-2 py-1 font-semibold',
    'border-theme-warning-500 text-theme-secondary-900' => $active === $current,
    'text-theme-secondary-500 border-theme-warning-500' => $active > $current,
    'text-theme-secondary-500 border-theme-secondary-200' => $active < $current,
])></div>
