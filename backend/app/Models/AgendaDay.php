<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaDay extends Model
{
    protected $fillable = ['dia', 'nombre', 'fecha', 'mes', 'titulo', 'poster_path', 'poster_visible', 'table_visible'];

    protected $casts = [
        'poster_visible' => 'boolean',
        'table_visible'  => 'boolean',
    ];

    /**
     * Ruta pública relativa del póster subido (o null si no hay uno).
     * Relativa a propósito: funciona igual en local y en producción, sin
     * depender de APP_URL.
     */
    public function posterUrl(): ?string
    {
        return $this->poster_path ? '/storage/'.$this->poster_path : null;
    }
}
