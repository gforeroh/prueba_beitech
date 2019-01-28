<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Chronos\Chronos;

/**
 * Order Controller
 *
 * @property \App\Model\Table\OrderTable $Order
 *
 * @method \App\Model\Entity\Order[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OrdersController extends AppController
{
    /*
    * Method: GET
    * path: orders/orders?customer_id=:customer_id&timeStampStart=:timeStampStart&timeStampEnd=:timeStampEnd
    * Ejemplo: http://localhost/prueba_beitech/orders/orders?customer_id=1&timeStampStart=1546300800&timeStampEnd=1548806400
    * https://www.epochconverter.com/    --  Convertir
    * $timeStampStart = 1546300800 - 2019-01-01;
    * $timeStampEnd = 1548806400 - 2019-01-30;
    * headers: Accept - application/json
    * headers: Content-Type - application/json
    */
    public function orders(){
        // $this->request->allowMethod(['get']);
        $this->loadModel('Customer');
        $conditions=[];
        $functionBetween = [];
        $nameCustomer = '';

        $customer_id=null;
        $timeStampStart=null;
        $timeStampEnd=null;

        if($_GET){
            $customer_id = ($_GET['customer_id'] != 'null') ? $_GET['customer_id'] : null;
            $timeStampStart = ($_GET['timeStampStart'] != 'null') ? $_GET['timeStampStart'] : null;
            $timeStampEnd = ($_GET['timeStampEnd'] != 'null') ? $_GET['timeStampEnd'] : null;
        }

        if($customer_id){
            $conditions['Customer.customer_id'] = $customer_id;
            $customer = $this->Customer->find('all')->where(['Customer.customer_id' => $customer_id])->first();

            if($customer){
                $nameCustomer = $customer->name;
            }
        } else {
            $conditions['Orders.order_id'] = null;
        }

        if($timeStampStart && $timeStampEnd){
            $dtStart = new \DateTime("@$timeStampStart"); 
            $dtEnd = new \DateTime("@$timeStampEnd");

            $functionBetween = function($exp) use ($dtStart, $dtEnd) {
                return $exp->between('Orders.creation_date', $dtStart, $dtEnd);
            };
        }

        $orders = $this->Orders->find('all')
            ->contain(['Customer', 'OrderDetail' => ['Product']])
            ->where([$conditions, $functionBetween])
            ->order(['Orders.creation_date' => 'ASC']);

        if ($orders->count()) {
            $message = 'Ordenes registradas a: ' . $nameCustomer;
        } else {
            $message = 'No se encontraron ordenes registradas';
        }

        $customersList = $this->Customer->find('list')->toArray();

        $this->set([
            'customersList' => $customersList,
            'message' => $message,
            'orders' => $orders,
            '_serialize' => ['message', 'orders', 'customersList']
        ]);
    }

    /*
    * Method: POST
    * path: orders/order_add
    * Ejemplo: http://localhost/prueba_beitech/orders/order_add
    * data format json
    * headers: Accept - application/json
    * headers: Content-Type - application/json
    */
    public function orderAdd(){
        $this->request->allowMethod(['post']);
        $this->loadModel('OrderDetail');
        $this->loadModel('Customer');
        $today = Chronos::today();
        $message = '';
        $response = [];

        if ($this->request->is(['post'])) {
            $data = $this->request->getData();
            $countOrderDetails = count($data['OrderDetails']); 
            $customer_id = $data['customer_id'];
            
            $customer = $this->Customer->find('all')->where(['Customer.customer_id' => $customer_id])->contain(['Product'])->first();
            $products = $customer->product;
            $nameCustomer = $customer->name;

            if($countOrderDetails <= 5){
                $listProductsPerm = [];
                foreach ($products as $key => $product) {
                    $listProductsPerm[] = $product['product_id'];
                }

                
                $order = $this->Orders->newEntity();    
                $orderDetails = $data['OrderDetails'];
                $order = $this->Orders->patchEntity($order, $data);

                $total = 0;
                $listProductsRequest = [];
                if($orderDetails){
                    foreach ($orderDetails as $key => $detail) {
                        $total += $detail['price'] * $detail['quantity'];
                        $listProductsRequest[] = $detail['product_id'];
                    }
                }
                $listProductsDiff = array_diff($listProductsRequest, $listProductsPerm);

                $order->creation_date=$today;
                $order->total=$total;
                if(!$listProductsDiff){
                    if ($this->Orders->save($order)) {
                        $order_id = $order->order_id;
                        
                        if($orderDetails){
                            foreach ($orderDetails as $key => $detail) {
                                $orderDetails[$key]['order_id'] = $order_id;
                            }
                        }
                        
                        $details = $this->OrderDetail->newEntity();            
                        $details = $this->OrderDetail->patchEntities($details, $orderDetails);
                        
                        $this->OrderDetail->saveMany($details);

                        $response['data'] = $order = $this->Orders->find('all')->where(['Orders.order_id' => $order_id])->contain(['OrderDetail', 'Customer'])->first();
                        $message = 'La orden del Sr.(a) ' . $nameCustomer . ' fue guardada correctamente';                        
                        $this->response->statusCode($code = 200);  
                    } else {
                        $message = 'Se presento un error al guardar la orden';
                        $this->response->statusCode($code = 400);
                    }
                } else {
                    $message = 'La orden cuenta con ' . count($listProductsDiff) . ' productos con que no son permitidos para este cliente y no se permite guardar';
                    $this->response->statusCode($code = 202);
                }
            } else {
                $message = 'NO se permiten mas de 5 productos por orden';
                $this->response->statusCode($code = 202);
            }
        } else {
            $message = 'Metodo no permitido';
                $this->response->statusCode($code = 405);
        }

        $this->set([
            'code' => $code,
            'message' => $message,
            'response' => $response,
            '_serialize' => ['message', 'response', 'code']
        ]);
    }
}
