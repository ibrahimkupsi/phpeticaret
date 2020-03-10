<?php

namespace App\Controller;

use App\Entity\OrderDetail;
use App\Entity\Orders;
use App\Form\OrdersType;
use App\Repository\OrderDetailRepository;
use App\Repository\OrdersRepository;
use App\Repository\ShopcartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/orders")
 */
class OrdersController extends AbstractController
{
    /**
     * @Route("/", name="orders_index", methods={"GET"})
     */
    public function index(OrdersRepository $ordersRepository): Response
    {
        return $this->render('orders/index.html.twig', [
            'orders' => $ordersRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="orders_new", methods={"GET","POST"})
     */
    public function new(Request $request, ShopcartRepository $shopcartRepository): Response
    {
        $orders = new Orders();
        $form = $this->createForm(OrdersType::class, $orders);
        $form->handleRequest($request);

        $user = $this->getUser(); // Calling Login user data
        $userid = $user->getid();
        $total=$shopcartRepository->getUserShopCartTotal($userid); // Get total amount of user shopcart

        $submittedToken = $request->request->get('token'); // get csrf token information

        if ($this->isCsrfTokenValid('form-order', $submittedToken)) {
            if ($form->isSubmitted()) {
                // Kredi kartı bilgilerini ilgili banka servisine gönder
                // Onay gelirse kaydetmeye devam et yoksa order sayfasına hata gönder

                $em = $this->getDoctrine()->getManager();

                $orders->setUserid($userid);
                $orders->setAmount($total);
                $orders->setStatus("New");

                $em->persist($orders);
                $em->flush();

                $orderid = $orders->getId(); //  Get last insert orders data id

                $shopcart = $shopcartRepository->getAllShopcartUser($userid);

                foreach ($shopcart as $item) {

                    $orderdetail = new OrderDetail();
                    // Filling Orderdetails data from shopcart
                    $orderdetail->setOrderid($orderid);
                    $orderdetail->setUserid($user->getid()); // login user id
                    $orderdetail->setSiparisid($item["siparisid"]);
                    $orderdetail->setQuantity($item["quantity"]);
                    
                    $orderdetail->setName($item["title"]);
                    $orderdetail->setStatus("Ordered");

                    $em->persist($orderdetail);
                    $em->flush();

                }



                $this->addFlash('success', 'Siparişiniz Başarıyla Gerçekleştirilmiştir. Teşekkür Ederiz');
                return $this->redirectToRoute('orders_index');
            }
        }

        return $this->render('orders/new.html.twig', [
            'order' => $orders,
            'total' => $total,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}", name="orders_show", methods={"GET"})
     */
    public function show(Orders $order,OrderDetailRepository $orderDetailRepository): Response
    {
        $user=$this->getUser();
        $userid=$user->getid();
        $orderid=$order->getId();
        $orderdetail=$orderDetailRepository->findBy(
            ['orderid'=>$orderid]
        );
        return $this->render('orders/show.html.twig', [
            'order' => $order,
            'orderdetail'=>$orderdetail,

        ]);
    }

    /**
     * @Route("/{id}/edit", name="orders_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Orders $order): Response
    {
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('orders_index');
        }

        return $this->render('orders/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="orders_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Orders $order): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('orders_index');
    }

    private function createQuery(string $string)
    {
    }


}
