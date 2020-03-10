<?php

namespace App\Controller;

use App\Entity\Admin\Comment;
use App\Entity\Orders;
use App\Entity\User;
use App\Form\Admin\CommentType;
use App\Form\OrdersType;
use App\Form\UserType;
use App\Repository\Admin\CommentRepository;
use App\Repository\OrdersRepository;
use App\Repository\ShopcartRepository;
use App\Repository\SiparisRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('user/show.html.twig');
    }
    /**
     * @Route("/comments", name="user_comments", methods={"GET"})
     */
    public function comments(CommentRepository $commentRepository): Response
    {
        $user=$this->getUser();

        $comments=$commentRepository->getAllCommentsUser($user->getId());
        //dump($comments);
        //die();
        return $this->render('user/comments.html.twig',[
            'comments'=>$comments,

            ]);
    }
    /**
     * @Route("/shopcarts", name="user_shopcarts", methods={"GET"})
     */
    public function shopcart(ShopcartRepository $shopcartRepository): Response
    {   $user=$this->getUser();

        $shopcart=$shopcartRepository->getAllShopcartUser($user->getId());
        //dump($shopcarts);
        //die();
        return $this->render('user/shopcarts.html.twig',[
            'shopcart'=>$shopcart,

        ]);
    }
    /**
     * @Route("/siparis", name="user_siparis", methods={"GET"})
     */
    public function siparis( SiparisRepository $siparisRepository): Response
    {
        $user=$this->getUser();
        return $this->render('user/siparis.html.twig',[
            'siparis'=>$siparisRepository->findBy(['userid'=>$user->getId()]),
        ]);
    }
    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request,UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
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
                $user->setImage($filename);

            }
            // encode the  password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder, $id): Response
    {
        $user = $this->getUser();
        if($user->getId() != $id)
        {
            echo  "Wrong user";
            die();
        }
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['image']->getData();
            if ($file) {
                $filename = $this->generateUniqueFileName() . '.' . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $filename
                    );
                } catch (FileException $e) {

                }
                $user->setImage($filename);
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('user_index');
            }
            // encode the  password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
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
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */

    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
    /**
     * @Route("/comment/{id}", name="user_comment", methods={"GET","POST"})
     */
    public function comment(Request $request, $id, CommentRepository $commentRepository): Response
    {

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        $submittedToken=$request->request->get('token');
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->isCsrfTokenValid('comment', $submittedToken)) {
                $entityManager = $this->getDoctrine()->getManager();
                $comment->setStatus('New');
                $comment->setIp($_SERVER['REMOTE_ADDR']);
                $comment->setSiparisid($id);
                $user = $this->getUser();
                $comment->setUserid($user->getId());
                $entityManager->persist($comment);
                $this->addFlash('sucsess','Yapmış olduğunuz yorum başaıryla gönderildi');
                $entityManager->flush();

                return $this->redirectToRoute('user_comment',['id'=>$id]);
            }
        }

        return $this->redirectToRoute('siparis_show',['id'=>$id]);
    }
    /**
     * @Route("/shopcart/", name="user_siparis_new", methods={"GET","POST"})
     */
    public function newsiparis(Request $request,$rid,SiparisRepository $siparisRepositorytRepository,OrdersRepository $ordersRepository): Response
    {
        $number=$_REQUEST["number"];

        $siparis=$siparisRepositorytRepository->findOneBy(['id'=>$rid]);


        $total=$number * $siparis->getPrice();
        // echo $total;
        // die();

        $order = new Orders();
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);

        $submittedToken = $request->request->get('token');

        if ($form->isSubmitted()) {
            if($this->IsCsrfTokenValid('form-siparis', $submittedToken)) {
                $entityManager = $this->getDoctrine()->getManager();

                $order->setStatus('New');
                $order->setIp($_SERVER['REMOTE_ADDR']); //ip alıyoruz
                $order->setSiparisid($rid);

                $user = $this->getUser(); //get login user data
                $order->setUserid($user->getId());
                $order->setTotal($total);
                $order->setCreatedAt(new \DateTime()); //get current datetime

                $entityManager->persist($sipari);
                $entityManager->flush();

                return $this->redirectToRoute('user_sipari');
            }
        }

        return $this->render('user/newsiparis.html.twig', [
            'order' => $order,
            'total'=> $total,
            'number'=> $number,
            'form' => $form->createView(),
        ]);
    }


}

