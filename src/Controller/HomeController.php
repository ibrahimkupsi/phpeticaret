<?php

namespace App\Controller;

use App\Controller\Admin\CommentController;
use App\Entity\Admin\Messages;
use App\Entity\Siparis;
use App\Form\Admin\MessagesType;
use App\Repository\Admin\CommentRepository;
use App\Repository\SettingRepository;
use App\Repository\SiparisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Bridge\Google\Smtp\GmailTransport;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Routing\Annotation\Route;
use function Twig\Tests\html;
use Symfony\Component\Mime\Email;



class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(SettingRepository $settingRepository,SiparisRepository $siparisRepository)

    {
        $setting=$settingRepository->findAll();
        $slider=$siparisRepository->findBy([],[],3);
        $siparis=$siparisRepository->findBy([],[],6);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'setting'=>$setting,
            'slider'=>$slider,
            'siparis'=>$siparis,

        ]);
    }
    /**
     * @Route("siparis/{id}", name="siparis_show", methods={"GET"})
     */
    public function show(Siparis $sipari,$id,SiparisRepository $siparisRepository,CommentRepository $commentRepository): Response
    {   $comments=$commentRepository->findBy(['siparisid'=>$id,'status'=>'True']);
        $siparis=$siparisRepository->findBy(['id'=>$id],[],1);
        $slider=$siparisRepository->findBy([],[],3);
        return $this->render('home/siparishow.html.twig', [
            'sipari' => $sipari,
            'slider'=>$slider,
            'siparis'=>$siparis,
            'comments'=>$comments,
        ]);
    }
    /**
     * @Route("about", name="home_about", methods={"GET"})
     */
    public function about(SettingRepository $settingRepository): Response
    {
        $setting=$settingRepository->findAll();

        return $this->render('home/aboutus.html.twig', [
            'setting'=>$setting,
        ]);
    }

    /**
     * @Route("contact", name="home_contact", methods={"GET","POST"})
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function contact(SettingRepository $settingRepository,Request $request): Response
    {
        $setting=$settingRepository->findAll();
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);
        $submittedToken=$request->request->get('token');

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->isCsrfTokenValid('form-message', $submittedToken)) {
                $entityManager = $this->getDoctrine()->getManager();
                $message->setStatus('New');
                $message->setIp($_SERVER['REMOTE_ADDR']);
                $entityManager->persist($message);
                $this->addFlash('sucsess','Mesajınız başaıryla gönderildi');
                $email = (new Email())
                        ->from($setting[0]->getSmtpemail())
                        ->to($form['email']->getData())
                        //->cc('cc@example.com')
                        //->bcc('bcc@example.com')
                        //->replyTo('fabien@example.com')
                        //->priority(Email::PRIORITY_HIGH)
                        ->subject('your request')
                        ->html("Dear " . $form['name']->getData()."<br>
                                 <p>we will evaluate your request and contact as soon as possibly</p>
                                 thank you<br>
                                 =================================================================
                                <br>".$setting[0]->getCompany()."<br>
                                Address:".$setting[0]->getAdress()."<br>
                                Phone:".$setting[0]->getPhone()."<br>"

                        );
                        //->text('Sending emails is fun again!')


                $transport= new GmailTransport($setting[0]->getSmtpemail(),$setting[0]->getSmtppassword());
                $mailer=new Mailer($transport);
                /** @var TYPE_NAME $mailer */
                $mailer->send($email);

                $entityManager->flush();

                return $this->redirectToRoute('home_contact');
            }
        }
        return $this->render('home/contactus.html.twig', [
            'setting'=>$setting,
            'form' => $form->createView(),
        ]);
    }


}
