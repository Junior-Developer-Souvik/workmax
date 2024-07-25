@extends('admin.layouts.app')
@section('page', 'Add Returns')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Returns</li>
        <li>Add</li>
    </ul>
    {{-- @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif --}}
    <form id="myForm" method="post" action="{{ route('admin.returns.save') }}">
    @csrf
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    @php
                        $genAutoIncreNoInv = genAutoIncreNoInv(10,'returns');
                        $genAutoIncreNoInv = "RETRORD".$genAutoIncreNoInv;
                    @endphp
                    <div class="col-sm-2">
                        <label for="">ORDER NO</label>
                        <input type="text" name="order_no" value="{{ $genAutoIncreNoInv }}" class="form-control select-md" readonly id="">
                    </div>            
                    <div class="col-sm-4">
                        <label for="">Store <span class="text-danger">*</span></label>
                        <input type="text" name="store_name" class="form-control select-md" id="store_name" placeholder="Please Choose Store First Here" onkeyup="getStores(this.value);" value="{{ old('store_name') }}" autocomplete="off">
                        <input type="hidden" name="store_id" id="store_id" value="{{ old('store_id') }}">
                        <div class="respDropStore" id="respDropStore" style="position: relative;"></div>

                        @error('store_id') <p class="small text-danger">{{ $message }}</p> @enderror
                    </div> 
                </div>                
            </div>                         
        </div>
        <div class="row">
            <div class="table-responsive order-addmore" id="prodDiv">
                <table class="table table-sm" id="timePriceTable">
                    <h6>Item Details</h6>
                    <thead>
                        <tr>
                            <th width="400px">Product <span class="text-danger">*</span></th>
                            <th>HSN Code <span class="text-danger">*</span></th>
                            <th>Pcs per Ctn <span class="text-danger">*</span></th>
                            <th>Price/Pc (Inc.Tax) <span class="text-danger">*</span></th>
                            <th>No of Ctns <span class="text-danger">*</span></th>
                            <th>Price Per Ctn (Inc.Tax)</th>
                            <th colspan="2">Total Amount (Inc.Tax)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(old('details'))
                        @php
                            $old_details = old('details');
                        @endphp
                        @foreach ($old_details as $key=>$details)
                        <tr id="tr_{{$key}}" class="tr_pro">
                            <td>
                                <input type="text" name="details[{{$key}}][product]"  placeholder="Search product by name ..." class="form-control select-md"  id="product_name{{$key}}" onkeyup="getProductByName(this.value, {{$key}});" value="{{ old('details.'.$key.'.product') }}" autocomplete="off"> 
                                <input type="hidden" name="details[{{$key}}][product_id]" id="product_id{{$key}}" value="{{ old('details.'.$key.'.product_id') }}" class="productids">
                                <div class="respDrop" id="respDrop{{$key}}"></div>
                                @error('details.'.$key.'.product_id') <p class="small text-danger">{{ $message }}</p> @enderror
                            </td>
                            <td>
                                <input type="text"  name="details[{{$key}}][hsn_code]" id="hsn_code{{$key}}" class="form-control" placeholder="Enter HSN Code" value="{{ old('details.'.$key.'.hsn_code') }}" maxlength="6">
                                @error('details.'.$key.'.hsn_code') <p class="small text-danger">{{ $message }}</p> @enderror
                            </td>
                            <td>
                                <input type="number"  name="details[{{$key}}][pcs]" id="pcs{{$key}}" class="form-control" value="{{ old('details.'.$key.'.pcs') }}" onkeyup="if(value<0) value=0;calculatePrice({{$key}})" onchange="if(value<0) value=0;calculatePrice({{$key}})">
                                @error('details.'.$key.'.pcs') <p class="small text-danger">{{ $message }}</p> @enderror
                            </td>
                            
                            <td>
                                <input type="text" name="details[{{$key}}][piece_price]"  id="piece_price{{$key}}" class="form-control piece_price"  placeholder="Product Cost Price" onkeyup="calculatePrice({{$key}})"  onkeypress="validateNum(event)" value="{{ old('details.'.$key.'.piece_price') }}">
                                @error('details.'.$key.'.piece_price') <p class="small text-danger">{{ $message }}</p> @enderror
                            </td>
                            <td>
                                <input type="number" name="details[{{$key}}][qty]" id="qty{{$key}}" min="1" class="form-control" placeholder="Product qty"   onkeyup="if(value<0) value=0;calculatePrice({{$key}})" onchange="if(value<0) value=0;calculatePrice({{$key}})" value="{{ old('details.'.$key.'.qty') }}">
                                @error('details.'.$key.'.qty') <p class="small text-danger">{{ $message }}</p> @enderror
                            </td>
                            <td>
                                <input type="text" name="details[{{$key}}][price_per_carton]" readonly class="form-control"  id="price_per_carton{{$key}}" placeholder="Price per Carton" value="{{ old('details.'.$key.'.price_per_carton') }}">
                            </td>
                            <td>
                                <input type="text" readonly  name="details[{{$key}}][total_price]" id="total_price{{$key}}" class="form-control total_price" placeholder="Total Product Price" value="{{ old('details.'.$key.'.total_price') }}">
                            </td>
                            <td id="btn_td_{{$key}}">
                                <a class="btn btn-sm btn-success actionTimebtn addNewTime" id="addNew{{$key}}">+</a>
                                <a class="btn btn-sm btn-danger actionTimebtn removeTimePrice" onclick="removeRow({{$key}})" id="removeNew{{$key}}">X</a>
                                <a class="btn btn-sm btn-secondary actionTimebtn " id="viewItem{{$key}}" onclick="return viewItem({{$key}});" title="View Item Details"><i class="fa fa-ellipsis-h"></i></a>
                            </td>
                        </tr>  
                        @endforeach
                        @else
                        <tr id="tr_1" class="tr_pro">
                            <td>
                                <input type="text" name="details[1][product]" value="" placeholder="Search product by name ..." class="form-control select-md"  id="product_name1" onkeyup="getProductByName(this.value, 1);" autocomplete="off" > 
                                <input type="hidden" name="details[1][product_id]" id="product_id1" class="productids">
                                <div class="respDrop" id="respDrop1"></div>
                            </td>
                            <td>
                                <input type="text" name="details[1][hsn_code]" id="hsn_code1" class="form-control" placeholder="Enter HSN Code" maxlength="6">
                                
                            </td>
                            <td>
                                <input type="number"  name="details[1][pcs]" id="pcs1" class="form-control" value="1" onkeyup="if(value<0) value=0;calculatePrice(1)" onchange="if(value<0) value=0;calculatePrice(1)">
                            </td>
                            
                            <td>
                                <input type="text"   name="details[1][piece_price]"  id="piece_price1" class="form-control piece_price" value="" placeholder="Product Cost Price" onkeyup="calculatePrice(1)"  onkeypress="validateNum(event)">
                            </td>
                            <td>
                                <input type="number"   name="details[1][qty]" id="qty1" min="1" class="form-control" placeholder="Product qty" value="1" onkeyup="if(value<0) value=0;calculatePrice(1)" onchange="if(value<0) value=0;calculatePrice(1)">
                            </td>
                            <td>
                                <input type="text" name="details[1][price_per_carton]" readonly class="form-control"  id="price_per_carton1" placeholder="Price per Carton">
                            </td>
                            <td>
                                <input type="text" readonly  name="details[1][total_price]" id="total_price1" class="form-control total_price" placeholder="Total Product Price" value="">
                            </td>
                            <td id="btn_td_1">
                                <a class="btn btn-sm btn-success actionTimebtn addNewTime" id="addNew1">+</a>
                                <a class="btn btn-sm btn-danger actionTimebtn removeTimePrice" onclick="removeRow(1)" id="removeNew1">X</a>
                                <a class="btn btn-sm btn-secondary actionTimebtn " id="viewItem1" onclick="return viewItem(1);" title="View Item Details"><i class="fa fa-ellipsis-h"></i></a>
                            </td>
                        </tr>  
                        @endif
                    </tbody>
                </table>
            </div>                
            
            <div class="card shadow-sm">
                <div class="card-body"> 
                    <div class="row mb-3 justify-content-end">
                        <div class="col-md-8">
                            <h6 class="text-muted mb-2">Total Amount (Inc.Tax)</h6>
                        </div>
                        <div class="col-md-4 text-end">
                            <table class="w-100">            
                                <tr class="border-top">
                                    <td>
                                        <h6 class="text-dark mb-0 text-end"> Rs <span id="total_po_price">0</span></h6>
                                        
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>   
            <div class="card shadow-sm">
                <div class="card-body">
                    <a href="{{ route('admin.returns.list') }}" class="btn btn-danger select-md">Back</a>
                    <a href="" class="btn btn-warning select-md">Reset</a>
                    <button type="submit" id="submitBtn" class="btn btn-success select-md">Add</button>
                </div>
            </div>
        </div> 
    </form>
    <!-- Button trigger modal -->
    {{-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal"> Launch demo modal </button> --}}
  
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="prodTitle"></h5>
                    
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Last Three Order History</h6>
                    <div class="table-responsive">
                        <table class="table" id="prodHistTable">
                            <thead>
                                <th>Date</th>
                                <th>Price/Pcs</th>
                                <th>Quantity</th>
                            </thead>
                            <tbody>
                                
                            </tbody>
                        </table>
                    </div>
                    <div id="other-det">

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ok</button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
<script>
    
    var rowCount = $('#timePriceTable tbody tr').length;
    var proIdArr = [];
    var notproIdArr = [];
    $(document).ready(function(){  
        // alert(rowCount)
        if(rowCount == 1){
            $('#removeNew1').hide();
        }

        $('#prodDiv').hide();
        $('#submitBtn').attr('disabled', true);

        @if(old('details'))

            $('#prodDiv').show();
            $('#submitBtn').attr('disabled', false);
            
            var order_amount = 0;
            $('.total_price').each(function(){
                if($(this).val() != ''){
                    order_amount += parseFloat($(this).val());
                }
            });
            $('#total_po_price').text(order_amount);

            
        @endif

        $('.productids').each(function(){ 
            if($(this).val() != ''){
                proIdArr.push($(this).val())
            }  
        });

        // alert(proIdArr)
        
    });


    function getStores(val){
        if(val.length > 0){
            $.ajax({
                url: "{{ route('admin.ledger.getUsersByType') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    term: val,
                    type: 'store'
                },
                success: function(result) {

                    console.log(result);
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show w-100 user-dropdown" aria-labelledby="dropdownMenuButton">`;

                        $.each(result, (key, value) => {                        
                            if(value.bussiness_name != ''){
                                content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.bussiness_name}')">${value.bussiness_name}</a>`;
                            } else {
                                content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.name}')">${value.name}</a>`;
                            }                        
                            
                        })
                        content += `</div>`;
                        // $($this).parent().after(content);
                    } else {
                        content += `<div class="dropdown-menu show w-100 user-dropdown" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No store found</li></div>`;
                    }
                    $('.respDropStore').html(content);
                }
            });
        } else {
            $('.respDropStore').text('');
            $('#store_id').val(0);
            $('#store_name').val('');
        }
        
    }

    function fetchCode(id,name) {
        $('.user-dropdown').hide()
        $('input[name="store_id"]').val(id)
        $('input[name="store_name"]').val(name)
        
        $('#prodDiv').show();
        $('#submitBtn').attr('disabled', false);
        $('#store_name').attr('disabled', true);
        
    }
    
    function validateNum(evt) {
        var theEvent = evt || window.event;

        // Handle paste
        if (theEvent.type === 'paste') {
            key = event.clipboardData.getData('text/plain');
        } else {
        // Handle key press
            var key = theEvent.keyCode || theEvent.which;
            key = String.fromCharCode(key);
        }
        var regex = /[0-9]|\./;
        if( !regex.test(key) ) {
            theEvent.returnValue = false;
            if(theEvent.preventDefault) theEvent.preventDefault();
        }
    }

    
    var i = 2;
    @if (old('details'))
        // {{count(old('details'))}}          
        @foreach($old_details as $key=>$details)
            var totalDetails = "{{$key}}";
        @endforeach        
        // var totalDetails = "{{count(old('details'))}}"; 
        totalDetails = parseInt(totalDetails)    
        console.log('totalDetails:- '+totalDetails);
        i = totalDetails+1;        
    @endif

    console.log('index:- '+i);

    $(document).on('click','.addNewTime',function(){
        var thisClickedBtn = $(this);
        
        var toAppend = `
        <tr id="tr_`+i+`" class="tr_pro">
            <td>
                <input type="text" name="details[`+i+`][product]" value="" placeholder="Search product by name ..." class="form-control select-md"  id="product_name`+i+`" onkeyup="getProductByName(this.value, `+i+`);" autocomplete="off" > 
                <input type="hidden" name="details[`+i+`][product_id]" id="product_id`+i+`" class="productids">
                <div class="respDrop" id="respDrop`+i+`"></div>
            </td>
            <td>
                <input type="text"  name="details[`+i+`][hsn_code]" id="hsn_code`+i+`" class="form-control" placeholder="Enter HSN Code" maxlength="6">
                
            </td>
            <td>
                <input type="number"  name="details[`+i+`][pcs]" id="pcs`+i+`" class="form-control" value="1" onkeyup="if(value<0) value=0;calculatePrice(`+i+`)" onchange="if(value<0) value=0;calculatePrice(`+i+`)">
            </td>
            <td>
                <input type="text"   name="details[`+i+`][piece_price]"  id="piece_price`+i+`" class="form-control piece_price" value="" placeholder="Product Cost Price" onkeyup="calculatePrice(`+i+`)"  onkeypress="validateNum(event)">
            </td>
            <td>
                <input type="number"   name="details[`+i+`][qty]" id="qty`+i+`" min="1" class="form-control" placeholder="Product qty" value="1" onkeyup="if(value<0) value=0;calculatePrice(`+i+`)" onchange="if(value<0) value=0;calculatePrice(`+i+`)">
            </td>
            <td>
                <input type="text" name="details[`+i+`][price_per_carton]" readonly class="form-control"  id="price_per_carton`+i+`" placeholder="Price per Carton">
            </td>
            <td>
                <input type="text" readonly  name="details[`+i+`][total_price]" id="total_price`+i+`" class="form-control total_price" placeholder="Total Product Price" value="">
            </td>
            <td id="btn_td_`+i+`">
                <a class="btn btn-sm btn-success actionTimebtn addNewTime" id="addNew`+i+`">+</a>
                <a class="btn btn-sm btn-danger actionTimebtn removeTimePrice" onclick="removeRow(`+i+`)" id="removeNew`+i+`">X</a>
                <a class="btn btn-sm btn-secondary actionTimebtn " id="viewItem`+i+`" onclick="return viewItem(`+i+`);" title="View Item Details"><i class="fa fa-ellipsis-h"></i></a>
            </td>
        </tr>
        `;

        $('#timePriceTable').append(toAppend);
        i++;
    });

    
    function removeRow(i){
        // alert(i);
        var count_tr_pro = $('.tr_pro').length;        
        // alert(count_tr_pro);
        if(count_tr_pro > 1){
            var total_price = $('#total_price'+i).val();
            var total_po_price = $('#total_po_price').html();
            var now_po_price = (total_po_price - total_price);
            
            var proId = $('#product_id'+i).val();                        
            proIdArr =  proIdArr.filter(e => e!=proId)

            $('#tr_'+i).remove();
            $('#total_po_price').html(now_po_price);
        }        
    }

    function financial(x) {
        return Number.parseFloat(x).toFixed(2).replace(/[.,]00$/, "");
    }

    function calculatePrice(number)
    {
        // alert(number);
        /* Clear previous output */   
        // $('#total_price'+number).val('');

        /* Calculate new entry */
        var pcs = $('#pcs'+number).val();
        var piece_price = $('#piece_price'+number).val();
        var qty = $('#qty'+number).val();

        var price_per_carton = (pcs * piece_price) ; 
        $('#price_per_carton'+number).val(price_per_carton);

        var new_price_per_ctn = $('#price_per_carton'+number).val();
        var totalPrice = (new_price_per_ctn * qty);
        // var productPrice = (price_per_carton / pcs);
        totalPrice = financial(totalPrice);
        $('#total_price'+number).val(totalPrice);
        
        var sumPO = 0;
        $('.total_price').each(function(){
            sumPO += parseFloat($(this).val());
        });
        $('#total_po_price').text(sumPO);
        // alert(sumPO);        
    }
    
    function getProductByName(name, count) {  
        var store_id = $('#store_id').val();
        if(name.length > 0) {
            $.ajax({
                url: "{{ route('admin.product.searchByName') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    term: name,
                    store_id: store_id
                    // idnotin: proIdArr,

                },
                success: function(result) {
                    // console.log(result);
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show w-100 product-dropdown select-md" aria-labelledby="dropdownMenuButton">`;

                        $.each(result, (key, value) => {
                            content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchProduct('${count}',${value.id})">${value.name}</a>`;
                        })
                        content += `</div>`;
                        // $($this).parent().after(content);
                    } else {
                        content += `<div class="dropdown-menu show w-100 product-dropdown select-md" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No product found</li></div>`;
                    }
                    $('#respDrop'+count).html(content);
                }
            });
        }   else {
            $('.product-dropdown').hide()
        }   
        
    }

    function fetchProduct(count,id) {
        $('.product-dropdown').hide()
        var store_id = $('#store_id').val();
        $.ajax({
            url: "{{ route('admin.product.viewDetail') }}",
            method: 'post',
            data: {
                '_token': '{{ csrf_token() }}',
                id: id,
                store_id: store_id
            },
            success: function(result) {
                console.log(result);
                var ordHistory = result.last_three_order_product;
                console.log(ordHistory);
                var count_stock = result.count_stock;
                console.log('count_stock:- '+count_stock);

                $('#exampleModal').modal('show');
                $('#prodTitle').html(result.name);
                var prodOrdHistoryHTML = otherDet = ``;
                if(ordHistory.length > 0){
                    for(var i=0; i < ordHistory.length; i++)
                    prodOrdHistoryHTML += `
                    <tr>
                        <td>`+ordHistory[i].created_date+`</td>
                        <td>`+ordHistory[i].piece_price+`</td>
                        <td>`+ordHistory[i].qty+` ctns (`+ordHistory[i].pcs+` pcs)</td>
                    </tr>
                    `;

                    
                } else {
                    prodOrdHistoryHTML += `
                    <tr>
                        <td colspan="3" style="text-align: center;">No sales amount found.</td>
                    </tr>
                    `;
                    otherDet += `
                    <span>Master Sell Price is <strong>Rs. `+result.sell_price+`</strong></span> <br/>
                    
                    `;
                }
                otherDet += `
                    <span> Current Stock:- <strong> `+count_stock+` </strong> ctns </span> <br/>
                    `;                    
                $('#prodHistTable tbody').html(prodOrdHistoryHTML);
                $('#other-det').html(otherDet);




                var name = result.name;
                var hsn_code = result.hsn_code;
                // var sell_price = result.sell_price;
                var piece_price = result.piece_price;
                var pcs = result.pcs;
                $('#product_name'+count).val(name);
                $('#product_id'+count).val(id);
                
                $("#hsn_code" + count).val(hsn_code);
                $("#pcs" + count).val(pcs);
                $('#piece_price'+count).val(piece_price);

                var qty = $('#qty'+count).val();
                var pcs = $('#pcs'+count).val();

                var price_per_carton = (pcs * piece_price);
                $('#price_per_carton'+count).val(price_per_carton);

                var new_price_per_ctn = $('#price_per_carton'+count).val();
                var totalPrice = (new_price_per_ctn * qty);
                totalPrice = financial(totalPrice);

                $('#total_price'+count).val(totalPrice);        
                $('#removeNew'+count).show();

                var sumPO = 0;
                $('.total_price').each(function(){
                    if($(this).val() != ''){
                        sumPO += parseFloat($(this).val());
                    }
                });
                $('#total_po_price').text(sumPO);

                proIdArr.push(id);
                
            }
        });                
    }

    $(document).ready(function(){
        $("#myForm").submit(function() {
            // $('input').attr('disabled', 'disabled');
            $('#submitBtn').attr('disabled', 'disabled');
            return true;
        });
    });

    function viewItem(i){
        // alert(i);
        var productId = $('#product_id'+i).val();
        var store_id = $('#store_id').val();
        if(productId != ''){
            $.ajax({
                url: "{{ route('admin.product.viewDetail') }}",
                method: 'post',
                dataType: 'json',
                data: {
                    '_token': '{{ csrf_token() }}',
                    id: productId,
                    store_id: store_id
                },
                success: function(result) {
                    console.log(result);
                    var ordHistory = result.last_three_order_product;
                    console.log(ordHistory);
                    var count_stock = result.count_stock;
                    console.log('count_stock:- '+count_stock);
                    $('#exampleModal').modal('show');
                    $('#prodTitle').html(result.name)
                    var prodOrdHistoryHTML = ``;
                    var otherDet = ``;
                    if(ordHistory.length > 0){
                        for(var i=0; i < ordHistory.length; i++)
                        prodOrdHistoryHTML += `
                        <tr>
                            <td>`+ordHistory[i].created_date+`</td>
                            <td>`+ordHistory[i].piece_price+`</td>
                            <td>`+ordHistory[i].qty+` ctns (`+ordHistory[i].pcs+` pcs)</td>
                        </tr>
                        `;
                    } else {
                        prodOrdHistoryHTML += `
                        <tr>
                            <td colspan="3" style="text-align: center;">No sales amount found.</td>
                        </tr>
                        `;
                        otherDet += `
                        <span>Master Sell Price is <strong>Rs. `+result.sell_price+`</strong></span> <br/>
                        
                        `;
                    }
                    otherDet += `
                        <span> Current Stock:- <strong> `+count_stock+` </strong> ctns </span> <br/>
                        `;                    
                    $('#prodHistTable tbody').html(prodOrdHistoryHTML);
                    $('#other-det').html(otherDet);
                    
                }
            });
        } else {
            alert('Please Choose Product First');
        }
    }

</script>
@endsection
