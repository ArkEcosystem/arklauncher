<?php

declare(strict_types=1);

namespace App\Token\Components;

use ARKEcosystem\Foundation\UserInterface\Components\UploadImageSingle;
use Domain\Token\Models\Token;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\Components\Concerns\HasDefaultRender;

/**
 * @property TemporaryUploadedFile|null $imageSingle
 * @method void validateImageSingle(string $propertyName = 'imageSingle')
 */
final class LogoUpload extends Component
{
    use HasDefaultRender;
    use UploadImageSingle;
    use AuthorizesRequests;

    public Token $token;

    public ?string $logo = null;

    public bool $imageDeleted = false;

    public ?string $withoutBorder = null;

    public ?string $iconSize = null;

    public ?string $dimensions = null;

    public bool $displayText = true;

    public ?string $uploadTooltip = null;

    public function mount(Token $token): void
    {
        $this->token        = $token;
        $this->logo         = $this->token->getFirstMedia('logo') !== null ? $this->token->getFirstMedia('logo')->getUrl() : null;
        $this->imageDeleted = false;
    }

    public function updatedImageSingle(): void
    {
        $this->authorize('update', $this->token);

        if (! $this->imageSingle instanceof TemporaryUploadedFile) {
            $this->imageSingle = TemporaryUploadedFile::createFromLivewire($this->imageSingle);
        }

        $this->validateImageSingle();

        $this->setTokenImage();
    }

    public function setTokenImage(): void
    {
        /** @var TemporaryUploadedFile $imageSingle */
        $imageSingle = $this->imageSingle;

        $this->token
            ->addMedia($imageSingle->getRealPath())
            ->withResponsiveImages()
            ->usingName($imageSingle->hashName())
            ->toMediaCollection('logo');

        $this->token->refresh();
        $this->logo         = $this->token->logo;
        $this->imageDeleted = false;
    }

    public function deleteImageSingle(): void
    {
        /** @var Media $media */
        $media = $this->token->getFirstMedia('logo');

        $media->delete();
        $this->token->refresh();

        if ($this->imageSingle instanceof TemporaryUploadedFile) {
            $this->imageSingle->delete();
        }

        $this->imageSingle  = null;
        $this->logo         = null;
        $this->imageDeleted = true;
    }
}
