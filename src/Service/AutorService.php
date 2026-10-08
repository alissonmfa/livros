<?php

namespace App\Service;

use App\Entity\Autor;
use App\Repository\AutorRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class AutorService
{
    public function __construct(
        private AutorRepository $autorRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function salvar(Autor $autor, string $nome): array
    {
        $nome = trim($nome);
        $erros = [];

        if ($nome === '' || mb_strlen($nome) > 40) {
            $erros[] = 'Informe o nome com no máximo 40 caracteres.';
        } else {
            $existente = $this->autorRepository->findOneBy(['nome' => $nome]);
            if ($existente !== null && $existente->getCodAu() !== $autor->getCodAu()) {
                $erros[] = 'Já existe um autor com este nome.';
            }
        }

        if ($erros !== []) {
            return $erros;
        }

        $autor->setNome($nome);
        $this->em->persist($autor);

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            return ['Já existe um autor com este nome.'];
        } catch (NotNullConstraintViolationException) {
            return ['Não foi possível salvar o autor porque o nome é obrigatório.'];
        }

        return [];
    }

    public function excluir(Autor $autor): array
    {
        foreach ($autor->getLivros()->toArray() as $livro) {
            $livro->removeAutor($autor);
        }

        $this->em->remove($autor);

        try {
            $this->em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return ['Não foi possível excluir o autor porque ele ainda está vinculado a um livro.'];
        }

        return [];
    }
}
