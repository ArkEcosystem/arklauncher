<?php

declare(strict_types=1);

use App\View\Components\SortByButton;

it('store the button paramaters', function () {
    $name          = 'name';
    $sortBy        = 'name';
    $sortDirection = 'asc';

    $slot = 'the label';

    $component = new SortByButton($name, $sortBy, $sortDirection);

    expect($component->data()['name'])->toBe($name);
    expect($component->data()['sortBy'])->toBe($sortBy);
    expect($component->data()['sortDirection'])->toBe($sortDirection);
});

it('shows the sort icon if the attribute is selected', function () {
    $name          = 'name';
    $sortBy        = 'name';
    $sortDirection = 'asc';

    $slot = 'the label';

    $component = new SortByButton($name, $sortBy, $sortDirection);

    $view = $component->render()->with(
        array_merge($component->data(), ['slot' => $slot])
    );

    expect($view->getName())->toBe('components.sort-by-button');

    $html = $view->render();

    expect($html)->not()->toBeEmpty();

    expect(strpos($html, 'svg') !== false)->toBeTrue();
});

it('does not show the sort icon if the attribute is not selected', function () {
    $name          = 'name';
    $sortBy        = 'another_name';
    $sortDirection = 'asc';

    $slot = 'the label';

    $component = new SortByButton($name, $sortBy, $sortDirection);

    $view = $component->render()->with(
        array_merge($component->data(), ['slot' => $slot])
    );

    expect($view->getName())->toBe('components.sort-by-button');

    $html = $view->render();

    expect($html)->not()->toBeEmpty();

    expect(strpos($html, 'svg'))->toBeFalse();
});
