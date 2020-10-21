<?php


namespace Luxo\Action\Announcement;


use Luxo\Action\Action;
use Luxo\Entity\Announcement;
use Luxo\Entity\Image;
use Luxo\Entity\User;
use Luxo\Form\AddAnnouncementForm;
use Luxo\Repository\AnnouncementRepository;
use Luxo\Repository\UserRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\String\Slugger\AsciiSlugger;


class AddAction extends Action
{
    /**
     * @Route(path="/user/new")
     * @param FormFactory $formFactory
     * @param RequestStack $requestStack
     * @param AnnouncementRepository $announcementRepository
     * @param TokenStorage $tokenStorage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(FormFactory $formFactory, RequestStack $requestStack, AnnouncementRepository $announcementRepository, TokenStorage $tokenStorage, UserRepository $userRepository)
    {
        $user = $tokenStorage->getToken()->getUser();
        $announcement = new Announcement();
        $form = $formFactory->createBuilder(AddAnnouncementForm::class)
            ->getForm();
        $form->handleRequest($requestStack->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var Announcement $announcement
             */
            $announcement = $form->getData();
            $announcement->setPostedBy($user);
            $announcement->getImages()->map(function (Image $image) use ($announcement) {
                if ($file = $image->getFile()) {
                    $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = (new AsciiSlugger())->slug($originalFileName);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
                    try {
                        $publicPath = 'media/' . $announcement->getTypeName();
                        $path = realpath(__DIR__ . '/../../../public/').'/'.$publicPath;
                        @mkdir($path,0777,true);

                        $file->move($path, $newFilename);

                    } catch (FileException $e) {
                        throw $e;
                    }
                    $image->setPath($publicPath.'/'.$newFilename)->setName($originalFileName);
                    return $image;
                }
            });

            $announcementRepository->persist($announcement);
            return $this->redirectToUrl('http://google.fr');
        }

        return $this->render('Announcement/Add.html.twig', [
            'form' => $form->createView(),
        ]);

    }
}
