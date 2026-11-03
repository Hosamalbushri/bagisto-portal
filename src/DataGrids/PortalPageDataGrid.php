<?php

namespace Platform\Portal\DataGrids;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Webkul\DataGrid\DataGrid;

class PortalPageDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'id';

    /**
     * Prepare query builder.
     *
     * @return Builder
     */
    public function prepareQueryBuilder()
    {
        $currentLocale = core()->getRequestedLocaleCode();

        $queryBuilder = DB::table('portal_pages')
            ->select(
                'portal_pages.id',
                'portal_pages.slug',
                'portal_pages.status',
                'portal_pages.is_home_page',
                'portal_pages.created_at',
                'portal_page_translations.title',
                DB::raw('COALESCE(channel_translations.name, channels.code) as channel_name')
            )
            ->leftJoin('portal_page_translations', function ($join) use ($currentLocale) {
                $join->on('portal_pages.id', '=', 'portal_page_translations.portal_page_id')
                    ->where('portal_page_translations.locale', '=', $currentLocale);
            })
            ->leftJoin('channels', 'portal_pages.channel_id', '=', 'channels.id')
            ->leftJoin('channel_translations', function ($join) use ($currentLocale) {
                $join->on('channels.id', '=', 'channel_translations.channel_id')
                    ->where('channel_translations.locale', '=', $currentLocale);
            });

        $this->addFilter('id', 'portal_pages.id');
        $this->addFilter('slug', 'portal_pages.slug');
        $this->addFilter('status', 'portal_pages.status');
        $this->addFilter('is_home_page', 'portal_pages.is_home_page');
        $this->addFilter('channel_name', 'channel_translations.name');

        return $queryBuilder;
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('portal::app.admin.pages.index.datagrid.id'),
            'type' => 'integer',
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'title',
            'label' => trans('portal::app.admin.pages.index.datagrid.title'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'slug',
            'label' => trans('portal::app.admin.pages.index.datagrid.slug'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'channel_name',
            'label' => trans('portal::app.admin.pages.index.datagrid.channel'),
            'type' => 'string',
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'is_home_page',
            'label' => trans('portal::app.admin.pages.index.datagrid.home-page'),
            'type' => 'boolean',
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                if ($row->is_home_page) {
                    return '<span class="badge badge-md badge-info">'.trans('portal::app.admin.pages.index.datagrid.default-home').'</span>';
                }

                return '-';
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('portal::app.admin.pages.index.datagrid.status'),
            'type' => 'boolean',
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                if ($row->status) {
                    return '<span class="badge badge-md badge-success">'.trans('portal::app.admin.pages.index.datagrid.published').'</span>';
                }

                return '<span class="badge badge-md badge-danger">'.trans('portal::app.admin.pages.index.datagrid.disabled').'</span>';
            },
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $this->addAction([
            'icon' => 'icon-view',
            'title' => trans('portal::app.admin.pages.index.datagrid.view'),
            'method' => 'GET',
            'target' => '_blank',
            'url' => function ($row) {
                if ($row->is_home_page) {
                    return route('portal.home');
                }

                if ($row->status) {
                    return route('portal.page.show', $row->slug);
                }

                return URL::temporarySignedRoute(
                    'portal.page.preview',
                    now()->addHours(2),
                    ['slug' => $row->slug]
                );
            },
        ]);

        if (bouncer()->hasPermission('portal.pages.customize') || bouncer()->hasPermission('pages.customize')) {
            $this->addAction([
                'icon' => 'icon-settings',
                'title' => trans('portal::app.admin.pages.index.datagrid.customize'),
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.portal.pages.customize', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('portal.pages.edit') || bouncer()->hasPermission('pages.edit')) {
            $this->addAction([
                'icon' => 'icon-edit',
                'title' => trans('portal::app.admin.pages.index.datagrid.edit'),
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.portal.pages.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('portal.pages.delete') || bouncer()->hasPermission('pages.delete')) {
            $this->addAction([
                'icon' => 'icon-delete',
                'title' => trans('portal::app.admin.pages.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.portal.pages.delete', $row->id);
                },
            ]);
        }
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('portal.pages.delete') || bouncer()->hasPermission('pages.delete')) {
            $this->addMassAction([
                'title' => trans('portal::app.admin.pages.index.datagrid.delete'),
                'method' => 'POST',
                'url' => route('admin.portal.pages.mass_delete'),
            ]);
        }

        if (bouncer()->hasPermission('portal.pages.edit') || bouncer()->hasPermission('pages.edit')) {
            $this->addMassAction([
                'title' => trans('portal::app.admin.pages.index.datagrid.update-status'),
                'method' => 'POST',
                'url' => route('admin.portal.pages.mass_update'),
                'options' => [
                    [
                        'label' => trans('portal::app.admin.pages.index.datagrid.published'),
                        'value' => 1,
                    ],
                    [
                        'label' => trans('portal::app.admin.pages.index.datagrid.disabled'),
                        'value' => 0,
                    ],
                ],
            ]);
        }
    }
}
