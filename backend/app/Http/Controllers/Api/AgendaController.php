<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgendaDay;
use App\Models\AgendaItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AgendaController extends Controller
{
    /** GET /api/agenda — agenda pública: filas + metadatos de cada jornada. */
    public function index(): JsonResponse
    {
        $items = AgendaItem::orderBy('dia')
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        $porDia = [];
        foreach (AgendaItem::DIAS as $dia) {
            $porDia[$dia] = $items->where('dia', $dia)->values();
        }

        return response()->json([
            'items'   => $items,
            'por_dia' => $porDia,
            'dias'    => $this->daysPayload(),
        ]);
    }

    /** POST /api/admin/agenda — crear fila. */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        if (! array_key_exists('orden', $data) || $data['orden'] === null) {
            $data['orden'] = (int) AgendaItem::where('dia', $data['dia'])->max('orden') + 1;
        }

        $item = AgendaItem::create($data);

        return response()->json($item, 201);
    }

    /** PUT /api/admin/agenda/{item} — actualizar fila. */
    public function update(Request $request, AgendaItem $item): JsonResponse
    {
        $item->update($this->validated($request));

        return response()->json($item);
    }

    /** DELETE /api/admin/agenda/{item} — eliminar fila. */
    public function destroy(AgendaItem $item): JsonResponse
    {
        $item->delete();

        return response()->json(['ok' => true]);
    }

    /** PUT /api/admin/agenda/reorder — reordenar filas de un día. */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:agenda_items,id',
        ]);

        foreach ($data['ids'] as $orden => $id) {
            AgendaItem::where('id', $id)->update(['orden' => $orden]);
        }

        return response()->json(['ok' => true]);
    }

    /** PUT /api/admin/agenda/days/{dia} — editar título / visibilidad del póster. */
    public function updateDay(Request $request, string $dia): JsonResponse
    {
        $day = $this->findDay($dia);

        $data = $request->validate([
            'nombre'         => 'nullable|string|max:40',
            'fecha'          => 'nullable|string|max:20',
            'mes'            => 'nullable|string|max:30',
            'titulo'         => 'nullable|string|max:255',
            'poster_visible' => 'sometimes|boolean',
            'table_visible'  => 'sometimes|boolean',
        ]);

        $day->update($data);

        return response()->json($this->dayPayload($day));
    }

    /** POST /api/admin/agenda/days/{dia}/poster — subir un póster para el día. */
    public function uploadPoster(Request $request, string $dia): JsonResponse
    {
        $day = $this->findDay($dia);

        $request->validate([
            'poster' => 'required|image|mimes:jpeg,jpg,png,webp|max:8192', // 8 MB
        ]);

        // Borrar el anterior subido (si lo había) antes de reemplazar.
        if ($day->poster_path) {
            Storage::disk('public')->delete($day->poster_path);
        }

        $path = $request->file('poster')->store("agenda/posters", 'public');

        $day->update(['poster_path' => $path, 'poster_visible' => true]);

        return response()->json($this->dayPayload($day));
    }

    /** DELETE /api/admin/agenda/days/{dia}/poster — quitar el póster subido (vuelve al por defecto). */
    public function deletePoster(string $dia): JsonResponse
    {
        $day = $this->findDay($dia);

        if ($day->poster_path) {
            Storage::disk('public')->delete($day->poster_path);
            $day->update(['poster_path' => null]);
        }

        return response()->json($this->dayPayload($day));
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function findDay(string $dia): AgendaDay
    {
        abort_unless(in_array($dia, AgendaItem::DIAS, true), 404);

        return AgendaDay::firstOrCreate(['dia' => $dia]);
    }

    private function daysPayload(): array
    {
        $days = AgendaDay::all()->keyBy('dia');

        return collect(AgendaItem::DIAS)
            ->map(fn ($dia) => $this->dayPayload($days->get($dia) ?? new AgendaDay(['dia' => $dia, 'poster_visible' => true])))
            ->all();
    }

    private function dayPayload(AgendaDay $day): array
    {
        return [
            'dia'            => $day->dia,
            'nombre'         => $day->nombre,
            'fecha'          => $day->fecha,
            'mes'            => $day->mes,
            'titulo'         => $day->titulo,
            'poster_url'     => $day->posterUrl(),
            'poster_visible' => (bool) ($day->poster_visible ?? true),
            'table_visible'  => (bool) ($day->table_visible ?? true),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'dia'         => ['required', Rule::in(AgendaItem::DIAS)],
            'bloque'      => 'nullable|string|max:20',
            'tipo'        => 'nullable|string|max:30',
            'hora'        => 'nullable|string|max:40',
            'titulo'      => 'required|string|max:255',
            'ponente'     => 'nullable|string|max:255',
            'pais'        => 'nullable|string|max:80',
            'etiqueta'    => 'nullable|string|max:80',
            'descripcion' => 'nullable|string',
            'lugar'       => 'nullable|string|max:255',
            'orden'       => 'nullable|integer|min:0',
        ]);
    }
}
