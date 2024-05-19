<?php

namespace Devdojo\Auth\Actions\TwoFactorAuth;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class GenerateQrCodeAndSecretKey
{
    /**
     * Generate new recovery codes for the user.
     *
     * @param  mixed  $user
     * @return void
     */
    public function __invoke($user): array
    {

        $google2fa = new Google2FA();
        $secret_key = $google2fa->generateSecretKey();

        //$secretKeyEncrypted = encrypt($secret_key);
        //echo $google2fa->generateSecretKey();

        // TODO - Make sure config('app.name') works below.
        $g2faUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret_key
        );

        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(800),
                new ImagickImageBackEnd()
            )
        );

        $qrcode_image = base64_encode($writer->writeString($g2faUrl));

        return [$qrcode_image, $secret_key];

    }
}
