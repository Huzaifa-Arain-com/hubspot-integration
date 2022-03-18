<?php

namespace App\DataTables;

use App\Models\Contact;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ContactDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('company', function ($row) {
                if (! empty($row->company)) {
                    return [
                        'Id' => $row->company->id,
                        'HubspotId' => $row->company->hs_object_id,
                        'name' => $row->company->name,
                    ];
                }

                return null;
            })
            ->editColumn('hs_object_id', function ($row) {
                if (isset($row->hs_object_id)) {
                    $baseUrl = config('hubspot-integration.base_url');
                    $portalId = config('hubspot-integration.portal_id');
                    $url = implode('', [
                        $baseUrl, 'contacts/', $portalId, '/contact/', $row->hs_object_id,
                    ]);

                    return "<a href='{$url}' target='_blank'>{$row->hs_object_id}</a>";
                }

                return $row->hs_object_id;
            })
            ->rawColumns(['hs_object_id', 'company']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Contact $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Contact $model)
    {
        return $model
            ->with(['company'])
            ->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('contacts-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('contacts'))
            ->dom('Bflriptip')
            ->orderBy(0)
            ->buttons(
                Button::make('reload'),
                Button::make('export'),
                Button::make('print'),
                Button::make('reset'),
            )
            ->responsive()
            ->lengthMenu([10, 50, 100, 500, 1000])
            ->pageLength(500);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('id'),
            Column::make('hs_object_id'),
            Column::computed('company', 'company')
                ->render('`<pre>${JSON.stringify(data,null,2)}</pre>`'),
            Column::make('first_name'),
            Column::make('last_name'),
            Column::make('email'),
            Column::make('synched_at'),
            Column::make('failed_at'),
            Column::make('created_at'),
            Column::make('updated_at'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Contact_' . date('YmdHis');
    }
}
