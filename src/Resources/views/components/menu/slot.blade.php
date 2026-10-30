@props([
    'id',
    'label' => null,
    'class' => '',
    'itemClass' => '',
    'direction' => null,
    'maxDepth' => 3,
])

<x-portal::menu-slot
    :id="$id"
    :label="$label"
    :class="$class"
    :item-class="$itemClass"
    :direction="$direction"
    :max-depth="$maxDepth"
    {{ $attributes }}
/>
