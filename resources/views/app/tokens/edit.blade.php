@extends('layouts.app')

@section('content')
    <x-ark-container>
        <div class="mx-auto w-full max-w-3xl">
            <livewire:update-token :token-object="$token" />
        </div>
    </x-ark-container>
@endsection
