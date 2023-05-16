<x-tokens.heading
    :title="trans('pages.token.network.title')"
    :description="trans('pages.token.network.description')"
    :link="trans('urls.documentation.customize_network')"
/>

<x-divider spacing="6" />

<section>
    <x-tokens.subheading
        :title="trans('pages.token.network_title')"
        :description="trans('pages.token.network_description')"
        fields="['forgers', 'blocktime', 'transactionsPerBlock', 'maxBlockPayload', 'totalPremine','rewardHeightStart', 'rewardPerBlock', 'vendorFieldLength', 'wif']"
    />

    <div class="grid grid-cols-1 gap-4 mt-5 sm:grid-cols-2 md:grid-cols-3">
        @foreach (['forgers', 'blocktime', 'transactionsPerBlock', 'maxBlockPayload', 'totalPremine', 'rewardHeightStart', 'rewardPerBlock', 'vendorFieldLength', 'wif'] as $item)
            <x-ark-input
                type="text"
                :name="$item"
                :label="trans('forms.create_token.input_' . Str::snake($item))"
                :placeholder="trans('forms.create_token.input_' . Str::snake($item) . '_placeholder')"
                :errors="$errors"
            />
        @endforeach
    </div>
</section>

<x-divider spacing="6" />

<section>
    <x-tokens.subheading
        :title="trans('pages.token.ports_title')"
        :description="trans('pages.token.ports_description')"
        fields="['p2pPort', 'apiPort', 'webhookPort', 'monitorPort', 'explorerPort']"
    />

    <div class="grid grid-cols-1 gap-4 mt-5 sm:grid-cols-2 md:grid-cols-3">
        @foreach (['p2pPort', 'apiPort', 'webhookPort', 'monitorPort', 'explorerPort'] as $item)
            <x-ark-input
                type="text"
                :name="$item"
                :label="trans('forms.create_token.input_' . Str::snake($item))"
                :placeholder="trans('forms.create_token.input_' . Str::snake($item) . '_placeholder')"
                :errors="$errors"
            />
        @endforeach
    </div>
</section>

<x-divider spacing="6" />

<section>
    <x-tokens.subheading
        :title="trans('pages.token.database_title')"
        :description="trans('pages.token.database_description')"
        fields="['databaseHost', 'databasePort', 'databaseName']"
    />

    <div class="grid grid-cols-1 gap-4 mt-5 sm:grid-cols-2 md:grid-cols-3">
        @foreach (['databaseHost', 'databasePort', 'databaseName'] as $item)
            <x-ark-input
                type="text"
                :name="$item"
                :label="trans('forms.create_token.input_' . Str::snake($item))"
                :placeholder="trans('forms.create_token.input_' . Str::snake($item) . '_placeholder')"
                :errors="$errors"
            />
        @endforeach
    </div>
</section>
