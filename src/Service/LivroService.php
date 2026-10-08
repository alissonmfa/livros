<?php

namespace App\Service;

use App\Entity\Livro;
use App\Repository\AssuntoRepository;
use App\Repository\AutorRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class LivroService
{
    public function __construct(
        private AutorRepository $autorRepository,
        private AssuntoRepository $assuntoRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function salvar(Livro $livro, string $titulo, string $editora, string $edicao, string $ano, string $valor, array $autorIds, array $assuntoIds): array
    {
        $titulo = trim($titulo);
        $editora = trim($editora);
        $edicao = trim($edicao);
        $ano = trim($ano);
        $valor = str_replace(',', '.', str_replace('.', '', trim($valor)));
        $erros = [];
        $edicaoInt = filter_var($edicao, FILTER_VALIDATE_INT);
        $valorNormalizado = null;

        if ($titulo === '' || mb_strlen($titulo) > 40) {
            $erros[] = 'Informe o título com no máximo 40 caracteres.';
        }
        if ($editora === '' || mb_strlen($editora) > 40) {
            $erros[] = 'Informe a editora com no máximo 40 caracteres.';
        }
        if ($edicaoInt === false) {
            $erros[] = 'Informe a edição como número inteiro.';
        }
        if (!ctype_digit($ano) || strlen($ano) !== 4) {
            $erros[] = 'Informe o ano de publicação com 4 números.';
        } elseif ((int) $ano > (int) date('Y')) {
            $erros[] = 'O ano de publicação não pode ser maior que o ano atual.';
        }
        if (preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', $valor, $partes) !== 1) {
            $erros[] = 'Informe o valor com até duas casas decimais.';
        } else {
            $inteiro = ltrim($partes[1], '0');
            $valorNormalizado = ($inteiro === '' ? '0' : $inteiro).'.'.str_pad($partes[2] ?? '', 2, '0');
            if (strlen($inteiro) > 8) {
                $valorNormalizado = null;
                $erros[] = 'Informe o valor com até duas casas decimais.';
            }
        }

        if ($erros !== []) {
            return $erros;
        }

        $livro->setTitulo($titulo);
        $livro->setEditora($editora);
        $livro->setEdicao($edicaoInt);
        $livro->setAnoPublicacao($ano);
        $livro->setValor($valorNormalizado);

        foreach ($livro->getAutores()->toArray() as $autor) {
            $livro->removeAutor($autor);
        }
        if ($autorIds !== []) {
            foreach ($this->autorRepository->findBy(['codAu' => $autorIds]) as $autor) {
                $livro->addAutor($autor);
            }
        }

        foreach ($livro->getAssuntos()->toArray() as $assunto) {
            $livro->removeAssunto($assunto);
        }
        if ($assuntoIds !== []) {
            foreach ($this->assuntoRepository->findBy(['codAs' => $assuntoIds]) as $assunto) {
                $livro->addAssunto($assunto);
            }
        }

        $this->em->persist($livro);

        try {
            $this->em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return ['Não foi possível salvar o livro porque um dos autores ou assuntos selecionados não existe mais.'];
        } catch (NotNullConstraintViolationException) {
            return ['Não foi possível salvar o livro porque algum campo obrigatório ficou vazio.'];
        }

        return [];
    }

    public function excluir(Livro $livro): array
    {
        foreach ($livro->getAutores()->toArray() as $autor) {
            $livro->removeAutor($autor);
        }
        foreach ($livro->getAssuntos()->toArray() as $assunto) {
            $livro->removeAssunto($assunto);
        }

        $this->em->remove($livro);

        try {
            $this->em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return ['Não foi possível excluir o livro porque ele ainda está referenciado.'];
        }

        return [];
    }
}
