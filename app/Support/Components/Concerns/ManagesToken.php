<?php

declare(strict_types=1);

namespace Support\Components\Concerns;

use ARKEcosystem\Foundation\UserInterface\Support\Enums\FlashType;
use Domain\SecureShell\Facades\SecureShellKey;
use Domain\Token\Enums\TokenAttributeEnum;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Models\Token;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Support\Rules\Port;
use Support\Services\Json;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Throwable;

trait ManagesToken
{
    use WithFileUploads;

    public bool $isVisible = false;

    public bool $hasReachedReviewStage = false;

    public Token $tokenObject;

    public string $token = '';

    public mixed $config = null;

    public string $chainName = '';

    public string $symbol = '';

    public string $mainnetPrefix = '';

    public string $devnetPrefix = '';

    public string $testnetPrefix = '';

    public string $forgers = '';

    public string $blocktime = '';

    public string $transactionsPerBlock = '';

    public string  $maxBlockPayload = '';

    public string $totalPremine = '';

    public string $rewardHeightStart = '';

    public string $rewardPerBlock = '';

    public string $vendorFieldLength = '';

    public string $wif = '';

    public string $p2pPort = '';

    public string $apiPort = '';

    public string $webhookPort = '';

    public string $monitorPort = '';

    public string $coreIp = '0.0.0.0';

    public string $explorerIp = '0.0.0.0';

    public string $explorerPort = '';

    public string $databaseHost = '';

    public string $databasePort = '';

    public string $databaseName = '';

    public array $inputs = [];

    public int $step = 1;

    public int $steps = 4;

    public array $fees = [
        'static' => [
            'transfer'             => null,
            'secondSignature'      => null,
            'delegateRegistration' => null,
            'vote'                 => null,
            'multiSignature'       => null,
            'ipfs'                 => null,
            'multiPayment'         => null,
            'delegateResignation'  => null,
        ],
        'dynamic' => [
            'enabled'         => true,
            'minFeePool'      => null,
            'minFeeBroadcast' => null,
            'addonBytes'      => [
                'transfer'             => null,
                'secondSignature'      => null,
                'delegateRegistration' => null,
                'vote'                 => null,
                'multiSignature'       => null,
                'ipfs'                 => null,
                'multiPayment'         => null,
                'delegateResignation'  => null,
            ],
        ],
    ];

    public function mount(Token $tokenObject): void
    {
        if (is_array($tokenObject->config)) {
            $this->fill($tokenObject->config);
        }

        $this->tokenObject = $tokenObject;
    }

    public function render(): View
    {
        return view('livewire.manage-token');
    }

    public function updatedConfig(): void
    {
        $this->validate([
            'config' => ['required', 'max:1024'],
        ]);

        try {
            if (! is_string($content = file_get_contents($this->config->getRealPath()))) {
                // We'll throw this error to break out of the try catch. We can't test if the NoFileException is thrown
                throw new NoFileException(); // @codeCoverageIgnore
            }

            $config = Json::parseConfig($content);

            foreach ($config as $key => $value) {
                if ($key === 'fees') {
                    $this->fees = array_merge($this->fees, $value);
                } else {
                    $this->setProtectedPropertyValue($key, $value === 'localhost' ? '127.0.0.1' : $value);
                }
            }
        } catch (Throwable) {
            $this->addError('config', 'We were unable to parse the provided config file');
        }
    }

    public function updated(string $propertyName): void
    {
        $this->validateRequest($propertyName);
    }

    public function update(): void
    {
        $data = $this->validateRequest();

        $this->store($data, true);
    }

    public function cancel(): void
    {
        $this->redirectRoute('home');
    }

    public function store(array $data, bool $final = false): void
    {
        $this->tokenObject->name = $data['chainName'];

        if ($this->step === 3 && ! $this->fees['dynamic']['enabled']) {
            unset($data['fees']['dynamic']['addonBytes']);
        }

        $this->tokenObject->config = $data;

        $this->tokenObject->setMetaAttribute(TokenAttributeEnum::REPO_NAME, $this->tokenObject->config['chainName'] ?? null);

        if ($final && TokenStatusEnum::isPending($this->tokenObject->status)) {
            // Create the SSH keys for this token...
            $this->tokenObject->setKeypair(SecureShellKey::make(''));

            $this->tokenObject->setStatus(TokenStatusEnum::FINISHED);
        }

        $this->tokenObject->save();

        if ($final) {
            // Complete the configuration onboarding....
            $this->tokenObject->onboarding()->completeConfiguration();

            alert('tokens.token_updated', FlashType::SUCCESS);

            $this->redirectRoute('tokens.show', $this->tokenObject);
        }
    }

    public function next(): void
    {
        $data = $this->validateRequest();

        $this->store(array_merge($this->tokenObject->config ?? [], $data));

        $this->step += 1;

        if ($this->isLastStep()) {
            $this->hasReachedReviewStage = true;
        }
    }

    public function previous(): void
    {
        $this->step -= 1;
    }

    public function isLastStep(): bool
    {
        return $this->step === 4;
    }

    public function setStep(int $step): void
    {
        if ($step > 0 && $step <= $this->steps) {
            $this->step = $step;
        }
    }

    public function handleDefaults(array $inputs): void
    {
        $this->inputs = $inputs;

        foreach ($inputs as $input) {
            if ($this->getPropertyValue($input) !== '') {
                $this->emit('askForConfirmation', false);

                return;
            }
        }

        $this->setDefaults();
    }

    public function handleFeeDefaults(array $inputs): void
    {
        $this->inputs = $inputs;

        foreach ($inputs as $input) {
            if (! is_null(Arr::get($this->fees, $input))) {
                $this->emit('askForConfirmation', true);

                return;
            }
        }

        $this->setFeeDefaults();
    }

    public function setDefaults(?bool $overwrite = false): void
    {
        foreach ($this->inputs as $input) {
            if ($overwrite === false && $this->getPropertyValue($input)) {
                // Skip fields that are already filled in
                continue;
            }
            $this->setProtectedPropertyValue($input, trans('forms.create_token.input_'.Str::snake($input).'_placeholder'));
            $this->updated($input);
        }
        $this->emit('closeModal');
    }

    public function setFeeDefaults(?bool $overwrite = false): void
    {
        foreach ($this->inputs as $input) {
            if ($overwrite === false && ! is_null(Arr::get($this->fees, $input))) {
                // Skip fields that are already filled in
                continue;
            }
            Arr::set($this->fees, $input, trans('forms.create_token.'.Str::snake($input).'_placeholder'));
            $this->updated($input);
        }
        $this->emit('closeModal');
    }

    public function returnToReview(): void
    {
        $data = $this->validateRequest();

        $this->store(array_merge($this->tokenObject->config ?? [], $data));

        $this->setStep(4);
    }

    public function cancelChanges(): void
    {
        $this->fill($this->tokenObject->config);
        $this->resetErrorBag();
        $this->setStep(4);
    }

    private function validateRequest(?string $propertyName = null): array
    {
        $step1 = $this->step1Rules();

        $step2 = [
            'forgers'                                      => ['required', 'integer'],
            'blocktime'                                    => ['required', 'integer'],
            'transactionsPerBlock'                         => ['required', 'integer'],
            'maxBlockPayload'                              => ['required', 'integer'],
            'totalPremine'                                 => ['required', 'integer'],
            'rewardHeightStart'                            => ['required', 'integer'],
            'rewardPerBlock'                               => ['required', 'integer'],
            'vendorFieldLength'                            => ['required', 'integer', 'max:255'],
            'wif'                                          => ['required', 'integer', 'max:255'],
            'p2pPort'                                      => ['required', new Port(), ...$this->uniquePortRules('p2pPort')],
            'apiPort'                                      => ['required', new Port(), ...$this->uniquePortRules('apiPort')],
            'webhookPort'                                  => ['required', new Port(), ...$this->uniquePortRules('webhookPort')],
            'monitorPort'                                  => ['required', new Port(), ...$this->uniquePortRules('monitorPort')],
            'coreIp'                                       => ['required', 'ipv4'],
            'explorerIp'                                   => ['required', 'ipv4'],
            'explorerPort'                                 => ['required', new Port(), ...$this->uniquePortRules('explorerPort')],
            'databaseHost'                                 => ['required', 'ipv4'],
            'databasePort'                                 => ['required', new Port(), ...$this->uniquePortRules('databasePort')],
            'databaseName'                                 => ['required', 'max:32'],
        ];

        $step3 = [
            'fees'                                         => ['required', 'array'],
            'fees.static'                                  => ['required', 'array'],
            'fees.static.transfer'                         => ['required', 'integer'],
            'fees.static.vote'                             => ['required', 'integer'],
            'fees.static.secondSignature'                  => ['required', 'integer'],
            'fees.static.delegateRegistration'             => ['required', 'integer'],
            'fees.static.multiSignature'                   => ['required', 'integer'],
            'fees.static.ipfs'                             => ['required', 'integer'],
            'fees.static.multiPayment'                     => ['required', 'integer'],
            'fees.static.delegateResignation'              => ['required', 'integer'],
            'fees.dynamic'                                 => ['required', 'array'],
            'fees.dynamic.enabled'                         => ['required', 'bool'],
            'fees.dynamic.minFeePool'                      => ['required', 'integer'],
            'fees.dynamic.minFeeBroadcast'                 => ['required', 'integer'],
            'fees.dynamic.addonBytes'                      => ['required_if:fees.dynamic.enabled,true', 'array'],
            'fees.dynamic.addonBytes.transfer'             => ['required_if:fees.dynamic.enabled,true|integer'],
            'fees.dynamic.addonBytes.secondSignature'      => ['required_if:fees.dynamic.enabled,true|integer'],
            'fees.dynamic.addonBytes.delegateRegistration' => ['required_if:fees.dynamic.enabled,true|integer'],
            'fees.dynamic.addonBytes.vote'                 => ['required_if:fees.dynamic.enabled,true|integer'],
            'fees.dynamic.addonBytes.multiSignature'       => ['required_if:fees.dynamic.enabled,true|integer'],
            'fees.dynamic.addonBytes.ipfs'                 => ['required_if:fees.dynamic.enabled,true|integer'],
            'fees.dynamic.addonBytes.multiPayment'         => ['required_if:fees.dynamic.enabled,true|integer'],
            'fees.dynamic.addonBytes.delegateResignation'  => ['required_if:fees.dynamic.enabled,true|integer'],
        ];

        $validators = [
            '1' => $step1,
            '2' => $step2,
            '3' => $step3,
            '4' => array_merge($step1, $step2, $step3),
        ];

        if ($propertyName !== null) {
            return $this->validateOnly($propertyName, $validators[$this->step]);
        }

        return $this->validate($validators[$this->step]);
    }

    private function uniquePortRules(string $field) : array
    {
        return array_values(array_diff([
            'different:p2pPort',
            'different:apiPort',
            'different:webhookPort',
            'different:monitorPort',
            'different:explorerPort',
            'different:databasePort',
        ], ['different:'.$field]));
    }
}
