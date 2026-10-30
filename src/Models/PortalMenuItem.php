<?php

namespace Platform\Portal\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;
use Platform\Portal\Contracts\PortalMenuItem as PortalMenuItemContract;
use Webkul\Core\Eloquent\TranslatableModel;

class PortalMenuItem extends TranslatableModel implements PortalMenuItemContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'portal_menu_items';

    /**
     * Translation model foreign key column.
     *
     * @var string
     */
    protected $translationForeignKey = 'portal_menu_item_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'url',
        'target',
        'icon',
        'sort_order',
        'status',
        'portal_menu_id',
        'parent_id',
        'portal_page_id',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatedAttributes = [
        'title',
    ];

    /**
     * Get the menu that owns the item.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(PortalMenuProxy::modelClass(), 'portal_menu_id');
    }

    /**
     * Get the parent menu item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(PortalMenuItemProxy::modelClass(), 'parent_id');
    }

    /**
     * Get child menu items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(PortalMenuItemProxy::modelClass(), 'parent_id')
            ->orderBy('sort_order', 'asc');
    }

    /**
     * Get the modular portal page associated with the menu item.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(PortalPageProxy::modelClass(), 'portal_page_id');
    }

    /**
     * Resolve the target URL dynamically.
     */
    public function getUrl(): string
    {
        if ($this->type === 'page' && $this->page) {
            return route('portal.page.show', $this->page->slug);
        }

        if ($this->type === 'route' && ! empty($this->url)) {
            return Route::has($this->url) ? route($this->url) : url($this->url);
        }

        return $this->url ?? '#';
    }

    /**
     * Resolve the display title for the specified locale.
     * Returns empty string if no translation exists for this locale.
     */
    public function getTitle(?string $locale = null): string
    {
        $resolvedLocale = $locale ?? app()->getLocale();

        $translation = $this->translate($resolvedLocale);

        if ($translation && filled($translation->title)) {
            return $translation->title;
        }

        if ($this->type === 'page' && $this->page) {
            $pageTitle = $this->page->translate($resolvedLocale)?->title;
            if (filled($pageTitle)) {
                return $pageTitle;
            }
        }

        return '';
    }

    /**
     * Check if this menu item represents the current page.
     */
    public function isActive(): bool
    {
        $url = $this->getUrl();

        if ($url === '#' || empty($url)) {
            return false;
        }

        $current = url()->current();

        return $current === $url || request()->fullUrlIs($url);
    }
}
