<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DDJJRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_designacion'          => ['required', 'integer', 'exists:designaciones,id'],
            'fecha_inicio'            => ['required', 'date'],
            'fecha_fin'               => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'observaciones'           => ['nullable', 'string', 'max:1000'],

            'horarios'                => ['required', 'array', 'min:1'],
            'horarios.*.dia_semana'   => ['required', 'in:lunes,martes,miercoles,jueves,viernes'],
            'horarios.*.hora_entrada' => ['required', 'date_format:H:i'],
            'horarios.*.hora_salida'  => ['required', 'date_format:H:i', 'after:horarios.*.hora_entrada'],
            'horarios.*.modalidad'    => ['required', 'in:presencial,remoto'],
            'horarios.*.id_institucion_externa' => ['nullable', 'integer', 'exists:instituciones,id'],
            'horarios.*.id_dependencia'         => ['nullable', 'integer', 'exists:dependencias,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_designacion.required'         => 'La designación es obligatoria.',
            'id_designacion.exists'           => 'La designación seleccionada no existe.',
            'fecha_inicio.required'           => 'La fecha de inicio es obligatoria.',
            'fecha_fin.after_or_equal'        => 'La fecha de fin debe ser posterior o igual a la de inicio.',
            'horarios.required'               => 'Debe declarar al menos un bloque horario.',
            'horarios.*.dia_semana.required'  => 'El día de la semana es obligatorio.',
            'horarios.*.dia_semana.in'        => 'El día debe ser un día hábil (lunes a viernes).',
            'horarios.*.hora_entrada.required' => 'La hora de entrada es obligatoria.',
            'horarios.*.hora_salida.required'  => 'La hora de salida es obligatoria.',
            'horarios.*.hora_salida.after'     => 'La hora de salida debe ser posterior a la de entrada.',
            'horarios.*.modalidad.required'    => 'La modalidad es obligatoria.',
            'horarios.*.modalidad.in'          => 'La modalidad debe ser presencial o remoto.',
        ];
    }
}
