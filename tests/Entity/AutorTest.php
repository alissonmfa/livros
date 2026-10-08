<?php

namespace App\Tests\Entity;

use App\Entity\Autor;
use App\Entity\Livro;
use PHPUnit\Framework\TestCase;

class AutorTest extends TestCase
{
    public function testCadastro(): void
    {
        $autor = new Autor();
        $livro = new Livro();

        $autor->setNome('J.R.R. Tolkien');
        $autor->addLivro($livro);

        $this->assertSame('J.R.R. Tolkien', $autor->getNome());
        $this->assertEquals([$livro], $autor->getLivros()->getValues());
        $this->assertEquals([$autor], $livro->getAutores()->getValues());
    }

    public function testEdicao(): void
    {
        $autor = new Autor();
        $livroAntigo = new Livro();
        $autor->setNome('J.R.R. Tolkien');
        $autor->addLivro($livroAntigo);

        $livroNovo = new Livro();
        $autor->setNome('Christopher Tolkien');
        $autor->removeLivro($livroAntigo);
        $autor->addLivro($livroNovo);

        $this->assertSame('Christopher Tolkien', $autor->getNome());
        $this->assertEquals([$livroNovo], $autor->getLivros()->getValues());
        $this->assertEquals([$autor], $livroNovo->getAutores()->getValues());
        $this->assertCount(0, $livroAntigo->getAutores());
    }
}
