<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines (Español)
    |--------------------------------------------------------------------------
    */

    'accepted' => 'El campo :attribute debe ser aceptado.',
    'accepted_if' => 'El campo :attribute debe ser aceptado cuando :other sea :value.',
    'active_url' => 'El campo :attribute no es una URL válida.',
    'after' => 'El campo :attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => 'El campo :attribute debe ser una fecha posterior o igual a :date.',
    'alpha' => 'El campo :attribute solo debe contener letras.',
    'alpha_dash' => 'El campo :attribute solo debe contener letras, números, guiones y guiones bajos.',
    'alpha_num' => 'El campo :attribute solo debe contener letras y números.',
    'array' => 'El campo :attribute debe ser un conjunto.',
    'before' => 'El campo :attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
    'between' => [
        'array' => 'El campo :attribute tiene que tener entre :min - :max elementos.',
        'file' => 'El campo :attribute debe estar entre :min - :max kilobytes.',
        'numeric' => 'El campo :attribute debe estar entre :min - :max.',
        'string' => 'El campo :attribute debe estar entre :min - :max caracteres.',
    ],
    'boolean' => 'El campo :attribute debe tener un valor verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'current_password' => 'La contraseña es incorrecta.',
    'date' => 'El campo :attribute no es una fecha válida.',
    'date_equals' => 'El campo :attribute debe ser una fecha igual a :date.',
    'date_format' => 'El campo :attribute no corresponde al formato :format.',
    'declined' => 'El campo :attribute debe ser rechazado.',
    'decimal' => 'El campo :attribute debe tener :decimal decimales.',
    'different' => 'Los campos :attribute y :other deben ser diferentes.',
    'digits' => 'El campo :attribute debe tener :digits dígitos.',
    'digits_between' => 'El campo :attribute debe tener entre :min y :max dígitos.',
    'email' => 'El campo :attribute debe ser una dirección de correo válida.',
    'exists' => 'El campo :attribute seleccionado no es válido.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'max' => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string' => 'El campo :attribute no debe ser mayor que :max caracteres.',
    ],
    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string' => 'El campo :attribute debe contener al menos :min caracteres.',
    ],
    'numeric' => 'El campo :attribute debe ser un número entero o decimal válido.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
    'unique' => 'El campo :attribute ya ha sido registrado.',
    'url' => 'El campo :attribute debe ser una URL válida.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'nombre' => 'nombre',
        'precio_venta' => 'precio de venta',
        'monto_real' => 'monto real',
        'fecha' => 'fecha',
        'cantidad_entregada' => 'cantidad entregada',
        'cantidad_vendida_normal' => 'cantidad vendida',
        'cantidad_sobrante' => 'cantidad sobrante',
        'carrito_id' => 'carrito',
        'variante_id' => 'variante',
        'unidad_medida' => 'unidad de medida',
        'precio_unitario' => 'precio unitario',
        'cantidad' => 'cantidad',
    ],

];
