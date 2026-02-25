<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
class InvoiceController extends Controller
{

    public function InvoicePage(){
       return view('pages.dashboard.invoice-page');
    }


    public function SalePage(){
        return view('pages.dashboard.sale-page');
    }


    public function invoiceCreate(Request $request){

        DB::beginTransaction();

        try {

        $user_id=$request->header('user_id');
        $total=$request->input('total');
        $discount=$request->input('discount');
        $vat=$request->input('vat');
        $payable=$request->input('payable');
        $customer_id=$request->input('customer_id');
        $invoice= Invoice::create([
            'total'=>$total,
            'discount'=>$discount,
            'vat'=>$vat,
            'payable'=>$payable,
            'user_id'=>$user_id,
            'customer_id'=>$customer_id,
        ]);

       $invoiceID=$invoice->id;
       $products= $request->input('products');

       foreach ($products as $EachProduct) {
            InvoiceProduct::create([
                'invoice_id' => $invoiceID,
                'user_id'=>$user_id,
                'product_id' => $EachProduct['product_id'],
                'qty' =>  $EachProduct['qty'],
                'sale_price'=>  $EachProduct['sale_price'],
            ]);
        }

       DB::commit();
       return 1;

        }
        catch (Exception $e) {
            DB::rollBack();
            return 0;
        }
    }
}
