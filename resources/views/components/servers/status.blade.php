@props ([
    'server'
])

<div {{ $attributes->class('flex items-center space-x-2') }}>
    @if ($server->isOffline())
        <span class="flex justify-center items-center w-5 h-5 rounded-full border-2 border-theme-secondary-500"></span>
        <span class="font-semibold text-theme-secondary-500">{{ trans('pages.server.status.offline') }}</span>
    @elseif ($server->isFailed())
        <x-ark-icon name="circle.cross" class="text-theme-danger-400" />
        <span class="font-semibold text-theme-danger-400">{{ trans('pages.server.status.failed') }}</span>
    @elseif ($server->isProvisioned())
        <x-ark-icon name="circle.check-mark" class="text-theme-success-600" />
        <span class="font-semibold text-theme-success-600">{{ trans('pages.server.status.online') }}</span>
    @else
        <x-ark-icon name="circle.arrow-down" class="text-theme-primary-600" />
        <a href="{{ $server->pathShow() }}" class="font-semibold border-b border-transparent text-theme-primary-600 hover:border-theme-primary-600">
            {{ trans('pages.server.status.provisioning') }}
        </a>
    @endif
</div>
