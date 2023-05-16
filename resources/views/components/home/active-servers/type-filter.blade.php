@props([
    'mobile' => false,
])

<x-ark-simple-filter-dropdown
    :options="trans('pages.home.filter.types')"
    model="serverType"
    :initial-value="$this->serverType"
    :mobile="$mobile"
/>
