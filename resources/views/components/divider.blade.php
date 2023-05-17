@props ([
    'spacing' => '12'
])

<hr {{ $attributes->class('border-t border-theme-secondary-300 border-dashed')->class([
    '4' => 'pt-4 mt-4',
    '6' => 'pt-6 mt-6',
    '8' => 'pt-8 mt-8',
    '12' => 'pt-12 mt-12',
][$spacing] ?? 'pt-12 mt-12') }}>
