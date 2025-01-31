@extends('admin.layouts.app')
@section('page', ($user_type == 1)? 'Create Customer' : 'Create Supplier')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Master</li>
        <li><a href="{{ route('admin.user.index', 'supplier') }}">Supplier Management</a></li>
        <li>Create Supplier</li>
    </ul>
    <form id="myForm" method="post" action="{{ route('admin.user.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-sm-9">
                <div class="form-group mb-3">                    
                    <input type="hidden" name="type" value="{{$user_type}}">
                    @error('type') <p class="small text-danger">{{ $message }}</p> @enderror
                </div>
                <div class="card shadow-sm">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label for="">Person Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" placeholder="User Name" class="form-control" value="{{old('name')}}">
                                @error('name') <p class="small text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>                    
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label for="">Email</label>
                                <input type="email" name="email" placeholder="User email" class="form-control" value="{{old('email')}}">
                                @error('email') <p class="small text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group mb-3">
                                <label for="">Mobile</label>
                                <input type="text" maxlength="10" name="mobile" id="mobile"  placeholder="User mobile" class="form-control" value="{{old('mobile')}}" onkeypress="validateNum(event)">
                                @error('mobile') <p class="small text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <input type="hidden" name="is_wa_same" id="checkWhatsappHidden" value="0">
                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" name="is_wa_same" id="checkWhatsappSame">
                                    <label class="form-check-label" for="checkWhatsappSame">
                                      Same as Phone Number
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group mb-3">
                                <label for="">Whatsapp no <span class="text-danger">*</span></label>
                                <input type="text" maxlength="10" name="whatsapp_no" id="whatsapp_no" placeholder="User whatsapp no" class="form-control" value="{{old('whatsapp_no')}}" onkeypress="validateNum(event)">
                                @error('whatsapp_no') <p class="small text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group mb-3">
                                <label for="">GST Number </label>
                                <input type="text" name="gst_number" placeholder="GST Number" maxlength="50" class="form-control" value="{{old('gst_number')}}">
                                @error('gst_number') <p class="small text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>                    
                        
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        Address
                    </div>
                    <div class="card-body pt-0">
                        <div class="admin__content">
                            <aside>
                                <nav>Billing Address</nav>
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Address</label>
                                    </div>
                                    <div class="col-9">
                                        <input type="text" id="billing_addr" class="form-control" aria-describedby="priceHelpInline" name="billing_address" value="{{old('billing_address')}}">
                                        @error('billing_address') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Landmark</label>
                                    </div>
                                    <div class="col-9">
                                        <input type="text" id="billing_landmark" class="form-control" aria-describedby="priceHelpInline" name="billing_landmark" value="{{old('billing_landmark')}}">
                                        @error('billing_landmark') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    
                                </div>                                
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">City</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="billing_city" class="form-control" aria-describedby="priceHelpInline" name="billing_city" value="{{old('billing_city')}}">
                                        @error('billing_city') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="col-3 text-end">
                                        <label for="" class="col-form-label">State</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="billing_state" class="form-control" aria-describedby="priceHelpInline" name="billing_state" value="{{old('billing_state')}}">
                                        @error('billing_state') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>                           
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Country</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="billing_country" class="form-control" aria-describedby="priceHelpInline" name="billing_country" value="{{old('billing_country')}}">
                                        @error('billing_country') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="col-3 text-end">
                                        <label for="" class="col-form-label">Pincode</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="billing_pin" class="form-control" aria-describedby="priceHelpInline" name="billing_pin" value="{{old('billing_pin')}}" onkeypress="validateNum(event)">
                                        @error('billing_pin') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </content>
                        </div>
                        <div class="admin__content">
                            <aside>
                                
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <input type="hidden" name="is_billing_shipping_same" id="checkSameBillingHidden" value="0">
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" name="is_billing_shipping_same" id="checkSameBilling">
                                            <label class="form-check-label" for="checkSameBilling">
                                              Same as Billing Address
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </content>
                        </div>
                        <div class="admin__content">
                            <aside>
                                <nav>Shipping Address</nav>
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Address</label>
                                    </div>
                                    <div class="col-9">
                                        <input type="text" id="shipping_addr" class="form-control" aria-describedby="priceHelpInline" name="shipping_address" value="{{old('shipping_address')}}">
                                        @error('shipping_address') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Landmark</label>
                                    </div>
                                    <div class="col-9">
                                        <input type="text" id="shipping_landmark" class="form-control" aria-describedby="priceHelpInline" name="shipping_landmark" value="{{old('shipping_landmark')}}">
                                        @error('shipping_landmark') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">City</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="shipping_city" class="form-control" aria-describedby="priceHelpInline" name="shipping_city" value="{{old('shipping_city')}}">
                                        @error('shipping_city') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="col-3 text-end">
                                        <label for="" class="col-form-label">State</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="shipping_state" class="form-control" aria-describedby="priceHelpInline" name="shipping_state" value="{{old('shipping_state')}}">
                                        @error('shipping_state') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>                          
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Country</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="shipping_country" class="form-control" aria-describedby="priceHelpInline" name="shipping_country" value="{{old('shipping_country')}}">
                                        @error('shipping_country') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="col-3 text-end">
                                        <label for="" class="col-form-label">Pincode</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="shipping_pin" class="form-control" aria-describedby="priceHelpInline" name="shipping_pin" value="{{old('shipping_pin')}}" onkeypress="validateNum(event)">
                                        @error('shipping_pin') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </content>
                        </div>
                    </div>
                </div>

                

            </div>
            <div class="col-sm-3">
                <div class="card shadow-sm">
                <div class="card-header">
                    Create Supplier
                </div>
                <div class="card-body text-end">
                    <a href="{{ route('admin.user.index', 'supplier') }}" class="btn btn-sm btn-danger">Back</a>
                    <button type="submit" id="submitBtn" class="btn btn-sm btn-success">Add </button>
                </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-header">
                        GST certificate image
                    </div>
                    <div class="card-body">
                        <div class="w-100 product__thumb">
                        <label for="thumbnail"><img id="output" src="{{ asset('admin/images/placeholder-image.jpg') }}"/></label>
                        @error('gst_file') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <input type="hidden" name="is_gst_file_uploaded" value="0" id="is_gst_file_uploaded">
                        <input type="file" id="thumbnail" accept="image/*" name="gst_file" onchange="loadFile(event)" class="d-none">
                        <script>
                        var loadFile = function(event) {
                            var output = document.getElementById('output');
                            output.src = URL.createObjectURL(event.target.files[0]);
                            output.onload = function() {
                            URL.revokeObjectURL(output.src) // free memory
                            }

                            $('#is_gst_file_uploaded').val(1);
                        };
                        </script>
                    </div>
                </div>

            </div>
        </div>
    </form>
</section>
@endsection

@section('script')
<script>
    $(document).ready(function(){
        var contactWhatsapp = $('#whatsapp_no').val().length;

        if(contactWhatsapp >= 10){
            $('#submitBtn').prop('disabled',false);
            $('#checkWhatsappSame').attr('disabled',false);
        } else {
            $('#submitBtn').prop('disabled',true);
            $('#checkWhatsappSame').attr('disabled',true);
        }

        $("#myForm").submit(function() {
            // $('input').attr('disabled', 'disabled');
            $('#submitBtn').attr('disabled', 'disabled');
            return true;
        });
    })  
    
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

    $('#mobile').on('keyup', function(){
        var contactLength = $('#mobile').val().length;
        if(contactLength >= 10){
            $('#checkWhatsappSame').attr('disabled',false);
        }else{
            $('#checkWhatsappSame').attr('disabled',true);
        }
    })

    $('#whatsapp_no').on('keyup', function(){        
        var contactWhatsapp = $('#whatsapp_no').val().length;        
        if(contactWhatsapp >= 10){
            $('#submitBtn').prop('disabled',false);
        }else{
            $('#submitBtn').prop('disabled',true);
        }
    })

    $("input:checkbox#checkSameBilling").change(function() {
        var ischecked= $(this).is(':checked');
        var checkSameBillingHidden = $('#checkSameBillingHidden').val();

        var billing_addr = $('#billing_addr').val();
        var billing_landmark = $('#billing_landmark').val();
        var billing_city = $('#billing_city').val();
        var billing_state = $('#billing_state').val();
        var billing_country = $('#billing_country').val();
        var billing_pin = $('#billing_pin').val();

        if(ischecked){
            $('#shipping_addr').val(billing_addr);
            $('#shipping_landmark').val(billing_landmark);
            $('#shipping_city').val(billing_city);
            $('#shipping_state').val(billing_state);
            $('#shipping_country').val(billing_country);
            $('#shipping_pin').val(billing_pin);
            $('#checkSameBillingHidden').val(1);
        }else{
            $('#shipping_addr').val('');
            $('#shipping_landmark').val('');
            $('#shipping_city').val('');
            $('#shipping_state').val('');
            $('#shipping_country').val('');
            $('#shipping_pin').val('');
            $('#checkSameBillingHidden').val(0);
        }

       
    }); 

    $("input:checkbox#checkWhatsappSame").change(function() {
        var ischecked= $(this).is(':checked');
        var mobile = $('#mobile').val();
        var whatsapp_no = $('#whatsapp_no').val();  
        var checkWhatsappHidden = $('#checkWhatsappHidden').val();      

        if(ischecked){
            $('#whatsapp_no').val(mobile);  
            $('#whatsapp').prop('readonly', true);    
            $('#checkWhatsappHidden').val(1);       
            $('#submitBtn').prop('disabled',false); 
        }else{
            $('#whatsapp_no').val('');     
            $('#whatsapp').prop('readonly', false);   
            $('#checkWhatsappHidden').val(0);      
            $('#submitBtn').prop('disabled',true);     
        }       
    });
</script>
@endsection
