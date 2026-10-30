<?php

namespace Platform\Portal\DataGrids;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class PortalMenuDataGrid extends DataGrid
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

        $queryBuilder = DB::table('portal_menus')
            ->select(
                'portal_menus.id',
                'portal_menus.name',
                'portal_menus.slug',
                'portal_menus.status',
                'portal_menus.created_at',
                DB::raw('COALESCE(channel_translations.name, channels.code, "All Channels") as channel_name'),
                DB::raw('(SELECT COUNT(*) FROM portal_menu_items WHERE portal_menu_items.portal_menu_id = portal_menus.id) as items_count')
            )
            ->leftJoin('channels', 'portal_menus.channel_id', '=', 'channels.id')
            ->leftJoin('channel_translations', function ($join) use ($currentLocale) {
                $join->on('channels.id', '=', 'channel_translations.channel_id')
                    ->where('channel_translations.locale', '=', $currentLocale);
            });

        $this->addFilter('id', 'portal_menus.id');
        $this->addFilter('name', 'portal_menus.name');
        $this->addFilter('slug', 'portal_menus.slug');
        $this->addFilter('status', 'portal_menus.status');
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
            'label' => trans('portal::app.admin.menus.index.datagrid.id'),
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('portal::app.admin.menus.index.datagrid.name'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'slug',
            'label' => trans('portal::app.admin.menus.index.datagrid.slug'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'items_count',
            'label' => trans('portal::app.admin.menus.index.datagrid.items-count'),
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'channel_name',
            'label' => trans('portal::app.admin.menus.index.datagrid.channel'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('portal::app.admin.menus.index.datagrid.status'),
            'type' => 'boolean',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->status) {
                    return '<span class="label-active">'.trans('portal::app.admin.menus.index.datagrid.active').'</span>';
                }

                return '<span class="label-info">'.trans('portal::app.admin.menus.index.datagrid.inactive').'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('portal::app.admin.menus.index.datagrid.created-at'),
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('portal_menus.builder')) {
            $this->addAction([
                'icon' => 'icon-menu',
                'title' => trans('portal::app.admin.menus.index.datagrid.builder'),
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.portal.menus.builder', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('portal_menus.edit')) {
            $this->addAction([
                'icon' => 'icon-edit',
                'title' => trans('portal::app.admin.menus.index.datagrid.edit'),
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.portal.menus.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('portal_menus.delete')) {
            $this->addAction([
                'icon' => 'icon-delete',
                'title' => trans('portal::app.admin.menus.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.portal.menus.delete', $row->id);
                },
            ]);
        }
    }
}
