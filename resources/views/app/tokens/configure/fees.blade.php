<x-tokens.heading
    :title="trans('pages.token.fees.title')"
    :description="trans('pages.token.fees.description')"
    :link="trans('urls.documentation.customize_fees')"
/>

<x-divider spacing="8" />

<div class="mb-6">
    <x-ark-toggle
        name="fees.dynamic.enabled"
        :default="$fees['dynamic']['enabled'] ? 'true' : 'false'"
        :label="trans('forms.create_token.input_enabled')"
    />
</div>

<section class="space-y-8">
    <div>
        <x-tokens.subheading
            :title="trans('forms.create_token.transaction_pool')"
            wire="handleFeeDefaults(['dynamic.minFeePool', 'dynamic.minFeeBroadcast'])"
        />

        <div class="grid grid-cols-1 gap-4 mt-5 sm:grid-cols-2">
            <x-ark-input
                type="text"
                name="fees.dynamic.minFeePool"
                :label="trans('forms.create_token.min_fee_pool')"
                :placeholder="trans('forms.create_token.dynamic.min_fee_pool_placeholder')"
                :errors="$errors"
            />

            <x-ark-input
                type="text"
                name="fees.dynamic.minFeeBroadcast"
                :label="trans('forms.create_token.min_fee_broadcast')"
                :placeholder="trans('forms.create_token.dynamic.min_fee_broadcast_placeholder')"
                :errors="$errors"
            />
        </div>
    </div>

    @foreach ([
        'transfer', 'secondSignature', 'delegateRegistration', 'vote',
        'multiSignature', 'ipfs', 'multiPayment', 'delegateResignation',
    ] as $type)
        <div>
            <x-tokens.subheading
                :title="trans('forms.create_token.'.Str::snake($type))"
                wire="handleFeeDefaults(['static.{{ $type }}', 'dynamic.addonBytes.{{ $type }}'])"
            />

            <div class="grid grid-cols-1 gap-4 mt-5 sm:grid-cols-2">
                <x-ark-input
                    type="text"
                    :name="'fees.static.'.$type"
                    :label="trans('forms.create_token.input_static')"
                    :placeholder="trans('forms.create_token.static.'.Str::snake($type).'_placeholder')"
                    :errors="$errors"
                />

                @if ($fees['dynamic']['enabled'])
                    <x-ark-input
                        type="text"
                        :name="'fees.dynamic.addonBytes.'.$type"
                        :label="trans('forms.create_token.input_addon_bytes')"
                        :placeholder="trans('forms.create_token.dynamic.addon_bytes.'.Str::snake($type).'_placeholder')"
                        :errors="$errors"
                    />
                @endif
            </div>
        </div>
    @endforeach
</section>
