<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class viagens extends Model
{
    use HasFactory;
    protected $fillable = ['Nome_do_Solicitante', 'Destino', 'Data_de_Ida' , 'Data_de_Volta', 'status'];

    public function rules () {
        return [
            'Nome_do_Solicitante' => 'required|string',
            'Destino' => 'required|string',
            'Data_de_Ida' => 'required|date',
            'Data_de_Volta' => 'required|date',
            'status' => 'required|string'
        ];
    }

    public function feedback() {
        return  [
            'required' => 'Obrigatório preencher esse campo',
            'Nome_do_Solicitante.string' => 'Esse campo aceita apenas string',
            'Destino.string' => 'Esse campo aceita apenas string',
            'Data_de_Ida.date' => 'Esse campo aceita apenas datas (siga esse padrão: DD/MM/YYYY)',
            'Data_de_Volta.date' => 'Esse campo aceita apenas datas (siga esse padrão: DD/MM/YYYY)',
            'status.string' => 'Esse campo aceita apenas string.',
        ];
    }
}
