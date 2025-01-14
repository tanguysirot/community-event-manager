<?php

declare(strict_types=1);

namespace App\Controller\SpecialBenefit;

use App\Dto\SpecialBenefitRequest;
use App\Form\SpecialBenefitType;
use App\Repository\SpecialBenefit\SpecialBenefitManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment as Twig;

final class Edit
{
    private $renderer;
    private $specialBenefitManager;
    private $formFactory;
    private $router;

    public function __construct(
        Twig $renderer,
        SpecialBenefitManagerInterface $specialBenefitManager,
        FormFactoryInterface $formFactory,
        RouterInterface $router
    ) {
        $this->renderer = $renderer;
        $this->specialBenefitManager = $specialBenefitManager;
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function handle(Request $request): Response
    {
        $specialBenefit = $this->specialBenefitManager->find($request->attributes->get('id'));
        $specialBenefitRequest = SpecialBenefitRequest::createFromEntity($specialBenefit);

        $form = $this->formFactory->create(SpecialBenefitType::class, $specialBenefitRequest, [
            'method' => Request::METHOD_PUT,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $specialBenefitRequest = $form->getData();
            $specialBenefitRequest->updateEntity($specialBenefit);
            $this->specialBenefitManager->save($specialBenefit);

            return new RedirectResponse($this->router->generate('special_benefit_index'));
        }

        return new Response($this->renderer->render('specialBenefit/edit.html.twig', [
            'specialBenefit' => $specialBenefit,
            'form' => $form->createView(),
        ]));
    }
}
