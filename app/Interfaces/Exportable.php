<?php

namespace App\Interfaces;

interface Exportable
{
    public function getExportColumns();
    public function getExportFileName(): string;
}