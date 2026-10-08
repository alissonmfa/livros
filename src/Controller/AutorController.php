<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Repository\AutorRepository;
use App\Service\AutorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/autor')]
class AutorController extends AbstractController
{
    public function __construct(private AutorService $autorService)
    {
    }

    #[Route('', name: 'autor_index', methods: ['GET'])]
    public function index(AutorRepository $autorRepository): Response
    {
        return $this->render('autor/index.html.twig', [
            'autores' => $autorRepository->findBy([], ['nome' => 'ASC']),
        ]);
    }

    #[Route('/novo', name: 'autor_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->save(new Autor(), $request);
    }

    #[Route('/{autor}/editar', name: 'autor_edit', methods: ['GET', 'POST'])]
    public function edit(Autor $autor, Request $request): Response
    {
        return $this->save($autor, $request);
    }

    #[Route('/{autor}/excluir', name: 'autor_delete', methods: ['POST'])]
    public function delete(Autor $autor): Response
    {
        foreach ($this->autorService->excluir($autor) as $erro) {
            $this->addFlash('erro', $erro);
        }

        return $this->redirectToRoute('autor_index');
    }

    private function save(Autor $autor, Request $request): Response
    {
        $nome = trim((string) $request->request->get('nome', $autor->getNome() ?? ''));
        $erros = [];

        if ($request->isMethod('POST')) {
            $erros = $this->autorService->salvar($autor, $nome);
            if ($erros === []) {
                return $this->redirectToRoute('autor_index');
            }
        }

        return $this->render('autor/form.html.twig', [
            'autor' => $autor,
            'nome' => $nome,
            'erros' => $erros,
        ]);
    }
}
