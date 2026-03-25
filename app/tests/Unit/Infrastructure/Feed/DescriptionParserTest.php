<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Feed;

use App\Infrastructure\Http\Feed\DescriptionParser;
use PHPUnit\Framework\TestCase;

final class DescriptionParserTest extends TestCase
{
    private DescriptionParser $parser;

    protected function setUp(): void
    {
        $this->parser = new DescriptionParser();
    }

    public function test_parse__when_structured_technique_and_dimensions__should_extract_both(): void
    {
        $description = "Técnica: Acuarela\nMedidas: 30 x 40 cm\nSe entrega sin enmarcar.";

        $result = $this->parser->parse($description);

        $this->assertSame('Acuarela', $result['technique']);
        $this->assertSame('30 x 40 cm', $result['dimensions']);
    }

    public function test_parse__when_medidas_de_la_estampa__should_extract_dimensions(): void
    {
        $description = "Técnica: Fotopolímero\nMedidas de la estampa: 50 x 48 cm\nFirmado y numerado.";

        $result = $this->parser->parse($description);

        $this->assertSame('Fotopolímero', $result['technique']);
        $this->assertSame('50 x 48 cm', $result['dimensions']);
    }

    public function test_parse__when_technique_only__should_return_null_dimensions(): void
    {
        $description = "Técnica: Acuarela montada sobre bastidor de madera de 11 cm de diámetro";

        $result = $this->parser->parse($description);

        $this->assertSame('Acuarela montada sobre bastidor de madera de 11 cm de diámetro', $result['technique']);
        $this->assertNull($result['dimensions']);
    }

    public function test_parse__when_no_structured_fields__should_return_nulls(): void
    {
        $description = "Tote bag 100% algodón";

        $result = $this->parser->parse($description);

        $this->assertNull($result['technique']);
        $this->assertNull($result['dimensions']);
    }

    public function test_parse__when_empty_description__should_return_nulls(): void
    {
        $result = $this->parser->parse('');

        $this->assertNull($result['technique']);
        $this->assertNull($result['dimensions']);
    }

    public function test_parse__when_technique_has_extra_spaces__should_trim(): void
    {
        $description = "Técnica:  Print sobre papel de acuarela de 300 gr  \nMedidas:  21,5 X 29 cm  ";

        $result = $this->parser->parse($description);

        $this->assertSame('Print sobre papel de acuarela de 300 gr', $result['technique']);
        $this->assertSame('21,5 X 29 cm', $result['dimensions']);
    }

    public function test_parse__when_real_feed_format__should_extract_correctly(): void
    {
        $description = "Técnica: Aguafuerte, aguatinta iluminada y linografía\n"
            . "Medidas de la estampa: 50 x 48 cm\n"
            . "Cada estampa está firmada y numerada.\n"
            . "Se entrega sin enmarcar.\n"
            . "Los envíos se realizan los martes de cada semana.";

        $result = $this->parser->parse($description);

        $this->assertSame('Aguafuerte, aguatinta iluminada y linografía', $result['technique']);
        $this->assertSame('50 x 48 cm', $result['dimensions']);
    }
}
