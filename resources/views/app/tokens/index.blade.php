@component('layouts.app', ['fullWidth' => true])

    @section('content')
        <x-ark-container>
            @livewire('manage-tokens')
        </x-ark-container>
    @endsection

@endcomponent
