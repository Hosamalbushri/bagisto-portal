<?php

namespace Platform\Portal\DataGrids;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
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
        $currentLocale = app()->getLocale();

        $queryBuilder = DB::table('portal_pages')
            ->select(
                'portal_pages.id',
                'portal_pages.slug',
                'portal_pages.status',
                'portal_pages.created_at',
                'portal_page_translations.title',
                'channels.name as channel_name'
            )
            ->leftJoin('portal_page_translations', function ($join) use ($currentLocale) {
                $join->on('portal_pages.id', '=', 'portal_page_translations.portal_page_id')
                    ->where('portal_page_translations.locale', '=', $currentLocale);
            })
            ->leftJoin('channels', 'portal_pages.channel_id', '=', 'channels.id');

        $this->addFilter('id', 'portal_pages.id');
        $this->addFilter('slug', 'portal_pages.slug');
        $this->addFilter('status', 'portal_pages.status');

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
            'label' => 'المعرف',
            'type' => 'integer',
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'title',
            'label' => 'عنوان الصفحة',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'slug',
            'label' => 'الرابط (Slug)',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'channel_name',
            'label' => 'القناة',
            'type' => 'string',
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'الحالة',
            'type' => 'boolean',
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                if ($row->status) {
                    return '<span class="badge badge-md badge-success">منشورة</span>';
                }

                return '<span class="badge badge-md badge-danger">معطلة</span>';
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
            'title' => 'معاينة في المتجر',
            'method' => 'GET',
            'target' => '_blank',
            'url' => function ($row) {
                return route('portal.page.show', $row->slug);
            },
        ]);

        $this->addAction([
            'icon' => 'icon-settings',
            'title' => 'تخصيص المكونات',
            'method' => 'GET',
            'url' => function ($row) {
                return route('admin.portal.pages.customize', $row->id);
            },
        ]);

        $this->addAction([
            'icon' => 'icon-edit',
            'title' => 'تعديل البيانات',
            'method' => 'GET',
            'url' => function ($row) {
                return route('admin.portal.pages.edit', $row->id);
            },
        ]);

        $this->addAction([
            'icon' => 'icon-delete',
            'title' => 'حذف الصفحة',
            'method' => 'DELETE',
            'url' => function ($row) {
                return route('admin.portal.pages.delete', $row->id);
            },
        ]);
    }
}
