<?php

declare(strict_types=1);

namespace Support\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Spatie\SchemalessAttributes\SchemalessAttributes;

trait HasSchemalessAttributes
{
    public function getExtraAttributesAttribute(): SchemalessAttributes
    {
        $attributes = SchemalessAttributes::createForModel($this, 'extra_attributes');

        /* @phpstan-ignore-next-line  */
        if (property_exists($this, 'encryptedExtraAttributes')) {
            foreach ($this->encryptedExtraAttributes as $encryptedExtraAttribute) {
                $resolvedEncryptedAttribute = $attributes->get($encryptedExtraAttribute);
                if (! is_null($resolvedEncryptedAttribute)) {
                    $attributes->set($encryptedExtraAttribute, decrypt($resolvedEncryptedAttribute));
                }
            }
        }

        return $attributes;
    }

    public function setExtraAttributesAttribute(mixed $value): void
    {
        /* @phpstan-ignore-next-line  */
        if (property_exists($this, 'encryptedExtraAttributes')) {
            foreach ($this->encryptedExtraAttributes as $encryptedExtraAttribute) {
                if (! is_null(Arr::get($value, $encryptedExtraAttribute))) {
                    $value[$encryptedExtraAttribute] = encrypt($value[$encryptedExtraAttribute]);
                }
            }
        }

        $this->attributes['extra_attributes'] = json_encode($value);
    }

    public function scopeWithExtraAttributes(): Builder
    {
        return $this->extra_attributes->modelScope();
    }

    public function getMetaAttribute(mixed $name): mixed
    {
        return $this->extra_attributes->get($name);
    }

    public function setMetaAttribute(mixed $name, mixed $value): void
    {
        $this->extra_attributes->set($name, $value);

        $this->save();
    }

    public function forgetMetaAttribute(mixed $name): void
    {
        $this->extra_attributes->forget($name);

        $this->save();
    }
}
