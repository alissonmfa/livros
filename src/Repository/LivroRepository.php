<?php

namespace App\Repository;

use App\Entity\Livro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Livro>
 */
class LivroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livro::class);
    }

    /**
     * @param list<int> $autorIds
     * @param list<int> $assuntoIds
     *
     * @return list<Livro>
     */
    public function buscar(string $titulo, string $editora, string $ano, array $autorIds, array $assuntoIds): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.autores', 'autor')->addSelect('autor')
            ->leftJoin('l.assuntos', 'assunto')->addSelect('assunto')
            ->distinct()
            ->orderBy('l.titulo', 'ASC')
            ->addOrderBy('autor.nome', 'ASC')
            ->addOrderBy('assunto.descricao', 'ASC');

        if ($titulo !== '') {
            $qb->andWhere('l.titulo LIKE :titulo')
                ->setParameter('titulo', '%'.$this->escaparLike($titulo).'%');
        }

        if ($editora !== '') {
            $qb->andWhere('l.editora LIKE :editora')
                ->setParameter('editora', '%'.$this->escaparLike($editora).'%');
        }

        if ($ano !== '') {
            $qb->andWhere('l.anoPublicacao = :ano')
                ->setParameter('ano', $ano);
        }

        if ($autorIds !== []) {
            $sub = $this->getEntityManager()->createQueryBuilder()
                ->select('livroAutor.codl')
                ->from(Livro::class, 'livroAutor')
                ->innerJoin('livroAutor.autores', 'autorFiltro')
                ->where('autorFiltro.codAu IN (:autores)');
            $qb->andWhere($qb->expr()->in('l.codl', $sub->getDQL()))
                ->setParameter('autores', $autorIds);
        }

        if ($assuntoIds !== []) {
            $sub = $this->getEntityManager()->createQueryBuilder()
                ->select('livroAssunto.codl')
                ->from(Livro::class, 'livroAssunto')
                ->innerJoin('livroAssunto.assuntos', 'assuntoFiltro')
                ->where('assuntoFiltro.codAs IN (:assuntos)');
            $qb->andWhere($qb->expr()->in('l.codl', $sub->getDQL()))
                ->setParameter('assuntos', $assuntoIds);
        }

        return $qb->getQuery()->getResult();
    }

    private function escaparLike(string $termo): string
    {
        return addcslashes($termo, '%_\\');
    }
}
