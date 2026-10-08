<?php

namespace App\Tests\Service;

use App\Entity\Assunto;
use App\Repository\AssuntoRepository;
use App\Service\AssuntoService;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class AssuntoServiceTest extends TestCase
{
    public function testCadastro(): void
    {
        $assunto = new Assunto();
        [$service, $assuntoRepository, $em] = $this->service();

        $assuntoRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['descricao' => 'Fantasia'])
            ->willReturn(null);
        $em->expects($this->once())->method('persist')->with($assunto);
        $em->expects($this->once())->method('flush');

        $erros = $service->salvar($assunto, '  Fantasia  ');

        $this->assertSame([], $erros);
        $this->assertSame('Fantasia', $assunto->getDescricao());
    }

    public function testEdicao(): void
    {
        $assunto = new Assunto();
        $this->definirId($assunto, 3);
        $assunto->setDescricao('Fantasia');
        [$service, $assuntoRepository, $em] = $this->service();

        $assuntoRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['descricao' => 'Mitologia'])
            ->willReturn(null);
        $em->expects($this->once())->method('persist')->with($assunto);
        $em->expects($this->once())->method('flush');

        $erros = $service->salvar($assunto, '  Mitologia  ');

        $this->assertSame([], $erros);
        $this->assertSame('Mitologia', $assunto->getDescricao());
    }

    public function testSalvarDescricaoDuplicadaNoBanco(): void
    {
        $assunto = new Assunto();
        [$service, $assuntoRepository, $em] = $this->service();

        $assuntoRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);
        $em->expects($this->once())
            ->method('flush')
            ->willThrowException($this->excecaoBanco(UniqueConstraintViolationException::class));

        $erros = $service->salvar($assunto, 'Fantasia');

        $this->assertSame(['Já existe um assunto com esta descrição.'], $erros);
    }

    public function testSalvarDescricaoObrigatoriaNoBanco(): void
    {
        $assunto = new Assunto();
        [$service, $assuntoRepository, $em] = $this->service();

        $assuntoRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);
        $em->expects($this->once())
            ->method('flush')
            ->willThrowException($this->excecaoBanco(NotNullConstraintViolationException::class));

        $erros = $service->salvar($assunto, 'Fantasia');

        $this->assertSame(['Não foi possível salvar o assunto porque a descrição é obrigatória.'], $erros);
    }

    public function testExcluirAssuntoVinculado(): void
    {
        $assunto = new Assunto();
        [$service, , $em] = $this->service();

        $em->expects($this->once())->method('remove')->with($assunto);
        $em->expects($this->once())
            ->method('flush')
            ->willThrowException($this->excecaoBanco(ForeignKeyConstraintViolationException::class));

        $erros = $service->excluir($assunto);

        $this->assertSame(['Não foi possível excluir o assunto porque ele ainda está vinculado a um livro.'], $erros);
    }

    /**
     * @return array{AssuntoService, AssuntoRepository, EntityManagerInterface}
     */
    private function service(): array
    {
        $assuntoRepository = $this->createMock(AssuntoRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);

        return [new AssuntoService($assuntoRepository, $em), $assuntoRepository, $em];
    }

    private function definirId(Assunto $assunto, int $id): void
    {
        $propriedade = new \ReflectionProperty($assunto, 'codAs');
        $propriedade->setValue($assunto, $id);
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
