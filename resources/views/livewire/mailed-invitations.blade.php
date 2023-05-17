<div>
    @if($this->invitations->isNotEmpty())
    <div class="flex flex-col pt-10 mt-10 border-t border-dashed">
        <span class="text-2xl font-semibold text-theme-secondary-900">@lang('tokens.invitations.mailed_invitations_title')</span>
        <span>@lang('tokens.invitations.mailed_invitations_description')</span>

        <div class="mt-5 table-container">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th>@lang('tables.email')</th>
                        <th>@lang('tables.date_added')</th>
                        <th><span class="block text-right">@lang('tables.actions')</span></th>
                    </tr>
                <thead>
                <tbody>
                @foreach ($this->invitations as $invitation)
                    <tr>
                        <td class="font-semibold text-theme-secondary-700">{{ $invitation->email }}</td>
                        <td>{{ $invitation->created_at_local->format(DateFormat::DATE) }}</td>
                        <td class="flex justify-end">
                            <button
                                class="w-10 h-10 button-icon"
                                wire:click="cancel('{{ $invitation->id }}')">
                                <x-ark-icon name="trash" size="sm" />
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
