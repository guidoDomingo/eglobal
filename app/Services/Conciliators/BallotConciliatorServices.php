<?php

/**
 * User: avisconte
 * Date: 26/02/2021
 * Time: 16:26 pm
 */

namespace App\Services\Conciliators;

use Excel;

class BallotConciliatorServices
{
    /**
     * Esta función sirve para mostrar las opciones de paquetigo, internet etc etc.
     * 
     * @method getRecords
     * @access public
     * @category Service
     * @uses $list = $this->$drs->getRecords($files);
     * @param $files
     * @return array $multi_list_banks 
     * @property-write  $file->getClientOriginalName(); $file->getClientOriginalExtension();
     */
    public function get_record_validations($files, $timestamp, $global_list)
    {
        $parameters = [
            'files' => $files,
            'timestamp' => $timestamp,
            'global_list' => $global_list
        ];

        $class = __CLASS__;
        $function = __FUNCTION__;
        $inputs = json_encode($parameters);

        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");


        $multi_list_banks      = null;
        $list_of_deposit_slips = null;

        try {
            //Orden de Bancos: Itau, Sudameris, Vision, Basa, Familiar.
            //Niveles: 1°, 2°, 3°, 4°, 5°

            $banks_exists = true;

            $list_of_existing_banks = [
                'Itau', 'Sudameris', 'Vision', 'Basa', 'Familiar', 'Interfisa', 'Atlas'
            ];

            $multi_list_banks = array(
                'Itau' => array(
                    'labels'  => array(
                        'Extracto de Cuenta',
                        'Cliente', 'Tipo de Cuenta', 'Nro de Cuenta', 'Moneda', 'Agencia', 'Periodo', 'Gerente',
                        'Saldo Anterior', 'Saldo Actual', 'Saldo a Confirmar', 'Saldo Disponible', 'Saldo Intercheque',
                        'Fecha', 'Descripción', 'Movimiento', 'Débitos', 'Créditos', 'Saldo'
                    ),
                    'files'   => array(),
                    'data'    => array(),
                    'columns' => array(
                        'description' => 'Descripción',
                        'number'      => 'Movimiento',
                        'amount'      => 'Créditos',
                        'date'        => 'Fecha'
                    )
                ),
                'Sudameris' => array(
                    'labels'  => array(
                        'Fecha:', 'Hora:',
                        'Movimientos De Cuenta:',
                        'Tipo de Cuenta:', 'Número de Cuenta:', 'Moneda de Cuenta:', 'Denominación de Cuenta:',
                        'Saldo Mes Anterior:', 'Promedio Mes Pasado:', 'Saldo Contable:', 'Saldo Disponible:', 'Promedio Mes Actual:',
                        'Fecha Operación', 'Fecha Proceso',    'Descripción', 'Referencia', 'Importe', 'Saldo'
                    ),
                    'files'   => array(),
                    'data'    => array(),
                    'columns' => array(
                        'description' => 'Descripción',
                        'number'      => 'Referencia',
                        'amount'      => 'Importe',
                        'date'        => 'Fecha Operación'
                    )
                ),
                'Vision' => array(
                    'labels'  => array(
                        'Movimientos',
                        'Cuenta', 'Moneda', 'Saldo Anterior', 'Saldo a Confirmar', 'Bloqueado', 'Disponible',
                        'Fecha Mov.', 'Hora', 'Fecha Valor', 'Comprobante', 'Descripción', 'Imp. Débito', 'Imp. Crédito', 'Saldo Actual'
                    ),
                    'files'   => array(),
                    'data'    => array(),
                    'columns' => array(
                        'description' => 'Descripción',
                        'number'      => 'Comprobante',
                        'amount'      => 'Imp. Crédito',
                        'date'        => 'Fecha Mov.'
                    )
                ),
                'Basa' => array(
                    'labels'  => array(
                        'Reporte Extracto de Cuenta',
                        'Denominación', 'Moneda', 'Saldo Disponible', 'Saldo Mes Anterior', 'Cuenta', 'Saldo Contable',
                        'Fecha y Hora Movimiento', 'Comprobante', 'Descripción', 'Débito PYG', 'Crédito PYG', 'Saldo PYG'
                    ),
                    'files'   => array(),
                    'data'    => array(),
                    'columns' => array(
                        'description' => 'Descripción',
                        'number'      => 'Comprobante',
                        'amount'      => 'Crédito PYG',
                        'date'        => 'Fecha y Hora Movimiento'
                    )
                ),
                'Familiar' => array(
                    'labels'  => array(
                        'Denominación:', 'Dirección:', 'Nro. Cuenta:', 'Moneda:', 'Saldo actual:', 'Saldo retenido:', 'Saldo anterior:', 'Fecha Desde:', 'Fecha Hasta:',
                        'Fecha Confirmación', 'Fecha Movimiento', 'Comprobante', 'Transacción', 'Débito', 'Crédito', 'Saldo', 'Código Transacción', 'Código Movimiento',
                        'Fecha', 'Monto'
                    ),
                    'files'   => array(),
                    'data'    => array(),
                    'columns' => array(
                        'description' => 'Transacción',
                        'number'      => 'Comprobante',
                        'amount'      => 'Crédito',
                        'date'        => 'Fecha Confirmación'
                    )
                ),
                'Interfisa' => array(
                    'labels'  => array(
                        'Extracto de Cuenta Corriente',
                        'Nombre', 'Tipo de Cuenta', 'Numero de Cuenta',    'Agencia', 'Periodo', 'Moneda',
                        'Fecha Trx.', 'Hora Trx.', 'Fecha Conf.', 'Hora Conf.', 'Concepto', 'Nro. Movimiento', 'Debito', 'Credito', 'Saldo'
                    ),
                    'files'   => array(),
                    'data'    => array(),
                    'columns' => array(
                        'description' => 'Concepto',
                        'number'      => 'Nro. Movimiento',
                        'amount'      => 'Credito',
                        'date'        => 'Fecha Trx.'
                    )
                ),
                'Atlas' => array(
                    'labels' => array(
                        'Fecha Movim.', 'Fecha Valor', 'Hora', 'Descripcion', 'Documento', 'Debito', 'Credito'
                    ),
                    'files'   => array(),
                    'data'    => array(),
                    'columns' => array(
                        'description' => 'Descripcion',
                        'number'      => 'Documento',
                        'amount'      => 'Credito',
                        'date'        => 'Fecha Movim.'
                    )
                ),
                'Desconocido' => array(
                    'labels'  => array(),
                    'files'   => array(),
                    'data'    => array(),
                    'columns' => array(
                        'description' => null,
                        'number'      => null,
                        'amount'      => null,
                        'date'        => null
                    )
                ),
            );

            $list_coincidences     = array();
            $list_of_deposit_slips = array();

            foreach ($multi_list_banks as $key => $name) {
                $list_coincidences[$key] = 0;
                $list_of_deposit_slips[$key] = array();
            }

            $aux  = explode(' - ', str_replace('/', '-', $timestamp));
            $from = date('Y-m-d H:i:s', strtotime($aux[0]));
            $to   = date('Y-m-d H:i:s', strtotime($aux[1]));
            $year = date('Y', strtotime($aux[1])); // Obtener el año para agregar a las fechas que tiene solo día y mes

            $records_list = \DB::table('boletas_depositos as bd')
                ->select(
                    'b.id as bank_id',
                    'b.descripcion as bank', //Banco
                    'cb.id as bank_account_id',
                    'cb.numero_banco as bank_account', //Cuenta bancaria
                    'tp.id as payment_type_id',
                    'tp.descripcion as payment_type', //Tipo de pago
                    'bd.id',
                    'bd.boleta_numero as ballot_number',
                    'bd.monto as amount',
                    'bd.estado as status',
                    \DB::raw('false as correct_data'),
                    \DB::raw("to_char(bd.fecha, 'DD/MM/YYYY') as date") //Boleta
                )
                ->join('cuentas_bancarias as cb', 'cb.id', '=', 'bd.cuenta_bancaria_id')
                ->join('bancos as b', 'b.id', '=', 'cb.banco_id')
                ->join('tipo_pago as tp', 'tp.id', '=', 'bd.tipo_pago_id')
                ->whereRaw("bd.fecha between '{$from}' and '{$to}'")
                //->whereRaw('bd.estado is null') // No se proceso su conciliación
                ->whereRaw('bd.deleted_at is null') // No se eliminó
                ->orderBy('b.descripcion', 'asc')
                ->orderBy('bd.fecha', 'asc')
                ->get();

            foreach ($records_list as $item) {
                $bank = $item->bank;

                if (in_array($bank, $list_of_existing_banks)) {

                    $data = array();

                    foreach ($item as $key => $value) {
                        $data[$key] = $value;
                    }

                    array_push($multi_list_banks[$bank]['data'], $data);
                    array_push($list_of_deposit_slips[$bank], $data);
                } else {
                    $banks_exists = false;
                    \Log::info("El banco: $bank no encuentra parametrizado en el conciliador automático.");
                    break;
                }
            }

            if ($banks_exists) {
                $size = count(collect($files));
                $extensions = array('xls', 'xlsx');

                //Ciclo de recolección.
                for ($i = 0; $i < $size; $i++) {
                    $file = $files[$i];

                    if ($file !== null) {
                        $client_original_name      = $file->getClientOriginalName();
                        $client_original_extension = $file->getClientOriginalExtension();
                        $file_size                 = $file->getSize();
                        $file_type                 = $file->getMimeType();
                        $file_real_path            = $file->getRealPath();

                        $file_size_ = ($file_size < 1000000) ? floor($file_size / 1000) . ' KB' : floor($file_size / 1000000) . ' MB';

                        $extension = $client_original_extension;

                        if (!in_array($extension, $extensions)) {
                            break;
                        } else {
                            $excel = array();

                            foreach ($list_coincidences as $key => $name) {
                                $list_coincidences[$key] = 0;
                            }

                            if (is_readable($file)) {
                                //\Log::info('ES LEIBLE');
                            } else {
                                //\Log::error('NO ES LEIBLE');
                            }

                            Excel::load($file, function ($reader) use (&$excel, &$multi_list_banks, &$list_coincidences, &$class, $function) {
                                try {
                                    $sheet          = $reader->getExcel()->getSheet(0);
                                    $highest_row    = $sheet->getHighestRow();
                                    $highest_column = $sheet->getHighestColumn();

                                    for ($j = 1; $j <= $highest_row; $j++) {
                                        $cell_name = "A$j:$highest_column$j";
                                        $row_data  = $sheet->rangeToArray($cell_name, null, true, false);
                                        $sub_list  = $row_data[0];

                                        //\Log::info($sub_list);
                                        //Para determinar de que banco es.
                                        for ($k = 0; $k < count($sub_list); $k++) {
                                            $cell_value   = $sub_list[$k];
                                            $cell_value   = ltrim($cell_value); //Quitar espacios de izquierda
                                            $cell_value   = rtrim($cell_value); //Quitar espacios de derecha
                                            $cell_value = str_replace("'", '', $cell_value);
                                            $sub_list[$k] = $cell_value;

                                            if ($cell_value !== null and $cell_value !== '' and !is_null($cell_value)) {
                                                foreach ($multi_list_banks as $key => $name) {
                                                    if (in_array($cell_value, $multi_list_banks[$key]['labels'], true)) {
                                                        $list_coincidences[$key]++;
                                                    }
                                                    //\Log::info('Etiquetas $key:');
                                                    //\Log::info($multi_list_banks[$key]['labels']);
                                                }
                                            }
                                        }

                                        array_push($excel, $sub_list);
                                        //\Log::info($sub_list);
                                    }
                                } catch (\Exception $e) {
                                    $error_detail = [
                                        'from' => 'CMS',
                                        'message' => 'Error al crear datos de documento.',
                                        'exception' => $e->getMessage(),
                                        'file' => $e->getFile(),
                                        'class' => $class,
                                        'function' => $function,
                                        'line' => $e->getLine()
                                    ];

                                    $error_detail = json_encode($error_detail);

                                    \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
                                }
                            });

                            $max  = max($list_coincidences);
                            $bank = 'Desconocido';

                            foreach ($list_coincidences as $key => $name) {
                                if ($max == $list_coincidences[$key]) {
                                    $bank = $key;
                                }
                            }

                            // \Log::info('Banco del archivo: $bank');
                            // \Log::info($list_coincidences);

                            $columns          = $multi_list_banks[$bank]['columns'];
                            $description_name = $columns['description']; //Columna de descripción
                            $number_name      = $columns['number'];      //Columna de número
                            $amount_name      = $columns['amount'];      //Columna de monto
                            $date_name        = $columns['date'];        //Columna de fecha

                            $description_index = -1;
                            $number_index      = -1;
                            $amount_index      = -1;
                            $date_index        = -1;
                            $row_index         = -1;

                            for ($j = 0; $j < count($excel); $j++) {
                                $row = $excel[$j];

                                for ($k = 0; $k < count($row); $k++) {
                                    $cell = $row[$k];

                                    if ($number_name == $cell) {
                                        $number_index = $k;
                                        $row_index = $j;
                                    } else if ($description_name == $cell) {
                                        $description_index = $k;
                                    } else if ($amount_name == $cell) {
                                        $amount_index = $k;
                                    } else if ($date_name == $cell) {
                                        $date_index = $k;
                                    }
                                }
                            }

                            /*
                            \Log::info('row_index: $row_index');
                            \Log::info('number_index: $number_index');
                            \Log::info('description_index: $description_index');
                            \Log::info('amount_index: $amount_index');
                            \Log::info('date_index: $date_index');
                            */

                            $valid = ($number_index      !== -1 and
                                $description_index !== -1 and
                                $amount_index      !== -1 and
                                $date_index        !== -1 and
                                $row_index         !== -1) ? true : false; //Verificar si el archivo es correcto

                            //\Log::info('valid: ');
                            //\Log::info($valid);

                            if ($valid) {
                                for ($j = $row_index; $j < count($excel); $j++) {
                                    $row         = $excel[$j];
                                    $description = $row[$description_index];
                                    $number      = $row[$number_index];
                                    $amount      = $row[$amount_index];
                                    $date        = $row[$date_index];

                                    $correct_labels = ($description !== '' and $description !== $description_name and
                                        $number      !== '' and $number      !== $number_name      and
                                        $amount      !== '' and $amount      !== $amount_name      and
                                        $date        !== '' and $date        !== $date_name);

                                    if ($correct_labels) {

                                        /**
                                         * basa      : '2021-01-04
                                         * itau      : 13/01/2021
                                         * vision    : '31/12
                                         * sudameris : '29/01/2021
                                         * familiar  : ?
                                         */

                                        /**
                                         * Los documentos de excel suelen tener columnas formateadas de fecha
                                         * estas son las formulas para convertir un timestamp de excel a un timestamp unix:
                                         * 
                                         * Unix Timestamp = (excel Timestamp - 25569) * 86400 
                                         * Excel Timestamp = (Unix Timestamp / 86400) + 25569
                                         */

                                        //12/02/2021
                                        //42398 timestamp excel
                                        //1234123421341234 timestamp unix

                                        //Itau: 13/01/2021
                                        //Sadumeris: 29/01/2021
                                        //Familiar: '05/04/2021
                                        //Vision: '05/04

                                        //Interfiza: 05-04-2021
                                        //Basa: '2021-04-05 11:45:03

                                        if (is_numeric($date)) {
                                            $timestamp_unix = (intval($date) - 25569) * 86400;
                                            $date           = gmdate('d/m/Y', $timestamp_unix);
                                        } else {
                                            $date = str_replace("'", '', $date);
                                            $middle_dash = strpos($date, '-'); //La fecha tiene guión medio ?
                                            $slash = strpos($date, '/'); //La fecha tiene barra ?

                                            if ($middle_dash) {
                                                $aux = explode('-', $date);
                                            } else if ($slash) {
                                                $aux = explode('/', $date);
                                            }

                                            if ($bank == 'Sudameris' or $bank == 'Familiar' or $bank == 'Vision' or $bank == 'Interfisa' or $bank == 'Atlas') {
                                                $day = $aux[0];
                                                $month = $aux[1];
                                            } else if ($bank == 'Basa' or $bank == 'Atlas') {
                                                $day = $aux[2];
                                                $day_aux = explode(' ', $day);
                                                $day = $day_aux[0];
                                                $month = $aux[1];
                                            } else if ($bank == 'Itau') {
                                                $day = $aux[0];
                                                $month = $aux[1];

                                                $date_format_list = [
                                                    'ENE' => '01',
                                                    'FEB' => '02',
                                                    'MAR' => '03',
                                                    'ABR' => '04',
                                                    'MAY' => '05',
                                                    'JUN' => '06',
                                                    'JUL' => '07',
                                                    'AGO' => '08',
                                                    'SEP' => '09',
                                                    'OCT' => '10',
                                                    'NOV' => '11',
                                                    'DIC' => '12'
                                                ];

                                                $month = $date_format_list[$month];
                                            }

                                            $date = "$day/$month/$year";
                                        }

                                        \Log::info("Banco $bank, Fecha final: $date");

                                        $number = str_replace('.', '', $number);
                                        $number = ltrim($number, '0'); //Validación de ceros a la izquierda

                                        $amount = str_replace('.', '', $amount);
                                        $amount = str_replace('Gs', '', $amount);
                                        $amount = ltrim($amount, '0'); //Validación de ceros a la izquierda
                                        $amount = trim($amount);

                                        # $amount = preg_replace('/^0+/', '', $amount) //Con regex

                                        for ($k = 0; $k < count($multi_list_banks[$bank]['data']); $k++) {
                                            $data       = $multi_list_banks[$bank]['data'][$k];
                                            $number_aux = $data['ballot_number'];
                                            $amount_aux = $data['amount'];
                                            $date_aux   = $data['date'];

                                            //\Log::info('$date == $date_aux');
                                            //\Log::info('$number == $number_aux && $amount == $amount_aux && $date == $date_aux');

                                            if ($number == $number_aux && $amount == $amount_aux && $date == $date_aux) {
                                                $multi_list_banks[$bank]['data'][$k]['correct_data'] = true;
                                                $list_of_deposit_slips[$bank][$k]['correct_data']    = true;
                                                //\Log::info('Dato:');
                                                //\Log::info($multi_list_banks[$bank]['data'][$k]);
                                            } else {
                                                // \Log::info('Dato__:');
                                                // \Log::info($multi_list_banks[$bank]['data'][$k]);
                                                // \Log::info('$date == $date_aux');
                                            }
                                        }
                                    }
                                }
                            } else {
                                \Log::info('Las etiquetas Descripción, Monto etc del banco $bank cambiaron.');
                                \Log::info('Columnas definidas:');
                                \Log::info($multi_list_banks[$bank]['columns']);
                            }

                            //\Log::info('Familiar:');
                            //\Log::info($multi_list_banks['Familiar']['data']);
                            $bank_and_data = array(
                                'name'         => $client_original_name,
                                'extension'    => $client_original_extension,
                                'size'         => $file_size_,
                                'type'         => $file_type,
                                'path'         => $file_real_path,
                                'data'         => $excel,
                                'coincidences' => $list_coincidences[$bank],
                                'valid'        => $valid,
                                'columns'      => array(
                                    'description' => $description_index,
                                    'number'      => $number_index,
                                    'amount'      => $amount_index
                                )
                            );

                            array_push($multi_list_banks[$bank]['files'], $bank_and_data);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => 'Error al leer los archivos.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => $class,
                'function' => $function,
                'line' => $e->getLine()
            ];

            $error_detail = json_encode($error_detail);

            \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
        }

        if ($global_list) {
            $list = $multi_list_banks;
        } else {
            $list = $list_of_deposit_slips;
        }

        /**
         * ---------------------------------------------------------------------------------------------------
         * Para mostrar todos los detalles en el log.
         */
        $response_aux = json_encode($list);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        return $list;
    }
}
