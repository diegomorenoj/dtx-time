<?php

namespace App\Http\Controllers;

use JWTAuth;
use Validator;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    /**
     * @var
     */
    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $data = Budget::get();
        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List Budgets Successfully'
        ];
        
        return response()->json($response, 200);
    }
     
    public function MainBudgets()
    {       
        log::info("---- presupuesto principal -----");

        $mainBudgets = Budget::where('is_main', true)->get();

        
        $response = [
            'success' => true,
            'data' => $mainBudgets,
            'message' => 'List Budgets Successfully'
        ];
        return response()->json($response, 200);
    }

    /**
     * Store a new Budget
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * 
     */
    public function store(Request $request) {
        
        $input = $request->all();
        log::info($input);
        $budget = Budget::create($input);

        $response = [
            'success' => true,
            'data' => $budget,
            'message' => "Success Created!"
        ];

        return response()->json($response, 200);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeOld(Request $request)
    {
        try
        {
            $input = $request->all();

            $rules = [
                'anio' => 'required',
                'value' => 'required',
            ];

            $messages = [
                'anio.required' => 'El año es obligatorio.',
                'value.required' => 'El valor es obligatorio.',
            ];

            // VALIDACIONES
            $validator = Validator::make($input, $rules, $messages);

            if ($validator->fails()) {

                $response = [
                    'success' => false,
                    'data' => 'Validation Error.',
                    'message' => $validator->errors()
                ];

                return response()->json($response, 500);
            }

            // VALIDAR QUE EL PRESUPUESTO NO EXISTA
            $budgetsAux = DB::table('budgets AS b')
                ->select('b.*', DB::raw('null AS budgets'))
                ->where('b.anio',$input['anio'])
                ->whereNull('city')
                ->whereNull('area')
                ->get();
            
            if($budgetsAux->count() > 0){
                $response = [
                    'success' => true,
                    'data' => $budgetsAux,
                    'message' => 'El presupuesto para este año ya existe'
                ];
        
                return response()->json($response, 500);
            }

            DB::beginTransaction();

            $budget = new Budget;
            $budget->anio = $input['anio'];
            $budget->value = $input['value'];
            // GUARDAR
            $budget->save();

            DB::commit();

            $response = [
                'success' => true,
                'data' => $budget,
                'message' => 'Presupuesto guardado correctamente'
            ];

            return response()->json($response, 200);
        } catch(\Exception $e){
            DB::rollback();
            $response = [
                'success' => false,
                'data' => $e->getMessage(),
                'message' => 'Error al crear el presupuesto.'
            ];
            return response()->json($response, 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeBudgets(Request $request)
    {
        try
        {
            $input = $request->all();

            $rules = [
                'budgets' => 'required',
            ];

            $messages = [
                'budgets.required' => 'La distribución del presupuesto es obligatoria.',
            ];

            // VALIDACIONES
            $validator = Validator::make($input, $rules, $messages);

            if ($validator->fails()) {

                $response = [
                    'success' => false,
                    'data' => 'Validation Error.',
                    'message' => $validator->errors()
                ];

                return response()->json($response, 500);
            }

            // VALIDAR QUE EL PRESUPUESTO NO EXISTA
            $budgetsAux = DB::table('budgets AS b')
                ->select(
                    'b.*'
                    , DB::raw('null AS budgets')
                )
                ->where('b.anio', $input['anio'])
                ->whereNull('city')
                ->whereNull('area')
                ->get();
            
            if($budgetsAux->count() == 0){
                $response = [
                    'success' => true,
                    'data' => $budgetsAux,
                    'message' => 'El presupuesto para este año no existe.'
                ];
        
                return response()->json($response, 500);
            }

            if($input['budgets'] != null)
            {
                DB::beginTransaction();

                // ELIMINAR EL EXISTENTE Y VOLVER A GUARDAR
                Budget::where('anio', $input['anio'])
                    ->whereNotNull('city')
                    ->whereNotNull('area')
                    ->delete();

                foreach ($input['budgets'] as $item) {
                    $budget = new Budget;
                    $budget->anio  = $input['anio'];
                    $budget->value = $item['value'];
                    $budget->city  = $item['city'];
                    $budget->area  = $item['area'];
                    $budget->save();
                }

                DB::commit();
            }
            else
            {
                $response = [
                    'success' => true,
                    'data' => null,
                    'message' => 'No hay distribución de presupuesto para guardar.'
                ];
        
                return response()->json($response, 500);
            }

            $response = [
                'success' => true,
                'data' => $budget,
                'message' => 'Distribución de presupuesto guardada correctamente.'
            ];

            return response()->json($response, 200);
        } catch(\Exception $e){
            DB::rollback();
            $response = [
                'success' => false,
                'data' => $e->getMessage(),
                'message' => 'Error al crear la distribución presupuestal.'
            ];
            return response()->json($response, 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Budget  $budget
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Budget $budget)
    {
        $input = $request->all();

        $rules = [
            'anio' => 'required',
            'value' => 'required',
        ];

        $messages = [
            'anio.required' => 'El año es obligatorio.',
            'value.required' => 'El valor es obligatorio.',
        ];

        // VALIDACIONES
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {

            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, 500);
        }

        $budget->anio = $input['anio'];
        $budget->value = $input['value'];
        $budget->city = $input['city'];
        $budget->area = $input['area'];

        // GUARDAR
        $budget->save();

        $response = [
            'success' => true,
            'data' => $budget,
            'message' => 'Presupuesto actualizado correctamente.'
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Budget  $budget
     * @return \Illuminate\Http\Response
     */
    public function destroy(Budget $budget)
    {
        try
        {
            DB::beginTransaction();

            // ELIMINAR EL EXISTENTE Y VOLVER A GUARDAR
            Budget::where('anio', $budget->anio)
                ->whereNotNull('city')
                ->whereNotNull('area')
                ->delete();

            $budget->delete();
            DB::commit();
            $data = null;

            $response = [
                'success' => true,
                'data' => $budget,
                'message' => 'Presupuesto eliminado correctamente.'
            ];

            return response()->json($response, 200);
        } catch(\Exception $e){
            DB::rollback();
            $response = [
                'success' => false,
                'data' => $e->getMessage(),
                'message' => 'Error al eliminar la distribución presupuestal.'
            ];
            return response()->json($response, 404);
        }
    }
}
