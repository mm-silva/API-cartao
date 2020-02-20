<?php

namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TransactionService
{
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
      
    // }

    public static function validar($request)
    {   
        //data de hoje 
        $today = Carbon::now()->format('Y-m-d');
        //regras
        $rules =  [ 'nome'           => "required|min:3|max:50|not_regex:/([0-9])/",
                    'cartao_credito' => 'required|min:16|max:16',
                    'data_compra'    => "required|date_format:Y-m-d|after_or_equal:$today",
                    'valor_compra'   => 'required|numeric|min:1',
                    'parcelamento'   => 'required|numeric|integer|min:1|max:12',
                    'bandeira'       => 'required|in:MASTER,VISA,AMEX',
                    'operacao'       => 'required|in:CREDITO'
                  ];
        //mensagens          
        $messages = ['cartao_credito.required' => 'Digite os numeros do cartao',
                     'cartao_credito.min' => 'Preencha todos os numeros do cartao',
                     'cartao_credito.max' => 'Você excedeu o numero máximo do cartao',
                     'nome.required' => 'Preencha o nome',
                     'nome.min' => 'Preencha todo nome',
                     'nome.max' => 'O nome deve ter menos que 50 caracteres',
                     'nome.not_regex' => 'Preencha o nome apenas com letras',
                     'data_compra.required' => 'Preencha a data da compra',
                     'data_compra.after_or_equal' => 'A data da compra só pode ser feita apartir de hoje',
                     'data_compra.date_format' => 'A data está fora de formato (Y-m-d)',
                     'valor_compra.required' => 'Preencha o valor da compra',
                     'valor_compra.numeric' => 'Preencha o valor da compra somente com numeros',
                     'valor_compra.min' => 'Valor da compra deve ser maior que zero',
                     'parcelamento.required'  => 'Preencha o numero de parcelamento',
                     'parcelamento.numeric'  => 'Preencha somente numeros para o numero de parcelamento',
                     'parcelamento.integer'  => 'Preencha somente numeros inteiros para o numero de parcelamento',
                     'parcelamento.min'  => 'O numero de parcelas deve ser maior que zero',
                     'parcelamento.max'  => 'O numero maximo de parcelas e de 12x',
                     'bandeira.required' =>  'Preencha a bandeira',
                     'bandeira.in' =>  'Preencha alguma bandeira disponivel',
                     'operacao.required' => 'Preencha a operacao',
                     'operacao.in' => 'Escolha alguma operacao disponivel'
                     ];

                     $validator = Validator::make($request,$rules,$messages);

                     //retorna um objeto para validação
                     return $validator;
        }

        public static function parcelar(Request $request){

            //O desconto é dado de acordo com a bandeira
            switch($request->bandeira){
                case 'MASTER':
                    $desconto = 5;
                break;
                case 'VISA':
                    $desconto = 3;
                break;
                case 'AMEX':
                    $desconto = 4.9;
                break;
            }
                    //formatando      valor
             $total = number_format($request->valor_compra, 2, '.', '');
                    //formatando    valor com desconto             
             $valor = number_format($total - ($desconto * $total)/100, 2, '.', '');
                        // formatando  valor de parcelas
             $parcela = number_format($valor/$request->parcelamento, 2, '.', '');
            // separando dia,mes e ano
            $data =  explode('-',$request->data_compra);
            $ano = $data[0];
            $mes = $data[1];
            $dia = $data[2];
            //array 
            $pagamentos = [
               'transacao_success' => true,
               'cartao_credito_mascarado' => substr($request->cartao_credito, -4),
               'total_parcelas' => intval($request->parcelamento),
               'pagamentos' => null,
               'valor_compra' => floatval($total),
               'valor_pagamento' => floatval($valor),
               'percentual_repasse' => $desconto .'%'
           ];
           
           // parcelas
            for($i = 1; $i <= $request->parcelamento; $i++){
                //incrementando mais um no mes
                $mes++;
                $mes = ($mes == 13 ? 1 : $mes);

                $pagamentos['pagamentos'][] = [
                  'data_pagamento' => $ano.'-'.$mes.'-'.$dia,
                  'valor'   => floatval($parcela),
                  'parcela' =>  $i 
                ];
           
    
            }
            //retorna o array com todas as parcelas
            return $pagamentos;

        }
    
}
