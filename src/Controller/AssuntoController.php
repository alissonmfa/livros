<?php

namespace App\Controller;

use App\Entity\Assunto;
use App\Repository\AssuntoRepository;
use App\Service\AssuntoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assunto')]
class AssuntoController extends AbstractController
{
    public function __construct(private AssuntoService $assuntoService)
    {
    }

    #[Route('', name: 'assunto_index', methods: ['GET'])]
    public function index(AssuntoRepository $assuntoRepository): Response
    {
        return $this->render('assunto/index.html.twig', [
            'assuntos' => $assuntoRepository->findBy([], ['descricao' => 'ASC']),
        ]);
    }

    #[Route('/novo', name: 'assunto_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->save(new Assunto(), $request);
    }

    #[Route('/{assunto}/editar', name: 'assunto_edit', methods: ['GET', 'POST'])]
    public function edit(Assunto $assunto, Request $request): Response
    {
        return $this->save($assunto, $request);
    }

    #[Route('/{assunto}/excluir', name: 'assunto_delete', methods: ['POST'])]
    public function delete(Assunto $assunto): Response
    {
        foreach ($this->assuntoService->excluir($assunto) as $erro) {
            $this->addFlash('erro', $erro);
        }

        return $this->redirectToRoute('assunto_index');
    }

    private function save(Assunto $assunto, Request $request): Response
    {
        $descricao = trim((string) $request->request->get('descricao', $assunto->getDescricao() ?? ''));
        $erros = [];

        if ($request->isMethod('POST')) {
            $erros = $this->assuntoService->salvar($assunto, $descricao);
            if ($erros === []) {
                return $this->redirectToRoute('assunto_index');
            }
        }

        return $this->render('assunto/form.html.twig', [
            'assunto' => $assunto,
            'descricao' => $descricao,
            'erros' => $erros,
        ]);
    }
}
