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
     * Get default options structure for this section.
     */
    abstract public function getDefaultOptions(): array;

    /**
     * Sanitize input options before saving.
     */
    public function sanitize(array $options): array
    {
        return $options;
    }
}
