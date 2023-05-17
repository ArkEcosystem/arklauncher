<div class="flex justify-center items-center {{ $dimensions() }}">
    @unless ($token->logo)
        <x-ark-avatar class="{{ $dimensions() }} {{ $rounded() }}" :identifier="$token->name" show-identifier-letters />
    @else
        <div class="{{ $dimensions() }} bg-center bg-no-repeat bg-contain {{ $rounded() }} bg-theme-primary-100" style="background-image: url({{ $token->logo }})"></div>
    @endunless
</div>
