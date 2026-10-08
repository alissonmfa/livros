<?php

namespace App\Tests\Entity;

use App\Entity\Assunto;
use App\Entity\Livro;
use PHPUnit\Framework\TestCase;

class AssuntoTest extends TestCase
{
    public function testCadastro(): void
    {
        $assunto = new Assunto();
        $livro = new Livro();

        $assunto->setDescricao('Fantasia');
        $assunto->addLivro($livro);

        $this->assertSame('Fantasia', $assunto->getDescricao());
        $this->assertEquals([$livro], $assunto->getLivros()->getValues());
        $this->assertEquals([$assunto], $livro->getAssuntos()->getValues());
    }

    public function testEdicao(): void
    {
        $assunto = new Assunto();
        $livroAntigo = new Livro();
        $assunto->setDescricao('Fantasia');
        $assunto->addLivro($livroAntigo);

        $livroNovo = new Livro();
        $assunto->setDescricao('Mitologia');
        $assunto->removeLivro($livroAntigo);
        $assunto->addLivro($livroNovo);

        $this->assertSame('Mitologia', $assunto->getDescricao());
        $this->assertEquals([$livroNovo], $assunto->getLivros()->getValues());
        $this->assertEquals([$assunto], $livroNovo->getAssuntos()->getValues());
        $this->assertCount(0, $livroAntigo->getAssuntos());
    }
}
