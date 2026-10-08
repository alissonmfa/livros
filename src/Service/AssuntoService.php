<?php

namespace App\Service;

use App\Entity\Assunto;
use App\Repository\AssuntoRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class AssuntoService
{
    public function __construct(
        private AssuntoRepository $assuntoRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function salvar(Assunto $assunto, string $descricao): array
    {
        $descricao = trim($descricao);
        $erros = [];

        if ($descricao === '' || mb_strlen($descricao) > 20) {
            $erros[] = 'Informe a descrição com no máximo 20 caracteres.';
        } else {
            $existente = $this->assuntoRepository->findOneBy(['descricao' => $descricao]);
            if ($existente !== null && $existente->getCodAs() !== $assunto->getCodAs()) {
                $erros[] = 'Já existe um assunto com esta descrição.';
            }
        }

        if ($erros !== []) {
            return $erros;
        }

        $assunto->setDescricao($descricao);
        $this->em->persist($assunto);

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            return ['Já existe um assunto com esta descrição.'];
        } catch (NotNullConstraintViolationException) {
            return ['Não foi possível salvar o assunto porque a descrição é obrigatória.'];
        }

        return [];
    }

    public function excluir(Assunto $assunto): array
    {
        foreach ($assunto->getLivros()->toArray() as $livro) {
            $livro->removeAssunto($assunto);
        }

        $this->em->remove($assunto);

        try {
            $this->em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return ['Não foi possível excluir o assunto porque ele ainda está vinculado a um livro.'];
        }

        return [];
    }
}
