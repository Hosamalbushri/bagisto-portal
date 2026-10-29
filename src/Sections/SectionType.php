<?php

namespace Platform\Portal\Sections;

use Illuminate\Support\Str;

abstract class SectionType
{
    /**
     * Unique section type code.
     */
    protected string $code;

    /**
     * Translation key or readable title.
     */
    protected ?string $title = null;

    /**
     * Icon class for the section tile in admin.
     */
    protected string $icon = 'icon-cms';

    /**
     * Description of the section.
     */
    protected ?string $description = null;

    /**
     * Whether a channel may hold only one section of this type.
     */
    protected bool $singleton = false;

    /**
     * Whether the section is fixed to the bottom of the page.
     */
    protected bool $pinned = false;

    /**
     * Whether the layout draws the section on every page.
     */
    protected bool $layout = false;

    /**
     * Get the section code.
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Get the title for display.
     */
    public function getTitle(): string
    {
        return $this->title
            ? trans($this->title)
            : Str::headline($this->code);
    }

    /**
     * Get the icon class.
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Get the description.
     */
    public function getDescription(): string
    {
        return $this->description ? trans($this->description) : '';
    }

    /**
     * Whether a page may hold only one section of this type.
     */
    public function isSingleton(): bool
    {
        return $this->singleton;
    }

    /**
     * Whether the section is fixed to the bottom of the page.
     */
    public function isPinned(): bool
    {
        return $this->pinned;
    }

    /**
     * Whether the layout draws the section on every page.
     */
    public function rendersInLayout(): bool
    {
        return $this->layout;
    }

    /**
     * Array representation for the editor.
     */
    public function toArray(): array
    {
        return [
            'code' => $this->getCode(),
            'title' => $this->getTitle(),
            'icon' => $this->getIcon(),
            'is_singleton' => $this->isSingleton(),
            'is_pinned' => $this->isPinned(),
        ];
    }

    /**
     * Get the fields schema for the visual editor.
     */
    abstract public function getFields(): array;

    /**
     * Get default options structure for this section.
     */
    abstract public function getDefaultOptions(): array;

    /**
     * Prepare options for editor if needed.
     */
    public function prepareForEditor(array $options): array
    {
        return $options;
    }

    /**
     * Sanitize input options before saving.
     */
    public function sanitize(array $options): array
    {
        return $options;
    }
}
