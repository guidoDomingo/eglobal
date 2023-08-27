<?php 

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExcelExport implements FromArray, WithHeadings, WithMultipleSheets
{
    protected $data;
    protected $columna;
    protected $detalle_general;
    protected $columna2;
    protected $data3;
    protected $columna3;

    public function __construct($data, $columna, $detalle_general = null, $columna2 = null, $data3 = null, $columna3 = null)
    {
        $this->data = $data;
        $this->columna = $columna;
        $this->detalle_general = $detalle_general;
        $this->columna2 = $columna2;
        $this->data3 = $data3;
        $this->columna3 = $columna3;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->columna;
    }

    public function sheets(): array
    {
        $sheets = [ $this->createReusableSheet($this->data, $this->columna)];

        if ($this->detalle_general && $this->columna2) {
            $sheets[] = $this->createReusableSheet($this->detalle_general,$this->columna2);
        }

        if ($this->data3 && $this->columna3) {
            $sheets[] = $this->createReusableSheet($this->data3,$this->columna3);
        }

        return $sheets;
    }

    private function createReusableSheet($data, $columna)
    {
        return new class($data, $columna) implements FromArray, WithHeadings {
            protected $data;
            protected $columna;

            public function __construct($data, $columna)
            {
                $this->data = $data;
                $this->columna = $columna;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                return $this->columna;
            }
        };
    }
}
