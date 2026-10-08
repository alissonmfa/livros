<?php

namespace App\Controller;

use App\Repository\AssuntoRepository;
use App\Repository\AutorRepository;
use App\Repository\LivroRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RelatorioController extends AbstractController
{
    #[Route('/relatorio', name: 'relatorio_index', methods: ['GET'])]
    public function index(
        Request $request,
        LivroRepository $livroRepository,
        AutorRepository $autorRepository,
        AssuntoRepository $assuntoRepository,
    ): Response {
        $titulo = trim((string) $request->query->get('titulo', ''));
        $editora = trim((string) $request->query->get('editora', ''));
        $ano = trim((string) $request->query->get('anoPublicacao', ''));
        $autorIds = array_values(array_map(intval(...), $request->query->all('autores')));
        $assuntoIds = array_values(array_map(intval(...), $request->query->all('assuntos')));
        $buscou = $request->query->getBoolean('buscar');

        return $this->render('relatorio/index.html.twig', [
            'buscou' => $buscou,
            'livros' => $buscou ? $livroRepository->buscar($titulo, $editora, $ano, $autorIds, $assuntoIds) : [],
            'titulo' => $titulo,
            'editora' => $editora,
            'ano' => $ano,
            'autorIds' => $autorIds,
            'assuntoIds' => $assuntoIds,
            'autores' => $autorRepository->findBy([], ['nome' => 'ASC']),
            'assuntos' => $assuntoRepository->findBy([], ['descricao' => 'ASC']),
        ]);
    }
}
