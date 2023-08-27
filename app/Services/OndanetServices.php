<?php
/**
 * Created by PhpStorm.
 * User: thavo
 * Date: 15/02/17
 * Time: 11:12 AM
 */

namespace App\Services;

use Carbon\Carbon;

class OndanetServices
{

    /**
     * REVERSION: Método P_CLIENTE_MINITERMINAL
     *
     * PARAMETROS
     * @CLIENTE VARCHAR(80) -> Sería la Razón Social del cliente
     * @RUC VARCHAR(20)     -> Sería el RUC del cliente
     * @DIRECCION VARCHAR(80) -> Sería la Dirección del Cliente
     * @CELULAR VARCHAR(25)   -> Sería el N.º de Teléfono o Celular del cliente
    */
    public function sendCliente($group_id){
        try
        {
            $group = \DB::table('business_groups')->where('id',$group_id)->first();
            
            $query = "SET NOCOUNT ON;
            SET ANSI_WARNINGS OFF;
            DECLARE @rv Numeric(25)
            EXEC @rv = [DBO].[P_CLIENTE_MINITERMINAL]
            '$group->description', '$group->ruc', '$group->direccion', '$group->telefono'
            SELECT @rv";

            \Log::info($query);
            $results = $this->get_one($query);
            \Log::info($results);

            $response = $results[0];
            $result = '';
            \Log::info($response);
            foreach ($response as $key => $value ){
                $result = $value;
                \Log::info($result);
            }

            //se configura un array con los estados de error conocidos
            $errors = array(
                "-4 | Vendedor no existe"                               =>"-4",
                "-5 | El campo RUC se encuentra Vacio"                  =>"-5",
                "212 | otros errores no definidos en el procedimiento"  =>"212",
            );

            $check = array_search($result, $errors);
            \Log::info($check);
            if(!$check){
                $data['error'] = false;
                $data['status'] = $result;
                return $data;
            }else{

                $message = explode("|", $check);

                $data['error'] = true;
                $data['status'] = $check;
                $data['code'] = $message[0];
                return $data;
            }

        }catch (\Exception $e){
            $response['error']   = true;
            $response['status']  = '212';
            $response['code']    = '212';
            \Log::warning('[Eglobal - Cliente]',['result'=>$e]);
            return $response;
        }

    }


    /**
     * VENTAS: Método P_FACTURA_MINI_TERMINAL
     *
    */
    public function sendVentas(){
        try
        {
            $group_id=341;
            
            $sales = \DB::table('miniterminales_sales')
            ->select('movements.id', 'business_groups.ruc as pdv', 'miniterminales_sales.fecha as fecha', 'movements.amount as importe','miniterminales_sales.nro_venta as numero_venta')
            ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
            ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
            ->join('business_groups', 'business_groups.id', '=', 'current_account.group_id')
            ->whereIn('movements.destination_operation_id', [0, 212])
            ->where('movements.movement_type_id', 1)
            ->where('current_account.group_id', 341)
            ->orderBy('movements.id','DESC')
            ->take(20)
            ->get();

            foreach($sales as $sale){
                //proceed to export deposits to ondanet
                $fecha      =  date("d-m-Y", strtotime($sale->fecha));
                $pdv        =  $sale->pdv;
                $importe    =  $sale->importe;
                $nro_venta  =  $sale->numero_venta;

                $query = "
                SET NOCOUNT ON;
                SET ANSI_WARNINGS OFF;
                DECLARE @rv Numeric(25) 
                DECLARE @FECHA DATE 
                SET @FECHA = (SELECT CONVERT(DATE, '$fecha', 103)) 
                EXEC @rv = [DBO].[P_FACTURA_MINI_TERMINAL] 
                '$pdv','4','COMIN','$nro_venta',@FECHA,'INT070','$importe','VENTAS MINI-TERMINAL' 
                SELECT @rv";

                \Log::info($query);
                $results = $this->get_one($query);
                \Log::info($results);

                $response = $results[0];
                $result = '';
                \Log::info($response);
                foreach ($response as $key => $value ){
                    $result = $value;
                    \Log::info($result);
                }

                $data['movement_id'] = $sale->id;
                //se configura un array con los estados de error conocidos
                $errors = array(
                    "-4 | Vendedor no existe"                               =>"-4",
                    "-5 | El campo RUC se encuentra Vacio"                  =>"-5",
                    "212 | otros errores no definidos en el procedimiento"  =>"212",
                );

                $check = array_search($result, $errors);
                \Log::info($check);
                if(!$check){
                    $data['error'] = false;
                    $data['status'] = $result;
                    return $data;
                }else{

                    $message = explode("|", $check);

                    $data['error'] = true;
                    $data['status'] = $check;
                    $data['code'] = $message[0];
                    return $data;
                }
            }
        }catch (\Exception $e){
            $response['error']   = true;
            $response['status']  = '212';
            $response['code']    = '212';
            \Log::warning('[Eglobal - Cliente]',['result'=>$e]);
            return $response;
        }

    }

    /**
     * COBRANZAS: Método P_FACTURA_MINI_TERMINAL
     *
    */
    public function sendCobranzas(){
        try
        {
            
            $cobranzas = \DB::table('mt_recibos_cobranzas')
            ->select('movements.id as id_cobranza', 'mt_recibos.recibo_nro as recibo', 
            'boletas_depositos.fecha as fecha', 'mt_recibos.monto as importe',
            'mt_recibos_cobranzas.ventas_cobradas as ventas')
            ->join('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_cobranzas.recibo_id')
            ->join('movements', 'movements.id', '=', 'mt_recibos.movement_id')
            ->join('boletas_depositos', 'boletas_depositos.id', '=', 'mt_recibos_cobranzas.boleta_deposito_id')
            ->whereIn('movements.destination_operation_id', [0, 212])
            ->whereNotNull('mt_recibos_cobranzas.ventas_cobradas')
            ->orderBy('movements.id','ASC')
            ->take(20)
            ->get();
            //dd($cobranzas);
            foreach($cobranzas as $cobranza){
                //proceed to export deposits to ondanet
                $recibo     =  $cobranza->recibo;
                $fecha      =  date("d-m-Y", strtotime($cobranza->fecha));
                $importe    =  $cobranza->importe;
                $ventas     =  $cobranza->ventas;

                $query = "
                SET NOCOUNT ON;
                SET ANSI_WARNINGS OFF;
                DECLARE @RC Numeric(18) 
                DECLARE @FECHA DATE SET @FECHA = (SELECT CONVERT(DATE, '$fecha', 103)) 
                EXECUTE @RC = [DBO].[P_COBRO_RECIBO] '9000', '$recibo', @FECHA, '$importe','$ventas' 
                SELECT @RC";

                \Log::info($query);
                $results = $this->get_one($query);
                \Log::info($results);

                $response = $results[0];
                $result = '';
                \Log::info($response);
                foreach ($response as $key => $value ){
                    $result = $value;
                    \Log::info($result);
                }

                $data['movement_id'] = $cobranza->id_cobranza;
                //se configura un array con los estados de error conocidos
                $errors = array(
                    "-4 | Vendedor no existe"                               =>"-4",
                    "-5 | El campo RUC se encuentra Vacio"                  =>"-5",
                    "212 | otros errores no definidos en el procedimiento"  =>"212",
                );

                $check = array_search($result, $errors);
                \Log::info($check);
                if(!$check){
                    $data['error'] = false;
                    $data['status'] = $result;
                    return $data;
                }else{

                    $message = explode("|", $check);

                    $data['error'] = true;
                    $data['status'] = $check;
                    $data['code'] = $message[0];
                    return $data;
                }
            }
        }catch (\Exception $e){
            $response['error']   = true;
            $response['status']  = '212';
            $response['code']    = '212';
            \Log::warning('[Eglobal - Cliente]',['result'=>$e]);
            return $response;
        }

    }

    /**
     * COBRANZAS a favor: Método P_COBRO_RECIBO_A_FAVOR
     *
    */
    public function sendCobranzasFavor(){
        try
        {
            $user_id=58;
            
            $cobranzas = \DB::table('mt_recibos_cobranzas')
            ->select('movements.id as id_cobranza', 'mt_recibos.recibo_nro as recibo', 
            'boletas_depositos.fecha as fecha', 'boletas_depositos.monto as importe', 'business_groups.ruc')
            ->join('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_cobranzas.recibo_id')
            ->join('movements', 'movements.id', '=', 'mt_recibos.movements_id')
            ->join('boletas_depositos', 'boletas_depositos.id', '=', 'mt_recibos_cobranzas.boleta_deposito_id')
            ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
            ->join('business_groups', 'business_groups.id', '=', 'current_account.group_id')
            ->whereIn('movements.destination_operation_id', [666])
            ->whereNull('mt_recibos_cobranzas.ventas_cobradas')
            ->where('boletas_depositos.user_id', $user_id)
            ->orderBy('movements.id','ASC')
            ->take(20)
            ->get();
            dd($cobranzas);
            foreach($cobranzas as $cobranza){
                //proceed to export deposits to ondanet
                $recibo     =  $cobranza->recibo;
                $fecha      =  date("d-m-Y", strtotime($cobranza->fecha));
                $importe    =  $cobranza->importe;
                $pdv        =  $cobranza->ruc;
                
                $query = "
                SET NOCOUNT ON;
                SET ANSI_WARNINGS OFF;
                DECLARE @RC Numeric(25) 
                DECLARE @FECHA DATE SET @FECHA = (SELECT CONVERT(DATE, '$fecha', 103)) 
                EXECUTE @RC = [DBO].[P_COBRO_RECIBO_A_FAVOR] '9000', '$recibo', @FECHA, '$importe','$pdv', 'REC' 
                SELECT @RC";

                \Log::info($query);
                $results = $this->get_one($query);
                \Log::info($results);

                $response = $results[0];
                $result = '';
                \Log::info($response);
                foreach ($response as $key => $value ){
                    $result = $value;
                    \Log::info($result);
                }

                $data['movement_id'] = $cobranza->id_cobranza;
                //se configura un array con los estados de error conocidos
                $errors = array(
                    "-1 | Nro. de recibo ya existe para el tipo de Comprobante" =>"-1",
                    "-2 | Vendedor no existe"                                   =>"-2",
                    "-3 | Cobrador no existe"                                   =>"-3",
                    "-4 | Caja del vendedor no se encuentra"                    =>"-4",
                    "-5 | Tipo de comprobante no existe"                        =>"-5",
                    "-10| Deposito no existe"                                   =>"-10",
                    "-11| Tipo del Comprobante solo pueden ser REC Y RCASH"     =>"-11",
                    "-23 | Caja cerrada en la fecha"                            =>"-23",
                    "-26 | No se encuentra creado el cliente en ONDANET"        =>"-26",
                    "212 | otros errores no definidos en el procedimiento"      =>"212",
                );

                $check = array_search($result, $errors);
                \Log::info($check);
                if(!$check){
                    $data['error'] = false;
                    $data['status'] = $result;
                    return $data;
                }else{

                    $message = explode("|", $check);

                    $data['error'] = true;
                    $data['status'] = $check;
                    $data['code'] = $message[0];
                    return $data;
                }
            }
        }catch (\Exception $e){
            $response['error']   = true;
            $response['status']  = '212';
            $response['code']    = '212';
            \Log::warning('[Eglobal - Cliente]',['result'=>$e]);
            return $response;
        }

    }

    /**
     * Ventas Miniterminal: Método P_FACTURA_MINI_TERMINAL
     *
     * PARAMETROS
     * @PDV VARCHAR(20) -> Es el código cliente que se encuentra creado tanto en el ADMIN como en ONDANET para que salga a su nombre la factura y afecte extracto en ONDANET, si no se encuentra creado en ONDANET retornará un mensaje de error (-26)
     * @IDVENDEDOR VARCHAR(10) -> Es el N.º del Vendedor donde se efectúa la operación para que ingrese en la caja especificada, siempre mantendremos el vendedor: 4  (BILLETERO - MINI TERMINALES RED EGLOBAL)
     * @TIPOCOMPROBANTE VARCHAR(5) -> El tipo de comprobante utilizado, en este proceso.. mantendremos siempre el tipo de comprobante: COMIN (COBRANZA MINITERMINALES)
     * @NROVENTA VARCHAR(20) -> Es una numeración interna para la facturación (exenta) enviado secuencialmente, preferiblemente utilizando 14 dígitos
     * @FECHA VARCHAR(20) -> La fecha de la operación
     * @IDPRODUCTO1 VARCHAR(25) -> Es el código del producto, en este proceso.. mantendremos siempre el producto INT070
     * @IMPORTE NUMERIC(18,5) -> Es el importe total de la venta
     * @TIPO1 VARCHAR(100) -> Es un campo de observación, podemos mantener siempre 'VENTAS MINI-TERMINAL'
    */

    public function sendVentaMini($movement_id){
        try
        {
            $sale = \DB::table('miniterminales_sales')
            ->select('movements.id', 'business_groups.ruc as pdv', 'miniterminales_sales.fecha as date', 'movements.amount as importe','miniterminales_sales.nro_venta as numero_venta')
            ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
            ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
            ->join('business_groups', 'business_groups.id', '=', 'current_account.group_id')
            ->where('movements.id', $movement_id)
            ->first();

            $date=date("d-m-Y", strtotime($sale->date));
            
            $query = "SET NOCOUNT ON;
            SET ANSI_WARNINGS OFF;
            DECLARE @rv Numeric(25)
            DECLARE @FECHA DATE
            SET @FECHA = (SELECT CONVERT(DATE, '$date', 103))
            EXEC @rv = [DBO].[P_FACTURA_MINI_TERMINAL]
            '$sale->pdv', '4','COMIN','$sale->numero_venta',@FECHA,'INT070','$sale->importe', 'VENTAS MINI-TERMINAL'
            SELECT @rv";

            \Log::info($query);
            $results = $this->get_one($query);
            \Log::info($results);

            $response = $results[0];
            $result = '';
            \Log::info($response);
            foreach ($response as $key => $value ){
                $result = $value;
                \Log::info($result);
            }

            //se configura un array con los estados de error conocidos
            $errors = array(
                "-2 | Sucursal no existe"                                                               =>"-2",
                "-3 | Vendedor no existe"                                                               =>"-3",
                "-4 | Tipo de comprobante no existe"                                                    =>"-4",
                "-5 | Sucursal venta del vendedor no existe"                                            =>"-5",
                "-6 | Comprobante no habilitado.. solo puede ser COMIN"                                 =>"-6",
                "-9 | Verificar el usuario asignado al vendedor"                                        =>"-9",
                "-10 | Moneda no encontrada.. Ver configuración del sistema"                            =>"-10",
                "-11 | Sucursal descarga del producto del vendedor no existe"                           =>"-11",
                "-12 | Cobrador no existe"                                                              =>"-12",
                "-13 | Caja del vendedor no se encuentra"                                               =>"-13",
                "-14 | Numero de venta ya existe con el mismo tipo de comprobante y número de timbrado" =>"-14",
                "-16 | Producto no existe.. solo puede ser INT070 "                                     =>"-16",
                "-17 | IVA no existe.. verifique el tipo de IVA del producto"                           =>"-17",
                "-21 | Producto sin precio"                                                             =>"-21",
                "-23 | Caja cerrada en la fecha"                                                        =>"-23",
                "-26 | No se encuentra el código Cliente en ONDANET"                                    =>"-26",
                "-27 | El Nro. de venta no puede ser cero"                                              =>"-27",
                "212 | Otros errores no definidos ni citados en el procedimiento"                       =>"212",
            );

            $check = array_search($result, $errors);
            \Log::info($check);
            if(!$check){
                $data['error'] = false;
                $data['status'] = $result;
                return $data;
            }else{

                $message = explode("|", $check);

                $data['error'] = true;
                $data['status'] = $check;
                $data['code'] = $message[0];
                return $data;
            }

        }catch (\Exception $e){
            $response['error']   = true;
            $response['status']  = '212';
            $response['code']    = '212';
            \Log::warning('[Eglobal - Cliente]',['result'=>$e]);
            return $response;
        }

    }

    /**
     * COBRANZAS: Método P_FACTURA_MINI_TERMINAL
     *
    */
    public function registerCobranzas($recibo, $fecha, $importe, $forcobro, $ventas){
        try
        {

            $query = "
            SET NOCOUNT ON;
            SET ANSI_WARNINGS OFF;
            SET DATEFORMAT mdy;
            DECLARE @RC Numeric(18) 
            DECLARE @FECHA DATE SET @FECHA = (SELECT CONVERT(DATE, '$fecha', 103)) 
            EXECUTE @RC = [DBO].[P_COBRO_RECIBO] '9000', '$recibo', @FECHA, '$importe','$forcobro','$ventas' 
            SELECT @RC";

            \Log::info($query);
            $results = $this->get_one($query);
            \Log::info($results);

            $response = $results[0];
            $result = '';
            \Log::info($response);
            foreach ($response as $key => $value ){
                $result = $value;
                \Log::info($result);
            }

            //se configura un array con los estados de error conocidos
            $errors = array(
                "-1 | Nro. de recibo ya existe"                         =>"-1",
                "-2 | Vendedorno existe"                                =>"-2",
                "-3 | Cobrador no existe"                               =>"-3",
                "-4 | Caja del vendedor no se encuentra"                =>"-4",
                "-6 | Sin saldo pendiente"                              =>"-6",
                "-7 | El saldo es menor a lo cobrado"                   =>"-7",
                "-8 | Mas de un cliente para las facturas"              =>"-8",
                "-9 | Ciente no existe"                                 =>"-9",
                "-10 | Deposito no existe"                              =>"-10",
                "-23 | Caja cerrada en la fecha"                        =>"-23",
                "212 | otros errores no definidos en el procedimiento"  =>"212",
            );

            $check = array_search($result, $errors);
            \Log::info($check);
            if(!$check){
                $data['error'] = false;
                $data['status'] = $result;
                return $data;
            }else{

                $message = explode("|", $check);

                $data['error'] = true;
                $data['status'] = $check;
                $data['code'] = $message[0];
                return $data;
            }
        }catch (\Exception $e){
            $response['error']   = true;
            $response['status']  = '212';
            $response['code']    = '212';
            \Log::warning('[Eglobal - Cliente]',['result'=>$e]);
            return $response;
        }

    }

    /** FUNCIONES PRIVADAS COMUNES*/

    public function get_one($query){

        try
        {
            \DB::beginTransaction();
            $db     = \DB::connection('ondanet')->getPdo();
            $stmt   = $db->prepare($query);           
            $stmt->execute();

            $register = array();
            do
            {
                $register = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            while($stmt->nextRowset());
            /*$register = array();
            while ($stmt->columnCount()) {
                $register = $stmt->fetchAll(); //or, $pdo->fetchAll() 
                $stmt->nextRowset();
            }*/
            \DB::commit();

            return $register;
        }
        catch(\Exception $e)
        {   
            \DB::rollback();
            return $e;
        }


    }

    public function registerCobranzasaFavor($id_ondanet, $importe, $ventas){

        $query = "
                SET NOCOUNT ON;
                SET ANSI_WARNINGS OFF;
                DECLARE @RC Numeric(25) 
                EXECUTE @RC = [DBO].[P_AFECTAR_COBRO_RECIBO_A_FAVOR] '$importe', '$id_ondanet', '$ventas' 
                SELECT @RC";

        \Log::info($query);
        $results = $this->get_one($query);
        \Log::info($results);

        $response = $results[0];
        $result = '';
        \Log::info($response);
        foreach ($response as $key => $value ){
            $result = $value;
            \Log::info($result);
        }

        $errors = array(
            "-1 | Status Cobranza no Existe en ONDANET"                             =>"-1",
            "-6 | Sin saldo pendiente"                                              =>"-6",
            "-7 | El saldo es menor a lo cobrado"                                   =>"-7",
            "-8 | Mas de un cliente para los status ventas"                         =>"-8",
            "-9 | Cliente no existe"                                                =>"-9",
            "-10| El Cliente de la cobranza es distinto al CLiente de la venta"     =>"-10",
            "-11| El saldo del recibo es 0"                                         =>"-11",
            "-12 | El saldo del recibo es menor al importe que se quiere afectar"   =>"-12",
            "212 | otros errores no definidos en el procedimiento"                  =>"212",
        );

        $check = array_search($result, $errors);

        //\Log::info($check);

        if(!$check){
            $data['error'] = false;
            $data['status'] = $result;
            return $data;
        }else{

            $message = explode("|", $check);
            \Log::info($check);
            $data['error'] = true;
            $data['type_error'] = $check;
            $data['code'] = $message[0];
            return $data;
        }
    }

    public function registerRecibosaFavor($recibo, $fecha, $importe, $pdv, $comprobante, $forcobro){

        $query = "
                SET NOCOUNT ON;
                SET ANSI_WARNINGS OFF;
                SET DATEFORMAT mdy;
                DECLARE @RC Numeric(25) 
                DECLARE @FECHA DATE SET @FECHA = (SELECT CONVERT(DATE, '$fecha', 103)) 
                EXECUTE @RC = [DBO].[P_COBRO_RECIBO_A_FAVOR] '9000', '$recibo', @FECHA, '$importe','$pdv', '$comprobante', '$forcobro'
                SELECT @RC";

        /*print_r($query);
        die();*/
        \Log::info($query);
        $results = $this->get_one($query);
        \Log::info($results);

        $response = $results[0];
        $result = '';
        \Log::info($response);
        foreach ($response as $key => $value ){
            $result = $value;
            \Log::info($result);
        }

        //se configura un array con los estados de error conocidos
        $errors = array(
            "-1 | Nro. de recibo ya existe para el tipo de Comprobante" =>"-1",
            "-2 | Vendedor no existe"                                   =>"-2",
            "-3 | Cobrador no existe"                                   =>"-3",
            "-4 | Caja del vendedor no se encuentra"                    =>"-4",
            "-5 | Tipo de comprobante no existe"                        =>"-5",
            "-10| Deposito no existe"                                   =>"-10",
            "-11| Tipo del Comprobante solo pueden ser REC Y RCASH"     =>"-11",
            "-23 | Caja cerrada en la fecha"                            =>"-23",
            "-26 | No se encuentra creado el cliente en ONDANET"        =>"-26",
            "212 | otros errores no definidos en el procedimiento"      =>"212",
        );

        $check = array_search($result, $errors);

        //\Log::info($check);

        if(!$check){
            $data['error'] = false;
            $data['status'] = $result;
            return $data;
        }else{
            \Log::info($check);
            $message = explode("|", $check);

            $data['error'] = true;
            $data['status'] = $check;
            $data['code'] = $message[0];
            return $data;
        }
    }

}