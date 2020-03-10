<?php

namespace App\Controller;

use App\Entity\Siparis;
use App\Form\Siparis1Type;
use App\Repository\SiparisRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/user/siparis")
 */
class SiparisController extends AbstractController
{
    /**
     * @Route("/", name="user_siparis_index", methods={"GET"})
     */
    public function index(SiparisRepository $siparisRepository): Response
    {
        $user = $this->getUser();
        return $this->render('siparis/index.html.twig', [
           'siparis' => $siparisRepository->findBy(['userid'=>$user->getId()]),
        ]);
    }

    /**
     * @Route("/new", name="user_siparis_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $sipari = new Siparis();
        $form = $this->createForm(Siparis1Type::class, $sipari);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager=$this->getDoctrine()->getManager();
            $user=$this->getUser();
            $sipari->setUserid($user->getId());

            $file=$form['image']->getData();
            if($file){
                $filename=$this->generateUniqueFileName() . '.' . $file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('images_directory'),
                        $filename
                    );
                }catch (FileException $e){

                }
                $sipari->setImage($filename);

            }

            $sipari->setStatus("New");
            $entityManager->persist($sipari);
            $entityManager->flush();
           $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('user_siparis_index');
        }

        return $this->render('siparis/new.html.twig', [
            'sipari' => $sipari,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_siparis_show", methods={"GET"})
     */
    public function show(Siparis $sipari): Response
    {
        return $this->render('siparis/show.html.twig', [
            'sipari' => $sipari,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_siparis_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Siparis $sipari): Response
    {
        $form = $this->createForm(Siparis1Type::class, $sipari);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file=$form['image']->getData();
            if($file){
                $filename=$this->generateUniqueFileName() . '.' . $file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('images_directory'),
                        $filename
                    );
                }catch (FileException $e){

                }
                $sipari->setImage($filename);

            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_siparis_index');
        }
        return $this->render('siparis/edit.html.twig', [
            'sipari' => $sipari,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @return string
     */
    private function generateUniqueFileName(){
        //md5() reduces the similarity of the file name
        //uniqid(), which is based on timestamps
        return md5(uniqid());
    }
    /**
     * @Route("/{id}", name="user_siparis_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Siparis $sipari): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sipari->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($sipari);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_siparis_index');
    }
}
