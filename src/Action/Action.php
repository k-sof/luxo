<?php

namespace Luxo\Action;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;

abstract class Action
{
    /**
     * @var \Twig\Environment
     */
    private $twig;
    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    public function __construct(\Twig\Environment $twig, UrlGenerator $urlGenerator)
    {
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param $template
     * @param array $context
     *
     * @return string
     */
    public function renderTemplate($template, $context = [])
    {
        return $this->twig->render($template, $context);
    }

    /**
     * @param $template
     * @param $context
     *
     * @return Response
     */
    public function render($template, $context = [])
    {
        $response = new Response();
        $response->setContent($this->renderTemplate($template, $context));

        return $response;
    }

    /**
     * @param string $url
     *
     * @return RedirectResponse
     */
    public function redirectToUrl(string $url)
    {
        return new RedirectResponse($url);
    }

    /**
     * @param string $name
     * @param array  $parameters
     *
     * @return RedirectResponse
     */
    public function redirectToRoute(string $name, $parameters = [])
    {
        return $this->redirectToUrl(
            $this->urlGenerator->generate($name, $parameters, UrlGenerator::ABSOLUTE_URL)
        );
    }
}
