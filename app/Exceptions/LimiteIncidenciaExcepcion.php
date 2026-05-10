<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LimiteIncidenciaExcepcion extends HttpException
{
    public function __construct(string $mensaje)
    {
        parent::__construct(422, $mensaje);
    }

    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => $this->getMessage(), 'tipo' => 'limite_incidencia'], 422);
        }

        return redirect()->back()->with('error', $this->getMessage());
    }
}
