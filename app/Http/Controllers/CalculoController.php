<?php

namespace App\Http\Controllers;
use DB;
use App\Models\Mayor;
use App\Models\PlanCuenta;
use DateTime;


use Illuminate\Http\Request;

class CalculoController extends Controller
{
    function calculo(){
        set_time_limit(0);
        $now = new DateTime();
        $milisegundos = (int)$now->format('u');
        echo "<h6>Hora Inicio Proceso: " . date("Y-m-d H:i:s").".".$milisegundos."</h6>";        
        $start_time = microtime(true);

        $cuentas = PlanCuenta::where('nivel', '=', 5)->get()->take(30);
        foreach($cuentas as $cuenta){
            $mayores = Mayor::select(
                                DB::raw('YEAR(fecha) year'),
                                DB::raw('MONTH(fecha) month'), 
                                DB::raw('SUM(monto) monto'), 
                                DB::raw('COUNT(*) numero'))
                        ->where('cuenta', '=', $cuenta->codigo)
                        ->whereBetween('fecha', ['2018-01-01', '2020-12-01'])
                        ->groupBy('year', 'month')
                        ->get();
            foreach($mayores as $mayor){
                self::imprimirRegistro($cuenta, $mayor);        
            }
        }
        $end_time = microtime(true);
        $duration=$end_time-$start_time;
        $hours = (int)($duration/60/60);
        $minutes = (int)($duration/60)-$hours*60;
        $seconds = (int)$duration-$hours*60*60-$minutes*60;
        $now = new DateTime();
        $milisegundos_fin = (int)$now->format('u');
        echo "<h6>Hora Fin Proceso: " .date("Y-m-d H:i:s").".".$milisegundos_fin." segundos</h6>";
        $milisegundos = $milisegundos - $milisegundos_fin;
        $milisegundos = abs($milisegundos);
        echo "Tiempo empleado para terminar el proceso: <strong>" . $hours.' horas, '.$minutes.' minutos y '.$seconds.' segundos y '.$milisegundos .' milisegundos</strong>';
    }

    public function imprimirRegistro($cuenta, $mayor){
        echo "Cuenta -> ".$cuenta->codigo ." ".$cuenta->nombre ."---> #(".$mayor['numero'].") Bs.".$mayor['monto']." ". $mayor['month']." ". $mayor['year']." <br>";
    }
}
