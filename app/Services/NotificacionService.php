<?php

namespace App\Services;

use App\Enums\TipoNotificacion;
use App\Mail\IncidenciaAlertaSLAMail;
use App\Mail\IncidenciaAsignadaMail;
use App\Mail\IncidenciaCambioEstadoMail;
use App\Mail\IncidenciaConfirmadaMail;
use App\Mail\IncidenciaSolicitudInfoMail;
use App\Models\Incidencia;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificacionService
{
    public function enviarConfirmacion(Incidencia $incidencia): void
    {
        $email = $incidencia->emailDestino();
        if (!$email) return;

        $notificacion = $this->registrar(
            incidencia: $incidencia,
            tipo: TipoNotificacion::ConfirmacionRegistro,
            email: $email,
        );

        $this->enviar($notificacion, new IncidenciaConfirmadaMail($incidencia));
    }

    public function enviarCambioEstado(Incidencia $incidencia): void
    {
        $email = $incidencia->emailDestino();
        if (!$email) return;

        $notificacion = $this->registrar(
            incidencia: $incidencia,
            tipo: TipoNotificacion::CambioEstado,
            email: $email,
            userId: $incidencia->user_id,
        );

        $this->enviar($notificacion, new IncidenciaCambioEstadoMail($incidencia));
    }

    public function enviarAsignacion(Incidencia $incidencia, User $coordinador): void
    {
        $notificacion = $this->registrar(
            incidencia: $incidencia,
            tipo: TipoNotificacion::Asignacion,
            email: $coordinador->email,
            userId: $coordinador->id,
        );

        $this->enviar($notificacion, new IncidenciaAsignadaMail($incidencia, $coordinador));
    }

    public function enviarSolicitudInfo(Incidencia $incidencia, string $mensaje): void
    {
        $email = $incidencia->emailDestino();
        if (!$email) return;

        $notificacion = $this->registrar(
            incidencia: $incidencia,
            tipo: TipoNotificacion::SolicitudInfo,
            email: $email,
            userId: $incidencia->user_id,
        );

        $this->enviar($notificacion, new IncidenciaSolicitudInfoMail($incidencia, $mensaje));
    }

    public function enviarAlertaSla(Incidencia $incidencia): void
    {
        $emails = array_filter([
            $incidencia->asignadoA?->email,
            $incidencia->area?->subdirector?->email,
        ]);

        foreach ($emails as $email) {
            $notificacion = $this->registrar(
                incidencia: $incidencia,
                tipo: TipoNotificacion::AlertaSla,
                email: $email,
            );
            $this->enviar($notificacion, new IncidenciaAlertaSLAMail($incidencia));
        }
    }

    private function registrar(
        Incidencia $incidencia,
        TipoNotificacion $tipo,
        string $email,
        ?int $userId = null,
    ): Notificacion {
        return Notificacion::create([
            'incidencia_id'      => $incidencia->id,
            'user_id'            => $userId,
            'destinatario_email' => $email,
            'tipo'               => $tipo,
            'asunto'             => $tipo->asunto($incidencia->folio),
        ]);
    }

    private function enviar(Notificacion $notificacion, Mailable $mail): void
    {
        try {
            Mail::to($notificacion->destinatario_email)->send($mail);
            $notificacion->update(['enviada_at' => now()]);
        } catch (\Throwable $e) {
            Log::error("Error enviando notificación [{$notificacion->id}]: {$e->getMessage()}");
        }
    }
}
