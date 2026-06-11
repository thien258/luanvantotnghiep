<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class RawArrayImport implements ToArray
{
    public array $data = [];

    public function array(array $array): void
    {
        $this->data = $array;
    }
}
