<?php

namespace App\Traits;

trait DatatableRenderTrait
{
    /**
     * Render a Blade view for datatable column types (e.g., input, select, text).
     *
     * @param string $type The column type (e.g., 'input', 'select', 'text')
     * @param array $params Parameters to pass to the view
     * @return string Rendered HTML
     */
    public function renderDatatableColumn(string $type, array $params = []): string
    {
        $view = "components.datatable.{$type}";
        return view()->exists($view) ? view($view, $params)->render() : '';
    }
}
