<?php

namespace Tests\Unit;

use App\Services\WhatsAppService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WhatsAppServiceTest extends TestCase
{
    public static function numerosSenegalais(): array
    {
        return [
            'local avec espaces' => ['77 123 45 67'],
            'local collé' => ['771234567'],
            'avec +221' => ['+221 77 123 45 67'],
            'avec 00221' => ['00221771234567'],
            'avec 221 sans +' => ['221771234567'],
            'avec tirets' => ['77-123-45-67'],
        ];
    }

    #[DataProvider('numerosSenegalais')]
    public function test_normalise_sans_doubler_l_indicatif(string $saisie): void
    {
        $this->assertSame('221771234567', (new WhatsAppService)->normaliserNumero($saisie));
    }

    public function test_conserve_un_numero_etranger_international(): void
    {
        $this->assertSame('33612345678', (new WhatsAppService)->normaliserNumero('+33 6 12 34 56 78'));
    }

    public function test_refuse_un_numero_invalide(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new WhatsAppService)->normaliserNumero('12345');
    }

    public function test_encode_le_message_dans_le_lien(): void
    {
        $lien = (new WhatsAppService)->lien('771234567', 'Bonjour Awa ! À très bientôt & merci');

        $this->assertSame('https://wa.me/221771234567?text=Bonjour%20Awa%20%21%20%C3%80%20tr%C3%A8s%20bient%C3%B4t%20%26%20merci', $lien);
    }

    public function test_remplace_les_variables_du_modele(): void
    {
        $texte = (new WhatsAppService)->remplir('Bonjour [Prénom], le [date] à [heure].', ['Prénom' => 'Awa', 'date' => '03/11', 'heure' => '10h00']);

        $this->assertSame('Bonjour Awa, le 03/11 à 10h00.', $texte);
    }
}
