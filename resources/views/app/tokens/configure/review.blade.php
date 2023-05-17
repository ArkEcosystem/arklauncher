<x-tokens.heading
    :title="trans('pages.token.review.title')"
    :description="trans('pages.token.review.description')"
/>

<x-divider spacing="6" />

{{-- General --}}
<section>
    <h2>{{ trans('pages.token.general.title') }}</h2>

    <section class="grid grid-cols-1 gap-8 mt-6 md:grid-cols-2">
        <div>
            <h3>{{ trans('pages.token.general_title') }}</h3>

            <div class="divide-y divide-theme-secondary-200">
                @foreach (['chainName', 'token', 'symbol'] as $item)
                    <div class="flex justify-between py-3">
                        <span>{{ trans('forms.create_token.input_'.Str::snake($item)) }}:</span>
                        <span class="flex font-semibold text-theme-secondary-900">{{ $this->{$item} }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <h3>{{ trans('pages.token.address_prefix_title') }}</h3>

            <div class="divide-y divide-theme-secondary-200">
                @foreach (['mainnetPrefix', 'devnetPrefix', 'testnetPrefix'] as $item)
                    <div class="flex justify-between py-3">
                        <span>{{ trans('forms.create_token.input_'.Str::snake($item)) }}:</span>
                        <span class="flex font-semibold text-theme-secondary-900">{{ $this->{$item} }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="justify-end mt-3 sm:flex">
        <button type="button" class="w-full sm:w-auto button-secondary" wire:click="setStep(1)">
            {{ trans('actions.edit_general') }}
        </button>
    </div>
</section>

<x-divider spacing="6" />

{{-- Network --}}
<section>
    <h2>{{ trans('pages.token.network_title') }}</h2>

    <section class="grid grid-cols-1 gap-x-8 mt-3 md:grid-cols-2">
        <div class="border-b divide-y md:border-b-0 divide-theme-secondary-200 border-theme-secondary-200">
            @foreach (['forgers', 'blocktime', 'transactionsPerBlock', 'maxBlockPayload', 'totalPremine'] as $item)
                <div class="flex justify-between py-3">
                    <span>{{ trans('forms.create_token.input_'.Str::snake($item)) }}:</span>
                    <span class="flex font-semibold text-theme-secondary-900">{{ $this->{$item} }}</span>
                </div>
            @endforeach
        </div>

        <div class="divide-y divide-theme-secondary-200">
            @foreach (['rewardHeightStart', 'rewardPerBlock', 'vendorFieldLength', 'wif'] as $item)
                <div class="flex justify-between py-3">
                    <span>{{ trans('forms.create_token.input_'.Str::snake($item)) }}:</span>
                    <span class="flex font-semibold text-theme-secondary-900">{{ $this->{$item} }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <h3 class="mt-4">{{ trans('pages.token.ports_title') }}</h3>

    <section class="grid grid-cols-1 gap-x-8 mt-3 md:grid-cols-2">
        <div class="border-b divide-y md:border-b-0 divide-theme-secondary-200 border-theme-secondary-200">
            @foreach (['p2pPort', 'apiPort', 'webhookPort'] as $item)
                <div class="flex justify-between py-3">
                    <span>{{ trans('forms.create_token.input_'.Str::snake($item)) }}:</span>
                    <span class="flex font-semibold text-theme-secondary-900">{{ $this->{$item} }}</span>
                </div>
            @endforeach
        </div>

        <div class="divide-y divide-theme-secondary-200">
            @foreach (['monitorPort', 'explorerPort'] as $item)
                <div class="flex justify-between py-3">
                    <span>{{ trans('forms.create_token.input_'.Str::snake($item)) }}:</span>
                    <span class="flex font-semibold text-theme-secondary-900">{{ $this->{$item} }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <h3 class="mt-4">{{ trans('pages.token.database_title') }}</h3>

    <section class="grid grid-cols-1 gap-x-8 mt-3 md:grid-cols-2">
        <div class="border-b divide-y md:border-b-0 divide-theme-secondary-200 border-theme-secondary-200">
            @foreach (['databaseHost', 'databasePort'] as $item)
                <div class="flex justify-between py-3">
                    <span>{{ trans('forms.create_token.input_'.Str::snake($item)) }}:</span>
                    <span class="flex font-semibold text-theme-secondary-900">{{ $this->{$item} }}</span>
                </div>
            @endforeach
        </div>

        <div class="divide-y divide-theme-secondary-200">
            @foreach (['databaseName'] as $item)
                <div class="flex justify-between py-3">
                    <span>{{ trans('forms.create_token.input_'.Str::snake($item)) }}:</span>
                    <span class="flex font-semibold text-theme-secondary-900">{{ $this->{$item} }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <div class="justify-end mt-3 sm:flex">
        <button type="button" class="w-full sm:w-auto button-secondary" wire:click="setStep(2)">
            {{ trans('actions.edit_network') }}
        </button>
    </div>
</section>

<x-divider spacing="6" />

{{-- Fees --}}
<section>
    <h2>{{ trans('pages.token.fees.title') }}</h2>

    <section class="grid grid-cols-1 gap-8 mt-6 md:grid-cols-2">
        <div>
            <h3>{{ trans('forms.create_token.transaction_pool') }}</h3>

            <div class="flex justify-between py-3 border-b border-theme-secondary-200">
                <span>{{ trans('forms.create_token.min_fee_pool') }}:</span>
                <span class="flex font-semibold text-theme-secondary-900">{{ $this->fees['dynamic']['minFeePool'] }}</span>
            </div>

            <div class="flex justify-between py-3">
                <span>{{ trans('forms.create_token.min_fee_broadcast') }}:</span>
                <span class="flex font-semibold text-theme-secondary-900">{{ $this->fees['dynamic']['minFeeBroadcast'] }}</span>
            </div>
        </div>

        <div>
            <h3>{{ trans('forms.create_token.transfer') }}</h3>

            <div class="flex justify-between py-3">
                <span>{{ trans('forms.create_token.transfer') }}:</span>
                <span class="flex font-semibold text-theme-secondary-900">{{ $this->fees['static']['transfer'] }}</span>
            </div>

            @if ($this->fees['dynamic']['enabled'])
                <div class="flex justify-between py-3 border-t border-theme-secondary-200">
                    <span>{{ trans('forms.create_token.input_addon_bytes') }}:</span>
                    <span class="flex font-semibold text-theme-secondary-900">{{ $this->fees['dynamic']['addonBytes']['transfer'] }}</span>
                </div>
            @endif
        </div>
    </section>

    @foreach (array_chunk([
        'secondSignature',
        'delegateRegistration',
        'vote',
        'multiSignature',
        'ipfs',
        'multiPayment',
        'delegateResignation'
    ], 2) as $items)
        <section class="grid grid-cols-1 gap-8 mt-8 md:grid-cols-2">
            @foreach ($items as $item)
                <div>
                    <h3>{{ trans('forms.create_token.'.Str::snake($item)) }}</h3>

                    <div class="flex justify-between py-3">
                        <span>{{ trans('forms.create_token.'.Str::snake($item)) }}:</span>
                        <span class="flex font-semibold text-theme-secondary-900">{{ $this->fees['static'][$item] }}</span>
                    </div>

                    @if ($this->fees['dynamic']['enabled'])
                        <div class="flex justify-between py-3 border-t border-theme-secondary-200">
                            <span>{{ trans('forms.create_token.input_addon_bytes') }}:</span>
                            <span class="flex font-semibold text-theme-secondary-900">{{ $this->fees['dynamic']['addonBytes'][$item] }}</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </section>
    @endforeach

    <div class="justify-end mt-3 sm:flex">
        <button type="button" class="w-full sm:w-auto button-secondary" wire:click="setStep(3)">
            {{ trans('actions.edit_fees') }}
        </button>
    </div>
</section>

<x-divider spacing="6" />
