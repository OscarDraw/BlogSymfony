<?php

namespace App\Controller;

use App\Entity\BlogEntry;
use App\Form\BlogEntryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/blog/entry')]
class BlogEntryController extends AbstractController
{

    #[Route('/', name: 'blog_entry_index', methods: ['GET'])]
    public function index(): Response
    {

        if($this->getUser()){
            $blogEntries = $this->getDoctrine()
                ->getRepository(BlogEntry::class)
                ->findBy(['createBy' => $this->getUser()->getId()]);
        }else{
            $blogEntries = $this->getDoctrine()
                ->getRepository(BlogEntry::class)
                ->findAll();
        }

        return $this->render('blog_entry/index.html.twig', [
            'blog_entries' => $blogEntries,
        ]);
    }

    #[Route('/new', name: 'blog_entry_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $blogEntry = new BlogEntry();
        $blogEntry->setCreateBy($user);
        $form = $this->createForm(BlogEntryType::class, $blogEntry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            // image is not required
            if($imageFile){
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // to include the filename as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where images are stored
                try {
                    $imageFile->move(
                        $this->getParameter('blogImages'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    print "ERROR file: ".$e;
                    die();
                }
            }
            $blogEntry->setImage($newFilename);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($blogEntry);
            $entityManager->flush();

            return $this->redirectToRoute('show_entries');
        }

        return $this->render('blog_entry/new.html.twig', [
            'blog_entry' => $blogEntry,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'blog_entry_show', methods: ['GET'])]
    public function show(BlogEntry $blogEntry): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('blog_entry/show.html.twig', [
            'blog_entry' => $blogEntry,
        ]);
    }

    #[Route('/{id}/edit', name: 'blog_entry_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BlogEntry $blogEntry, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $url = '/uploads/blogImages/'.$blogEntry->getImage();
        $imagePath = $blogEntry->getImage();

        $form = $this->createForm(BlogEntryType::class, $blogEntry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
            // image is not required
            if($imageFile){

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                if($originalFilename != $blogEntry->getImage()){
                    // to include the filename as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                    // Move the file to the directory where images are stored
                    try {
                        $imageFile->move(
                            $this->getParameter('blogImages'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        print "ERROR file: ".$e;
                        die();
                    }
                }
            }else{
                $newFilename = $imagePath;
            }

            $blogEntry->setImage($newFilename);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('show_entries');
        }

        return $this->render('blog_entry/edit.html.twig', [
            'blog_entry' => $blogEntry,
            'form' => $form->createView(),
            'url' => $url
        ]);
    }

    #[Route('/{id}', name: 'blog_entry_delete', methods: ['POST'])]
    public function delete(Request $request, BlogEntry $blogEntry): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->isCsrfTokenValid('delete'.$blogEntry->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($blogEntry);
            $entityManager->flush();
        }

        return $this->redirectToRoute('show_entries');
    }

    #[Route('_blog', name: 'show_entries', methods: ['GET'])]
    public function showEntries(): Response
    {
        $current_user = ($this->getUser()) ?  $this->getUser()->getId() : '0';
        $blogEntries = $this->getDoctrine()
            ->getRepository(BlogEntry::class)
            ->findBy([],['id' => 'DESC']);

        return $this->render('blog_entry/blog.html.twig', [
            'blog_entries' => $blogEntries,
            'current_user' => $current_user
        ]);
    }

    #[Route('/entry/', name: 'entry_show', methods: ['GET', 'POST'])]
    public function showEntry(Request $request): Response
    {

        $blogEntryId = $request->request->get('id');
        $current_user = ($this->getUser()) ? $request->request->get('current_user') : '0';
        $blogEntry = $this->getDoctrine()
            ->getRepository(BlogEntry::class)
            ->find($blogEntryId);

        return $this->render('blog_entry/blog_entry.html.twig', [
            'blog_entry' => $blogEntry,
            'current_user' => $current_user
        ]);
    }
}
