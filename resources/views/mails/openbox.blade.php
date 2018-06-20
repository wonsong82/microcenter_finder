<p>{{$item->product_description}}</p>
<a href="{{$item->product_link}}">{{$item->product_link}}</a>
<br>
<table cellpadding="5" cellspacing="0" border="1">
    <tr>
        <th>Price</th>
        <td>{{$item->openbox_price}}</td>
    </tr>
    <tr>
        <th>Original Price</th>
        <td>
            @if($item->product_original_price)
            {{$item->product_original_price}} =>
            @endif
            {{$item->product_price}}
        </td>
    </tr>
    <tr>
        <th>SKU</th>
        <td>{{$item->sku}}</td>
    </tr>
    <tr>
        <th>Product ID</th>
        <td>{{$item->product_id}}</td>
    </tr>
    <tr>
        <th>Openbox ID</th>
        <td>{{$item->openbox_id}}</td>
    </tr>
    <tr>
        <th>Store No.</th>
        <td>{{$item->store_number}}</td>
    </tr>
    <tr>
        <th>MPN</th>
        <td>{{$item->mpn}}</td>
    </tr>
    <tr>
        <th>Brand</th>
        <td>{{$item->brand}}</td>
    </tr>


</table>