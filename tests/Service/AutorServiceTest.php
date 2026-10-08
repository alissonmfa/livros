<?php

namespace App\Tests\Service;

use App\Entity\Autor;
use App\Repository\AutorRepository;
use App\Service\AutorService;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class AutorServiceTest extends TestCase
{
    public function testCadastro(): void
    {
        $autor = new Autor();
        [$service, $autorRepository, $em] = $this->service();

        $autorRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['nome' => 'J.R.R. Tolkien'])
            ->willReturn(null);
        $em->expects($this->once())->method('persist')->with($autor);
        $em->expects($this->once())->method('flush');

        $erros = $service->salvar($autor, '  J.R.R. Tolkien  ');

        $this->assertSame([], $erros);
        $this->assertSame('J.R.R. Tolkien', $autor->getNome());
    }

    public function testEdicao(): void
    {
        $autor = new Autor();
        $this->definirId($autor, 3);
        $autor->setNome('J.R.R. Tolkien');
        [$service, $autorRepository, $em] = $this->service();

        $autorRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['nome' => 'Christopher Tolkien'])
            ->willReturn(null);
        $em->expects($this->once())->method('persist')->with($autor);
        $em->expects($this->once())->method('flush');

        $erros = $service->salvar($autor, '  Christopher Tolkien  ');

        $this->assertSame([], $erros);
        $this->assertSame('Christopher Tolkien', $autor->getNome());
    }

    public function testSalvarNomeDuplicadoNoBanco(): void
    {
        $autor = new Autor();
        [$service, $autorRepository, $em] = $this->service();

        $autorRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);
        $em->expects($this->once())
            ->method('flush')
            ->willThrowException($this->excecaoBanco(UniqueConstraintViolationException::class));

        $erros = $service->salvar($autor, 'J.R.R. Tolkien');

        $this->assertSame(['Já existe um autor com este nome.'], $erros);
    }

    public function testSalvarNomeObrigatorioNoBanco(): void
    {
        $autor = new Autor();
        [$service, $autorRepository, $em] = $this->service();

        $autorRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);
        $em->expects($this->once())
            ->method('flush')
            ->willThrowException($this->excecaoBanco(NotNullConstraintViolationException::class));

        $erros = $service->salvar($autor, 'J.R.R. Tolkien');

        $this->assertSame(['Não foi possível salvar o autor porque o nome é obrigatório.'], $erros);
    }

    public function testExcluirAutorVinculado(): void
    {
        $autor = new Autor();
        [$service, , $em] = $this->service();

        $em->expects($this->once())->method('remove')->with($autor);
        $em->expects($this->once())
            ->method('flush')
            ->willThrowException($this->excecaoBanco(ForeignKeyConstraintViolationException::class));

        $erros = $service->excluir($autor);

        $this->assertSame(['Não foi possível excluir o autor porque ele ainda está vinculado a um livro.'], $erros);
    }

    /**
     * @return array{AutorService, AutorRepository, EntityManagerInterface}
     */
    private function service(): array
    {
        $autorRepository = $this->createMock(AutorRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);

        return [new AutorService($autorRepository, $em), $autorRepository, $em];
    }

    private function definirId(Autor $autor, int $id): void
    {
        $propriedade = new \ReflectionProperty($autor, 'codAu');
        $propriedade->setValue($autor, $id);
    }

    /**
     * @param class-string<UniqueConstraintViolationException|NotNullConstraintViolationException|ForeignKeyConstraintViolationException> $classe
     */
    private function excecaoBanco(string $classe): UniqueConstraintViolationException|NotNullConstraintViolationException|ForeignKeyConstraintViolationException
    {
        $driver = $this->createMock(DriverException::class);
        $driver->method('getSQLState')->willReturn('23000');

        return new $classe($driver, null);
    }
}
