<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
      
    // }

    public function index(Request $request)
    {
        //Valida request 
        $valida = TransactionService::validar($request->all());
        //verifica se existe erros
        if($valida->fails()){
           //retorna os erros
            return response()->json($valida->errors());
                    
        }
            //Faz o calculo e retorna as parcelas
             $parcelas = TransactionService::parcelar($request);
             return response()->json($parcelas);
       }
    //
}
