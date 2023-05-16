@component('layouts.app', ['fullWidth' => true])

    @section('content')
        <x-ark-container>
            <livewire:manage-notifications renderAsHtml />
        </x-ark-container>
    @endsection

@endcomponent
