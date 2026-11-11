<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            // Asegúrate de apuntar a la ruta que configuramos en Vue
            $frontendUrl = "http://localhost:5173/email/verify";

            $userId = $notifiable->getKey();
            $vueUrl = "{$frontendUrl}/{$userId}";

            return (new MailMessage)
                ->subject('Verifica tu dirección de correo electrónico - LigaPro')
                ->line('Por favor haz clic en el botón de abajo para verificar tu cuenta.')
                ->action('Verificar Correo Electrónico', $vueUrl)
                ->line('Si no creaste una cuenta, no se requiere ninguna acción adicional.');
        });
    }
}
