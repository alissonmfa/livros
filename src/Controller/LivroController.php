<?php

namespace App\Controller;

use App\Entity\Livro;
use App\Repository\AssuntoRepository;
use App\Repository\AutorRepository;
use App\Repository\LivroRepository;
use App\Service\LivroService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/livro')]
class LivroController extends AbstractController
{
    public function __construct(private LivroService $livroService)
    {
    }

    #[Route('', name: 'livro_index', methods: ['GET'])]
    public function index(LivroRepository $livroRepository): Response
    {
        return $this->render('livro/index.html.twig', [
            'livros' => $livroRepository->findBy([], ['titulo' => 'ASC']),
        ]);
    }

    #[Route('/novo', name: 'livro_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AutorRepository $autorRepository, AssuntoRepository $assuntoRepository): Response
    {
        return $this->save(new Livro(), $request, $autorRepository, $assuntoRepository);
    }

    #[Route('/{livro}/editar', name: 'livro_edit', methods: ['GET', 'POST'])]
    public function edit(Livro $livro, Request $request, AutorRepository $autorRepository, AssuntoRepository $assuntoRepository): Response
    {
        return $this->save($livro, $request, $autorRepository, $assuntoRepository);
    }

    #[Route('/{livro}/excluir', name: 'livro_delete', methods: ['POST'])]
    public function delete(Livro $livro): Response
    {
        foreach ($this->livroService->excluir($livro) as $erro) {
            $this->addFlash('erro', $erro);
        }

        return $this->redirectToRoute('livro_index');
    }

    private function save(Livro $livro, Request $request, AutorRepository $autorRepository, AssuntoRepository $assuntoRepository): Response
    {
        $titulo = trim((string) $request->request->get('titulo', $livro->getTitulo() ?? ''));
        $editora = trim((string) $request->request->get('editora', $livro->getEditora() ?? ''));
        $ano = trim((string) $request->request->get('anoPublicacao', $livro->getAnoPublicacao() ?? ''));
        $edicao = $request->isMethod('POST')
            ? trim((string) $request->request->get('edicao', ''))
            : ($livro->getEdicao() ?? '');
        $valor = $request->isMethod('POST')
            ? trim((string) $request->request->get('valor', ''))
            : ($livro->getValor() ?? '');
        $autorIds = $request->isMethod('POST')
            ? array_map(intval(...), $request->request->all('autores'))
            : $livro->getAutores()->map(fn ($autor) => $autor->getCodAu())->toArray();
        $assuntoIds = $request->isMethod('POST')
            ? array_map(intval(...), $request->request->all('assuntos'))
            : $livro->getAssuntos()->map(fn ($assunto) => $assunto->getCodAs())->toArray();
        $autores = $autorRepository->findBy([], ['nome' => 'ASC']);
        $assuntos = $assuntoRepository->findBy([], ['descricao' => 'ASC']);
        $erros = [];

        if ($request->isMethod('POST')) {
            $erros = $this->livroService->salvar($livro, $titulo, $editora, (string) $edicao, $ano, $valor, $autorIds, $assuntoIds);
            if ($erros === []) {
                return $this->redirectToRoute('livro_index');
            }
        }

        return $this->render('livro/form.html.twig', [
            'livro' => $livro,
            'titulo' => $titulo,
            'editora' => $editora,
            'edicao' => $edicao,
            'ano' => $ano,
            'valor' => $valor,
            'autorIds' => $autorIds,
            'assuntoIds' => $assuntoIds,
            'autores' => $autores,
            'assuntos' => $assuntos,
            'erros' => $erros,
        ]);
    }
}
