
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="format-detection" content="date=no" />
	<meta name="format-detection" content="address=no" />
	<meta name="format-detection" content="telephone=no" />
	<meta name="x-apple-disable-message-reformatting" />
	<title>{{$order_no}}</title>
</head>

<body class="body" style="padding:0 !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; background:#f9f9f9; -webkit-text-size-adjust:none; font-family: Arial, Helvetica, sans-serif;">
	<table id="packing_table" style="width:700px; margin: 0 auto;" width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td colspan="4" style="font-size: 20px; font-weight: bolder; padding: 40px 0 20px; border-bottom: 1px solid #000; text-transform: uppercase;" align="center" valign="top">
				Goods Return Slip
			</td>
		</tr>
		<tr>
			<td width="100%" colspan="4">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td valign="top" style="font-size: 14px; font-weight: bold; padding: 10px; border-bottom: 1px solid #000;" width="70%">
                            {{$data->store->bussiness_name}}
                        </td>
						<td style="font-size: 14px; font-weight: normal; padding: 10px; border-left: 1px solid #000; border-bottom: 1px solid #000;" width="70%">{{$order_no}} <br> <br> {{ date('d/m/Y',strtotime($data->created_at)) }}</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<th style="font-size: 14px; padding:10px; border-bottom: 1px solid #000;" align="left">Description of goods</th>
			<th style="font-size: 14px; padding:10px; border-bottom: 1px solid #000;" align="center">Quantity (pcs)</th>
			<th style="font-size: 14px; padding:10px; border-bottom: 1px solid #000;" align="center">Rate</th>
			<th style="font-size: 14px; padding:10px; border-bottom: 1px solid #000;" align="right">Amount</th>
		</tr>
		@php
			$total_amount = 0;
		@endphp
		@foreach ( $data->return_products as $items )
		@php
			// $total_amount += $items->total_price; 
		@endphp
		<tr>
			<td style="font-size: 13px; line-height: 1; font-weight: normal; color:#222; padding: 5px 10px; border-bottom: 1px solid #eee;" align="left">{{$items->product->name}}</td>
			<td style="font-size: 13px; line-height: 1; font-weight: normal; color:#222; padding: 5px 10px; border-bottom: 1px solid #eee;" align="center">{{($items->pcs*$items->quantity)}}</td>
			<td style="font-size: 13px; line-height: 1; font-weight: normal; color:#222; padding: 5px 10px; border-bottom: 1px solid #eee;" align="center">Rs. {{ number_format((float)$items->piece_price, 2, '.', '') }}</td>
			<td style="font-size: 13px; line-height: 1; font-weight: normal; color:#222; padding: 5px 10px; border-bottom: 1px solid #eee;" align="right">Rs. {{ number_format((float)$items->total_price, 2, '.', '') }}</td>
		</tr>
		@endforeach	

		<tr>
			<td width="100%" valign="top" colspan="4" style="padding-top: 30px;">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td width="70%" style="text-transform: uppercase; padding: 10px 0; border-bottom: 1px solid #000; border-top: 1px solid #000; font-size: 14px;" align="right">Item Value</td>
						<td width="30%" style="padding: 10px 0; border-bottom: 1px solid #000; border-top: 1px solid #000; font-size: 14px;" align="right">Rs. {{ number_format((float)$data->amount, 2, '.', '') }}</td>
					</tr>
				</table>
			</td>
		</tr>
		</tbody>
	</table>
    <script>
       
    </script>
</body>
</html>