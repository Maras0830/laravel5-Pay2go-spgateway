# Laravel5-Pay2Go

Cash trading with Pay2Go Package on Laravel 5.*

## Official Documentation

Official Documentation for the Cash can be found on the [Pay2Go MPGapi_V1_1_8](https://www.pay2go.com/dw_files/info_api/pay2go_gateway_MPGapi_V1_1_8.pdf).

## Installation

```bash
"maras0830/laravel5-pay2go": "dev-master"
```

In ```config/app.php``` add ```providers```
```php
Maras0830\Pay2Go\Providers\Pay2GoServiceProvider::class
```
In ```config/app.php``` add ```aliases```  
```php
'Pay2Go' => Maras0830\Pay2Go\Pay2Go::class
```

In ```.env``` add, you can register on  

[Pay2Go](https://www.pay2go.com/) or 
[Pay2Go Test](https://cweb.pay2go.com/)    


```bash
CASH_STORE_ID=xxxxxxxx
CASH_STORE_HashKey=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
CASH_STORE_HashIV=xxxxxxxxxxxxxxxx
```

default return url
```bash
CASH_ReturnUrl=/order/completed
CASH_NotifyURL=/order/notify
CASH_Client_BackUrl=/order/cancel
```

Publish config.
```php
php artisan vendor:publish --force
```

## USAGE
routes.php
```php
Route::get('cash', 'CashController@index');
Route::post('cash/create', 'CashController@store');
```

CashController.php
```
public function index()
{
    return view('cash.index');
}
```

resources/views/cash/index.blade.php
```html
<html>
    <head>
        <title>Test Cash</title>
    </head>
    <body>
        <h1>智付寶 - 訂單測試</h1>
        <form name='Pay2go' method='post' action='{{ url('/cash/create') }}'>
            {!! csrf_field() !!}
            商店訂單編號：<input type="text" name="MerchantOrderNo" value="<?php echo "20160825" . random_int(1000,9999) ?>"/> <br/>
            訂單金額：<input type="text" name="Amt" value="<?php echo random_int(0,9999) ?>"> <br/>
            商品資訊：<input type="text" name="ItemDesc" value="測試商品資訊敘述"> <br/>
            Email：<input type="text" name="Email" value="Maras0830@gmail.com"> <br/>
    
            <input type='submit' value='Submit'>
        </form>
    </body>
</html>

````

CashController.php
```php
public function store(Request $request)
{
    $form = $request->except('_token');
    
    // 建立商店
    $pay2go = new Pay2Go(env('CASH_STORE_ID'), env('CASH_STORE_HashKey'), env('CASH_STORE_HashIV'));
    
    // 商品資訊
    $order = $pay2go->setOrder($form['MerchantOrderNo'], $form['Amt'], $form['ItemDesc'], $form['Email'])->submitOrder();  
    

    // 將資訊回傳至自定義 view javascript auto submit
    return view('cash.submit')->with(compact('order'));
}
```

resources/views/cash/submit.blade.php
```html
    <html>
        <head>
            <title>redirect pay2go ...</title>
        </head>
    
        <body>
            {!! $order !!}
        </body>
    </html>
```

## FEATURE
Only support add order, will add invoice feature and welcome developers join this project. 
##
