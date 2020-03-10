<?php

namespace App\Controller\Admin;

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
 * @Route("/admin/orders")
 */
class OrdersController extends AbstractController
{
    /**
     * @Route("/", name="admin_orders_index", methods={"GET"})
     */
    public function index(OrdersRepository $ordersRepository): Response
    {
        return $this->render('admin/orders/index.html.twig', [
            'orders' => $ordersRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_orders_new", methods={"GET","POST"})
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
                return $this->redirectToRoute('admin_orders_index');
            }
        }

        return $this->render('admin/orders/new.html.twig', [
            'order' => $orders,
            'total' => $total,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}", name="admin_orders_show", methods={"GET"})
     */
    public function show(Orders $order,OrderDetailRepository $orderDetailRepository): Response
    {
        $user=$this->getUser();
        $userid=$user->getid();
        $orderid=$order->getId();
        $orderdetail=$orderDetailRepository->findBy(
            ['orderid'=>$orderid]
        );
        return $this->render('admin/orders/show.html.twig', [
            'order' => $order,
            'orderdetail'=>$orderdetail,

        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_orders_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Orders $order): Response
    {
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_orders_index');
        }

        return $this->render('admin/orders/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_orders_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Orders $order): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_orders_index');
    }

    private function createQuery(string $string)
    {
    }
    /**
     * @Route("/orders/{id}/update", name="admin_orders_update", methods="POST")
     */
    public function orders_update($id, Request $request, Orders $orders) : Response
    {
        $em = $this->getDoctrine()->getManager();
        $sql = "UPDATE orders SET shipinfo=:shipinfo,note=:note,status=:status WHERE id=:id";
        $statement = $em->getConnection()->prepare($sql);
        $statement->bindValue('shipinfo', $request->request->get('shipinfo'));
        $statement->bindValue('note', $request->request->get('note'));
        $statement->bindValue('status', $request->request->get('status'));
        $statement->bindValue('id', $id);
        $statement->execute();
        $this->addFlash('success', 'Siparis Bilgileri Güncellenmistir');

        return $this->redirectToRoute('admin_orders_show', array('id' => $id));
    }
}
