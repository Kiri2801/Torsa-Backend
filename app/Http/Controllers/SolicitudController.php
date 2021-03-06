<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GaranteSolicitud;
use App\Models\Socio;
use App\Models\Solicitud;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Controllers\SocioController;
use Illuminate\Support\Facades\DB;

class SolicitudController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        //traer datos de base datos de solic reg
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        //registra la solici
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        //mostrando una solicitud segun su codigo
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function RegistrarSolicitud(Request $request)
    {
        DB::beginTransaction();
        try
        {
            //Solicitud y socio
            if($request['solicitud']['codSocio']!=null)
            {
                $solicitud = new Solicitud([
                    'codUsuario'=>$request['solicitud']['codUsuario'],
                    'codSocio'=>$request['solicitud']['codSocio'],
                    'monto'=>$request['solicitud']['monto'],
                    'motivo'=>$request['solicitud']['motivo'],
                    'fecha'=>Carbon::today()->format('Y-m-d'),
                    'estado'=>'PVC'
                ]);

                $socio = Socio::find($request['solicitud']['codSocio']);
                if($socio->tipo=='Garante')
                {
                    $socio->tipo = 'Socio';
                    $socio->save();
                }

                $solicitud->save();
            }
            else
            {
                $socio = new Socio([
                    'dni' =>$request['socio']['dni'],
                    'nombre' =>$request['socio']['nombre'],
                    'apePaterno'=>$request['socio']['apePaterno'],
                    'apeMaterno' =>$request['socio']['apeMaterno'],
                    'fecNacimiento' =>$request['socio']['fecNacimiento'],
                    'telefono'=>$request['socio']['telefono'],
                    'domicilio' =>$request['socio']['domicilio'],
                    'tipo' =>'Socio',
                    'activo'=>1
                ]);
                $socio->save();
                $solicitud = new Solicitud([
                    'codSocio'=>$socio->codSocio,
                    'codUsuario'=>$request['solicitud']['codUsuario'],
                    'monto'=>$request['solicitud']['monto'],
                    'motivo'=>$request['solicitud']['motivo'],
                    'fecha'=>Carbon::today()->format('Y-m-d'),
                    'estado'=>'PVC'
                ]);
                $solicitud->save();
            }
            //Garante uno
            if($request['garante1']['codGarante']!=null)
            {
                $garanteSolicitudUno = new GaranteSolicitud([
                    'codSolicitud'=>$solicitud->codSolicitud,
                    'codSocio'=>$request['garante1']['codGarante']
                ]);
                $garanteSolicitudUno->save();
            }
            else
            {
                $garanteUno = new Socio([
                    'dni'=>$request['garante1']['dni'],
                    'nombre' =>$request['garante1']['nombre'],
                    'apePaterno'=>$request['garante1']['apePaterno'],
                    'apeMaterno' =>$request['garante1']['apeMaterno'],
                    // 'fecNacimiento' =>$request['garante1']['fecNacimiento'],
                    'fecNacimiento' =>'1900-01-01',
                    'telefono'=>$request['garante1']['telefono'],
                    'domicilio' =>$request['garante1']['domicilio'],
                    'tipo'=>'Garante',
                    'activo'=>1
                    ]);
                $garanteUno->save();

                $garanteSolicitudUno = new GaranteSolicitud([
                    'codSolicitud'=>$solicitud->codSolicitud,
                    'codSocio'=>$garanteUno->codSocio
                ]);
                $garanteSolicitudUno->save();
            }
            //Garante dos
            if($request['garante2']['codGarante']!=null)
            {
                $garanteSolicitudDos = new GaranteSolicitud([
                    'codSolicitud'=>$solicitud->codSolicitud,
                    'codSocio'=>$request['garante2']['codGarante']
                ]);
                $garanteSolicitudDos->save();
            }
            else
            {
                $garanteDos = new Socio([
                    'dni'=>$request['garante2']['dni'],
                    'nombre' =>$request['garante2']['nombre'],
                    'apePaterno'=>$request['garante2']['apePaterno'],
                    'apeMaterno' =>$request['garante2']['apeMaterno'],
                    // 'fecNacimiento' =>$request['garante2']['fecNacimiento'],
                    'fecNacimiento' =>'1900-01-01',
                    'telefono'=>$request['garante2']['telefono'],
                    'domicilio' =>$request['garante2']['domicilio'],
                    'tipo'=>'Garante',
                    'activo'=>1
                ]);
                $garanteDos->save();
                $garanteSolicitudDos = new GaranteSolicitud([
                    'codSolicitud'=>$solicitud->codSolicitud,
                    'codSocio'=>$garanteDos->codSocio
                ]);
                $garanteSolicitudDos->save();
            }

            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollback();

            $mensaje = $e->getMessage();

            return response($mensaje, 500);
        }
    }
    public function listarSolicitudesDia($codigo){
        //dd(request()->all());

        //$fechaDeHoy = Carbon::today()->format('Y-m-d');
        $fechaDeHoy = '2022-03-04';

        //$codigo = Auth::codUsuario();
        //$codigo = auth()->user()->codUsuario;

        $solicitudesDia = Solicitud::select('solicitud.codSolicitud','solicitud.codUsuario',
                        'solicitud.codSocio','solicitud.monto','solicitud.motivo','solicitud.fecha','solicitud.estado',
                        'socio.codSocio','socio.dni','socio.nombre','socio.apePaterno','socio.apeMaterno','socio.telefono')
                        ->join("usuario","usuario.codUsuario","solicitud.codUsuario")
                        ->join("socio","socio.codSocio","solicitud.codSocio")
                        ->where([
                            'solicitud.codUsuario'=>$codigo,
                            'solicitud.fecha'=>$fechaDeHoy,
                            'solicitud.estado'=>"PVC"
                            ])
                        ->orderBy('solicitud.codSolicitud')
                            ->get();

        return response()->json($solicitudesDia,200);
    }

}
