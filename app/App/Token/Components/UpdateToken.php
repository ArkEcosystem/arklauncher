<?php

declare(strict_types=1);

namespace App\Token\Components;

use Domain\Token\Rules\ReservedTokenName;
use Domain\User\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Support\Components\Concerns\ManagesToken;
use Support\Rules\AddressPrefix;

final class UpdateToken extends Component
{
    use ManagesToken;
    use WithFileUploads;

    /** @var mixed */
    protected $listeners = [
        'setDefaults'         => 'setDefaults',
        'setFeeDefaults'      => 'setFeeDefaults',
    ];

    private function step1Rules(): array
    {
        /** @var User $user */
        $user = auth()->user();

        return [
            'chainName' => [
                'required', 'max:32', 'alpha_dash',
                Rule::unique('tokens', 'name')
                    ->where('user_id', $user->id)
                    ->whereNull('deleted_at')
                    ->ignore($this->tokenObject->id, 'id'),
            ],
            'token'         => ['required', 'max:6', 'alpha_num', new ReservedTokenName()],
            'symbol'        => ['required', 'max:3'],
            'mainnetPrefix' => ['required', 'max:1', new AddressPrefix()],
            'devnetPrefix'  => ['required', 'max:1', new AddressPrefix()],
            'testnetPrefix' => ['required', 'max:1', new AddressPrefix()],
        ];
    }
}
