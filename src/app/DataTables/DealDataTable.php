<?php

namespace App\DataTables;

use App\Models\Deal;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DealDataTable extends DataTable
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
            ->editColumn('hs_object_id', function ($row) {
                if (isset($row->hs_object_id)) {
                    $baseUrl = config('hubspot-integration.base_url');
                    $portalId = config('hubspot-integration.portal_id');
                    $url = implode('', [
                        $baseUrl, 'contacts/', $portalId, '/deals/', $row->hs_object_id,
                    ]);

                    return "<a href='{$url}' target='_blank'>{$row->hs_object_id}</a>";
                }

                return $row->hs_object_id;
            })
            ->addColumn('companies', function ($deal) {
                if ($deal->associations->count() == 0) {
                    return null;
                }

                return $deal->associations->map(function ($association) {
                    return [
                        'Id' => $association->associateable->id,
                        'Hubspot Id' => $association->associateable->hs_object_id,
                        'Company' => $association->associateable->name,
                    ];
                })->toArray();
            })
            ->addColumn('contacts', function ($deal) {
                if ($deal->associations->count() == 0) {
                    return null;
                }

                return $deal->associations->map(function ($association) {
                    return $association->associateable->contacts->map(function ($contact) {
                        return [
                            'Id' => $contact->id,
                            'Hubspot Id' => $contact->hs_object_id,
                            'Company' => $contact->company->name,
                            'Email' => $contact->email,
                        ];
                    })->toArray();
                })->toArray();
            })
            ->addColumn('line_items', function ($deal) {
                if ($deal->lineItems->count() == 0) {
                    return null;
                }

                return $deal->lineItems->map(function ($lineItem) {
                    return [
                        'Id' => $lineItem->id,
                        'Hubspot Id' => $lineItem->hs_object_id,
                        'Product Id' => $lineItem->product_id,
                        'Product' => $lineItem->product->name,
                        'Quantity' => $lineItem->quantity,
                        'Price' => $lineItem->product->price,
                        'Total' => $lineItem->product->price * $lineItem->quantity,
                    ];
                })->toArray();
            })
            ->rawColumns(['hs_object_id', 'companies', 'contacts', 'line_items']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Deal $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Deal $model)
    {
        return $model
            ->with([
                'associations',
                'associations.associateable',
                'associations.associateable.contacts',
                'lineItems',
                'lineItems.product',
            ])
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
            ->setTableId('deals-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('deals'))
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
            Column::computed('companies', 'Companies')->render('`<pre>${JSON.stringify(data,null,2)}</pre>`')
                ->addClass('none'),
            Column::computed('contacts', 'Contacts')->render('`<pre>${JSON.stringify(data,null,2)}</pre>`')
                ->addClass('none'),
            Column::computed('line_items', 'Line Items')->render('`<pre>${JSON.stringify(data,null,2)}</pre>`')
                ->addClass('none'),
            Column::make('deal_name'),
            Column::make('pipeline'),
            Column::make('deal_stage'),
            Column::make('amount'),
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
        return 'Deal_' . date('YmdHis');
    }
}
