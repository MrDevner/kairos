<?php

namespace App\Http\Requests;

use App\Models\Oficina;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DDJJStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_usuario'              => ['required', 'integer', 'exists:users,id'],
            'fecha_inicio'            => ['required', 'date'],
            'fecha_fin'               => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'observaciones'           => ['nullable', 'string', 'max:1000'],

            'horarios'                => ['required', 'array', 'min:1'],
            'horarios.*.id_designacion' => ['required', 'integer', 'exists:designaciones,id'],
            'horarios.*.dia_semana'   => ['required', 'in:lunes,martes,miercoles,jueves,viernes'],
            'horarios.*.hora_entrada' => ['required', 'date_format:H:i'],
            'horarios.*.hora_salida'  => ['required', 'date_format:H:i', 'after:horarios.*.hora_entrada'],
            'horarios.*.modalidad'    => ['required', 'in:presencial,remoto'],
            'horarios.*.id_institucion_externa' => ['nullable', 'integer', 'exists:instituciones,id'],
            'horarios.*.id_dependencia'         => ['nullable', 'integer', 'exists:dependencias,id'],
            'horarios.*.id_edificio'            => ['nullable', 'integer', 'exists:edificios,id'],
            'horarios.*.id_oficina'             => ['nullable', 'integer', 'exists:oficinas,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_usuario.required'             => 'El trabajador es obligatorio.',
            'id_usuario.exists'               => 'El trabajador seleccionado no existe.',
            'fecha_inicio.required'           => 'La fecha de inicio es obligatoria.',
            'fecha_fin.after_or_equal'        => 'La fecha de fin debe ser posterior o igual a la de inicio.',
            'horarios.required'               => 'Debe declarar al menos un bloque horario.',
            'horarios.*.id_designacion.required' => 'Cada horario debe estar asociado a una designación.',
            'horarios.*.id_designacion.exists'   => 'Designación inválida.',
            'horarios.*.dia_semana.required'  => 'El día de la semana es obligatorio.',
            'horarios.*.dia_semana.in'        => 'El día debe ser un día hábil (lunes a viernes).',
            'horarios.*.hora_entrada.required' => 'La hora de entrada es obligatoria.',
            'horarios.*.hora_salida.required'  => 'La hora de salida es obligatoria.',
            'horarios.*.hora_salida.after'     => 'La hora de salida debe ser posterior a la de entrada.',
            'horarios.*.modalidad.required'    => 'La modalidad es obligatoria.',
            'horarios.*.modalidad.in'          => 'La modalidad debe ser presencial o remoto.',
            'horarios.*.id_edificio.exists'    => 'El edificio seleccionado no existe.',
            'horarios.*.id_oficina.exists'     => 'La oficina seleccionada no existe.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ((array) $this->input('horarios', []) as $i => $h) {
                if (!empty($h['id_oficina']) && !empty($h['id_edificio'])) {
                    $oficina = Oficina::find($h['id_oficina']);
                    if ($oficina && $oficina->id_edificio !== (int) $h['id_edificio']) {
                        $validator->errors()->add(
                            "horarios.{$i}.id_oficina",
                            'La oficina seleccionada no pertenece al edificio indicado.'
                        );
                    }
                }
            }
        });
    }
}
