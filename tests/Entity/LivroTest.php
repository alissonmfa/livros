<?php

namespace App\Tests\Entity;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use PHPUnit\Framework\TestCase;

class LivroTest extends TestCase
{
    public function testCadastro(): void
    {
        $livro = new Livro();
        $autor = new Autor();
        $assunto = new Assunto();

        $livro->setTitulo('O Hobbit');
        $livro->setEditora('HarperCollins');
        $livro->setEdicao(1);
        $livro->setAnoPublicacao('1937');
        $livro->setValor('1234.56');
        $livro->addAutor($autor);
        $livro->addAssunto($assunto);

        $this->assertSame('O Hobbit', $livro->getTitulo());
        $this->assertSame('HarperCollins', $livro->getEditora());
        $this->assertSame(1, $livro->getEdicao());
        $this->assertSame('1937', $livro->getAnoPublicacao());
        $this->assertSame('1234.56', $livro->getValor());
        $this->assertEquals([$autor], $livro->getAutores()->getValues());
        $this->assertEquals([$assunto], $livro->getAssuntos()->getValues());
        $this->assertEquals([$livro], $autor->getLivros()->getValues());
        $this->assertEquals([$livro], $assunto->getLivros()->getValues());
    }

    public function testEdicao(): void
    {
        $livro = new Livro();
        $autorAntigo = new Autor();
        $assuntoAntigo = new Assunto();
        $livro->setTitulo('O Hobbit');
        $livro->setEditora('HarperCollins');
        $livro->setEdicao(1);
        $livro->setAnoPublicacao('1937');
        $livro->setValor('10.00');
        $livro->addAutor($autorAntigo);
        $livro->addAssunto($assuntoAntigo);

        $autorNovo = new Autor();
        $assuntoNovo = new Assunto();
        $livro->setTitulo('A Sociedade do Anel');
        $livro->setEditora('Martins Fontes');
        $livro->setEdicao(3);
        $livro->setAnoPublicacao('2019');
        $livro->setValor('89.90');
        $livro->removeAutor($autorAntigo);
        $livro->removeAssunto($assuntoAntigo);
        $livro->addAutor($autorNovo);
        $livro->addAssunto($assuntoNovo);

        $this->assertSame('A Sociedade do Anel', $livro->getTitulo());
        $this->assertSame('Martins Fontes', $livro->getEditora());
        $this->assertSame(3, $livro->getEdicao());
        $this->assertSame('2019', $livro->getAnoPublicacao());
        $this->assertSame('89.90', $livro->getValor());
        $this->assertEquals([$autorNovo], $livro->getAutores()->getValues());
        $this->assertEquals([$assuntoNovo], $livro->getAssuntos()->getValues());
        $this->assertEquals([$livro], $autorNovo->getLivros()->getValues());
        $this->assertEquals([$livro], $assuntoNovo->getLivros()->getValues());
        $this->assertCount(0, $autorAntigo->getLivros());
        $this->assertCount(0, $assuntoAntigo->getLivros());
    }
}
