<?php

namespace App\Tests\Service;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use App\Repository\AssuntoRepository;
use App\Repository\AutorRepository;
use App\Service\LivroService;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class LivroServiceTest extends TestCase
{
    public function testCadastro(): void
    {
        $livro = new Livro();
        $autor = new Autor();
        $assunto = new Assunto();
        [$service, $autorRepository, $assuntoRepository, $em] = $this->service();

        $autorRepository->expects($this->once())
            ->method('findBy')
            ->with(['codAu' => [1]])
            ->willReturn([$autor]);
        $assuntoRepository->expects($this->once())
            ->method('findBy')
            ->with(['codAs' => [2]])
            ->willReturn([$assunto]);
        $em->expects($this->once())->method('persist')->with($livro);
        $em->expects($this->once())->method('flush');

        $erros = $service->salvar($livro, '  O Hobbit  ', '  HarperCollins  ', ' 1 ', '1937', '  1.234,56  ', [1], [2]);

        $this->assertSame([], $erros);
        $this->assertSame('O Hobbit', $livro->getTitulo());
        $this->assertSame('HarperCollins', $livro->getEditora());
        $this->assertSame(1, $livro->getEdicao());
        $this->assertSame('1937', $livro->getAnoPublicacao());
        $this->assertSame('1234.56', $livro->getValor());
        $this->assertEquals([$autor], $livro->getAutores()->getValues());
        $this->assertEquals([$assunto], $livro->getAssuntos()->getValues());
    }

    public function testEdicao(): void
    {
        $livro = (new Livro())
            ->setTitulo('O Hobbit')
            ->setEditora('HarperCollins')
            ->setEdicao(1)
            ->setAnoPublicacao('1937')
            ->setValor('10.00');
        $autorAntigo = new Autor();
        $assuntoAntigo = new Assunto();
        $livro->addAutor($autorAntigo);
        $livro->addAssunto($assuntoAntigo);

        $autorNovo = new Autor();
        $assuntoNovo = new Assunto();
        [$service, $autorRepository, $assuntoRepository, $em] = $this->service();

        $autorRepository->expects($this->once())
            ->method('findBy')
            ->with(['codAu' => [10]])
            ->willReturn([$autorNovo]);
        $assuntoRepository->expects($this->once())
            ->method('findBy')
            ->with(['codAs' => [20]])
            ->willReturn([$assuntoNovo]);
        $em->expects($this->once())->method('persist')->with($livro);
        $em->expects($this->once())->method('flush');

        $erros = $service->salvar($livro, '  A Sociedade do Anel  ', '  Martins Fontes  ', ' 3 ', '2019', '  89,90  ', [10], [20]);

        $this->assertSame([], $erros);
        $this->assertSame('A Sociedade do Anel', $livro->getTitulo());
        $this->assertSame('Martins Fontes', $livro->getEditora());
        $this->assertSame(3, $livro->getEdicao());
        $this->assertSame('2019', $livro->getAnoPublicacao());
        $this->assertSame('89.90', $livro->getValor());
        $this->assertEquals([$autorNovo], $livro->getAutores()->getValues());
        $this->assertEquals([$assuntoNovo], $livro->getAssuntos()->getValues());
        $this->assertCount(0, $autorAntigo->getLivros());
        $this->assertCount(0, $assuntoAntigo->getLivros());
    }

    public function testSalvarAutorOuAssuntoInexistente(): void
    {
        $livro = new Livro();
        [$service, $autorRepository, $assuntoRepository, $em] = $this->service();

        $autorRepository->expects($this->once())->method('findBy')->willReturn([]);
        $assuntoRepository->expects($this->once())->method('findBy')->willReturn([]);
        $em->expects($this->once())
            ->method('flush')
            ->willThrowException($this->excecaoBanco(ForeignKeyConstraintViolationException::class));

        $erros = $service->salvar($livro, 'O Hobbit', 'HarperCollins', '1', '1937', '10,00', [1], [2]);

        $this->assertSame(['Não foi possível salvar o livro porque um dos autores ou assuntos selecionados não existe mais.'], $erros);
    }

    public function testSalvarCampoObrigatorioNoBanco(): void
    {
        $livro = new Livro();
        [$service, $autorRepository, $assuntoRepository, $em] = $this->service();

        $autorRepository->expects($this->never())->method('findBy');
        $assuntoRepository->expects($this->never())->method('findBy');
        $em->expects($this->once())
            ->method('flush')
            ->willThrowException($this->excecaoBanco(NotNullConstraintViolationException::class));

        $erros = $service->salvar($livro, 'O Hobbit', 'HarperCollins', '1', '1937', '10,00', [], []);

        $this->assertSame(['Não foi possível salvar o livro porque algum campo obrigatório ficou vazio.'], $erros);
    }

    public function testExcluirLivroReferenciado(): void
    {
        $livro = new Livro();
        [$service, , , $em] = $this->service();

        $em->expects($this->once())->method('remove')->with($livro);
        $em->expects($this->once())
            ->method('flush')
            ->willThrowException($this->excecaoBanco(ForeignKeyConstraintViolationException::class));

        $erros = $service->excluir($livro);

        $this->assertSame(['Não foi possível excluir o livro porque ele ainda está referenciado.'], $erros);
    }

    /**
     * @return array{LivroService, AutorRepository, AssuntoRepository, EntityManagerInterface}
     */
    private function service(): array
    {
        $autorRepository = $this->createMock(AutorRepository::class);
        $assuntoRepository = $this->createMock(AssuntoRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);

        return [new LivroService($autorRepository, $assuntoRepository, $em), $autorRepository, $assuntoRepository, $em];
    }

    /**
     * @param class-string<ForeignKeyConstraintViolationException|NotNullConstraintViolationException> $classe
     */
    private function excecaoBanco(string $classe): ForeignKeyConstraintViolationException|NotNullConstraintViolationException
    {
        $driver = $this->createMock(DriverException::class);
        $driver->method('getSQLState')->willReturn('23000');

        return new $classe($driver, null);
    }
}
