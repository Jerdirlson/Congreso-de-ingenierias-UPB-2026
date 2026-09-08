<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaItem extends Model
{
    protected $fillable = [
        'dia', 'bloque', 'tipo', 'hora', 'titulo',
        'ponente', 'pais', 'etiqueta', 'descripcion', 'lugar', 'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    /** Días válidos (misma clave que los tabs públicos de la agenda). */
    public const DIAS = ['mie', 'jue', 'vie', 'sab'];
}
