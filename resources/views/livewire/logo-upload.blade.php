<x-ark-upload-image-single
    id="logo"
    :image="$this->logo"
    wire:model="imageSingle"
    :dimensions="$dimensions ?? 'w-48 h-48'"
    :display-text="$displayText"
    :uploadTooltip="$uploadTooltip"
    :icon-size="$iconSize"
    :without-border="$withoutBorder"
    with-crop
    crop-options="{
        viewMode: 1,
        aspectRatio: 1 / 1,
        dragMode: 'move',
        restore: false,
        guides: false,
        center: false,
        highlight: false,
        cropBoxMovable: false,
        cropBoxResizable: false,
        toggleDragModeOnDblclick: false,
    }"
/>
