<?php

namespace App\Http\Controllers;


use App\ShopifyTrigger;
use App\ShopifyWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use App\HandwryttenApi;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Log;
use App\ShopifyOrder;
use App\ShopifyCustomer;



class ShopifyOrderController extends Controller
{


  public function getorder(Request $request)
  {


    $shopDomain = request()->header('x-shopify-shop-domain');
    $expShopName =  substr($shopDomain, 0, -14);
    $customer_id = $request['customer']['id'];
    $userdata = DB::table('users')->where([
      ['name', '=', $shopDomain]
    ])->first();
    $userid = $userdata->id;

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://' . $expShopName . '.myshopify.com/admin/api/2020-10/customers/' . $customer_id . '.json',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer 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',
        'Cookie: _master_udr=eyJfcmFpbHMiOnsibWVzc2FnZSI6IkJBaEpJaWt6TVRoaE1HTmpPQzFrTWpZeExUUTBZV1V0WVRjNU9DMWtaREZoWmpnME1EVTROV0lHT2daRlJnPT0iLCJleHAiOiIyMDIyLTEyLTA3VDE0OjA0OjExLjk2MVoiLCJwdXIiOiJjb29raWUuX21hc3Rlcl91ZHIifX0%3D--4db3a382ffd9f9a30a477ee081a20551867803e5; _secure_admin_session_id_csrf=a70a2d916a7ac8a14ab30b83ad4d0ac4; _secure_admin_session_id=a70a2d916a7ac8a14ab30b83ad4d0ac4; new_admin=1; _shopify_y=391b2156-cc2f-4a03-bac7-f4597f705f8e; _landing_page=%2Fadmin%2Fauth%2Flogin; _shopify_fs=2020-12-07T14%3A04%3A12Z; _orig_referrer=; _y=391b2156-cc2f-4a03-bac7-f4597f705f8e; identity-state=BAh7B0kiJWM4NzQ1MjAzOGFiOGU2MGZkYTU4MDFiNDkzNWM1NDA4BjoGRUZ7DEkiDnJldHVybi10bwY7AFRJIjhodHRwczovL2hhbmR3cmlpdGVuMS5teXNob3BpZnkuY29tL2FkbWluL2F1dGgvbG9naW4GOwBUSSIRcmVkaXJlY3QtdXJpBjsAVEkiRGh0dHBzOi8vaGFuZHdyaWl0ZW4xLm15c2hvcGlmeS5jb20vYWRtaW4vYXV0aC9pZGVudGl0eS9jYWxsYmFjawY7AFRJIhBzZXNzaW9uLWtleQY7AFQ6DGFjY291bnRJIg9jcmVhdGVkLWF0BjsAVGYXMTYwNzM0OTg1MS45ODQ0MDEySSIKbm9uY2UGOwBUSSIlNGEwMzY2OTk5YmQ5MjI0ZDY2NmZmYmY3NGRlOTNiN2QGOwBGSSIKc2NvcGUGOwBUWwtJIgplbWFpbAY7AFRJIjdodHRwczovL2FwaS5zaG9waWZ5LmNvbS9hdXRoL2Rlc3RpbmF0aW9ucy5yZWFkb25seQY7AFRJIgtvcGVuaWQGOwBUSSIMcHJvZmlsZQY7AFRJIk5odHRwczovL2FwaS5zaG9waWZ5LmNvbS9hdXRoL3BhcnRuZXJzLmNvbGxhYm9yYXRvci1yZWxhdGlvbnNoaXBzLnJlYWRvbmx5BjsAVEkiMGh0dHBzOi8vYXBpLnNob3BpZnkuY29tL2F1dGgvYmFua2luZy5tYW5hZ2UGOwBUSSIPY29uZmlnLWtleQY7AFRJIgxkZWZhdWx0BjsAVEkiJTkwYmQyNDY1MTQ1NzNjMjdhYjdmMjBiZTlmNmY3Y2JmBjsARnsMQAhJIjhodHRwczovL2hhbmR3cmlpdGVuMS5teXNob3BpZnkuY29tL2FkbWluL2F1dGgvbG9naW4GOwBUQApJIkRodHRwczovL2hhbmR3cmlpdGVuMS5teXNob3BpZnkuY29tL2FkbWluL2F1dGgvaWRlbnRpdHkvY2FsbGJhY2sGOwBUQAw7BkANZhcxNjA3MzQ5ODkxLjYwOTc5OTFAD0kiJTBmMjllY2U3OTcxOWEzNzRkYzZmZGQ5ZWVkZjc1MmZhBjsARkARWwtJIgplbWFpbAY7AFRJIjdodHRwczovL2FwaS5zaG9waWZ5LmNvbS9hdXRoL2Rlc3RpbmF0aW9ucy5yZWFkb25seQY7AFRJIgtvcGVuaWQGOwBUSSIMcHJvZmlsZQY7AFRJIk5odHRwczovL2FwaS5zaG9waWZ5LmNvbS9hdXRoL3BhcnRuZXJzLmNvbGxhYm9yYXRvci1yZWxhdGlvbnNoaXBzLnJlYWRvbmx5BjsAVEkiMGh0dHBzOi8vYXBpLnNob3BpZnkuY29tL2F1dGgvYmFua2luZy5tYW5hZ2UGOwBUQBlJIgxkZWZhdWx0BjsAVA%3D%3D--8ffec8a5ac48ae586055128ed62029fbb7ec054e; _ab=1; online_store_editor_user_locale=en'
     
      ),
    ));

    $response = curl_exec($curl);
    $resdecode = json_decode($response, true);

    $customer_ordercount = $resdecode['customer']['orders_count'];

    $tableNameOrders = $expShopName . '_shopifyorders';




    $triggeravilable = DB::table('shopify_triggers')->where([
      ['user_id', '=', $userid],
      ['trigger_status', '=', 1],
      ['trigger_name', '=', "First Order Placed"],
    ])->count();
    $order_created_date = $request['created_at'];
    $customer_created_date = $resdecode['customer']['created_at'];
    $datevalidate = 0;
    $explodeddate = explode("T", $order_created_date);
    $explodeddate2 = explode("T",   $customer_created_date);
    if ($explodeddate[0] != $explodeddate2[0]) {
      $datevalidate = 1;
    } else {
      $datevalidate = 0;
    }

    $handwryttendata = DB::table('handwrytten_apis')->where([
      ['user_id', '=', $userid],
    ])->first();
    $useremail = $handwryttendata->email;
    $pass = $handwryttendata->password;

    //first Order trigger Webhook

    if ($customer_ordercount == 1 && $triggeravilable == 1 && $datevalidate == 1) {


      $triggerget = DB::table('shopify_triggers')->where([
        ['user_id', '=', $userid],
        ['trigger_status', '=', 1],
        ['trigger_name', '=', "First Order Placed"],
      ])->first();
      DB::table($tableNameOrders)->insert([
        'order_id'                       =>  $request->id,
        'order_number'                   =>  $request->number,
        'email'                          =>  $request->email,
        'order_status_url'               =>  $request->order_status_url,
        'product_id'                     =>  $request['line_items'][0]['product_id'],
        'title'                          =>  $request['line_items'][0]['title'],
        'quantity'                       =>  $request['line_items'][0]['quantity'],
        'amount'                         =>  $request['total_line_items_price_set']['shop_money']['amount'],
        'currency_code'                  =>  $request['total_line_items_price_set']['shop_money']['currency_code'],
        'vendor'                         =>  $request['line_items'][0]['vendor'],
        'recipient_name'                 =>  $request['shipping_address']['name'],
        'recipient_business_name'        =>  $request['shipping_address']['company'],
        'recipient_address1'             =>  $request['shipping_address']['address1'],
        'recipient_address2'             =>  $request['shipping_address']['address2'],
        'recipient_city'                 =>  $request['shipping_address']['city'],
        'recipient_province'             =>  $request['shipping_address']['province'],
        'recipient_zip'                  =>  $request['shipping_address']['zip'],
        'recipient_country'              =>  $request['shipping_address']['country_code']
      ]);

      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.handwrytten.com/v1/auth/authorization",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => array('login' => $useremail, 'password' => $pass),
        CURLOPT_HTTPHEADER => array(
          "Accept: application/json",
        ),
      ));

      $response = curl_exec($curl);
      $data = json_decode($response);
      $uid = $data->uid;
      $credcardid = "";
      $wrapper_singlestep_order = array(
        "uid"                       => $uid,
        "card_id"                   => $triggerget->card_id,
        "denomination_id"           => "",
        "message"                   => $triggerget->trigger_message,
        "font_label"                => $triggerget->trigger_handwriting_style,
        "sender_name"               => "Randy Rose",
        "sender_business_name"      => "123456",
        "sender_address1"           => "2112 Manchester",
        "sender_address2"           => "",
        "sender_city"               => "Los Angeles",
        "sender_state"              => "CA",
        "sender_zip"                => "91111",
        "sender_country"            => "USA",
        "recipient_name"            => $request['shipping_address']['name'],
        "recipient_business_name"   => $request['shipping_address']['company'],
        "recipient_address1"        => $request['shipping_address']['address1'],
        "recipient_address2"        => $request['shipping_address']['address1'],
        "recipient_city"            => $request['shipping_address']['city'],
        "recipient_state"           => $request['shipping_address']['province'],
        "recipient_zip"             => $request['shipping_address']['zip'],
        "recipient_country"         => $request['shipping_address']['country_code'],
        "insert_id"                 => $triggerget->trigger_insert,
        "credit_card_id"            => $credcardid

      );
      $postdata = json_encode($wrapper_singlestep_order);

      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://backend.handwrytten.com/api.php/api/orders/singleStepOrder",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        //  CURLOPT_POSTFIELDS => $postdata,
        CURLOPT_POSTFIELDS => "{\r\n\"uid\":\"$uid\",\r\n\"card_id\":\"$triggerget->card_id\",\r\n\"denomination_id\":\"\",\r\n\"message\":\"$triggerget->trigger_message\",\r\n\"font_label\":\"$triggerget->trigger_handwriting_style\", \r\n\"sender_name\":\"Randy Rose\",\r\n\"sender_business_name\":\"123456\",\r\n\"sender_address1\":\"2112 Manchester\",\r\n\"sender_address2\":\"\",\r\n\"sender_city\":\"Los Angeles\",\r\n\"sender_state\":\"CA\",\r\n\"sender_zip\":\"91111\",\r\n\"sender_country\":\"USA\",\r\n\"recipient_name\":\"Josh Davis\",\r\n\"recipient_business_name\":\"Express Logistics and Transport\",\r\n\"recipient_address1\":\"621 SW 5th Avenue Suite 400\",\r\n\"recipient_address2\":\"\",\r\n\"recipient_city\":\"Portland\",\r\n\"recipient_state\":\"OR\",\r\n\"recipient_zip\":\"85123\",\r\n\"recipient_country\":\"USA\",\r\n\"insert_id\":\"\",\r\n\"credit_card_id\":\"$credcardid\"\r\n}",
        CURLOPT_HTTPHEADER => array(
          "cache-control: no-cache",
          "content-type: application/json",
          "postman-token: 5100f8af-c0f2-2691-8872-53f00bbd5fb4"
        ),
      ));

      $responseyt = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);
    }

    //	$ Purchase Threshold (Single Order)
    $triggeravilable_purchase_threshold = DB::table('shopify_triggers')->where([
      ['user_id', '=', $userid],
      ['trigger_status', '=', 1],
      ['trigger_name', '=', "$ Purchase Threshold (Single Order)"],
    ])->count();



    if ($triggeravilable_purchase_threshold == 1) {

      $triggerget_purchse_threshold_details = DB::table('shopify_triggers')->where([
        ['user_id', '=', $userid],
        ['trigger_status', '=', 1],
        ['trigger_name', '=', "$ Purchase Threshold (Single Order)"],
      ])->first();
      $order_total_price = $request['total_price'];
      $threshold_trigger_amount = $triggerget_purchse_threshold_details->trigger_amount;
      $validate_threshold = 0 ;
      if ($order_total_price >=$threshold_trigger_amount) {
        $validate_threshold = 1;
      }
      Log::info($order_total_price);
      if ($validate_threshold == 1) {
        DB::table($tableNameOrders)->insert([
          'order_id'                       =>  $request->id,
          'order_number'                   =>  $request->number,
          'email'                          =>  $request->email,
          'order_status_url'               =>  $request->order_status_url,
          'product_id'                     =>  $request['line_items'][0]['product_id'],
          'title'                          =>  $request['line_items'][0]['title'],
          'quantity'                       =>  $request['line_items'][0]['quantity'],
          'amount'                         =>  $request['total_line_items_price_set']['shop_money']['amount'],
          'currency_code'                  =>  $request['total_line_items_price_set']['shop_money']['currency_code'],
          'vendor'                         =>  $request['line_items'][0]['vendor'],
          'recipient_name'                 =>  $request['shipping_address']['name'],
          'recipient_business_name'        =>  $request['shipping_address']['company'],
          'recipient_address1'             =>  $request['shipping_address']['address1'],
          'recipient_address2'             =>  $request['shipping_address']['address2'],
          'recipient_city'                 =>  $request['shipping_address']['city'],
          'recipient_province'             =>  $request['shipping_address']['province'],
          'recipient_zip'                  =>  $request['shipping_address']['zip'],
          'recipient_country'              =>  $request['shipping_address']['country_code']
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.handwrytten.com/v1/auth/authorization",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => array('login' => $useremail, 'password' => $pass),
          CURLOPT_HTTPHEADER => array(
            "Accept: application/json",
          ),
        ));

        $response = curl_exec($curl);
        $data = json_decode($response);
        $uid = $data->uid;
        $credcardid = "";
        $wrapper_singlestep_order = array(
          "uid"                       => $uid,
          "card_id"                   => $triggerget_purchse_threshold_details->card_id,
          "denomination_id"           => "",
          "message"                   => $triggerget_purchse_threshold_details->trigger_message,
          "font_label"                => $triggerget_purchse_threshold_details->trigger_handwriting_style,
          "sender_name"               => "Randy Rose",
          "sender_business_name"      => "123456",
          "sender_address1"           => "2112 Manchester",
          "sender_address2"           => "",
          "sender_city"               => "Los Angeles",
          "sender_state"              => "CA",
          "sender_zip"                => "91111",
          "sender_country"            => "USA",
          "recipient_name"            => $request['shipping_address']['name'],
          "recipient_business_name"   => $request['shipping_address']['company'],
          "recipient_address1"        => $request['shipping_address']['address1'],
          "recipient_address2"        => $request['shipping_address']['address1'],
          "recipient_city"            => $request['shipping_address']['city'],
          "recipient_state"           => $request['shipping_address']['province'],
          "recipient_zip"             => $request['shipping_address']['zip'],
          "recipient_country"         => $request['shipping_address']['country_code'],
          "insert_id"                 => $triggerget_purchse_threshold_details->trigger_insert,
          "credit_card_id"            => $credcardid

        );
        $postdata = json_encode($wrapper_singlestep_order);

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://backend.handwrytten.com/api.php/api/orders/singleStepOrder",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          //  CURLOPT_POSTFIELDS => $postdata,
          CURLOPT_POSTFIELDS => "{\r\n\"uid\":\"$uid\",\r\n\"card_id\":\"$triggerget_purchse_threshold_details->card_id\",\r\n\"denomination_id\":\"\",\r\n\"message\":\"$triggerget_purchse_threshold_details->trigger_message\",\r\n\"font_label\":\"$triggerget_purchse_threshold_details->trigger_handwriting_style\", \r\n\"sender_name\":\"Randy Rose\",\r\n\"sender_business_name\":\"123456\",\r\n\"sender_address1\":\"2112 Manchester\",\r\n\"sender_address2\":\"\",\r\n\"sender_city\":\"Los Angeles\",\r\n\"sender_state\":\"CA\",\r\n\"sender_zip\":\"91111\",\r\n\"sender_country\":\"USA\",\r\n\"recipient_name\":\"Josh Davis\",\r\n\"recipient_business_name\":\"Express Logistics and Transport\",\r\n\"recipient_address1\":\"621 SW 5th Avenue Suite 400\",\r\n\"recipient_address2\":\"\",\r\n\"recipient_city\":\"Portland\",\r\n\"recipient_state\":\"OR\",\r\n\"recipient_zip\":\"85123\",\r\n\"recipient_country\":\"USA\",\r\n\"insert_id\":\"\",\r\n\"credit_card_id\":\"$credcardid\"\r\n}",
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/json",
            "postman-token: 5100f8af-c0f2-2691-8872-53f00bbd5fb4"
          ),
        ));

        $responseyt = curl_exec($curl);
        Log::info($responseyt);
        $err = curl_error($curl);
        curl_close($curl);
      }
    }
  }
}
